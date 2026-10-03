<?php

namespace App\Http\Controllers;

use App\Support\PrivateFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Streams a sensitive upload. The route carries the `signed` middleware, so
 * reaching this method means the URL was minted by PrivateFile::url() while
 * rendering a page the viewer was authorised to see. See PrivateFile.
 */
class PrivateFileController extends Controller
{
    /** Only these render inline; anything else downloads, so an uploaded HTML/SVG can never run in our origin. */
    private const INLINE_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'application/pdf'];

    public function show(Request $request, string $kind, int $id)
    {
        abort_unless(isset(PrivateFile::KINDS[$kind]), 404);
        [$model, $pathAttr, $nameAttr] = PrivateFile::KINDS[$kind];

        // The signature is the authorisation, so the tenant scope comes off
        // (it would wrongly hide the file from a contractor or customer guard
        // with no tenant context). Soft-deleted records stay hidden though:
        // a proof for a deleted booking shouldn't outlive the booking.
        $record = $model::withoutGlobalScopes()->findOrFail($id);
        abort_if(method_exists($record, 'trashed') && $record->trashed(), 404);

        $path = $record->{$pathAttr};
        $disk = PrivateFile::diskFor($path);
        abort_unless($disk, 404);

        $mime = Storage::disk($disk)->mimeType($path) ?: 'application/octet-stream';
        // Symfony rejects slashes in a disposition file name (and % in the ASCII fallback).
        $name = str_replace(['/', '\\'], '_', ($nameAttr ? $record->{$nameAttr} : null) ?: basename($path));

        // BinaryFileResponse (not a stream) so the web server gets Range
        // requests for PDFs and a Last-Modified it can answer with 304s.
        $response = new BinaryFileResponse(Storage::disk($disk)->path($path), 200, [
            'Content-Type'           => $mime,
            'X-Content-Type-Options' => 'nosniff',
            'X-Robots-Tag'           => 'noindex',
        ], public: false, autoLastModified: true);

        $response->setPrivate();
        $response->setMaxAge(PrivateFile::LINK_BUCKET_MINUTES * 60);
        $response->setContentDisposition(
            in_array($mime, self::INLINE_TYPES, true) ? ResponseHeaderBag::DISPOSITION_INLINE : ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $name,
            preg_replace('/[^\x20-\x7e]|%/', '_', $name) // ASCII fallback for non-latin file names
        );
        $response->isNotModified($request);

        return $response;
    }
}
