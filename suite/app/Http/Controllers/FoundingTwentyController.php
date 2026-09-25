<?php

namespace App\Http\Controllers;

use App\Models\FoundingTwentyApplication;
use App\Models\Tenant;
use App\Rules\SouthAfricanPhoneNumber;
use App\Notifications\FoundingTwentyApplicantMessage;
use App\Services\FoundingTwentyMessages;
use App\Services\FoundingTwentyScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
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

    /** Session key holding the id of the lead currently working through step 2. */
    private const LEAD_SESSION_KEY = 'founding_twenty_lead_id';

    /**
     * Step 1 of 2: who are we talking to. Short on purpose, because submitting it
     * saves them as a lead: even if they never finish the questionnaire, we know who
     * they are and how to reach them.
     */
    public function show(Request $request)
    {
        // Someone partway through goes straight back to where they were.
        if ($this->leadFromSession($request)) {
            return redirect()->route('founding-twenty.questions');
        }

        $source = $request->query('src');
        $campaignId = filter_var($request->query('campaign'), FILTER_VALIDATE_INT);

        $referrerId = filter_var($request->query('ref'), FILTER_VALIDATE_INT);
        $referredByTenantId = $referrerId && Tenant::where('id', $referrerId)->where('is_active', true)->exists()
            ? $referrerId
            : null;

        return view('founding-twenty.start', compact('source', 'campaignId', 'referredByTenantId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'owner_name' => 'required|string|max:255',
            'phone' => ['required', new SouthAfricanPhoneNumber],
            'preferred_contact_method' => 'required|in:whatsapp,call,email',
            'email' => 'required_if:preferred_contact_method,email|nullable|email|max:255',
            'best_contact_time' => 'nullable|string|max:255',
            'business_name' => 'required|string|max:255',
            'applicant_role' => 'required|in:owner,manager,staff,other',
            'why_founding_20' => 'nullable|string|max:1500',
            'heard_about_via' => 'nullable|in:tiktok,whatsapp,instagram_facebook,friend,website,other',
            'privacy_consent' => 'required|accepted',

            'source' => 'nullable|string|max:100',
            'outreach_campaign_id' => 'nullable|exists:outreach_campaigns,id',
            'referred_by_tenant_id' => ['nullable', Rule::exists('tenants', 'id')->where('is_active', true)],
        ], $this->validationMessages());

        // The same person coming back with the same number is one lead, not two.
        $last9 = substr(preg_replace('/\D/', '', $validated['phone']), -9);
        $existing = FoundingTwentyApplication::query()->get(['id', 'phone', 'submitted_at'])
            ->first(fn ($a) => substr(preg_replace('/\D/', '', (string) $a->phone), -9) === $last9);

        if ($existing?->isSubmitted()) {
            $message = "We already have an application from this number, so there's nothing more you need to do. We'll be in touch.";

            return back()->withInput()->withErrors(array_fill_keys(['phone'], $message));
        }

        $details = [
            'owner_name' => $validated['owner_name'],
            'phone' => $validated['phone'],
            'preferred_contact_method' => $validated['preferred_contact_method'],
            'email' => $validated['email'] ?? null,
            'best_contact_time' => $validated['best_contact_time'] ?? null,
            'business_name' => $validated['business_name'],
            'applicant_role' => $validated['applicant_role'],
            'why_founding_20' => $validated['why_founding_20'] ?? null,
            'heard_about_via' => $validated['heard_about_via'] ?? null,
            'ip_address' => $request->ip(),
            'privacy_consented_at' => now(),
        ];
        // Only overwrite where/how they arrived when this visit actually says so.
        $tracking = array_filter(
            Arr::only($validated, ['source', 'outreach_campaign_id', 'referred_by_tenant_id']),
            fn ($v) => $v !== null && $v !== ''
        );

        if ($existing) {
            $lead = FoundingTwentyApplication::findOrFail($existing->id);
            $lead->update($details + $tracking);
        } else {
            $lead = FoundingTwentyApplication::create($details + $tracking);
        }

        $request->session()->put(self::LEAD_SESSION_KEY, $lead->id);

        return redirect()->route('founding-twenty.questions');
    }

    /** Step 2 of 2: the questionnaire, for the lead in this session. */
    public function questions(Request $request)
    {
        $lead = $this->leadFromSession($request);

        if (! $lead) {
            return redirect()->route('founding-twenty.show');
        }

        return view('founding-twenty.questions', ['lead' => $lead]);
    }

    public function submit(Request $request, FoundingTwentyScoringService $scoring)
    {
        $lead = $this->leadFromSession($request);

        if (! $lead) {
            return redirect()->route('founding-twenty.show')
                ->withErrors(['session' => 'Your session timed out. Please enter your details again. If you use the same phone number, nothing is lost.']);
        }

        $validated = $request->validate([
            'business_type' => 'required|in:salon,beauty,wellness,fitness,service,other',
            'business_type_other' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',

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
        ], $this->validationMessages());

        $lead->update([
            ...$validated,
            'wants_founding_twenty' => $request->boolean('wants_founding_twenty', true),
            'willing_to_give_feedback' => $request->boolean('willing_to_give_feedback'),
            'submitted_at' => now(),
        ]);
        $lead->update($scoring->score($lead));

        $request->session()->forget(self::LEAD_SESSION_KEY);

        // Acknowledge straight away by email when we have one. Anyone without an email is
        // listed in the admin action queue so we can acknowledge them on WhatsApp.
        if ($lead->email) {
            $message = FoundingTwentyMessages::received($lead);
            Notification::route('mail', $lead->email)->notify(new FoundingTwentyApplicantMessage($message['subject'], $message['body']));
            $lead->update(['received_notified_at' => now()]);
        }

        return redirect()->route('founding-twenty.thanks')->with('applicant_first_name', $lead->firstName());
    }

    /** Beacon from the questionnaire: the furthest section this person has scrolled to. */
    public function progress(Request $request)
    {
        $lead = $this->leadFromSession($request);
        $section = (int) $request->input('section');

        if ($lead && ! $lead->isSubmitted() && $section >= 1 && $section <= 8 && $section > (int) $lead->last_section_reached) {
            $lead->update(['last_section_reached' => $section]);
        }

        return response()->noContent();
    }

    /** Pick an unfinished application back up, on any device, from a link. */
    public function resume(Request $request, FoundingTwentyApplication $foundingTwenty, string $token)
    {
        abort_unless(hash_equals($foundingTwenty->resumeToken(), $token), 403, 'Invalid or expired link.');

        if ($foundingTwenty->isSubmitted()) {
            return redirect()->route('founding-twenty.thanks')->with('applicant_first_name', $foundingTwenty->firstName());
        }

        $request->session()->put(self::LEAD_SESSION_KEY, $foundingTwenty->id);

        return redirect()->route('founding-twenty.questions');
    }

    /** "Not you?" on step 2: forget this session's lead and start again. */
    public function restart(Request $request)
    {
        $request->session()->forget(self::LEAD_SESSION_KEY);

        return redirect()->route('founding-twenty.show');
    }

    private function leadFromSession(Request $request): ?FoundingTwentyApplication
    {
        $id = $request->session()->get(self::LEAD_SESSION_KEY);
        if (! $id) {
            return null;
        }

        $lead = FoundingTwentyApplication::find($id);
        if (! $lead || $lead->isSubmitted()) {
            $request->session()->forget(self::LEAD_SESSION_KEY);

            return null;
        }

        return $lead;
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
            'applicant_role.required' => 'Please tell us your role in the business.',
            'applicant_role.in' => 'Please tell us your role in the business.',
            'phone.required' => 'Please add your phone number so we can reach you.',
            'email.required_if' => 'Please add your email address, since you chose email as your preferred contact method.',
            'email.email' => 'That email address doesn\'t look right. Please check it.',
            'privacy_consent.required' => 'Please tick the box to confirm you\'re happy for us to save your details and contact you.',
            'privacy_consent.accepted' => 'Please tick the box to confirm you\'re happy for us to save your details and contact you.',
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
            'proof_of_payment.extensions' => 'That file type isn\'t supported. Please upload a JPG, PNG, HEIC or PDF.',
            'proof_of_payment.max' => 'That file is too large. Please keep it under 15MB.',
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
            ->with('success', "Thank you. We've received your proof of payment and will confirm your spot shortly.");
    }
}
