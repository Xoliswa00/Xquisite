<?php

namespace App\Http\Controllers;

use App\Models\quote;
use App\Http\Requests\StorequoteRequest;
use App\Http\Requests\UpdatequoteRequest;
use App\Models\quote_items;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Notifications\QuoteStatusNotification;
use App\Notifications\ClientQuoteSentNotification;



class QuoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index()
    {
 
                    $quotes = \App\Models\Quote::where('company_id', auth()->user()->managedCompanies->first()->id)->latest()->get();



        return view('quotes.index', compact('quotes'));
    }

   /*
    |--------------------------------------------------------------------------
    | INDEX / VIEW
    |--------------------------------------------------------------------------
    */


        private function generateQuoteNumber(): string
{
    $year = date('Y');

    $count = Quote::whereYear('created_at', $year)->count() + 1;

    return 'QT-' . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
}
public function create()
{
    $clients = Client::select('id','name')->get();

    $products = Product::with(['items', 'prices'])->get()->map(function ($product) {

        $price = $product->prices->first();

        return [
            'id' => $product->id,
            'name' => $product->name,

            // PRICE MODEL (SAFE DEFAULTS)
            'price_type' => $price->pricing_type ?? 'fixed',

            'base_price' => (float) ($price->price ?? 0),

            'min_price' => (float) ($price->min_price ?? 0),
            'max_price' => (float) ($price->max_price ?? 0),

            // ITEMS (STRICT STRUCTURE)
            'items' => $product->items->map(function ($item) {

                return [
                    'product_item_id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description ?? '',

                    'is_included' => (bool) $item->is_included,

                    'price' => (float) ($item->price ?? 0),
                ];
            })->values(),
        ];
    });

    return view('quotes.create', compact('clients', 'products'));
}



    public function show($id)
    {
        $quote = Quote::with('items')->findOrFail($id);
        return view('quotes.show', compact('quote'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE (ENTRY POINT)
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {

            $quote = Quote::findOrFail($id);

            $this->replaceItems($quote, $request->items);

            $totals = $this->calculateTotals($quote->items);

            $quote->update($totals);

            return back();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS ACTIONS
    |--------------------------------------------------------------------------
    */

    public function submit($id)
    {
        $quote = Quote::findOrFail($id);

        if ($quote->source !== 'client') {
            abort(403);
        }

        $quote->update(['status' => 'submitted']);

        return back();
    }

    public function send($id)
    {
        $quote = Quote::with('client.user')->findOrFail($id);

        $quote->update(['status' => 'sent']);

        $companyName = auth()->user()->managedCompanies->first()->name ?? config('app.name');

        // Notify owner dashboard
        auth()->user()->notify(new QuoteStatusNotification(
            quoteNumber: $quote->quote_number,
            status:      'sent',
            clientName:  optional($quote->client)->name ?? 'Client',
            quoteId:     $quote->id,
        ));

        // Notify the client
        if ($quote->client && $quote->client->user) {
            $quote->client->user->notify(new ClientQuoteSentNotification(
                quoteNumber: $quote->quote_number,
                companyName: $companyName,
                total:       (float) $quote->total,
                quoteId:     $quote->id,
            ));
        }

        return back();
    }

    public function approve($id)
    {
        $quote = Quote::with('client')->findOrFail($id);

        $quote->update(['status' => 'approved']);

        auth()->user()->notify(new QuoteStatusNotification(
            quoteNumber: $quote->quote_number,
            status:      'approved',
            clientName:  optional($quote->client)->name ?? 'Client',
            quoteId:     $quote->id,
        ));

        return back();
    }

    public function reject($id)
    {
        $quote = Quote::with('client')->findOrFail($id);

        $quote->update(['status' => 'rejected']);

        auth()->user()->notify(new QuoteStatusNotification(
            quoteNumber: $quote->quote_number,
            status:      'rejected',
            clientName:  optional($quote->client)->name ?? 'Client',
            quoteId:     $quote->id,
        ));

        return back();
    }

    /*
    |--------------------------------------------------------------------------
    | PRIVATE METHODS (THIS IS WHAT MAKES IT CLEAN)
    |--------------------------------------------------------------------------
    */

    private function createQuoteHeader($request)
    {
        $isClient = $request->mode === 'client';
                    $company = auth()->user()->managedCompanies->first();


        return Quote::create([
            'company_id' => $company->id,
            'client_id' => $isClient
                ? auth()->user()->client_id
                : $request->client_id,

            'quote_number' => 'QT-' . now()->timestamp,

            'source' => $isClient ? 'client' : 'internal',
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);
    }

private function storeItems($quote, $items = [])
{
    $stored = [];

    foreach ($items as $item) {

        $qty = (float) ($item['quantity'] ?? 1);

        $price = (float) ($item['unit_price'] ?? 0);

        $total = $qty * $price;

        $stored[] = quote_items::create([
            'quote_id' => $quote->id,

            'product_id' =>
                $item['product_id'] ?? null,

            'product_item_id' =>
                $item['product_item_id'] ?? null,

            'name' => $item['name'],

            'description' =>
                $item['description'] ?? null,

            'quantity' => $qty,

            'unit_price' => $price,

            'total_price' => $total,
        ]);
    }

    return collect($stored);
}

    private function replaceItems($quote, $items)
    {
        $quote->items()->delete();
        return $this->storeItems($quote, $items);
    }

    private function calculateItemTotal($item)
    {
        switch ($item['pricing_type'] ?? 'fixed') {

            case 'fixed':
                return $item['unit_price'];

            case 'per_item':
            case 'hourly':
                return $item['quantity'] * $item['unit_price'];

            case 'range':
                return ($item['selected_value'] ?? 0) * $item['unit_price'];

            case 'custom':
                return $item['total_price'] ?? 0;

            default:
                return 0;
        }
    }

    private function calculateTotals($items)
    {
        $subtotal = collect($items)->sum('total_price');
        $vat = $subtotal * 0.15;
        $total = $subtotal + $vat;

        return [
            'subtotal' => $subtotal,
            'vat' => $vat,
            'total' => $total,
        ];
    }

    public function store(Request $request)
{
    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    $validated = $request->validate([
        'client_id' => ['required', 'exists:clients,id'],
        'payload'   => ['required', 'json'],
    ]);

    /*
    |--------------------------------------------------------------------------
    | DECODE PAYLOAD
    |--------------------------------------------------------------------------
    */

    $data = json_decode($validated['payload'], true);

    if (!is_array($data)) {

        return back()
            ->withErrors([
                'payload' => 'Invalid quote payload.'
            ])
            ->withInput();
    }

    /*
    |--------------------------------------------------------------------------
    | COMPANY
    |--------------------------------------------------------------------------
    */

    $company = auth()->user()?->managedCompanies?->first();

    if (!$company) {

        return back()
            ->withErrors([
                'system' => 'No managed company linked to your account.'
            ])
            ->withInput();
    }

    /*
    |--------------------------------------------------------------------------
    | NORMALIZE
    |--------------------------------------------------------------------------
    */

    $products = is_array($data['products'] ?? null)
        ? $data['products']
        : [];

    $custom = is_array($data['custom'] ?? null)
        ? $data['custom']
        : [];

    if (empty($products) && empty($custom)) {

        return back()
            ->withErrors([
                'quote' => 'Please add at least one product or custom item.'
            ])
            ->withInput();
    }

    /*
    |--------------------------------------------------------------------------
    | TRANSACTION
    |--------------------------------------------------------------------------
    */

    try {

        return DB::transaction(function () use (
            $validated,
            $company,
            $products,
            $custom
        ) {

            /*
            |--------------------------------------------------------------------------
            | CREATE QUOTE
            |--------------------------------------------------------------------------
            */

            $quote = Quote::create([
                'client_id'    => $validated['client_id'],
                'company_id'   => $company->id,
                'quote_number' => $this->generateQuoteNumber(),
                'status'       => 'draft',
                'subtotal'     => 0,
                'vat'   => 0,
                'total' => 0,
            ]);

            $itemsToInsert = [];

            /*
            |--------------------------------------------------------------------------
            | PRODUCTS
            |--------------------------------------------------------------------------
            */

            foreach ($products as $product) {

                try {

                    /*
                    |--------------------------------------------------------------------------
                    | SAFE PRODUCT ID
                    |--------------------------------------------------------------------------
                    */

                    $productId = (int) ($product['product_id'] ?? 0);

                    if (!$productId) {
                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | VERIFY PRODUCT EXISTS
                    |--------------------------------------------------------------------------
                    */

                    $productModel = Product::find($productId);

                    if (!$productModel) {
                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | SAFE BASE PRICE
                    |--------------------------------------------------------------------------
                    */

                    $basePrice = max(
                        (float) ($product['base_price'] ?? 0),
                        0
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | BASE PRODUCT LINE
                    |--------------------------------------------------------------------------
                    */

                    if ($basePrice > 0) {

                        $itemsToInsert[] = [
                            'quote_id'        => $quote->id,
                            'product_id'      => $productId,
                            'product_item' => null,
                            'description'     => $product['name'] ?? 'Product',
                            'quantity'        => 1,
                            'unit_price'      => $basePrice,
                            'total'     => $basePrice*1.15,
                            'created_at'      => now(),
                            'updated_at'      => now(),
                            'vat_amount'      => $basePrice * 0.15,
                            
                        ];
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | PRODUCT ITEMS
                    |--------------------------------------------------------------------------
                    */

                    $items = is_array($product['items'] ?? null)
                        ? $product['items']
                        : [];

                    foreach ($items as $item) {

                        try {

                            /*
                            |--------------------------------------------------------------------------
                            | ONLY SELECTED
                            |--------------------------------------------------------------------------
                            */

                            if (!($item['selected'] ?? false)) {
                                continue;
                            }

                            $qty = max(
                                (float) ($item['qty'] ?? 1),
                                1
                            );

                            $price = max(
                                (float) ($item['price'] ?? 0),
                                0
                            );

                            $included = (bool) (
                                $item['is_included'] ?? false
                            );

                            $itemId = $item['product_item_id'] ?? null;

                            $itemsToInsert[] = [
                                'quote_id'        => $quote->id,
                                'product_id'      => $productId,
                                'product_item' => $itemId,
                                'description'     => $item['name'] ?? 'Item',
                                'quantity'        => $qty,
                                'unit_price'      => $included ? 0 : $price,
                                'vat_amount'      => $included ? 0 : ($qty * $price) * 0.15,
                                'total'     => $included ? 0 : ($qty * $price) * 1.15,
                                'created_at'      => now(),
                                'updated_at'      => now(),
                            ];

                        } catch (\Throwable $e) {

                            logger()->warning(
                                'Quote item skipped',
                                [
                                    'item' => $item,
                                    'error' => $e->getMessage()
                                ]
                            );

                            continue;
                        }
                    }

                } catch (\Throwable $e) {

                    logger()->warning(
                        'Quote product skipped',
                        [
                            'product' => $product,
                            'error' => $e->getMessage()
                        ]
                    );

                    continue;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | CUSTOM ITEMS
            |--------------------------------------------------------------------------
            */

            foreach ($custom as $customItem) {

                try {

                    $name = trim(
                        $customItem['name'] ?? ''
                    );

                    if (!$name) {
                        continue;
                    }

                    $qty = max(
                        (float) ($customItem['quantity'] ?? 1),
                        1
                    );

                    $unitPrice = max(
                        (float) ($customItem['unit_price'] ?? 0),
                        0
                    );

                    $itemsToInsert[] = [
                        'quote_id'        => $quote->id,
                        'product_id'      => null,
                        'product_item' => null,
                        'description'     => $customItem['description'] ?? 'Custom Item',
                        'quantity'        => $qty,
                        'unit_price'      => $unitPrice,
                        'vat_amount'      => ($qty * $unitPrice) * 0.15,
                        'total'     => $qty * $unitPrice,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ];

                } catch (\Throwable $e) {

                    logger()->warning(
                        'Custom item skipped',
                        [
                            'item' => $customItem,
                            'error' => $e->getMessage()
                        ]
                    );

                    continue;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | GUARD
            |--------------------------------------------------------------------------
            */

            if (empty($itemsToInsert)) {

                return back()
                    ->withErrors([
                        'quote' => 'No valid quote items were generated.'
                    ])
                    ->withInput();
            }

            /*
            |--------------------------------------------------------------------------
            | INSERT ITEMS
            |--------------------------------------------------------------------------
            */

            quote_items::insert($itemsToInsert);

            /*
            |--------------------------------------------------------------------------
            | TOTALS
            |--------------------------------------------------------------------------
            */

            $subtotal = collect($itemsToInsert)
                ->sum('total');

            $vatRate = 0.15;

            $vatAmount = $subtotal * $vatRate;

            $totalAmount = $subtotal + $vatAmount;

            $quote->update([
                'subtotal'     => round($subtotal, 2),
                'vat'   => round($vatAmount, 2),
                'total' => round($totalAmount, 2),
            ]);

            /*
            |--------------------------------------------------------------------------
            | SUCCESS
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('quotes.show', $quote->id)
                ->with(
                    'success',
                    'Quote created successfully.'
                );
        });

    } catch (\Throwable $e) {

        report($e);

        return back()
            ->withErrors([
                'system' => 'Unable to create quote right now.'
            ])
            ->withInput();
    }
}


}
