<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Http\Requests\StorequoteRequest;
use App\Http\Requests\UpdatequoteRequest;
use App\Models\QuoteItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;



class QuoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index()
    {
        $company = auth()->user()->currentCompany;

        if (!$company) {
            return view('quotes.index', ['quotes' => collect()]);
        }

        $quotes = Quote::where('company_id', $company->id)->latest()->get();

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
        $quote = Quote::with('items', 'client')->findOrFail($id);
        return view('quotes.show', compact('quote'));
    }

    public function edit($id)
    {
        $quote = Quote::with('items')->findOrFail($id);

        $this->authorize('update', $quote);

        $company = auth()->user()->currentCompany;

        $clients = $company
            ? Client::where('company_id', $company->id)->select('id', 'name')->get()
            : collect();

        $products = Product::with(['items', 'prices'])->get()->map(function ($product) {
            $price = $product->prices->first();
            return [
                'id'         => $product->id,
                'name'       => $product->name,
                'price_type' => $price->pricing_type ?? 'fixed',
                'base_price' => (float) ($price->price ?? 0),
                'min_price'  => (float) ($price->min_price ?? 0),
                'max_price'  => (float) ($price->max_price ?? 0),
                'items'      => $product->items->map(fn($item) => [
                    'product_item_id' => $item->id,
                    'name'            => $item->name,
                    'description'     => $item->description ?? '',
                    'is_included'     => (bool) $item->is_included,
                    'price'           => (float) ($item->price ?? 0),
                ])->values(),
            ];
        });

        return view('quotes.edit', compact('quote', 'clients', 'products'));
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
        $quote = Quote::findOrFail($id);

        $this->authorize('update', $quote);

        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'payload'   => ['required', 'json'],
        ]);

        $data = json_decode($validated['payload'], true);

        if (!is_array($data)) {
            return back()->withErrors(['payload' => 'Invalid quote payload.'])->withInput();
        }

        $products = is_array($data['products'] ?? null) ? $data['products'] : [];
        $custom   = is_array($data['custom'] ?? null)   ? $data['custom']   : [];

        if (empty($products) && empty($custom)) {
            return back()->withErrors(['quote' => 'Please add at least one item.'])->withInput();
        }

        return DB::transaction(function () use ($quote, $validated, $products, $custom) {

            $quote->items()->delete();

            $itemsToInsert = [];

            foreach ($products as $product) {
                $productId = (int) ($product['product_id'] ?? 0);
                if (!$productId) continue;

                $basePrice = max((float) ($product['base_price'] ?? 0), 0);

                if ($basePrice > 0) {
                    $itemsToInsert[] = [
                        'quote_id'     => $quote->id,
                        'product_id'   => $productId,
                        'product_item' => null,
                        'description'  => $product['name'] ?? 'Product',
                        'quantity'     => 1,
                        'unit_price'   => $basePrice,
                        'vat_amount'   => $basePrice * 0.15,
                        'total'        => $basePrice,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];
                }

                foreach ($product['items'] ?? [] as $item) {
                    if (!($item['selected'] ?? false)) continue;
                    $qty      = max((float) ($item['qty'] ?? 1), 1);
                    $price    = max((float) ($item['price'] ?? 0), 0);
                    $included = (bool) ($item['is_included'] ?? false);
                    $lineTotal = $included ? 0 : ($qty * $price);
                    $itemsToInsert[] = [
                        'quote_id'     => $quote->id,
                        'product_id'   => $productId,
                        'product_item' => $item['product_item_id'] ?? null,
                        'description'  => $item['name'] ?? 'Item',
                        'quantity'     => $qty,
                        'unit_price'   => $included ? 0 : $price,
                        'vat_amount'   => $lineTotal * 0.15,
                        'total'        => $lineTotal,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];
                }
            }

            foreach ($custom as $customItem) {
                $name = trim($customItem['name'] ?? '');
                if (!$name) continue;
                $qty       = max((float) ($customItem['quantity'] ?? 1), 1);
                $unitPrice = max((float) ($customItem['unit_price'] ?? 0), 0);
                $lineTotal = $qty * $unitPrice;
                $itemsToInsert[] = [
                    'quote_id'     => $quote->id,
                    'product_id'   => null,
                    'product_item' => null,
                    'description'  => $customItem['description'] ?? 'Custom Item',
                    'quantity'     => $qty,
                    'unit_price'   => $unitPrice,
                    'vat_amount'   => $lineTotal * 0.15,
                    'total'        => $lineTotal,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
            }

            if (empty($itemsToInsert)) {
                return back()->withErrors(['quote' => 'No valid items were generated.'])->withInput();
            }

            QuoteItem::insert($itemsToInsert);

            $subtotal  = collect($itemsToInsert)->sum('total');
            $vatAmount = collect($itemsToInsert)->sum('vat_amount');

            $quote->update([
                'client_id' => $validated['client_id'],
                'subtotal'  => round($subtotal, 2),
                'vat'       => round($vatAmount, 2),
                'total'     => round($subtotal + $vatAmount, 2),
            ]);

            return redirect()->route('quotes.show', $quote->id)->with('success', 'Quote updated.');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS ACTIONS
    |--------------------------------------------------------------------------
    */

    public function download($id)
    {
        $quote = Quote::with('items', 'client', 'company')->findOrFail($id);
        $this->authorize('view', $quote);

        $pdf = Pdf::loadView('pdf.quote', compact('quote'))
            ->setPaper('a4', 'portrait');

        return $pdf->download($quote->quote_number . '.pdf');
    }

    public function submit($id)
    {
        $quote = Quote::findOrFail($id);

        $this->authorize('update', $quote);

        if ($quote->source !== 'client') {
            abort(403);
        }

        $quote->update(['status' => 'submitted']);

        return back();
    }

    public function send($id)
    {
        $quote = Quote::findOrFail($id);

        $this->authorize('update', $quote);

        $quote->update(['status' => 'sent']);

        return back();
    }

    public function approve($id)
    {
        $quote = Quote::findOrFail($id);

        $this->authorize('update', $quote);

        $quote->update(['status' => 'approved']);

        return back();
    }

    public function reject($id)
    {
        $quote = Quote::findOrFail($id);

        $this->authorize('update', $quote);

        $quote->update(['status' => 'rejected']);

        return back();
    }

    /*
    |--------------------------------------------------------------------------
    | PRIVATE METHODS (THIS IS WHAT MAKES IT CLEAN)
    |--------------------------------------------------------------------------
    */


private function storeItems($quote, $items = [])
{
    $stored = [];

    foreach ($items as $item) {

        $qty = (float) ($item['quantity'] ?? 1);

        $price = (float) ($item['unit_price'] ?? 0);

        $total = $qty * $price;

        $stored[] = QuoteItem::create([
            'quote_id'     => $quote->id,
            'product_id'   => $item['product_id'] ?? null,
            'product_item' => $item['product_item_id'] ?? null,
            'description'  => $item['name'] ?? ($item['description'] ?? null),
            'quantity'     => $qty,
            'unit_price'   => $price,
            'vat_amount'   => $total * 0.15,
            'total'        => $total,
        ]);
    }

    return collect($stored);
}

    private function replaceItems($quote, $items)
    {
        $quote->items()->delete();
        return $this->storeItems($quote, $items);
    }


    private function calculateTotals($items)
    {
        $subtotal = collect($items)->sum('total');
        $vat      = collect($items)->sum('vat_amount');
        $total    = $subtotal + $vat;

        return [
            'subtotal' => round($subtotal, 2),
            'vat'      => round($vat, 2),
            'total'    => round($total, 2),
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

    $company = auth()->user()?->currentCompany;

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
                            'quote_id'    => $quote->id,
                            'product_id'  => $productId,
                            'product_item' => null,
                            'description' => $product['name'] ?? 'Product',
                            'quantity'    => 1,
                            'unit_price'  => $basePrice,
                            'vat_amount'  => $basePrice * 0.15,
                            'total'       => $basePrice,
                            'created_at'  => now(),
                            'updated_at'  => now(),
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

                            $lineTotal = $included ? 0 : ($qty * $price);

                            $itemsToInsert[] = [
                                'quote_id'     => $quote->id,
                                'product_id'   => $productId,
                                'product_item' => $itemId,
                                'description'  => $item['name'] ?? 'Item',
                                'quantity'     => $qty,
                                'unit_price'   => $included ? 0 : $price,
                                'vat_amount'   => $lineTotal * 0.15,
                                'total'        => $lineTotal,
                                'created_at'   => now(),
                                'updated_at'   => now(),
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

                    $lineTotal = $qty * $unitPrice;

                    $itemsToInsert[] = [
                        'quote_id'     => $quote->id,
                        'product_id'   => null,
                        'product_item' => null,
                        'description'  => $customItem['description'] ?? 'Custom Item',
                        'quantity'     => $qty,
                        'unit_price'   => $unitPrice,
                        'vat_amount'   => $lineTotal * 0.15,
                        'total'        => $lineTotal,
                        'created_at'   => now(),
                        'updated_at'   => now(),
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

            QuoteItem::insert($itemsToInsert);

            /*
            |--------------------------------------------------------------------------
            | TOTALS
            |--------------------------------------------------------------------------
            */

            $subtotal    = collect($itemsToInsert)->sum('total');
            $vatAmount   = collect($itemsToInsert)->sum('vat_amount');
            $totalAmount = $subtotal + $vatAmount;

            $quote->update([
                'subtotal' => round($subtotal, 2),
                'vat'      => round($vatAmount, 2),
                'total'    => round($totalAmount, 2),
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
