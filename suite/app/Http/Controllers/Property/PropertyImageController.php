<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Modules\Property\Models\Property;
use App\Modules\Property\Models\PropertyImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PropertyImageController extends Controller
{
    public function store(Request $request, Property $property)
    {
        $request->validate([
            'photos'   => 'required|array|min:1',
            'photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        foreach ($request->file('photos') as $photo) {
            $property->images()->create([
                'path' => $photo->store('properties', 'public'),
            ]);
        }

        return back()->with('success', 'Photos added.');
    }

    public function destroy(Property $property, PropertyImage $image)
    {
        abort_unless($image->property_id === $property->id, 404);

        Storage::disk('public')->delete($image->path);
        $image->delete();

        return back()->with('success', 'Photo removed.');
    }
}
