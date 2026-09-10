<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Communication;
use App\Models\ServiceCategory;
use App\Models\Tenant;
use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\Customer;
use App\Modules\Booking\Models\Service;
use App\Modules\Booking\Models\Staff;
use App\Modules\Booking\Models\StaffSchedule;
use App\Modules\Ecommerce\Models\Order;
use App\Modules\Ecommerce\Models\OrderItem;
use App\Modules\POS\Models\Product;
use App\Modules\POS\Models\Sale;
use App\Modules\POS\Models\SaleItem;
use App\Modules\POS\Models\Supplier;
use App\Modules\Property\Models\Lease;
use App\Modules\Property\Models\Property;
use App\Modules\Property\Models\RentPayment;
use App\Modules\Property\Models\Renter;
use App\Modules\Property\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Fills the demo tenant with a coherent fictional business — "Marigold", a hair &
 * beauty studio in Braamfontein — across every live module. All dates are computed
 * relative to now() so the demo never looks stale, and the same cast (Nandi, Sipho,
 * Lerato, Fatima, Thandeka, Zanele, Grace) recurs across modules.
 *
 * Idempotent: wipes this tenant's module data first, so it is safe to re-run and is
 * the re-seed step behind `php artisan demo:reset`.
 *
 * WithoutModelEvents keeps the Auditable trait from writing a log row per insert and
 * stops any notification/side-effect hooks from firing during the seed.
 */
class DemoContentSeeder extends Seeder
{
    use WithoutModelEvents;

    private Tenant $tenant;
    private int $tid;
    private ?int $ownerId = null;
    private Carbon $today;

    public function run(): void
    {
        $tenant = Tenant::where('is_demo', true)->first();

        if (! $tenant) {
            $this->command->warn('No demo tenant found — run DemoSeeder first. Skipping DemoContentSeeder.');
            return;
        }

        $this->tenant  = $tenant;
        $this->tid     = $tenant->id;
        $this->ownerId = $tenant->users()->first()?->id;
        $this->today   = Carbon::today();

        // Rebrand the demo tenant to Marigold.
        $tenant->update([
            'name'     => 'Marigold Hair & Beauty',
            'industry' => 'Hair & Beauty',
        ]);

        $this->cleanup();

        [$staff, $services] = $this->seedStaffAndServices();
        $customers          = $this->seedCustomers();
        $this->seedAppointments($staff, $services, $customers);
        $products           = $this->seedProductsAndSuppliers();
        $this->seedSales($products, $services, $customers);
        $this->seedOrders($products);
        $this->seedMessaging($customers);
        $this->seedProperty();

        $this->command->info("Demo content seeded for [{$tenant->name}].");
    }

    // ── Cleanup ──────────────────────────────────────────────────────────────

    private function cleanup(): void
    {
        $tid = $this->tid;

        $staffIds = DB::table('staff')->where('tenant_id', $tid)->pluck('id');
        $apptIds  = DB::table('appointments')->where('tenant_id', $tid)->pluck('id');
        $saleIds  = DB::table('sales')->where('tenant_id', $tid)->pluck('id');
        $orderIds = DB::table('orders')->where('tenant_id', $tid)->pluck('id');
        $leaseIds = DB::table('leases')->where('tenant_id', $tid)->pluck('id');

        DB::table('appointment_services')->whereIn('appointment_id', $apptIds)->delete();
        DB::table('sale_items')->whereIn('sale_id', $saleIds)->delete();
        DB::table('order_items')->whereIn('order_id', $orderIds)->delete();
        DB::table('communications')->where('tenant_id', $tid)->delete();
        DB::table('lease_charges')->whereIn('lease_id', $leaseIds)->delete();
        DB::table('rent_payments')->where('tenant_id', $tid)->delete();

        DB::table('staff_services')->whereIn('staff_id', $staffIds)->delete();
        DB::table('staff_schedules')->whereIn('staff_id', $staffIds)->delete();
        DB::table('staff_blocks')->whereIn('staff_id', $staffIds)->delete();

        foreach ([
            'appointments', 'clients', 'customers', 'services', 'service_categories',
            'staff', 'products', 'suppliers', 'sales', 'orders', 'leases', 'renters',
            'units', 'properties',
        ] as $table) {
            DB::table($table)->where('tenant_id', $tid)->delete();
        }
    }

    // ── Bookings: staff, schedules, services ─────────────────────────────────

