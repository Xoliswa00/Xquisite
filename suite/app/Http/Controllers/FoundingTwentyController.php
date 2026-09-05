<?php

namespace App\Http\Controllers;

use App\Models\FoundingTwentyApplication;
use App\Rules\SouthAfricanPhoneNumber;
use App\Services\FoundingTwentyScoringService;
use Illuminate\Http\Request;

class FoundingTwentyController extends Controller
{
    public function show(Request $request)
    {
        $source = $request->query('src');

        return view('founding-twenty.show', compact('source'));
    }

    public function store(Request $request, FoundingTwentyScoringService $scoring)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => ['required', new SouthAfricanPhoneNumber],
            'business_type' => 'required|in:salon,beauty,wellness,fitness,service,other',
            'business_type_other' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'preferred_contact_method' => 'required|in:whatsapp,call,email',
            'best_contact_time' => 'nullable|string|max:255',

            'years_operating' => 'nullable|in:<1,1-3,3-5,5+',
            'staff_count' => 'nullable|in:1,2-5,6-10,10+',
            'locations_count' => 'nullable|integer|min:1|max:999',
            'monthly_customers' => 'nullable|in:0-50,51-150,151-300,300+',
            'monthly_appointments' => 'nullable|in:0-50,51-150,151-300,300+',

            'booking_methods' => 'nullable|array',
            'booking_methods.*' => 'string',
            'appointment_management_methods' => 'nullable|array',
            'appointment_management_methods.*' => 'string',
            'customer_data_methods' => 'nullable|array',
            'customer_data_methods.*' => 'string',
            'payment_tracking_methods' => 'nullable|array',
            'payment_tracking_methods.*' => 'string',
            'balance_tracking_methods' => 'nullable|array',
            'balance_tracking_methods.*' => 'string',
            'card_payment_device' => 'nullable|string|max:255',

            'pain_forgotten_appointments' => 'required|integer|min:1|max:5',
            'pain_late_cancellations' => 'required|integer|min:1|max:5',
            'pain_no_shows' => 'required|integer|min:1|max:5',
            'pain_double_bookings' => 'required|integer|min:1|max:5',
            'pain_booking_enquiry_time' => 'required|integer|min:1|max:5',
            'pain_staff_availability' => 'required|integer|min:1|max:5',
            'pain_tracking_balances' => 'required|integer|min:1|max:5',
            'pain_revenue_visibility' => 'required|integer|min:1|max:5',
            'pain_customer_data_organisation' => 'required|integer|min:1|max:5',

            'no_shows_per_month' => 'nullable|in:0,1-2,3-5,6-10,10+',
            'avg_appointment_value' => 'nullable|in:0-100,101-250,251-500,501-1000,1000+',

            'hours_booking_admin' => 'nullable|in:<1,1-3,3-5,5-10,10+',
            'hours_availability_messages' => 'nullable|in:<1,1-3,3-5,5-10,10+',
            'hours_manual_reminders' => 'nullable|in:<1,1-3,3-5,5-10,10+',

            'adoption_barriers' => 'nullable|array',
            'adoption_barriers.*' => 'string',
            'adoption_barrier_other' => 'nullable|string|max:255',
            'past_solution_frustration' => 'nullable|string|max:2000',

            'priority_features' => 'nullable|array|max:5',
            'priority_features.*' => 'string',
            'top_priority_feature' => 'nullable|string|max:255',
            'automation_wishlist' => 'nullable|string|max:2000',

            'value_rating' => 'required|integer|min:1|max:5',
            'value_open_text' => 'nullable|string|max:2000',

            'continuation_likelihood' => 'required|in:very_likely,likely,unsure,unlikely,very_unlikely',
            'continuation_driver' => 'nullable|string|max:2000',
            'churn_driver' => 'nullable|string|max:2000',

            'wants_founding_twenty' => 'nullable|boolean',
            'willing_to_give_feedback' => 'nullable|boolean',
            'privacy_consent' => 'required|accepted',

            'source' => 'nullable|string|max:100',
        ]);

        $application = FoundingTwentyApplication::create([
            ...$validated,
            'wants_founding_twenty' => $request->boolean('wants_founding_twenty', true),
            'willing_to_give_feedback' => $request->boolean('willing_to_give_feedback'),
            'ip_address' => $request->ip(),
            'privacy_consented_at' => now(),
        ]);

        $result = $scoring->score($application);
        $application->update($result);

        return redirect()->route('founding-twenty.thanks');
    }

    public function thanks()
    {
        return view('founding-twenty.thanks');
    }

    public function reserve(FoundingTwentyApplication $foundingTwenty, string $token)
    {
        abort_unless(hash_equals($foundingTwenty->reservationToken(), $token), 403, 'Invalid or expired link.');
        abort_unless($foundingTwenty->status === 'selected', 404);

        return view('founding-twenty.reserve', ['application' => $foundingTwenty, 'token' => $token]);
    }

    public function reserveStore(Request $request, FoundingTwentyApplication $foundingTwenty, string $token)
    {
        abort_unless(hash_equals($foundingTwenty->reservationToken(), $token), 403, 'Invalid or expired link.');
        abort_unless($foundingTwenty->status === 'selected', 404);

        $validated = $request->validate([
            'proof_of_payment' => 'required|file|mimes:jpg,jpeg,png,heic,heif,webp,pdf|max:15360',
        ], [
            'proof_of_payment.mimes' => 'That file type isn\'t supported — please upload a JPG, PNG, HEIC or PDF.',
            'proof_of_payment.max' => 'That file is too large — please keep it under 15MB.',
        ]);

        $foundingTwenty->update([
            'deposit_pop_path' => $request->file('proof_of_payment')->store('founding-twenty-deposits', 'private'),
            'deposit_submitted_at' => now(),
        ]);

        return redirect()->route('founding-twenty.reserve', [$foundingTwenty, $token])
            ->with('success', "Thanks — we've received your proof of payment and will confirm your spot shortly.");
    }
}
