<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreSettingsController extends Controller
{
    public function edit(): View
    {
        return view('settings.store', [
            'tenant' => auth()->user()->tenant,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'shipping_enabled' => 'boolean',
            'shipping_type'    => 'required|in:flat,free',
            'shipping_cost'    => 'nullable|numeric|min:0',
        ]);

        $tenant = auth()->user()->tenant;
        $tenant->update([
            'shipping_enabled' => (bool) ($data['shipping_enabled'] ?? false),
            'shipping_type'    => $data['shipping_type'],
            'shipping_cost'    => $data['shipping_type'] === 'flat' ? ($data['shipping_cost'] ?? 0) : 0,
        ]);

        return back()->with('success', 'Store settings saved.');
    }
}
