<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Models\Customer;
use App\Rules\SouthAfricanPhoneNumber;
use App\Services\ContactImportParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
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
            'email'     => 'nullable|email|max:255|unique:customers,email',
            'phone'     => ['nullable', new SouthAfricanPhoneNumber],
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
            'email'     => 'nullable|email|max:255|unique:customers,email,' . $customer->id,
            'phone'     => ['nullable', new SouthAfricanPhoneNumber],
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

    public function importForm()
    {
        return view('customers.import');
    }

    public function import(Request $request, ContactImportParser $parser)
    {
        $request->validate([
            // extensions (not mimes) — vCard files don't reliably sniff to a
            // recognized MIME type, so content-type detection rejects valid .vcf uploads.
            'file' => 'required|file|extensions:csv,txt,vcf|max:5120',
        ]);

        $contents = file_get_contents($request->file('file')->getRealPath());
        $rows     = $parser->parse($contents, $request->file('file')->getClientOriginalName());

        // Track phone numbers we've already claimed this run (existing +
        // newly imported) in normalized form, so a duplicate within the
        // file itself is caught too, not just duplicates of existing records.
        $existingPhones = Customer::whereNotNull('phone')->pluck('phone')
            ->map(fn ($p) => $this->normalizePhone($p))
            ->filter()
            ->flip()
            ->all();

        $imported = 0;
        $skippedDuplicate = 0;
        $skippedInvalid   = 0;

        foreach ($rows as $row) {
            $name  = $row['name'];
            $phone = $row['phone'];
            $email = $row['email'] ?: null;

            if (!$name || !$phone) {
                $skippedInvalid++;
                continue;
            }

            $validator = Validator::make(['phone' => $phone, 'email' => $email], [
                'phone' => ['required', new SouthAfricanPhoneNumber],
                'email' => 'nullable|email',
            ]);
            if ($validator->fails()) {
                $skippedInvalid++;
                continue;
            }

            $normalized = $this->normalizePhone($phone);
            if ($normalized && isset($existingPhones[$normalized])) {
                $skippedDuplicate++;
                continue;
            }

            // An imported contact's email might already belong to another
            // customer (unique constraint) — drop it rather than fail the
            // whole row over an optional field.
            if ($email && Customer::where('email', $email)->exists()) {
                $email = null;
            }

            Customer::create([
                'name'      => $name,
                'phone'     => $phone,
                'email'     => $email,
                'is_active' => true,
            ]);

            if ($normalized) {
                $existingPhones[$normalized] = true;
            }
            $imported++;
        }

        $message = "{$imported} customer" . ($imported === 1 ? '' : 's') . ' imported.';
        if ($skippedDuplicate) {
            $message .= " {$skippedDuplicate} already existed.";
        }
        if ($skippedInvalid) {
            $message .= " {$skippedInvalid} skipped (missing or invalid name/phone).";
        }

        return redirect()->route('customers.index')->with('success', $message);
    }

    /** Same normalization SouthAfricanPhoneNumber validates against, used here for dedup matching. */
    private function normalizePhone(?string $raw): ?string
    {
        if (!$raw) {
            return null;
        }
        $digits = preg_replace('/[^\d+]/', '', $raw);
        if (str_starts_with($digits, '+27')) {
            $digits = '0' . substr($digits, 3);
        } elseif (str_starts_with($digits, '27') && strlen($digits) === 11) {
            $digits = '0' . substr($digits, 2);
        }
        return preg_match('/^0\d{9}$/', $digits) ? $digits : null;
    }
}
