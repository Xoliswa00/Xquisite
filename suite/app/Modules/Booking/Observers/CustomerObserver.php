<?php

namespace App\Modules\Booking\Observers;

use App\Models\Client;
use App\Modules\Booking\Models\Customer;

class CustomerObserver
{
    public function created(Customer $customer): void
    {
        if (! $customer->tenant_id) {
            return;
        }

        $existing = Client::withTrashed()
            ->where('tenant_id', $customer->tenant_id)
            ->where('email', $customer->email)
            ->first();

        if ($existing) {
            $existing->customer_id = $customer->id;
            $existing->save();
        } else {
            Client::create([
                'tenant_id'   => $customer->tenant_id,
                'customer_id' => $customer->id,
                'name'        => $customer->name,
                'email'       => $customer->email,
                'phone'       => $customer->phone,
                'notes'       => $customer->notes,
            ]);
        }
    }

    public function updated(Customer $customer): void
    {
        if (! $customer->wasChanged(['name', 'email', 'phone'])) {
            return;
        }

        $client = Client::withTrashed()
            ->where('customer_id', $customer->id)
            ->where('tenant_id', $customer->tenant_id)
            ->first();

        if (! $client) {
            return;
        }

        $client->name  = $customer->name;
        $client->email = $customer->email;
        $client->phone = $customer->phone;
        $client->save();
    }
}
