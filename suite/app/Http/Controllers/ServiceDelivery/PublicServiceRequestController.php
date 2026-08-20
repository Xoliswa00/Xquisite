<?php

namespace App\Http\Controllers\ServiceDelivery;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\ServiceDelivery\Models\ServiceRequest;
use Illuminate\Http\Request;

class PublicServiceRequestController extends Controller
{
    public function show()
    {
        return view('public.service-requests.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:150',
            'email'        => 'required|email|max:150',
            'phone'        => 'nullable|string|max:30',
            'company'      => 'nullable|string|max:150',
            'category'     => 'required|in:software_solutions,business_automation,data_intelligence,digital_solutions,ongoing_support,other',
            'description'  => 'required|string|max:3000',
            'budget_range' => 'nullable|string|max:60',
            'timeline'     => 'nullable|string|max:60',
            // Honeypot — real visitors never see or fill this field.
            'website'      => 'prohibited',
        ]);

        $tenant = Tenant::where('slug', config('service_delivery.public_request_tenant_slug'))->first();
        abort_unless($tenant, 404);

        unset($validated['website']);

        ServiceRequest::create(array_merge($validated, [
            'tenant_id'  => $tenant->id,
            'ip_address' => $request->ip(),
        ]));

        return redirect()->route('request-service.show')->with('success', "Thanks — we've received your request and will be in touch shortly.");
    }
}
