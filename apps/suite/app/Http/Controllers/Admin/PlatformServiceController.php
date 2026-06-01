<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformService;
use App\Models\TenantServiceOrder;
use Illuminate\Http\Request;

class PlatformServiceController extends Controller
{
    public function index()
    {
        $services = PlatformService::ordered()->withCount('orders')->get();
        $orders   = TenantServiceOrder::with(['service', 'tenant', 'requester'])
            ->pending()
            ->latest()
            ->get();

        return view('admin.platform-services.index', compact('services', 'orders'));
    }

    public function create()
    {
        return view('admin.platform-services.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        PlatformService::create($data);

        return redirect()->route('admin.platform-services.index')
            ->with('success', "{$data['name']} added to the service catalog.");
    }

    public function edit(PlatformService $platformService)
    {
        return view('admin.platform-services.edit', ['service' => $platformService]);
    }

    public function update(Request $request, PlatformService $platformService)
    {
        $data = $this->validated($request, $platformService);
        $platformService->update($data);

        return redirect()->route('admin.platform-services.index')
            ->with('success', "{$platformService->name} updated.");
    }

    // ── Order management ──────────────────────────────────────────────────────

    public function updateOrder(Request $request, TenantServiceOrder $order)
    {
        $data = $request->validate([
            'status'       => 'required|in:requested,quoted,approved,in_progress,delivered,cancelled',
            'quoted_price' => 'nullable|numeric|min:0',
            'admin_notes'  => 'nullable|string|max:1000',
            'assigned_to'  => 'nullable|exists:users,id',
            'delivered_at' => 'nullable|date',
        ]);

        if ($data['status'] === 'delivered' && ! $order->delivered_at) {
            $data['delivered_at'] = $data['delivered_at'] ?? now()->toDateString();
        }

        $order->update($data);

        return back()->with('success', "Order updated — {$order->statusLabel()}.");
    }

    private function validated(Request $request, ?PlatformService $existing = null): array
    {
        $data = $request->validate([
            'key'            => 'required|string|alpha_dash|unique:platform_services,key' . ($existing ? ",{$existing->id}" : ''),
            'name'           => 'required|string|max:100',
            'description'    => 'required|string|max:500',
            'category'       => 'required|in:onboarding,training,support,custom',
            'billing_type'   => 'required|in:once_off,recurring',
            'price'          => 'nullable|numeric|min:0',
            'price_label'    => 'nullable|string|max:60',
            'icon'           => 'nullable|string|max:50',
            'sort_order'     => 'nullable|integer|min:0',
            'is_active'      => 'boolean',
            'is_requestable' => 'boolean',
        ]);

        $data['is_active']      = $request->boolean('is_active', true);
        $data['is_requestable'] = $request->boolean('is_requestable', true);
        $data['sort_order']     = $data['sort_order'] ?? 0;
        $data['icon']           = $data['icon'] ?? 'wrench';

        return $data;
    }
}
