<?php

namespace App\Http\Controllers;

use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    private function companyId(): int
    {
        return auth()->user()->managedCompanies->first()->id;
    }

    public function index()
    {
        $categories = ServiceCategory::where('company_id', $this->companyId())
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
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'color'       => 'required|in:' . implode(',', array_keys(ServiceCategory::colorClasses())),
            'icon'        => 'nullable|string|max:10',
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'boolean',
        ]);

        ServiceCategory::create([
            'company_id'  => $this->companyId(),
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'color'       => $validated['color'],
            'icon'        => $validated['icon'] ?? null,
            'sort_order'  => $validated['sort_order'] ?? 0,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('service-categories.index')->with('success', 'Category created.');
    }

    public function edit(ServiceCategory $serviceCategory)
    {
        $this->authorize($serviceCategory);
        $colors = ServiceCategory::colorClasses();
        return view('service-categories.create', compact('serviceCategory', 'colors'));
    }

    public function update(Request $request, ServiceCategory $serviceCategory)
    {
        $this->authorize($serviceCategory);

        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'color'       => 'required|in:' . implode(',', array_keys(ServiceCategory::colorClasses())),
            'icon'        => 'nullable|string|max:10',
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'boolean',
        ]);

        $serviceCategory->update([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'color'       => $validated['color'],
            'icon'        => $validated['icon'] ?? null,
            'sort_order'  => $validated['sort_order'] ?? 0,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()->route('service-categories.index')->with('success', 'Category updated.');
    }

    public function destroy(ServiceCategory $serviceCategory)
    {
        $this->authorize($serviceCategory);
        $serviceCategory->delete();

        return redirect()->route('service-categories.index')->with('success', 'Category deleted.');
    }

    public function apiList()
    {
        $categories = ServiceCategory::where('company_id', $this->companyId())
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'color', 'icon']);

        return response()->json($categories);
    }

    private function authorize(ServiceCategory $category): void
    {
        abort_if($category->company_id !== $this->companyId(), 403);
    }
}
