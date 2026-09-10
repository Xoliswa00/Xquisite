<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateServicePhotoDerivatives;
use App\Models\Promotion;
use App\Models\ServiceCategory;
use App\Models\ServiceCombo;
use App\Modules\Booking\Models\Service;
use App\Modules\Booking\Models\ServiceProduct;
use App\Modules\POS\Models\Product;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $allowedSorts = ['name', 'price', 'duration_minutes', 'created_at'];
        $sort      = in_array($request->sort, $allowedSorts) ? $request->sort : 'created_at';
        $direction = $request->direction === 'desc' ? 'desc' : 'asc';

        $query = Service::where('tenant_id', $tenantId)->with('category')
            ->orderBy('service_category_id')
            ->orderBy($sort, $direction);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $services = $query->paginate(200, ['*'], 'services_page')->withQueryString();

        $categories = ServiceCategory::where('tenant_id', $tenantId)
            ->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();

        $combos = ServiceCombo::where('tenant_id', $tenantId)
            ->with('services')->latest()->paginate(10, ['*'], 'combos_page');

        $promotions = Promotion::where('tenant_id', $tenantId)
            ->latest()->paginate(10, ['*'], 'promos_page');

        $tab = $request->get('tab', 'services');

        return view('services.index', compact('services', 'categories', 'combos', 'promotions', 'tab'));
    }

    /** Flat, searchable list for managing photos across many services at a glance. */
    public function photos(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $base = fn() => Service::where('tenant_id', $tenantId);

        $query = $base()
            ->with('category:id,name,icon')
            ->with('coverPhoto')
            ->withCount('photos')
            ->orderBy('name');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->string('search')->trim() . '%');
        }

        $filter = in_array($request->get('filter'), ['missing', 'has'], true) ? $request->get('filter') : null;
        match ($filter) {
            'missing' => $query->doesntHave('photos'),
            'has'     => $query->has('photos'),
            default   => null,
        };

        $services = $query->paginate(30)->withQueryString();

        $counts = [
            'all'     => $base()->count(),
            'missing' => $base()->doesntHave('photos')->count(),
            'has'     => $base()->has('photos')->count(),
        ];

        return view('services.photos', compact('services', 'filter', 'counts'));
    }

    public function create()
    {
        $tenantId     = auth()->user()->tenant_id;
        $hasInventory = auth()->user()->tenant?->hasModule('pos');
        $products     = $hasInventory ? $this->productList() : collect();
        $categories   = ServiceCategory::where('tenant_id', $tenantId)
            ->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $services     = Service::where('tenant_id', $tenantId)
            ->with('category')->orderBy('name')->get(['id', 'name', 'duration_minutes', 'price', 'service_category_id', 'is_active']);

        return view('services.create', compact('products', 'categories', 'services', 'hasInventory'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'service_category_id' => 'nullable|exists:service_categories,id',
            'description'         => 'nullable|string|max:2000',
            'duration_minutes'    => 'required|integer|min:5|max:43200',
            'pricing_type'        => 'nullable|in:flat,per_head,per_unit',
            'price'               => 'nullable|numeric|min:0',
            'cost_price'          => 'nullable|numeric|min:0',
            'price_per_unit'      => 'nullable|numeric|min:0',
            'unit_label'          => 'nullable|string|max:30',
            'is_active'           => 'boolean',
            'bundles'             => 'nullable|array',
            'bundles.*.product_id' => 'required|integer|exists:products,id',
            'bundles.*.quantity'   => 'required|integer|min:1',
            'photos'              => 'nullable|array|max:' . Service::MAX_PHOTOS,
            'photos.*'            => 'image|mimes:jpg,jpeg,png,webp|max:4096',
        ], [
            'photos.*.mimes' => 'Photos must be JPG, PNG or WebP. iPhone HEIC photos are not supported yet. In your Camera settings choose "Most Compatible", or share the photo first to convert it to JPG.',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $bundles = $data['bundles'] ?? [];
        unset($data['bundles'], $data['photos']);

        $service = Service::create($data);
        $this->syncBundles($service, $bundles);
        $this->storePhotos($service, $request);

        return redirect()->route('services.index')
            ->with('success', 'Service created.');
    }

    public function edit(Service $service)
    {
        $tenantId     = auth()->user()->tenant_id;
        $hasInventory = auth()->user()->tenant?->hasModule('pos');
        $products     = $hasInventory ? $this->productList() : collect();
        $categories   = ServiceCategory::where('tenant_id', $tenantId)
            ->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $service->load('serviceProducts', 'photosOrdered');
        return view('services.edit', compact('service', 'products', 'categories', 'hasInventory'));
    }

    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'service_category_id' => 'nullable|exists:service_categories,id',
            'description'         => 'nullable|string|max:2000',
            'duration_minutes'    => 'required|integer|min:5|max:43200',
            'pricing_type'        => 'nullable|in:flat,per_head,per_unit',
            'price'               => 'nullable|numeric|min:0',
            'cost_price'          => 'nullable|numeric|min:0',
            'price_per_unit'      => 'nullable|numeric|min:0',
            'unit_label'          => 'nullable|string|max:30',
            'is_active'           => 'boolean',
            'bundles'             => 'nullable|array',
            'bundles.*.product_id' => 'required|integer|exists:products,id',
            'bundles.*.quantity'   => 'required|integer|min:1',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $bundles = $data['bundles'] ?? [];
        unset($data['bundles']);

        $service->update($data);
        $this->syncBundles($service, $bundles);

        return redirect()->route('services.index')
            ->with('success', 'Service updated.');
    }

    private function productList(): \Illuminate\Support\Collection
    {
        return Product::where('is_active', true)
            ->orderBy('category')->orderBy('name')
            ->get(['id', 'name', 'category', 'cost_price']);
    }

    /** Save any photos uploaded alongside the create form (cap at Service::MAX_PHOTOS). */
    private function storePhotos(Service $service, Request $request): void
    {
        if (! $request->hasFile('photos')) {
            return;
        }

        $files = array_slice($request->file('photos'), 0, Service::MAX_PHOTOS);

        foreach (array_values($files) as $i => $file) {
            $photo = $service->photos()->create([
                'tenant_id'  => $service->tenant_id,
                'path'       => $file->store("services/{$service->id}", 'public'),
                'disk'       => 'public',
                'sort_order' => $i + 1,
                'is_primary' => $i === 0,
            ]);

            GenerateServicePhotoDerivatives::dispatch($photo->id);
        }
    }

    private function syncBundles(Service $service, array $bundles): void
    {
        $service->serviceProducts()->get()->each->delete();

        foreach ($bundles as $bundle) {
            ServiceProduct::create([
                'tenant_id'  => auth()->user()->tenant_id,
                'service_id' => $service->id,
                'product_id' => $bundle['product_id'],
                'quantity'   => (int) $bundle['quantity'],
            ]);
        }
    }

    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()->route('services.index')
            ->with('success', 'Service deleted.');
    }
}
