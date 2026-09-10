<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateServicePhotoDerivatives;
use App\Modules\Booking\Models\Service;
use App\Modules\Booking\Models\ServicePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ServicePhotoController extends Controller
{
    /** iPhone HEIC is the common rejection — say so in plain language. */
    private const UPLOAD_MESSAGES = [
        'photos.*.image' => 'Each file must be an image.',
        'photos.*.mimes' => 'Photos must be JPG, PNG or WebP. iPhone HEIC photos are not supported yet. In your Camera settings choose "Most Compatible", or share the photo first to convert it to JPG.',
        'photos.*.max'   => 'Each photo must be 4MB or smaller.',
        'photos.max'     => 'A service can have at most :max photos.',
    ];

    /** Attach one or more photos to a service, capped at Service::MAX_PHOTOS. */
    public function store(Request $request, Service $service)
    {
        $this->authorizeService($service);

        $request->validate([
            'photos'   => ['required', 'array', 'min:1', 'max:' . Service::MAX_PHOTOS],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], self::UPLOAD_MESSAGES);

        $incoming    = array_values($request->file('photos'));
        $dispatchIds = [];

        // Lock the service's photo rows inside the transaction so two concurrent
        // uploads (or an upload racing a cover change) can't exceed the cap or
        // both claim the cover.
        $result = DB::transaction(function () use ($service, $incoming, &$dispatchIds) {
            $existing  = $service->photos()->lockForUpdate()->get(['id', 'sort_order', 'is_primary']);
            $remaining = Service::MAX_PHOTOS - $existing->count();

            if ($remaining <= 0) {
                return ['added' => 0, 'skipped' => count($incoming)];
            }

            $files      = array_slice($incoming, 0, $remaining);
            $order      = (int) $existing->max('sort_order');
            $needsCover = ! $existing->contains('is_primary', true);

            foreach ($files as $i => $file) {
                $photo = $service->photos()->create([
                    'tenant_id'  => $service->tenant_id,
                    'path'       => $file->store("services/{$service->id}", 'public'),
                    'disk'       => 'public',
                    'sort_order' => ++$order,
                    'is_primary' => $needsCover && $i === 0,
                ]);
                $dispatchIds[] = $photo->id;
            }

            return ['added' => count($files), 'skipped' => count($incoming) - count($files)];
        });

        // Queue derivative jobs only after the rows are committed.
        foreach ($dispatchIds as $id) {
            GenerateServicePhotoDerivatives::dispatch($id);
        }

        if ($result['added'] === 0) {
            return back()->withErrors([
                'photos' => 'This service already has the maximum of ' . Service::MAX_PHOTOS . ' photos.',
            ]);
        }

        $message = 'Photos added.' . ($result['skipped'] > 0
            ? " {$result['skipped']} not uploaded — limit is " . Service::MAX_PHOTOS . '.'
            : '');

        return back()->with('success', $message);
    }

    /** Promote a photo to cover. Demote-then-promote under a row lock — never a window with two covers. */
    public function setPrimary(Service $service, ServicePhoto $photo)
    {
        $this->authorizeService($service);
        abort_unless((int) $photo->service_id === (int) $service->id, 404);

        if ($photo->isHidden()) {
            return back()->withErrors(['photo' => 'A hidden photo cannot be the cover. Un-hide it first.']);
        }

        DB::transaction(function () use ($service, $photo) {
            $service->photos()->lockForUpdate()->get(['id']);
            $service->photos()->where('id', '!=', $photo->id)->where('is_primary', true)
                ->update(['is_primary' => false]);
            $photo->update(['is_primary' => true]);
        });

        return back()->with('success', 'Cover photo updated.');
    }

    /** Delete a photo and its derivatives; the model promotes the next cover if needed. */
    public function destroy(Service $service, ServicePhoto $photo)
    {
        $this->authorizeService($service);
        abort_unless((int) $photo->service_id === (int) $service->id, 404);

        $paths = $photo->storagePaths();

        DB::transaction(function () use ($service, $photo) {
            $service->photos()->lockForUpdate()->get(['id']);
            $photo->delete(); // model hook promotes the next visible cover, in-transaction
        });

        // Remove the files only once the row delete is committed.
        Storage::disk($photo->diskName())->delete($paths);

        return back()->with('success', 'Photo removed.');
    }

    private function authorizeService(Service $service): void
    {
        abort_unless((int) $service->tenant_id === (int) auth()->user()->tenant_id, 404);
    }
}
