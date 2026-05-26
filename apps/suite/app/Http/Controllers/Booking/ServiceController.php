<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Models\Service;
use App\Modules\Booking\Models\ServiceProduct;
use App\Modules\POS\Models\Product;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::orderBy('name');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $services = $query->paginate(15)->withQueryString();

        return view('services.index', compact('services'));
    }

    public function create()
    {
        $products = $this->productList();
        return view('services.create', compact('products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string|max:2000',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'price'            => 'required|numeric|min:0',
            'is_active'        => 'boolean',
            'bundles'          => 'nullable|array',
            'bundles.*.product_id' => 'required|integer|exists:products,id',
            'bundles.*.quantity'   => 'required|integer|min:1',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $bundles = $data['bundles'] ?? [];
        unset($data['bundles']);

        $service = Service::create($data);
        $this->syncBundles($service, $bundles);

        return redirect()->route('services.index')
            ->with('success', 'Service created.');
    }

    public function edit(Service $service)
    {
        $products = $this->productList();
        $service->load('serviceProducts');
        return view('services.edit', compact('service', 'products'));
    }

    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string|max:2000',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'price'            => 'required|numeric|min:0',
            'is_active'        => 'boolean',
            'bundles'          => 'nullable|array',
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
            ->get(['id', 'name', 'category']);
    }

    private function syncBundles(Service $service, array $bundles): void
    {
        $service->serviceProducts()->delete();

        foreach ($bundles as $bundle) {
            ServiceProduct::create([
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
