<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Property\Models\Property;
use App\Services\Tenant\TenantContext;
use Illuminate\Http\Request;

/**
 * Public, unauthenticated "browse available rentals" page for one tenant —
 * the piece that was missing between the internal Property Management admin
 * and the public apply/{slug}/{property} flow: a prospective renter had no
 * way to discover what's available without already being sent a direct link.
 */
class PropertyListingController extends Controller
{
    private function resolveTenant(string $slug): Tenant
    {
        $tenant = Tenant::where('slug', $slug)->where('is_active', true)->firstOrFail();
        TenantContext::set($tenant->id);
        return $tenant;
    }

    public function index(string $slug, Request $request)
    {
        $tenant = $this->resolveTenant($slug);

        $query = Property::where('is_active', true)
            ->whereHas('units', fn ($q) => $q->where('status', 'vacant'))
            ->with(['coverImage', 'units' => fn ($q) => $q->where('status', 'vacant')->orderBy('monthly_rent')]);

        if ($city = $request->query('city')) {
            $query->where('city', $city);
        }

        if ($unitType = $request->query('type')) {
            $query->whereHas('units', fn ($q) => $q->where('status', 'vacant')->where('type', $unitType));
        }

        if ($maxPrice = $request->query('max_price')) {
            $query->whereHas('units', fn ($q) => $q->where('status', 'vacant')->where('monthly_rent', '<=', $maxPrice));
        }

        $properties = $query->get();

        // Filter option lists are drawn from this tenant's own active listings, not
        // a fixed/global set — a tenant only ever sees filters that mean something
        // for what they actually have available right now.
        $allVacantUnits = Property::where('is_active', true)
            ->whereHas('units', fn ($q) => $q->where('status', 'vacant'))
            ->with(['units' => fn ($q) => $q->where('status', 'vacant')])
            ->get()
            ->pluck('units')
            ->flatten();

        $cities     = Property::where('is_active', true)->whereHas('units', fn ($q) => $q->where('status', 'vacant'))->distinct()->pluck('city')->filter()->sort()->values();
        $unitTypes  = $allVacantUnits->pluck('type')->filter()->unique()->sort()->values();
        $priceRange = ['min' => (int) $allVacantUnits->min('monthly_rent'), 'max' => (int) $allVacantUnits->max('monthly_rent')];

        return view('property.public.listings', compact('tenant', 'slug', 'properties', 'cities', 'unitTypes', 'priceRange'));
    }
}
