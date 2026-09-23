<?php

namespace App\Http\Controllers;

use App\Models\FoundingTwentyApplication;
use App\Models\Tenant;
use App\Rules\SouthAfricanPhoneNumber;
use App\Services\FoundingTwentyScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FoundingTwentyController extends Controller
{
    /** The nine "how often have you experienced…" statements. Shared with the view so error messages can quote them. */
    public const PAIN_LABELS = [
        'pain_forgotten_appointments' => 'Clients forgetting appointments',
        'pain_late_cancellations' => 'Clients cancelling at the last minute',
        'pain_no_shows' => 'Clients not showing up at all',
        'pain_double_bookings' => 'Double bookings or scheduling conflicts',
        'pain_booking_enquiry_time' => 'Spending significant time responding to booking enquiries',
        'pain_staff_availability' => 'Struggling to know which staff member is available',
        'pain_tracking_balances' => 'Difficulty tracking what customers owe you',
        'pain_revenue_visibility' => 'Difficulty knowing how much revenue your business generated',
        'pain_customer_data_organisation' => 'Difficulty keeping customer information organised',
    ];

    public function show(Request $request)
    {
        $source = $request->query('src');
        $campaignId = filter_var($request->query('campaign'), FILTER_VALIDATE_INT);

        $referrerId = filter_var($request->query('ref'), FILTER_VALIDATE_INT);
        $referredByTenantId = $referrerId && Tenant::where('id', $referrerId)->where('is_active', true)->exists()
            ? $referrerId
            : null;

        return view('founding-twenty.show', compact('source', 'campaignId', 'referredByTenantId'));
    }

    public function store(Request $request, FoundingTwentyScoringService $scoring)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'email' => 'required_if:preferred_contact_method,email|nullable|email|max:255',
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

            'wants_founding_twenty' => 'nullable|boolean',
            'willing_to_give_feedback' => 'nullable|boolean',
            'privacy_consent' => 'required|accepted',

            'source' => 'nullable|string|max:100',
            'outreach_campaign_id' => 'nullable|exists:outreach_campaigns,id',
            'referred_by_tenant_id' => ['nullable', Rule::exists('tenants', 'id')->where('is_active', true)],
        ], $this->validationMessages());

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

    /**
     * Messages written for the person filling the form in, not for a developer.
     * The default "The pain forgotten appointments field is required." tells a
     * business owner nothing about which question they skipped.
     */
    private function validationMessages(): array
    {
        $messages = [
            'business_type.required' => 'Please choose the type of business you run.',
            'business_type.in' => 'Please choose the type of business you run.',
            'business_name.required' => 'Please tell us your business name.',
            'owner_name.required' => 'Please tell us your name.',
            'phone.required' => 'Please add your phone number so we can reach you.',
            'email.required_if' => 'Please add your email address, since you chose email as your preferred contact method.',
            'email.email' => 'That email address doesn\'t look right. Please check it.',
            'privacy_consent.required' => 'Please tick the privacy box at the end to confirm you\'re happy for us to use your answers.',
            'privacy_consent.accepted' => 'Please tick the privacy box at the end to confirm you\'re happy for us to use your answers.',
        ];

        foreach (self::PAIN_LABELS as $field => $label) {
            $messages["{$field}.required"] = "Please rate \"{$label}\" from 1 to 5 (Section 3).";
            $messages["{$field}.integer"] = "Please rate \"{$label}\" from 1 to 5 (Section 3).";
            $messages["{$field}.min"] = "Please rate \"{$label}\" from 1 to 5 (Section 3).";
            $messages["{$field}.max"] = "Please rate \"{$label}\" from 1 to 5 (Section 3).";
        }

        return $messages;
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
            // extensions: (filename-based), not mimes: (content-sniffed) — HEIC/HEIF from
            // iPhone screenshots (the common case for a SA banking-app payment proof)
            // isn't reliably detected by fileinfo and mimes: silently rejects valid files.
            'proof_of_payment' => ['required', 'file', 'extensions:jpg,jpeg,png,heic,heif,webp,pdf', 'max:15360'],
        ], [
            'proof_of_payment.extensions' => 'That file type isn\'t supported — please upload a JPG, PNG, HEIC or PDF.',
            'proof_of_payment.max' => 'That file is too large — please keep it under 15MB.',
        ]);

        if ($foundingTwenty->deposit_pop_path) {
            Storage::disk('private')->delete($foundingTwenty->deposit_pop_path);
        }

        $file = $request->file('proof_of_payment');
        // storeAs with the client's own extension — store()'s auto-naming guesses the
        // extension from content sniffing, which is exactly what's unreliable for HEIC.
        $filename = Str::random(40) . '.' . strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs('founding-twenty-deposits', $filename, 'private');

        $foundingTwenty->update([
            'deposit_pop_path' => $path,
            'deposit_submitted_at' => now(),
        ]);

        return redirect()->route('founding-twenty.reserve', [$foundingTwenty, $token])
            ->with('success', "Thanks — we've received your proof of payment and will confirm your spot shortly.");
    }
}
