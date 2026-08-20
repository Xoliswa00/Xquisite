<?php

namespace App\Http\Controllers\ServiceDelivery;

use App\Http\Controllers\Controller;
use App\Modules\ServiceDelivery\Models\ServiceRequest;
use Illuminate\Http\Request;

class ServiceRequestReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceRequest::latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', ['new', 'reviewed']);
        }

        $requests = $query->paginate(20)->withQueryString();

        return view('service-delivery.requests.index', compact('requests'));
    }

    public function show(ServiceRequest $serviceRequest)
    {
        $serviceRequest->load(['reviewer', 'convertedClient', 'convertedGig']);

        if ($serviceRequest->status === 'new') {
            $serviceRequest->update(['status' => 'reviewed', 'reviewed_at' => now(), 'reviewed_by' => auth()->id()]);
        }

        return view('service-delivery.requests.show', ['serviceRequest' => $serviceRequest]);
    }

    public function convert(ServiceRequest $serviceRequest)
    {
        abort_if($serviceRequest->status === 'converted', 422, 'Already converted.');

        $client = $serviceRequest->convert(auth()->id());

        return redirect()->route('gigs.index')
            ->with('success', $serviceRequest->converted_gig_id
                ? "Converted — {$client->name} added as a client with a new gig ready to scope."
                : "Converted — {$client->name} added as a client. Set up their service agreement from the Service Agreements page.");
    }

    public function dismiss(ServiceRequest $serviceRequest)
    {
        $serviceRequest->dismiss(auth()->id());

        return redirect()->route('service-requests.index')->with('success', 'Request dismissed.');
    }
}
