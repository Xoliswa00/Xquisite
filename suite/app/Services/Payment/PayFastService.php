<?php

namespace App\Services\Payment;

use App\Modules\Ecommerce\Models\Order;
use Illuminate\Http\Request;

class PayFastService
{
    private string $merchantId;
    private string $merchantKey;
    private string $passphrase;
    private bool   $sandbox;

    public function __construct()
    {
        $this->merchantId  = config('payfast.merchant_id');
        $this->merchantKey = config('payfast.merchant_key');
        $this->passphrase  = config('payfast.passphrase', '');
        $this->sandbox     = config('payfast.sandbox', true);
    }

    public function getPaymentUrl(): string
    {
        return $this->sandbox
            ? 'https://sandbox.payfast.co.za/eng/process'
            : 'https://www.payfast.co.za/eng/process';
    }

    public function buildPaymentData(Order $order, string $tenantSlug): array
    {
        $nameParts = explode(' ', $order->customer_name, 2);

        $data = [
            'merchant_id'   => $this->merchantId,
            'merchant_key'  => $this->merchantKey,
            'return_url'    => route('shop.payfast.return', $tenantSlug),
            'cancel_url'    => route('shop.payfast.cancel', $tenantSlug),
            'notify_url'    => route('shop.payfast.notify', $tenantSlug),
            'name_first'    => $nameParts[0],
            'name_last'     => $nameParts[1] ?? '-',
            'email_address' => $order->customer_email,
            'm_payment_id'  => $order->reference,
            'amount'        => number_format((float) $order->total, 2, '.', ''),
            'item_name'     => 'Order ' . $order->reference,
            'item_description' => $order->items->count() . ' item(s)',
        ];

        $data['signature'] = $this->sign($data);

        return $data;
    }

    public function validateIpn(Request $request, string $tenantSlug): bool
    {
        $posted = $request->except('signature');
        $signature = $this->sign($posted);

        if ($signature !== $request->input('signature')) {
            return false;
        }

        // Verify source IP belongs to PayFast (basic check)
        $validIps = ['197.97.145.144', '41.74.179.194', '41.74.179.195', '41.74.179.196'];
        if (!$this->sandbox && !in_array($request->ip(), $validIps)) {
            return false;
        }

        return true;
    }

    private function sign(array $data): string
    {
        $parts = [];
        foreach ($data as $key => $value) {
            if ($value !== '' && $key !== 'signature') {
                $parts[] = $key . '=' . urlencode(trim((string) $value));
            }
        }

        $str = implode('&', $parts);

        if ($this->passphrase !== '') {
            $str .= '&passphrase=' . urlencode(trim($this->passphrase));
        }

        return md5($str);
    }
}
