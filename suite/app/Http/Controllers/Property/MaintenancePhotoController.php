<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Modules\Property\Models\MaintenancePhoto;
use App\Modules\Property\Models\MaintenanceRequest;
use Illuminate\Http\Request;
use App\Support\PrivateFile;

class MaintenancePhotoController extends Controller
{
    public function store(Request $request, MaintenanceRequest $maintenance)
    {
        $validated = $request->validate([
            'photos'   => 'required|array|min:1',
            'photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:4096',
            'caption'  => 'nullable|string|max:255',
        ]);

        foreach ($request->file('photos') as $photo) {
            $maintenance->photos()->create([
                'path'    => PrivateFile::store($photo, 'maintenance'),
                'caption' => $validated['caption'] ?? null,
            ]);
        }

        return back()->with('success', 'Photos added.');
    }

    public function destroy(MaintenanceRequest $maintenance, MaintenancePhoto $photo)
    {
        abort_unless($photo->maintenance_request_id === $maintenance->id, 404);

        PrivateFile::delete($photo->path);
        $photo->delete();

        return back()->with('success', 'Photo removed.');
    }
}
