<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\PlatformModule;
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

        $allModules = PlatformModule::ordered()->get()->keyBy('key');
        $tenant->load(['tenantModules', 'pendingModuleRequests']);

        return view('settings.modules', compact('tenant', 'allModules'));
    }

    public function request(Request $request, BillingBridge $billing)
    {
        $validKeys = PlatformModule::pluck('key')->implode(',');

        $request->validate([
            'module' => 'required|string|in:' . $validKeys,
            'type'   => 'nullable|string|in:activation,modification',
            'notes'  => 'nullable|string|max:1000',
        ]);

        $tenant = auth()->user()->tenant;

        if (! $tenant) {
            return redirect()->route('dashboard')->with('error', 'Your account is not attached to a tenant.');
        }

        $moduleKey    = $request->module;
        $platformMod  = PlatformModule::where('key', $moduleKey)->firstOrFail();
        $moduleName   = $platformMod->name;
        $type         = $request->input('type', 'activation');

        $requiresReview = $type === 'modification' || ! $platformMod->auto_activate;

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
            // Don't create a billing subscription during the free trial
            $billingSubscriptionId = null;
            if (! $tenant->isOnTrial()) {
                $billingSubscriptionId = $billing->createModuleSubscription($tenant, $moduleKey);
            }

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

        $requesters = $tenant->users()->whereHas('roles', fn ($q) => $q->whereIn('name', ['tenant-owner', 'manager']))->get();
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
