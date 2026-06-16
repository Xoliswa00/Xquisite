<?php

namespace App\Http\Controllers;

use App\Models\ServiceCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceCategoryController extends Controller
{
    private function tenantId(): int
    {
        return Auth::user()->tenant_id ?? abort(403, 'No tenant assigned to this account.');
    }

    public function index()
    {
        $categories = ServiceCategory::where('tenant_id', $this->tenantId())
            ->withCount('services')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('service-categories.index', compact('categories'));
    }

    public function create()
    {
        $colors = ServiceCategory::colorClasses();
        return view('service-categories.create', compact('colors'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'color'       => 'required|in:' . implode(',', array_keys(ServiceCategory::colorClasses())),
            'icon'        => 'nullable|string|max:10',
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'boolean',
        ]);

        ServiceCategory::create(array_merge($data, [
            'tenant_id' => $this->tenantId(),
            'is_active' => $request->boolean('is_active', true),
            'icon'      => ($data['icon'] ?? null) ?: ServiceCategory::guessIcon($data['name']),
        ]));

        $redirect = $request->input('_redirect');
        if ($redirect === 'services.create') {
            return redirect()->route('services.create')->with('success', 'Category created. Now assign it to this service.');
        }

        return redirect()->route('service-categories.index')->with('success', 'Category created.');
    }

    public function edit(ServiceCategory $serviceCategory)
    {
        abort_unless($serviceCategory->tenant_id === $this->tenantId(), 403);
        $colors = ServiceCategory::colorClasses();
        return view('service-categories.create', ['category' => $serviceCategory, 'colors' => $colors]);
    }

    public function update(Request $request, ServiceCategory $serviceCategory)
    {
        abort_unless($serviceCategory->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'color'       => 'required|in:' . implode(',', array_keys(ServiceCategory::colorClasses())),
            'icon'        => 'nullable|string|max:10',
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'boolean',
        ]);

        $serviceCategory->update(array_merge($data, ['is_active' => $request->boolean('is_active', true)]));

        return redirect()->route('service-categories.index')->with('success', 'Category updated.');
    }

    public function destroy(ServiceCategory $serviceCategory)
    {
        abort_unless($serviceCategory->tenant_id === $this->tenantId(), 403);
        $serviceCategory->delete();

        return redirect()->route('service-categories.index')->with('success', 'Category deleted.');
    }

    public function apiList(): JsonResponse
    {
        $categories = ServiceCategory::where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'color', 'icon']);

        return response()->json($categories);
    }
}
