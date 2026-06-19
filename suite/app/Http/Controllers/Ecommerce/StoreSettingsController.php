<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StoreSettingsController extends Controller
{
    public function edit()
    {
        $tenant = $this->authorizedTenant();

        return view('settings.store', compact('tenant'));
    }

    public function update(Request $request)
    {
        $tenant = $this->authorizedTenant();

        $data = $request->validate([
            'shipping_enabled' => 'nullable|boolean',
            'shipping_type'    => 'required|in:flat,free',
            'shipping_cost'    => 'nullable|numeric|min:0|max:999999.99|required_if:shipping_type,flat',
        ]);

        $tenant->update([
            'shipping_enabled' => $request->boolean('shipping_enabled'),
            'shipping_type'    => $data['shipping_type'],
            // Flat rate keeps its value; "free" zeroes it so calculateShipping() is unambiguous.
            'shipping_cost'    => $data['shipping_type'] === 'flat' ? ($data['shipping_cost'] ?? 0) : 0,
        ]);

        return redirect()->route('store.settings')->with('success', 'Store settings saved.');
    }

    /** Only the tenant's owner/admin may change store-wide settings. */
    private function authorizedTenant()
    {
        $user   = auth()->user();
        $tenant = $user?->tenant;

        abort_unless($tenant, 403, 'Your account is not attached to a store.');
        abort_unless($user->isAdmin(), 403);

        return $tenant;
    }
}
