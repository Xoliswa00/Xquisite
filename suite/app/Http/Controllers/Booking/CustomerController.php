<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    private function tenantId(): int
    {
        return Auth::user()->tenant_id ?? abort(403, 'No tenant assigned to this account.');
    }

    public function index(Request $request)
    {
        $query = Customer::orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $customers = $query->paginate(15)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => [
                'nullable', 'email', 'max:255',
                Rule::unique('customers')->where(fn ($q) => $q->where('tenant_id', $this->tenantId())),
            ],
            'phone'     => 'nullable|string|max:50',
            'notes'     => 'nullable|string|max:2000',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        Customer::create($data);

        return redirect()->route('customers.index')
            ->with('success', 'Customer added.');
    }

    public function show(Customer $customer)
    {
        $appointments = $customer->appointments()
            ->with(['staff', 'services'])
            ->orderByDesc('scheduled_at')
            ->paginate(10);

        return view('customers.show', compact('customer', 'appointments'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => [
                'nullable', 'email', 'max:255',
                Rule::unique('customers')
                    ->where(fn ($q) => $q->where('tenant_id', $this->tenantId()))
                    ->ignore($customer->id),
            ],
            'phone'     => 'nullable|string|max:50',
            'notes'     => 'nullable|string|max:2000',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $customer->update($data);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer removed.');
    }
}
