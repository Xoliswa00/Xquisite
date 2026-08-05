<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\View;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = Template::ordered()->get()->groupBy(fn (Template $t) => $t->category ?: 'uncategorized');

        return view('admin.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.templates.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, isCreate: true);

        $data['is_active']   = $request->boolean('is_active', true);
        $data['is_visible']  = $request->boolean('is_visible', true);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['sort_order']  = $data['sort_order'] ?? 0;

        Template::create($data);

        return redirect()->route('admin.templates.index')
            ->with('success', "{$data['name']} added to the template catalog.");
    }

    public function edit(Template $template)
    {
        return view('admin.templates.edit', compact('template'));
    }

    public function update(Request $request, Template $template)
    {
        $data = $this->validated($request, isCreate: false);

        $data['is_active']   = $request->boolean('is_active');
        $data['is_visible']  = $request->boolean('is_visible');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['sort_order']  = $data['sort_order'] ?? $template->sort_order;

        $template->update($data);

        return redirect()->route('admin.templates.index')
            ->with('success', "{$template->name} updated.");
    }

    public function updateStatus(Request $request, Template $template)
    {
        $data = $request->validate([
            'is_active'  => 'sometimes|boolean',
            'is_visible' => 'sometimes|boolean',
        ]);

        $template->update($data);

        return back()->with('success', "{$template->name} status updated.");
    }

    private function validated(Request $request, bool $isCreate): array
    {
        $rules = [
            'name'               => 'required|string|max:100',
            'description'        => 'nullable|string|max:1000',
            'category'           => 'nullable|string|max:50',
            'preview_image_url'  => 'nullable|string|max:255',
            'blade_view'         => ['required', 'string', 'max:150', function ($attribute, $value, $fail) {
                if (! View::exists($value)) {
                    $fail("The blade view [{$value}] does not exist.");
                }
            }],
            'price_type'         => 'required|in:free,one_time,monthly,annual',
            'price'              => 'nullable|required_unless:price_type,free|numeric|min:0',
            'default_primary_color'   => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'default_secondary_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'default_accent_color'    => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'sort_order'         => 'nullable|integer|min:0',
            'is_active'          => 'boolean',
            'is_visible'         => 'boolean',
            'is_featured'        => 'boolean',
        ];

        if ($isCreate) {
            $rules['key'] = ['required', 'string', 'alpha_dash', Rule::unique('templates', 'key')];
        }

        return $request->validate($rules);
    }
}