    private function seedStaffAndServices(): array
    {
        $catDefs = [
            'Hair'       => ['color' => 'blue',    'icon' => '💇'],
            'Colour'     => ['color' => 'violet',  'icon' => '🎨'],
            'Treatments' => ['color' => 'emerald', 'icon' => '🧴'],
            'Barber'     => ['color' => 'amber',   'icon' => '💈'],
        ];
        $cats = [];
        $sort = 0;
        foreach ($catDefs as $name => $def) {
            $cats[$name] = ServiceCategory::create([
                'tenant_id'  => $this->tid,
                'name'       => $name,
                'color'      => $def['color'],
                'icon'       => $def['icon'],
                'sort_order' => $sort++,
                'is_active'  => true,
            ]);
        }

        $serviceDefs = [
            ['Gents Cut',                     'Barber',     45,  180],
            ['Kids Cut',                      'Barber',     30,  120],
            ['Beard Trim & Line-up',          'Barber',     30,  140],
            ['Ladies Cut & Blowout',          'Hair',       60,  320],
            ['Braids / Protective Style',     'Hair',      180,  650],
            ['Silk Press',                    'Hair',       90,  420],
            ['Full Colour',                   'Colour',    150,  890],
            ['Root Touch-up',                 'Colour',     90,  520],
            ['Highlights',                    'Colour',    180,  980],
            ['Deep-Conditioning Treatment',   'Treatments', 45,  260],
            ['Scalp Detox',                   'Treatments', 40,  240],
        ];
        $services = [];
        foreach ($serviceDefs as [$name, $cat, $mins, $price]) {
            $services[$name] = Service::create([
                'tenant_id'           => $this->tid,
                'name'                => $name,
                'service_category_id' => $cats[$cat]->id,
                'duration_minutes'    => $mins,
                'price'               => $price,
                'cost_price'          => round($price * 0.35, 2),
                'pricing_type'        => 'flat',
                'is_active'           => true,
            ]);
        }

        $staffDefs = [
            ['Nandi Dlamini',  'Owner / Senior Stylist', ['Ladies Cut & Blowout', 'Silk Press', 'Braids / Protective Style', 'Deep-Conditioning Treatment']],
            ['Sipho Mahlaba',  'Barber',                 ['Gents Cut', 'Kids Cut', 'Beard Trim & Line-up']],
            ['Lerato Sithole', 'Senior Stylist',         ['Ladies Cut & Blowout', 'Silk Press', 'Braids / Protective Style', 'Scalp Detox']],
            ['Fatima Adams',   'Colour Technician',      ['Full Colour', 'Root Touch-up', 'Highlights', 'Deep-Conditioning Treatment']],
        ];
        $staff = [];
        foreach ($staffDefs as $i => [$name, $role, $canDo]) {
            $member = Staff::create([
                'tenant_id' => $this->tid,
                'name'      => $name,
                'email'     => $this->handle($name) . '@marigold.co.za',
                'phone'     => $this->phone(10 + $i),
                'role'      => $role,
                'is_active' => true,
            ]);

            // Mon–Sat 09:00–17:00
            foreach (range(1, 6) as $dow) {
                StaffSchedule::create([
                    'tenant_id'   => $this->tid,
                    'staff_id'    => $member->id,
                    'day_of_week' => $dow,
                    'start_time'  => '09:00',
                    'end_time'    => '17:00',
                    'is_active'   => true,
                ]);
            }

            $now = now();
            $member->services()->attach(
                collect($canDo)->mapWithKeys(fn ($n) => [$services[$n]->id => ['created_at' => $now, 'updated_at' => $now]])->all()
            );
            $staff[$name] = $member;
        }

        return [$staff, $services];
    }

    // ── Bookings: customers ─────────────────────────────────────────────────

    private function seedCustomers(): array
    {
        $names = [
            'Thandeka Mokoena', 'Naledi Khoza', 'Aphiwe Dlamini', 'Karabo Molefe',
            'Zinhle Ngcobo', 'Palesa Motaung', 'Sibusiso Nkosi', 'Ayanda Zulu',
            'Refilwe Mahlangu', 'Bongani Sibiya', 'Kagiso Peterson', 'Nomsa Mbeki',
            'Tshepo Radebe', 'Lindiwe Cele', 'Yusuf Patel', 'Ntando Buthelezi',
        ];

        $customers = [];
        foreach ($names as $i => $name) {
            $customers[$name] = Customer::create([
                'tenant_id' => $this->tid,
                'name'      => $name,
                'email'     => $i % 3 === 0 ? null : $this->handle($name) . '@example.co.za',
                'phone'     => $this->phone(100 + $i),
                'is_active' => true,
                'notes'     => $i === 0 ? 'Regular — prefers Saturday mornings with Sipho.' : null,
            ]);
        }

        return $customers;
    }

