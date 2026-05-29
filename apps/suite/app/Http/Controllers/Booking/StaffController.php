<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Models\Staff;
use App\Modules\Booking\Models\Service;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = Staff::withCount('appointments')->orderBy('name');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $staff = $query->paginate(15)->withQueryString();

        return view('staff.index', compact('staff'));
    }

    public function create()
    {
        $services = Service::where('is_active', true)->orderBy('name')->get();

        return view('staff.create', compact('services'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:50',
            'role'        => 'nullable|string|max:100',
            'is_active'   => 'boolean',
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'exists:services,id',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $serviceIds = $data['service_ids'] ?? [];
        unset($data['service_ids']);

        $member = Staff::create($data);
        $member->services()->sync($serviceIds);

        return redirect()->route('staff.index')
            ->with('success', 'Staff member added.');
    }

    public function show(Staff $staff)
    {
        $staff->load([
            'services',
            'schedules',
            'blocks' => fn ($q) => $q->where('ends_at', '>', now())->orderBy('starts_at'),
        ]);

        $recentAppointments = $staff->appointments()
            ->with(['customer', 'service'])
            ->orderByDesc('scheduled_at')
            ->limit(10)
            ->get();

        return view('staff.show', compact('staff', 'recentAppointments'));
    }

    public function edit(Staff $staff)
    {
        $services = Service::where('is_active', true)->orderBy('name')->get();
        $staff->load('services');

        return view('staff.edit', compact('staff', 'services'));
    }

    public function update(Request $request, Staff $staff)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'nullable|email|max:255',
            'phone'         => 'nullable|string|max:50',
            'role'          => 'nullable|string|max:100',
            'is_active'     => 'boolean',
            'service_ids'   => 'nullable|array',
            'service_ids.*' => 'exists:services,id',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $serviceIds = $data['service_ids'] ?? [];
        unset($data['service_ids']);

        $staff->update($data);
        $staff->services()->sync($serviceIds);

        return redirect()->route('staff.show', $staff)
            ->with('success', 'Staff member updated.');
    }

    public function destroy(Staff $staff)
    {
        $staff->delete();

        return redirect()->route('staff.index')
            ->with('success', 'Staff member removed.');
    }
}
