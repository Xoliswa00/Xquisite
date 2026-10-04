<?php

namespace App\Http\Controllers;

use App\Support\PrivateFile;
use Illuminate\Support\Facades\Storage;

/**
 * Streams a sensitive upload. The route carries the `signed` middleware, so
 * reaching this method means the URL was minted by PrivateFile::url() while
 * rendering a page the viewer was authorised to see. See PrivateFile.
 */
class PrivateFileController extends Controller
{
    /** Only these render inline; anything else downloads, so an uploaded HTML/SVG can never run in our origin. */
    private const INLINE_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'application/pdf'];

    public function show(string $kind, int $id)
    {
        abort_unless(isset(PrivateFile::KINDS[$kind]), 404);
        [$model, $pathAttr, $nameAttr] = PrivateFile::KINDS[$kind];

        // The signature is the authorisation; tenant scoping would wrongly hide
        // the file from a contractor or customer guard with no tenant context.
        $record = $model::withoutGlobalScopes()->findOrFail($id);
        $path   = $record->{$pathAttr};
        $disk   = PrivateFile::diskFor($path);
        abort_unless($disk, 404);

        $mime = Storage::disk($disk)->mimeType($path) ?: 'application/octet-stream';
        $name = ($nameAttr ? $record->{$nameAttr} : null) ?: basename($path);
        $disposition = in_array($mime, self::INLINE_TYPES, true) ? 'inline' : 'attachment';

        return Storage::disk($disk)->response($path, $name, [
            'Content-Type'           => $mime,
            'Cache-Control'          => 'private, max-age=600',
            'X-Content-Type-Options' => 'nosniff',
            'X-Robots-Tag'           => 'noindex',
        ], $disposition);
    }
}