    // ── Bookings: appointments ─────────────────────────────────────────────

    private function seedAppointments(array $staff, array $services, array $customers): void
    {
        $custList  = array_values($customers);
        $staffList = array_values($staff);

        // Fixed slate for today so the calendar hero shot is predictable.
        // Note: Sipho's Saturday 10:00 is deliberately left open — that is the
        // slot the intro video books into.
        $todayPlan = [
            ['09:00', 'Sipho Mahlaba',  'Gents Cut',                  'Bongani Sibiya',   'completed'],
            ['09:30', 'Fatima Adams',   'Root Touch-up',              'Naledi Khoza',     'completed'],
            ['10:00', 'Lerato Sithole', 'Silk Press',                 'Zinhle Ngcobo',    'confirmed'],
            ['10:30', 'Sipho Mahlaba',  'Beard Trim & Line-up',       'Tshepo Radebe',    'confirmed'],
            ['11:30', 'Nandi Dlamini',  'Ladies Cut & Blowout',       'Palesa Motaung',   'confirmed'],
            ['12:00', 'Fatima Adams',   'Full Colour',                'Refilwe Mahlangu', 'confirmed'],
            ['14:00', 'Sipho Mahlaba',  'Gents Cut',                  'Yusuf Patel',      'confirmed'],
            ['14:30', 'Lerato Sithole', 'Braids / Protective Style',  'Aphiwe Dlamini',   'pending'],
            ['15:30', 'Nandi Dlamini',  'Deep-Conditioning Treatment','Lindiwe Cele',     'confirmed'],
        ];
        foreach ($todayPlan as [$time, $staffName, $serviceName, $custName, $status]) {
            [$h, $m] = explode(':', $time);
            $this->makeAppointment(
                $this->today->copy()->setTime((int) $h, (int) $m),
                $staff[$staffName],
                $services[$serviceName],
                $customers[$custName],
                $status,
            );
        }

        // History — last 12 days, mostly completed with a few no-shows/cancellations.
        for ($d = 12; $d >= 1; $d--) {
            $date  = $this->today->copy()->subDays($d);
            $count = 2 + ($d % 3);
            for ($n = 0; $n < $count; $n++) {
                $svc    = array_values($services)[($d * 3 + $n) % count($services)];
                $member = $this->staffFor($svc, $staffList);
                $status = match (($d + $n) % 7) {
                    5       => 'no_show',
                    6       => 'cancelled',
                    default => 'completed',
                };
                $this->makeAppointment(
                    $date->copy()->setTime(9 + ($n * 2), $n % 2 ? 30 : 0),
                    $member,
                    $svc,
                    $custList[($d * 2 + $n) % count($custList)],
                    $status,
                );
            }
        }

        // Upcoming — next 10 days, confirmed/pending.
        for ($d = 1; $d <= 10; $d++) {
            $date  = $this->today->copy()->addDays($d);
            $count = 1 + ($d % 3);
            for ($n = 0; $n < $count; $n++) {
                $svc    = array_values($services)[($d + $n) % count($services)];
                $member = $this->staffFor($svc, $staffList);
                $this->makeAppointment(
                    $date->copy()->setTime(10 + ($n * 2), $n % 2 ? 30 : 0),
                    $member,
                    $svc,
                    $custList[($d * 3 + $n) % count($custList)],
                    $n % 2 ? 'pending' : 'confirmed',
                );
            }
        }
    }

    private function makeAppointment(Carbon $when, Staff $member, Service $service, Customer $customer, string $status): Appointment
    {
        $appt = Appointment::create([
            'tenant_id'        => $this->tid,
            'customer_id'      => $customer->id,
            'staff_id'         => $member->id,
            'scheduled_at'     => $when,
            'duration_minutes' => $service->duration_minutes,
            'status'           => $status,
        ]);

        $appt->services()->attach($service->id, [
            'duration_minutes' => $service->duration_minutes,
            'price_at_booking' => $service->price,
            'quantity'         => 1,
            'sort_order'       => 0,
        ]);

        return $appt;
    }

