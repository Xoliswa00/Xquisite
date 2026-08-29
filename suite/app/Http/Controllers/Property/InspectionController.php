<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Modules\Property\Models\Inspection;
use App\Modules\Property\Models\InspectionSection;
use App\Modules\Property\Models\Lease;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InspectionController extends Controller
{
    /** Resolve (creating if needed) the move-in/move-out checklist for a lease, then hand off to the canonical show URL. */
    public function create(Lease $lease, string $type)
    {
        abort_unless(in_array($type, ['move_in', 'move_out']), 404);

        $inspection = $lease->inspections()->firstOrCreate(['type' => $type]);

        if ($inspection->sections()->count() === 0) {
            $inspection->sections()->createMany($this->standardSections($lease));
        }

        return redirect()->route('leases.inspections.show', [$lease, $inspection]);
    }

    /** Save whatever sections were filled in on this submit — a checklist can be completed across more than one visit. */
    public function store(Request $request, Lease $lease, Inspection $inspection)
    {
        abort_unless($inspection->lease_id === $lease->id, 404);

        $request->validate([
            'sections'                 => 'required|array',
            'sections.*.condition'     => 'nullable|in:good,fair,poor,damaged',
            'sections.*.notes'         => 'nullable|string|max:500',
            'sections.*.photo'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        foreach ($request->input('sections', []) as $sectionId => $data) {
            $section = InspectionSection::find($sectionId);
            if (!$section || $section->inspection_id !== $inspection->id) {
                continue;
            }

            $update = [
                'condition' => $data['condition'] ?? $section->condition,
                'notes'     => $data['notes'] ?? $section->notes,
            ];

            if ($request->hasFile("sections.{$sectionId}.photo")) {
                if ($section->photo_path) {
                    Storage::disk('public')->delete($section->photo_path);
                }
                $update['photo_path'] = $request->file("sections.{$sectionId}.photo")->store('inspections', 'public');
            }

            $section->update($update);
        }

        $inspection->refresh()->load('sections');

        if ($inspection->isComplete() && $inspection->status !== 'completed') {
            $inspection->update([
                'status'       => 'completed',
                'completed_by' => auth()->id(),
                'completed_at' => now(),
            ]);
        }

        return redirect()->route('leases.inspections.show', [$lease, $inspection])
            ->with('success', $inspection->status === 'completed' ? 'Inspection completed.' : 'Progress saved.');
    }

    public function show(Lease $lease, Inspection $inspection)
    {
        abort_unless($inspection->lease_id === $lease->id, 404);
        $inspection->load('sections');

        return view('property.inspections.show', compact('lease', 'inspection'));
    }

    /** Standard room breakdown, using the unit's actual bed/bath count so a 3-bed unit gets 3 bedroom sections, not a generic template. */
    private function standardSections(Lease $lease): array
    {
        $bedrooms  = max(1, (int) ($lease->unit?->bedrooms ?? 1));
        $bathrooms = max(1, (int) ($lease->unit?->bathrooms ?? 1));

        $sections = ['Kitchen', 'Living Area'];

        for ($i = 1; $i <= $bedrooms; $i++) {
            $sections[] = $bedrooms > 1 ? "Bedroom {$i}" : 'Bedroom';
        }
        for ($i = 1; $i <= $bathrooms; $i++) {
            $sections[] = $bathrooms > 1 ? "Bathroom {$i}" : 'Bathroom';
        }

        $sections[] = 'Exterior / Entrance';
        $sections[] = 'Other';

        return array_map(fn ($name) => ['name' => $name], $sections);
    }
}
