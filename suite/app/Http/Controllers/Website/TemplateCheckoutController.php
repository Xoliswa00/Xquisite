<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Models\TemplatePurchase;
use App\Models\Tenant;
use App\Services\Payment\PayFastService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TemplateCheckoutController extends Controller
{
    public function checkout(Request $request, Template $template): View|RedirectResponse
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403);
        abort_unless($template->is_active && $template->is_visible, 404);
        abort_unless($template->isPremium(), 400, 'This template is free — activate it directly from the catalog.');

        if (! $tenant->canActivateRealTemplate()) {
            $tenant->update(['preferred_template_key' => $template->key]);

            return redirect()->route('website.templates.index')->with('info',
                "{$template->name} is saved as your pick — you can buy and activate it once you're off your free trial."
            );
        }

        // Already paid for this template — go straight to activation instead
        // of charging again.
        $paid = TemplatePurchase::where('tenant_id', $tenant->id)
            ->where('template_key', $template->key)
            ->where('status', 'paid')
            ->exists();

        if ($paid) {
            return $this->finishActivation($tenant, $template)
                ->with('success', "You already own {$template->name} — it's now active.");
        }

        $purchase = TemplatePurchase::create([
            'reference'    => 'TPLP-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
            'tenant_id'    => $tenant->id,
            'template_key' => $template->key,
            'amount'       => $template->price ?? 0,
            'status'       => 'pending',
        ]);

        try {
            $payfast     = new PayFastService();
            $paymentData = $payfast->buildTemplatePurchaseData($purchase, $tenant->slug, $request->user()->name, $request->user()->email);

            return view('shop.payfast-redirect', [
                'paymentUrl'  => $payfast->getPaymentUrl(),
                'paymentData' => $paymentData,
            ]);
        } catch (\Throwable $e) {
            Log::error('Template PayFast handoff failed', [
                'tenant'   => $tenant->id,
                'template' => $template->key,
                'error'    => $e->getMessage(),
            ]);

            return redirect()->route('website.templates.show', $template)
                ->with('error', 'We could not reach the payment gateway. Please try again.');
        }
    }

    public function payfastNotify(Request $request, string $tenantSlug): \Illuminate\Http\Response
    {
        $tenant  = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $payfast = new PayFastService();

        if (! $payfast->validateIpn($request, $tenantSlug)) {
            Log::warning('Template PayFast IPN rejected (signature/IP)', [
                'tenant'  => $tenant->id,
                'payment' => $request->input('m_payment_id'),
                'ip'      => $request->ip(),
            ]);

            abort(400, 'Invalid IPN signature');
        }

        $purchase = TemplatePurchase::where('tenant_id', $tenant->id)
            ->where('reference', $request->input('m_payment_id'))
            ->first();

        if (! $purchase) {
            Log::warning('Template PayFast IPN for unknown purchase', [
                'tenant'  => $tenant->id,
                'payment' => $request->input('m_payment_id'),
            ]);

            return response('OK', 200);
        }

        if ($purchase->isPaid()) {
            return response('OK', 200); // duplicate callback guard
        }

        $status = strtoupper((string) $request->input('payment_status'));

        if ($status === 'COMPLETE') {
            $purchase->update([
                'status'             => 'paid',
                'payfast_payment_id' => $request->input('pf_payment_id'),
                'paid_at'            => now(),
            ]);

            $template = Template::where('key', $purchase->template_key)->first();
            if ($template) {
                $this->finishActivation($tenant, $template);
            }

            Log::info('Template PayFast payment completed', ['purchase' => $purchase->reference]);
        } else {
            $purchase->update(['status' => 'failed']);

            Log::info('Template PayFast payment not completed', [
                'purchase' => $purchase->reference,
                'status'   => $status,
            ]);
        }

        return response('OK', 200);
    }

    public function payfastReturn(string $tenantSlug): RedirectResponse
    {
        return redirect()->route('website.templates.index')
            ->with('info', 'Thank you! Your payment is being processed — your template will activate automatically once confirmed.');
    }

    public function payfastCancel(string $tenantSlug): RedirectResponse
    {
        return redirect()->route('website.templates.index')
            ->with('error', 'Payment was cancelled. You can try purchasing this template again any time.');
    }

    private function finishActivation(Tenant $tenant, Template $template): RedirectResponse
    {
        $hadTemplateBefore = $tenant->activeTemplate()->exists();

        $tenant->activateTemplate($template->key);

        if (! $tenant->branding) {
            $tenant->branding()->create([]);
        }

        if (! $hadTemplateBefore) {
            return redirect()->route('website.branding.edit');
        }

        return redirect()->route('website.templates.index');
    }
}