    /** Pick a staff member who can perform the service, else anyone. */
    private function staffFor(Service $service, array $staffList): Staff
    {
        foreach ($staffList as $member) {
            if ($member->services()->where('services.id', $service->id)->exists()) {
                return $member;
            }
        }
        return $staffList[0];
    }

    // ── POS: suppliers + products ─────────────────────────────────────────

    private function seedProductsAndSuppliers(): array
    {
        $marula = Supplier::create([
            'tenant_id'      => $this->tid,
            'name'           => 'Marula & Co. Distribution',
            'email'          => 'orders@marulaco.co.za',
            'phone'          => $this->phone(1),
            'contact_person' => 'Refiloe Mabaso',
            'payment_terms'  => '30 days',
            'is_active'      => true,
        ]);
        $bbs = Supplier::create([
            'tenant_id'      => $this->tid,
            'name'           => 'Braamfontein Beauty Supply',
            'email'          => 'hello@bbsupply.co.za',
            'phone'          => $this->phone(2),
            'contact_person' => 'Devan Naidoo',
            'payment_terms'  => 'COD',
            'is_active'      => true,
        ]);

        $defs = [
            ['Marula Repair Serum',          'Haircare',    240, 18, 6,  $marula],
            ['Argan Hair Oil 100ml',         'Haircare',    180, 24, 8,  $marula],
            ['Sulphate-Free Shampoo 250ml',  'Haircare',    150, 30, 10, $marula],
            ['Colour-Lock Conditioner 250ml','Haircare',    150, 12, 10, $marula],
            ['Heat Protectant Spray',        'Styling',     160, 9,  8,  $bbs],
            ['Edge Control 120ml',           'Styling',     90,  15, 8,  $bbs],
            ['Curl Defining Cream',          'Styling',     190, 13, 6,  $bbs],
            ['Leave-In Detangler',           'Styling',     170, 7,  8,  $bbs],
            ['Scalp Treatment Oil',          'Treatments',  210, 11, 5,  $marula],
            ['Silk Bonnet',                  'Accessories', 120, 20, 6,  $bbs],
            ['Wide-Tooth Comb',              'Accessories', 60,  40, 10, $bbs],
            ['Boar-Bristle Brush',           'Accessories', 280, 6,  4,  $bbs],
        ];
        $products = [];
        foreach ($defs as $i => [$name, $cat, $price, $stock, $reorder, $supplier]) {
            $products[$name] = Product::create([
                'tenant_id'           => $this->tid,
                'name'                => $name,
                'sku'                 => sprintf('MAR-%03d', $i + 1),
                'category'            => $cat,
                'description'         => null,
                'price'               => $price,
                'cost_price'          => round($price * 0.45, 2),
                'stock_quantity'      => $stock,
                'reorder_level'       => $reorder,
                'reorder_quantity'    => $reorder * 2,
                'supplier_id'         => $supplier->id,
                'track_stock'         => true,
                'is_active'           => true,
                'is_available_online' => $cat !== 'Accessories' || $i % 2 === 0,
            ]);
        }

        return $products;
    }

    // ── POS: sales history ───────────────────────────────────────────────

