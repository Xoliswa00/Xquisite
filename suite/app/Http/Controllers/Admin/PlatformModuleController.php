<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformModule;
use Illuminate\Http\Request;

class PlatformModuleController extends Controller
{
    public function index()
    {
        $modules = PlatformModule::ordered()->get()->groupBy('status');

        return view('admin.platform-modules.index', compact('modules'));
    }

    public function create()
    {
        return view('admin.platform-modules.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'key'           => 'required|string|alpha_dash|unique:platform_modules,key',
            'name'          => 'required|string|max:100',
            'description'   => 'required|string|max:500',
            'icon'          => 'required|string|max:50',
            'price'         => 'required|numeric|min:0',
            'status'        => 'required|in:active,beta,coming_soon',
            'launch_date'   => 'nullable|date',
            'sort_order'    => 'nullable|integer|min:0',
            'is_visible'    => 'boolean',
            'auto_activate' => 'boolean',
        ]);

        $data['is_visible']    = $request->boolean('is_visible', true);
        $data['auto_activate'] = $request->boolean('auto_activate');
        $data['sort_order']    = $data['sort_order'] ?? 0;

        PlatformModule::create($data);

        return redirect()->route('admin.platform-modules.index')
            ->with('success', "{$data['name']} added to the module registry.");
    }

    public function edit(PlatformModule $platformModule)
    {
        return view('admin.platform-modules.edit', ['module' => $platformModule]);
    }

    public function update(Request $request, PlatformModule $platformModule)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'description'   => 'required|string|max:500',
            'icon'          => 'required|string|max:50',
            'price'         => 'required|numeric|min:0',
            'status'        => 'required|in:active,beta,coming_soon',
            'launch_date'   => 'nullable|date',
            'sort_order'    => 'nullable|integer|min:0',
            'is_visible'    => 'boolean',
            'auto_activate' => 'boolean',
        ]);

        $data['is_visible']    = $request->boolean('is_visible');
        $data['auto_activate'] = $request->boolean('auto_activate');
        $data['sort_order']    = $data['sort_order'] ?? $platformModule->sort_order;

        $platformModule->update($data);

        return redirect()->route('admin.platform-modules.index')
            ->with('success', "{$platformModule->name} updated.");
    }

    public function updateStatus(Request $request, PlatformModule $platformModule)
    {
        $request->validate([
            'status' => 'required|in:active,beta,coming_soon',
        ]);

        $platformModule->update(['status' => $request->status]);

        return back()->with('success', "{$platformModule->name} moved to {$platformModule->status_label}.");
    }
}
