<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\BillingBridge;
use App\Models\ModuleRequest;
use App\Notifications\ModuleRequestSubmitted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class ModuleController extends Controller
{
 public function index()
    {
        $tenant = auth()->user()->tenant;

        if (! $tenant) {
            return redirect()->route('dashboard')->with('error', 'Your account is not attached to a tenant.');
        }

        $allModules = config('modules');
        $tenant->load(['tenantModules', 'pendingModuleRequests']);

        return view('settings.modules', compact('tenant', 'allModules'));
    }

    public function request(Request $request, BillingBridge $billing)
    {
        $request->validate([
            'module' => 'required|string|in:' . implode(',', array_keys(config('modules'))),
            'type'   => 'nullable|string|in:activation,modification',
            'notes'  => 'nullable|string|max:1000',
        ]);

        $tenant = auth()->user()->tenant;

        if (! $tenant) {
            return redirect()->route('dashboard')->with('error', 'Your account is not attached to a tenant.');
        }

        $moduleKey  = $request->module;
        $moduleName = config("modules.{$moduleKey}.name");
        $type       = $request->input('type', 'activation');

        $requiresReview = $type === 'modification' || ! config("modules.{$moduleKey}.auto_activate", true);

        $moduleRequest = ModuleRequest::create([
            'tenant_id'    => $tenant->id,
            'user_id'      => auth()->id(),
            'module'       => $moduleKey,
            'type'         => $type,
            'status'       => $requiresReview ? 'pending' : 'approved',
            'notes'        => $request->notes,
            'requested_at' => now(),
        ]);

        if (! $requiresReview) {
            // Create billing subscription before activating
            $billingSubscriptionId = $billing->createModuleSubscription($tenant, $moduleKey);

            $moduleRequest->update([
                'reviewed_at' => now(),
                'reviewed_by' => auth()->id(),
            ]);

            $tenant->activateModule(
                module:                $moduleKey,
                activatedBy:           auth()->id(),
                billingSubscriptionId: $billingSubscriptionId,
            );
        }

        $requesters = $tenant->users()->whereIn('role', ['owner', 'admin'])->get();
        $requesters->push(auth()->user());
        $requesters->unique('id')->each(fn ($user) => $user->notify(new ModuleRequestSubmitted($moduleRequest)));

        $supportEmail = config('app.support_email') ?: config('mail.from.address');
        if ($supportEmail) {
            Notification::route('mail', $supportEmail)->notify(new ModuleRequestSubmitted($moduleRequest));
        }

        if ($requiresReview) {
            return back()->with('success', "Your request for {$moduleName} has been submitted and is pending review.");
        }

        return back()->with('success', "{$moduleName} has been activated for your account.");
    }
}