    private function seedSales(array $products, array $services, array $customers): void
    {
        $prodList = array_values($products);
        $custList = array_values($customers);
        $methods  = ['cash', 'card', 'yoco'];

        for ($d = 14; $d >= 0; $d--) {
            $date  = $this->today->copy()->subDays($d)->setTime(10 + ($d % 6), ($d % 2) ? 15 : 45);
            $count = $d % 3 === 0 ? 2 : 1;

            for ($n = 0; $n < $count; $n++) {
                $lines = [];

                // Roughly every third sale pairs a service with a retail product.
                if (($d + $n) % 3 === 0) {
                    $svc = array_values($services)[($d + $n) % count($services)];
                    $lines[] = ['service', $svc->id, $svc->name, (float) $svc->price, 1];
                }
                $picks = 1 + (($d + $n) % 2);
                for ($p = 0; $p < $picks; $p++) {
                    $prod    = $prodList[($d * 2 + $n + $p) % count($prodList)];
                    $qty     = 1 + (($d + $p) % 2);
                    $lines[] = ['product', $prod->id, $prod->name, (float) $prod->price, $qty];
                }

                $subtotal = array_sum(array_map(fn ($l) => $l[3] * $l[4], $lines));

                $sale = Sale::create([
                    'tenant_id'       => $this->tid,
                    'reference'       => Sale::generateReference(),
                    'customer_id'     => ($d + $n) % 4 === 0 ? $custList[($d + $n) % count($custList)]->id : null,
                    'status'          => 'paid',
                    'subtotal'        => $subtotal,
                    'discount_amount' => 0,
                    'tax_amount'      => 0,
                    'total'           => $subtotal,
                    'payment_method'  => $methods[($d + $n) % count($methods)],
                    'paid_at'         => $date,
                    'created_at'      => $date,
                    'updated_at'      => $date,
                ]);

                foreach ($lines as [$type, $id, $name, $unit, $qty]) {
                    SaleItem::create([
                        'sale_id'    => $sale->id,
                        'item_type'  => $type,
                        'item_id'    => $id,
                        'name'       => $name,
                        'unit_price' => $unit,
                        'quantity'   => $qty,
                        'subtotal'   => $unit * $qty,
                    ]);
                }
            }
        }
    }

    // ── E-commerce: online orders ───────────────────────────────────────

    private function seedOrders(array $products): void
    {
        $onlineProducts = array_values(array_filter($products, fn ($p) => $p->is_available_online));

        $plan = [
            // [daysAgo, hour, status, paymentStatus, method, fulfillment]
            [0, 8,  'paid',      'paid', 'payfast', 'collection'],
            [0, 11, 'processing','paid', 'payfast', 'delivery'],
            [0, 15, 'paid',      'paid', 'eft',     'collection'],
            [1, 9,  'ready',     'paid', 'payfast', 'collection'],
            [2, 13, 'shipped',   'paid', 'payfast', 'delivery'],
            [3, 10, 'delivered', 'paid', 'eft',     'delivery'],
            [4, 16, 'delivered', 'paid', 'payfast', 'collection'],
            [5, 12, 'delivered', 'paid', 'payfast', 'delivery'],
            [6, 14, 'delivered', 'paid', 'eft',     'collection'],
            [8, 9,  'delivered', 'paid', 'payfast', 'delivery'],
        ];

        $buyers = [
            ['Thandeka Mokoena', 'thandeka@example.co.za', 'Braamfontein'],
            ['Naledi Khoza',     'naledi.k@example.co.za', 'Melville'],
            ['Karabo Molefe',    'karabo.m@example.co.za', 'Auckland Park'],
            ['Zinhle Ngcobo',    'zinhle@example.co.za',   'Parktown'],
            ['Ayanda Zulu',      'ayanda.z@example.co.za', 'Newtown'],
        ];

        foreach ($plan as $i => [$daysAgo, $hour, $status, $payStatus, $method, $fulfillment]) {
            $placedAt = $this->today->copy()->subDays($daysAgo)->setTime($hour, ($i % 2) ? 20 : 5);
            [$name, $email, $suburb] = $buyers[$i % count($buyers)];

            $picks    = 1 + ($i % 3);
            $lines    = [];
            for ($p = 0; $p < $picks; $p++) {
                $prod    = $onlineProducts[($i + $p) % count($onlineProducts)];
                $qty     = 1 + (($i + $p) % 2);
                $lines[] = [$prod, $qty];
            }
            $subtotal = array_sum(array_map(fn ($l) => $l[0]->price * $l[1], $lines));
            $shipping = $fulfillment === 'delivery' ? 65.0 : 0.0;

            $order = Order::create([
                'tenant_id'        => $this->tid,
                'reference'        => Order::generateReference(),
                'customer_name'    => $name,
                'customer_email'   => $email,
                'customer_phone'   => $this->phone(200 + $i),
                'fulfillment_type' => $fulfillment,
                'shipping_address' => $fulfillment === 'delivery'
                    ? ['line1' => (10 + $i) . ' Kingsway Ave', 'city' => $suburb, 'province' => 'Gauteng', 'postal_code' => '2001']
                    : null,
                'status'           => $status,
                'payment_status'   => $payStatus,
                'payment_method'   => $method,
                'subtotal'         => $subtotal,
                'discount_amount'  => 0,
                'shipping_cost'    => $shipping,
                'total'            => $subtotal + $shipping,
                'paid_at'          => $placedAt->copy()->addMinutes(3),
                'fulfilled_at'     => in_array($status, ['delivered', 'shipped']) ? $placedAt->copy()->addDays(1) : null,
                'created_at'       => $placedAt,
                'updated_at'       => $placedAt,
            ]);

            foreach ($lines as [$prod, $qty]) {
                OrderItem::create([
                    'order_id'          => $order->id,
                    'product_id'        => $prod->id,
                    'product_name'      => $prod->name,
                    'product_sku'       => $prod->sku,
                    'unit_price'        => $prod->price,
                    'quantity'          => $qty,
                    'subtotal'          => $prod->price * $qty,
                ]);
            }
        }
    }

