<?php

namespace App\Http\Controllers;

use App\Models\company_domains;
use App\Http\Requests\Storecompany_domainsRequest;
use App\Http\Requests\Updatecompany_domainsRequest;
use App\Models\Company;
use Illuminate\Http\Request;


class CompanyDomainsController extends Controller
{
   public function store(Request $request, Company $company)
    {
        $this->authorize('update', $company);

        $validated = $request->validate([
            'domain' => 'required|string|unique:company_domains,domain',
        ]);

        $domain = $company->domains()->create([
            'domain' => $validated['domain'],
        ]);

        return response()->json($domain, 201);
    }

    public function destroy(company_domains $companyDomain)
    {
        $this->authorize('update', $companyDomain->company);

        $companyDomain->delete();

        return response()->noContent();
    }
}
