<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FoundingTwentyApplication;
use App\Models\FoundingTwentyCheckin;
use App\Models\PromoCode;
use App\Models\Tenant;
use App\Notifications\FoundingTwentyApplicantMessage;
use App\Rules\SouthAfricanPhoneNumber;
use App\Services\AuditService;
use App\Services\FoundingTwentyMessages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class FoundingTwentyController extends Controller
{
    public function index()
    {
        // Only submitted applications are scored and ranked. Leads (details given, questionnaire
        // not finished) are listed separately so they can be followed up on, not lost.
        $applications = FoundingTwentyApplication::submitted()->latest('score')->latest()->get();
        $leads = FoundingTwentyApplication::leads()->latest()->get();

        $stats = [
            'total' => $applications->count(),
            'high' => $applications->where('tier', 'high')->count(),
            'good' => $applications->where('tier', 'good')->count(),
            'selected' => $applications->where('status', 'selected')->count(),
        ];

        return view('admin.founding-twenty.index', compact('applications', 'stats', 'leads'));
    }

    public function actionQueue()
    {
        $depositsAwaitingConfirmation = FoundingTwentyApplication::whereNotNull('deposit_submitted_at')
            ->whereNull('deposit_confirmed_at')
            ->orderBy('deposit_submitted_at')
            ->get();

        $incompleteCheckins = FoundingTwentyCheckin::whereNull('completed_at')
            ->with('application')
            ->orderBy('created_at')
            ->get();

        $checkinsOverdue = $incompleteCheckins->filter(fn ($c) => $c->created_at->diffInDays(now()) > 7);
        $checkinsAwaitingResponse = $incompleteCheckins->filter(fn ($c) => $c->created_at->diffInDays(now()) <= 7);

        $submitted = FoundingTwentyApplication::submitted();

        // Applicants nobody has acknowledged yet (no email on file, so nothing went out automatically).
        $toAcknowledge = (clone $submitted)->whereNull('received_notified_at')->where('status', 'pending')->orderBy('submitted_at')->get();

        // Decided, but the applicant has not been told. The most damaging silence.
        $toTellDecision = (clone $submitted)->whereIn('status', ['selected', 'waitlisted', 'rejected'])
            ->whereNull('decision_notified_at')->orderBy('reviewed_at')->get();

        // Waiting on a decision from us for longer than we promised.
        $overduePromise = (clone $submitted)->where('status', 'pending')
            ->where('submitted_at', '<', now()->subDays(config('founding_twenty.decision_within_days')))
            ->orderBy('submitted_at')->get();

        // Selected but gone quiet before paying the deposit.
        $reservationChase = (clone $submitted)->where('status', 'selected')->whereNull('deposit_submitted_at')
            ->where('decision_notified_at', '<', now()->subDays(config('founding_twenty.reservation_chase_days')))
            ->orderBy('decision_notified_at')->get();

        // Onboarded but no first win logged: the strongest early sign someone will not stay.
        $stuckOnboarding = FoundingTwentyApplication::whereNotNull('tenant_linked_at')->whereNull('first_value_milestone_at')
            ->where('tenant_linked_at', '<', now()->subDays(config('founding_twenty.activation_days')))
            ->whereNull('activation_nudge_sent_at')->orderBy('tenant_linked_at')->get();

        // Free period ending soon and they have not been told what happens next.
        $conversionDue = FoundingTwentyApplication::whereNotNull('tenant_linked_at')->where('status', '!=', 'converted')
            ->where('tenant_linked_at', '<=', now()->subDays(config('founding_twenty.conversion_notice_day')))
            ->whereNull('conversion_offer_sent_at')->orderBy('tenant_linked_at')->get();

        return view('admin.founding-twenty.action-queue', compact(
            'depositsAwaitingConfirmation', 'checkinsOverdue', 'checkinsAwaitingResponse',
            'toAcknowledge', 'toTellDecision', 'overduePromise', 'reservationChase', 'stuckOnboarding', 'conversionDue'
        ));
    }

    public function show(FoundingTwentyApplication $foundingTwenty)
    {
        $foundingTwenty->load(['reviewer', 'tenant', 'promoCodeRedemption.promoCode', 'checkins', 'outreachCampaign', 'referredByTenant']);

        $tenants = Tenant::orderBy('name')->get(['id', 'name']);

        return view('admin.founding-twenty.show', ['application' => $foundingTwenty, 'tenants' => $tenants]);
    }

    public function updateStatus(Request $request, FoundingTwentyApplication $foundingTwenty)
    {
        $request->validate([
            'status' => 'required|in:pending,reviewing,selected,waitlisted,rejected,converted',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $updates = [
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ];

        // First time an application is selected, issue its reservation deposit reference.
        if ($request->status === 'selected' && $foundingTwenty->deposit_amount === null) {
            $updates['deposit_amount'] = config('founding_twenty.deposit_amount');
            $updates['deposit_reference'] = 'F20-' . str_pad($foundingTwenty->id, 4, '0', STR_PAD_LEFT);
        }

        $foundingTwenty->update($updates);

        return back()->with('success', "Application marked as {$request->status}.");
    }

    public function confirmDeposit(FoundingTwentyApplication $foundingTwenty)
    {
        abort_unless($foundingTwenty->deposit_submitted_at !== null, 422, 'No proof of payment has been submitted yet.');

        $foundingTwenty->update(['deposit_confirmed_at' => now()]);

        return back()->with('success', 'Deposit confirmed — this business can now be onboarded.');
    }

    public function markDepositRefunded(FoundingTwentyApplication $foundingTwenty)
    {
        abort_unless($foundingTwenty->deposit_confirmed_at !== null, 422, 'Deposit has not been confirmed yet.');

        $foundingTwenty->update(['deposit_refunded_at' => now()]);

        return back()->with('success', 'Deposit marked as refunded.');
    }

    public function linkTenant(Request $request, FoundingTwentyApplication $foundingTwenty)
    {
        abort_if($foundingTwenty->tenant_id !== null, 422, 'This application is already linked to a tenant.');

        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
        ]);

        $foundingTwenty->update([...$validated, 'tenant_linked_at' => now()]);

        return back()->with('success', 'Tenant linked to this application.');
    }

    public function markMilestone(Request $request, FoundingTwentyApplication $foundingTwenty)
    {
        abort_if($foundingTwenty->first_value_milestone_at !== null, 422, 'A first-value milestone has already been logged for this application.');

        $validated = $request->validate([
            'first_value_milestone_note' => 'required|string|max:255',
        ]);

        $foundingTwenty->update([
            ...$validated,
            'first_value_milestone_at' => now(),
        ]);

        return back()->with('success', 'First-value milestone logged.');
    }

    public function issueCheckin(Request $request, FoundingTwentyApplication $foundingTwenty)
    {
        $request->validate([
            'checkin_type' => 'required|in:30_day,60_day,90_day',
        ]);

        abort_if(
            $foundingTwenty->checkins()->where('checkin_type', $request->checkin_type)->exists(),
            422,
            'A ' . str_replace('_', '-', $request->checkin_type) . ' check-in already exists for this application.'
        );

        $foundingTwenty->checkins()->create(['checkin_type' => $request->checkin_type]);

        return back()->with('success', 'Check-in link issued — copy it from the panel below.');
    }

    public function processReferralReward(Request $request, FoundingTwentyApplication $foundingTwenty)
    {
        abort_unless($foundingTwenty->referred_by_tenant_id !== null, 422, 'This application was not referred by anyone.');
        abort_unless($foundingTwenty->tenant_id !== null, 422, 'This application must be linked to a paying tenant first.');
        abort_if($foundingTwenty->referral_reward_processed_at !== null, 422, 'The referral reward has already been processed.');

        $referrer = Tenant::findOrFail($foundingTwenty->referred_by_tenant_id);
        $newTenant = Tenant::findOrFail($foundingTwenty->tenant_id);

        // Both-sides referral: the referrer gets a free month, the new business gets
        // a welcome discount — two canonical codes, reused across every referral.
        $rewardCode = PromoCode::firstOrCreate(
            ['code' => 'REFERRAL-REWARD'],
            ['type' => 'free_months', 'value' => 1, 'source' => 'referral_program', 'is_active' => true,
             'notes' => 'Auto-created — one free month for referring a paying business.']
        );
        $welcomeCode = PromoCode::firstOrCreate(
            ['code' => 'REFERRAL-WELCOME'],
            ['type' => 'percentage', 'value' => 50, 'source' => 'referral_program', 'is_active' => true,
             'notes' => 'Auto-created — 50% off for a new business that joined via referral.']
        );

        $rewardCode->redemptions()->create([
            'tenant_id' => $referrer->id,
            'discount_type' => $rewardCode->type,
            'discount_value' => $rewardCode->value,
            'financial_value' => $referrer->monthlyTotal(),
            'notes' => "Referral reward for referring {$newTenant->name}.",
            'redeemed_by' => $request->user()->id,
            'redeemed_at' => now(),
        ]);
        $rewardCode->increment('times_redeemed');

        $welcomeCode->redemptions()->create([
            'tenant_id' => $newTenant->id,
            'founding_twenty_application_id' => $foundingTwenty->id,
            'discount_type' => $welcomeCode->type,
            'discount_value' => $welcomeCode->value,
            'financial_value' => round($newTenant->monthlyTotal() * 0.5, 2),
            'notes' => "Welcome discount for joining via {$referrer->name}'s referral.",
            'redeemed_by' => $request->user()->id,
            'redeemed_at' => now(),
        ]);
        $welcomeCode->increment('times_redeemed');

        $foundingTwenty->update(['referral_reward_processed_at' => now()]);

        return back()->with('success', "Referral reward processed — {$referrer->name} got a free month, {$newTenant->name} got a welcome discount.");
    }

    /** Email the applicant one of the standard messages (when they gave an email), and record that they were told. */
    public function sendMessage(FoundingTwentyApplication $foundingTwenty, string $type)
    {
        $this->assertMessageType($foundingTwenty, $type);
        abort_unless($foundingTwenty->email, 422, 'This applicant has no email address. Send it on WhatsApp instead.');

        $message = FoundingTwentyMessages::for($type, $foundingTwenty);
        Notification::route('mail', $foundingTwenty->email)->notify(new FoundingTwentyApplicantMessage($message['subject'], $message['body']));
        $foundingTwenty->update([FoundingTwentyMessages::COLUMNS[$type] => now()]);

        return back()->with('success', 'Email sent to ' . $foundingTwenty->email . '.');
    }

    /** Record that we told them another way (WhatsApp or a call). */
    public function markMessaged(FoundingTwentyApplication $foundingTwenty, string $type)
    {
        $this->assertMessageType($foundingTwenty, $type);

        $foundingTwenty->update([FoundingTwentyMessages::COLUMNS[$type] => now()]);

        return back()->with('success', 'Marked as told.');
    }

    private function assertMessageType(FoundingTwentyApplication $a, string $type): void
    {
        abort_unless(in_array($type, FoundingTwentyMessages::TYPES, true), 404);
        abort_if($type === 'decision' && ! in_array($a->status, ['selected', 'waitlisted', 'rejected', 'converted'], true), 422, 'Set the decision first (selected, waitlisted or rejected).');
    }

    /** Add a business we approached directly, so who applies is not only who filled in a form. */
    public function create()
    {
        return view('admin.founding-twenty.create');
    }

    public function storeDirect(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'phone' => ['required', new SouthAfricanPhoneNumber],
            'email' => 'nullable|email|max:255',
            'business_type' => 'nullable|in:salon,beauty,wellness,fitness,other_service,other',
            'admin_notes' => 'nullable|string|max:2000',
            'consent_confirmed' => 'accepted',
        ], ['consent_confirmed.accepted' => 'Please confirm they agreed to us keeping their details.']);

        $last9 = substr(preg_replace('/\D/', '', $validated['phone']), -9);
        $existing = FoundingTwentyApplication::query()->get(['id', 'phone'])
            ->first(fn ($a) => substr(preg_replace('/\D/', '', (string) $a->phone), -9) === $last9);

        if ($existing) {
            return redirect()->route('admin.founding-twenty.show', $existing)->with('error', 'This number is already in the programme. Here is their application.');
        }

        $application = FoundingTwentyApplication::create([
            'business_name' => $validated['business_name'],
            'owner_name' => $validated['owner_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'business_type' => $validated['business_type'] ?? null,
            'admin_notes' => $validated['admin_notes'] ?? null,
            'preferred_contact_method' => 'whatsapp',
            'source' => 'direct',
            'privacy_consented_at' => now(),
            'submitted_at' => now(),
            'received_notified_at' => now(),
        ]);

        return redirect()->route('admin.founding-twenty.show', $application)->with('success', 'Added. Review them like any other application.');
    }

    /** Where the programme leaks: stage by stage, by source, by questionnaire section, against targets. */
    public function funnel()
    {
        $all = FoundingTwentyApplication::all();
        $submitted = $all->filter(fn ($a) => $a->isSubmitted());
        $selectedStatuses = ['selected', 'converted'];

        $stages = [
            'Gave their details' => $all->count(),
            'Finished the questionnaire' => $submitted->count(),
            'Selected' => $submitted->whereIn('status', $selectedStatuses)->count(),
            'Deposit confirmed' => $submitted->whereNotNull('deposit_confirmed_at')->count(),
            'Onboarded (tenant linked)' => $submitted->whereNotNull('tenant_linked_at')->count(),
            'First win logged' => $submitted->whereNotNull('first_value_milestone_at')->count(),
            'Converted to paying' => $submitted->where('status', 'converted')->count(),
        ];

        $bySource = $all->groupBy(fn ($a) => $a->source ?: 'unknown')->map(fn ($group) => [
            'started' => $group->count(),
            'finished' => $group->filter(fn ($a) => $a->isSubmitted())->count(),
            'selected' => $group->whereIn('status', $selectedStatuses)->count(),
            'converted' => $group->where('status', 'converted')->count(),
        ])->sortByDesc('started');

        $dropOff = $all->reject(fn ($a) => $a->isSubmitted())
            ->groupBy(fn ($a) => (int) $a->last_section_reached)
            ->map->count()->sortKeys();

        $decided = $submitted->filter(fn ($a) => $a->reviewed_at);
        $avgDaysToDecision = $decided->isEmpty() ? null : round($decided->avg(fn ($a) => $a->submitted_at->diffInHours($a->reviewed_at) / 24), 1);
        $overdue = $submitted->where('status', 'pending')
            ->filter(fn ($a) => $a->submitted_at->lt(now()->subDays(config('founding_twenty.decision_within_days'))))->count();

        $targets = config('founding_twenty.targets');
        $actuals = [
            'applications' => $submitted->count(),
            'selected' => $stages['Selected'],
            'activated' => $stages['First win logged'],
            'paying' => $stages['Converted to paying'],
        ];

        return view('admin.founding-twenty.funnel', compact('stages', 'bySource', 'dropOff', 'avgDaysToDecision', 'overdue', 'targets', 'actuals'));
    }

    public function downloadPop(FoundingTwentyApplication $foundingTwenty)
    {
        abort_unless($foundingTwenty->deposit_pop_path !== null, 404);

        AuditService::log(
            action: 'document.accessed',
            entityType: 'FoundingTwentyApplication',
            entityId: $foundingTwenty->id,
            meta: ['file' => 'deposit_pop', 'deposit_reference' => $foundingTwenty->deposit_reference],
        );

        return Storage::disk('private')->download(
            $foundingTwenty->deposit_pop_path,
            'deposit-pop-' . $foundingTwenty->deposit_reference . '.' . pathinfo($foundingTwenty->deposit_pop_path, PATHINFO_EXTENSION)
        );
    }
}