    // ── Client messaging ──────────────────────────────────────────────

    private function seedMessaging(array $customers): void
    {
        $pick = fn (string $n) => $customers[$n];

        $clientDefs = ['Thandeka Mokoena', 'Naledi Khoza', 'Palesa Motaung', 'Zinhle Ngcobo', 'Refilwe Mahlangu', 'Lindiwe Cele', 'Aphiwe Dlamini', 'Karabo Molefe'];
        $clients = [];
        foreach ($clientDefs as $name) {
            $cust = $pick($name);
            $clients[$name] = Client::create([
                'tenant_id'   => $this->tid,
                'customer_id' => $cust->id,
                'name'        => $cust->name,
                'email'       => $cust->email ?? $this->handle($name) . '@example.co.za',
                'phone'       => $cust->phone,
            ]);
        }

        $threads = [
            ['Thandeka Mokoena', [
                [true,  '-3 days', 'Your Saturday slot', 'Hi Thandeka, confirming your silk press with Nandi on Saturday at 11:00. See you then.'],
                [false, '-3 days', null, 'Perfect, thank you! Could I add a deep-conditioning treatment?'],
                [true,  '-2 days', null, 'Done — added. Total will be R680, about 2 hours in the chair.'],
            ]],
            ['Naledi Khoza', [
                [true,  '-6 days', 'Colour follow-up', 'Hi Naledi, it has been six weeks since your root touch-up. Want me to hold a slot this week?'],
                [false, '-5 days', null, 'Yes please, Thursday afternoon if you have it.'],
            ]],
            ['Palesa Motaung', [
                [false, '-1 day', 'Running late', 'Hi, stuck in traffic on Jan Smuts — I might be 10 minutes late for 11:30.'],
                [true,  '-1 day', null, 'No problem, Palesa. Nandi has you flexible until 12. Drive safe.'],
            ]],
        ];

        foreach ($threads as [$clientName, $msgs]) {
            foreach ($msgs as $i => [$fromOwner, $ago, $subject, $body]) {
                $at = Carbon::parse($ago)->setTime(9 + $i, 15 * $i);
                Communication::create([
                    'tenant_id'     => $this->tid,
                    'client_id'     => $clients[$clientName]->id,
                    'from_user_id'  => $fromOwner ? $this->ownerId : null,
                    'subject'       => $subject,
                    'body'          => $body,
                    'is_from_owner' => $fromOwner,
                    'read_at'       => $i === count($msgs) - 1 && ! $fromOwner ? null : $at->copy()->addHours(1),
                    'created_at'    => $at,
                    'updated_at'    => $at,
                ]);
            }
        }
    }

    // ── Property management: the studio Nandi sublets ────────────────

    private function seedProperty(): void
    {
        $property = Property::create([
            'tenant_id'      => $this->tid,
            'name'           => 'Marigold Studio',
            'address_line_1' => '12 Juta Street',
            'city'           => 'Braamfontein, Johannesburg',
            'province'       => 'Gauteng',
            'postal_code'    => '2001',
            'country'        => 'South Africa',
            'type'           => 'commercial',
            'description'    => 'Ground-floor salon with two private treatment rooms and four styling chairs.',
            'owner_name'     => 'Nandi Dlamini',
            'owner_email'    => 'nandi@marigold.co.za',
            'owner_phone'    => $this->phone(10),
            'is_active'      => true,
        ]);

        $unitDefs = [
            ['Room A',  'office', 'Treatment room', 3200, 'occupied', 'Zanele Khumalo',   'Nails by Zanele'],
            ['Room B',  'office', 'Treatment room', 3500, 'occupied', 'Grace Adeyemi',    'Grace Bodyworks (massage)'],
            ['Chair 3', 'other',  'Styling chair',  1800, 'occupied', 'Boitumelo Radebe', 'Freelance stylist'],
            ['Chair 4', 'other',  'Styling chair',  1800, 'vacant',   null,               null],
        ];

        foreach ($unitDefs as $i => [$number, $type, $label, $rent, $status, $renterName, $trade]) {
            $unit = Unit::create([
                'tenant_id'      => $this->tid,
                'property_id'    => $property->id,
                'unit_number'    => $number,
                'type'           => $type,
                'monthly_rent'   => $rent,
                'deposit_amount' => $rent,
                'status'         => $status,
                'notes'          => $label . ($trade ? ' — ' . $trade : ''),
            ]);

            if (! $renterName) {
                continue;
            }

            $renter = Renter::create([
                'tenant_id'              => $this->tid,
                'name'                   => $renterName,
                'email'                  => $this->handle($renterName) . '@example.co.za',
                'phone'                  => $this->phone(20 + $i),
                'emergency_contact_name' => 'Next of kin',
                'emergency_contact_phone'=> $this->phone(30 + $i),
            ]);

            $start = $this->today->copy()->subMonths(8)->startOfMonth();
            $lease = Lease::create([
                'tenant_id'      => $this->tid,
                'property_id'    => $property->id,
                'unit_id'        => $unit->id,
                'renter_id'      => $renter->id,
                'start_date'     => $start,
                'signed_date'    => $start->copy()->subDays(9),
                'end_date'       => $start->copy()->addYear(),
                'monthly_rent'   => $rent,
                'deposit_amount' => $rent,
                'deposit_paid'   => true,
                'status'         => 'active',
            ]);

            // Rent history: 8 settled months, then the current month.
            for ($m = 8; $m >= 1; $m--) {
                $period = $this->today->copy()->subMonths($m);
                RentPayment::create([
                    'tenant_id'      => $this->tid,
                    'lease_id'       => $lease->id,
                    'renter_id'      => $renter->id,
                    'unit_id'        => $unit->id,
                    'period'         => $period->format('Y-m'),
                    'amount_due'     => $rent,
                    'amount_paid'    => $rent,
                    'status'         => 'paid',
                    'due_date'       => $period->copy()->startOfMonth()->addDays(4),
                    'paid_date'      => $period->copy()->startOfMonth()->addDays(2 + ($m % 3)),
                    'payment_method' => 'eft',
                    'reference'      => 'RCT-' . strtoupper($period->format('ym')) . '-' . str_pad((string) $unit->id, 2, '0', STR_PAD_LEFT),
                ]);
            }

            $thisPeriod = $this->today->copy();
            $due        = $thisPeriod->copy()->startOfMonth()->addDays(4);

            // Room A (Zanele): almost settled — R450 still outstanding. This is the
            // balance the intro video clears to R0.
            // Room B (Grace): paid in full. Chair 3 (Boitumelo): nothing in yet.
            if ($number === 'Room A') {
                $duePart = ['amount_paid' => $rent - 450, 'status' => 'partial', 'paid_date' => null, 'reference' => null];
            } elseif ($number === 'Room B') {
                $duePart = ['amount_paid' => $rent, 'status' => 'paid', 'paid_date' => $due->copy()->subDay(),
                            'reference' => 'RCT-' . strtoupper($thisPeriod->format('ym')) . '-' . str_pad((string) $unit->id, 2, '0', STR_PAD_LEFT)];
            } else {
                $duePart = ['amount_paid' => 0, 'status' => 'pending', 'paid_date' => null, 'reference' => null];
            }

            RentPayment::create(array_merge([
                'tenant_id'      => $this->tid,
                'lease_id'       => $lease->id,
                'renter_id'      => $renter->id,
                'unit_id'        => $unit->id,
                'period'         => $thisPeriod->format('Y-m'),
                'amount_due'     => $rent,
                'due_date'       => $due,
                'payment_method' => 'eft',
            ], $duePart));
        }
    }

    // ── Helpers ────────────────────────────────────────────────────

    private function handle(string $name): string
    {
        $parts = preg_split('/\s+/', strtolower(trim($name)));
        return $parts[0] . '.' . substr($parts[count($parts) - 1], 0, 1);
    }

    private function phone(int $n): string
    {
        return sprintf('+27 %02d %03d %04d', 60 + ($n % 20), 100 + ($n % 900), 1000 + (($n * 37) % 9000));
    }
}
