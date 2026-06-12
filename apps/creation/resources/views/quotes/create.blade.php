<x-app-layout>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">
                    Create Quote
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Build enterprise quotes with products, optional items, and custom billing lines.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto p-6">

        {{-- GLOBAL ERRORS --}}
        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-300 bg-red-50 p-5">

                <div class="flex items-center gap-2 mb-3">
                    <div class="w-3 h-3 bg-red-500 rounded-full"></div>

                    <h3 class="font-bold text-red-800">
                        Quote could not be saved
                    </h3>
                </div>

                <ul class="space-y-1 text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>
                            • {{ $error }}
                        </li>
                    @endforeach
                </ul>

            </div>
        @endif

        {{-- SUCCESS --}}
        @if (session('success'))
            <div class="mb-6 rounded-xl border border-green-300 bg-green-50 p-4 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('quotes.store') }}"
            id="quoteForm"
        >
            @csrf

            {{-- CLIENT --}}
            <div class="bg-white shadow-sm border rounded-2xl p-6 mb-6">

                <div class="mb-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        Client Information
                    </h3>

                    <p class="text-sm text-gray-500">
                        Select the customer receiving this quote.
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">
                        Client
                    </label>

                    <select
                        name="client_id"
                        class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-black focus:border-black"
                        required
                    >
                        <option value="">
                            Select Client
                        </option>

                        @foreach($clients as $client)
                            <option
                                value="{{ $client->id }}"
                                {{ old('client_id') == $client->id ? 'selected' : '' }}
                            >
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('client_id')
                        <div class="text-red-600 text-sm mt-2">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

            </div>

            {{-- PRODUCT ADDER --}}
            <div class="bg-white shadow-sm border rounded-2xl p-6 mb-6">

                <div class="mb-4">
                    <h3 class="text-lg font-bold">
                        Add Products & Services
                    </h3>

                    <p class="text-sm text-gray-500">
                        Add predefined services and configure optional billing items.
                    </p>
                </div>

                <div class="flex gap-3">

                    <select
                        id="productSelect"
                        class="w-full border-gray-300 rounded-xl shadow-sm"
                    >
                        <option value="">
                            Select Product / Service
                        </option>

                        @foreach($products as $p)
                            <option value="{{ $p['id'] }}">
                                {{ $p['name'] }}
                                —
                                R {{ number_format($p['base_price'] ?? 0, 2) }}
                            </option>
                        @endforeach
                    </select>

                    <button
                        type="button"
                        onclick="addProduct()"
                        class="bg-black hover:bg-gray-800 text-white px-6 rounded-xl"
                    >
                        Add
                    </button>

                </div>

            </div>

            {{-- QUOTE BUILDER --}}
            <div class="bg-white shadow-sm border rounded-2xl p-6 mb-6">

                <div class="flex items-center justify-between mb-5">

                    <div>
                        <h3 class="text-lg font-bold">
                            Quote Builder
                        </h3>

                        <p class="text-sm text-gray-500">
                            Configure pricing, quantities and optional modules.
                        </p>
                    </div>

                    <div class="text-right">
                        <div class="text-xs text-gray-400 uppercase tracking-wide">
                            Live Quote Value
                        </div>

                        <div class="text-2xl font-bold text-black">
                            R <span id="total">0.00</span>
                        </div>
                    </div>

                </div>

                <div id="builder"></div>

                <div
                    id="emptyState"
                    class="border-2 border-dashed border-gray-300 rounded-2xl p-10 text-center text-gray-500"
                >
                    No products added yet.
                </div>

            </div>

            {{-- CUSTOM ITEMS --}}
            <div class="bg-white shadow-sm border rounded-2xl p-6 mb-6">

                <div class="mb-4">
                    <h3 class="text-lg font-bold">
                        Custom Billing Items
                    </h3>

                    <p class="text-sm text-gray-500">
                        Add one-off services, adjustments, consultation fees or manual charges.
                    </p>
                </div>

                <div class="grid grid-cols-12 gap-3 mb-4">

                    <div class="col-span-4">
                        <input
                            id="cName"
                            type="text"
                            placeholder="Item Name"
                            class="w-full border-gray-300 rounded-xl"
                        >
                    </div>

                    <div class="col-span-4">
                        <input
                            id="cDescription"
                            type="text"
                            placeholder="Description"
                            class="w-full border-gray-300 rounded-xl"
                        >
                    </div>

                    <div class="col-span-2">
                        <input
                            id="cPrice"
                            type="number"
                            step="0.01"
                            placeholder="Price"
                            class="w-full border-gray-300 rounded-xl"
                        >
                    </div>

                    <div class="col-span-2">
                        <button
                            type="button"
                            onclick="addCustom()"
                            class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-xl"
                        >
                            Add Item
                        </button>
                    </div>

                </div>

                <div id="customList"></div>

            </div>

            {{-- SUMMARY --}}
            <div class="bg-black text-white rounded-2xl p-6 mb-6">

                <div class="grid grid-cols-3 gap-6">

                    <div>
                        <div class="text-sm text-gray-300">
                            Subtotal
                        </div>

                        <div class="text-2xl font-bold">
                            R <span id="subtotal">0.00</span>
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-300">
                            VAT (15%)
                        </div>

                        <div class="text-2xl font-bold">
                            R <span id="vat">0.00</span>
                        </div>
                    </div>

                    <div class="text-right">
                        <div class="text-sm text-gray-300">
                            Grand Total
                        </div>

                        <div class="text-3xl font-extrabold">
                            R <span id="grandTotal">0.00</span>
                        </div>
                    </div>

                </div>

            </div>

            {{-- PAYLOAD --}}
            <input
                type="hidden"
                name="payload"
                id="payload"
            >

            <div class="flex justify-end">
                <button
                    type="submit"
                    class="bg-black hover:bg-gray-800 text-white px-8 py-3 rounded-xl font-semibold"
                >
                    Save Quote
                </button>
            </div>

        </form>

    </div>

<script>

const products = @json($products);

let state = {
    products: [],
    custom: []
};

const money = (v) => parseFloat(v || 0);

/*
|--------------------------------------------------------------------------
| ADD PRODUCT
|--------------------------------------------------------------------------
*/
function addProduct()
{
    const select = document.getElementById('productSelect');

    const id = select.value;

    if (!id) {
        alert('Please select a product.');
        return;
    }

    const product = products.find(p => p.id == id);

    if (!product) {
        alert('Selected product could not be found.');
        return;
    }

    state.products.push({

        product_id: product.id,

        name: product.name,

        base_price: money(product.base_price),

        items: (product.items || []).map(item => ({

            product_item_id: item.product_item_id ?? null,

            name: item.name ?? 'Item',

            description: item.description ?? '',

            qty: 1,

            price: money(item.price),

            selected: item.is_included ? 1 : 0,

            is_included: item.is_included ? 1 : 0
        }))
    });

    select.value = '';

    render();
}

/*
|--------------------------------------------------------------------------
| REMOVE PRODUCT
|--------------------------------------------------------------------------
*/
function removeProduct(index)
{
    state.products.splice(index, 1);
    render();
}

/*
|--------------------------------------------------------------------------
| ADD CUSTOM
|--------------------------------------------------------------------------
*/
function addCustom()
{
    const name = document.getElementById('cName').value.trim();

    const description = document.getElementById('cDescription').value.trim();

    const price = document.getElementById('cPrice').value;

    if (!name) {
        alert('Custom item name is required.');
        return;
    }

    state.custom.push({
        name,
        description,
        qty: 1,
        unit_price: money(price)
    });

    document.getElementById('cName').value = '';
    document.getElementById('cDescription').value = '';
    document.getElementById('cPrice').value = '';

    render();
}

/*
|--------------------------------------------------------------------------
| REMOVE CUSTOM
|--------------------------------------------------------------------------
*/
function removeCustom(index)
{
    state.custom.splice(index, 1);
    render();
}

/*
|--------------------------------------------------------------------------
| RENDER
|--------------------------------------------------------------------------
*/
function render()
{
    renderProducts();

    renderCustom();

    calcTotals();

    syncPayload();

    document.getElementById('emptyState').style.display =
        state.products.length ? 'none' : 'block';
}

/*
|--------------------------------------------------------------------------
| PRODUCTS
|--------------------------------------------------------------------------
*/
function renderProducts()
{
    let html = '';

    state.products.forEach((product, pi) => {

        let productTotal = money(product.base_price);

        html += `
        <div class="border rounded-2xl p-5 mb-5">

            <div class="flex justify-between items-start mb-5">

                <div>
                    <h3 class="font-bold text-lg">
                        ${product.name}
                    </h3>

                    <div class="text-sm text-gray-500 mt-1">
                        Base Product Price
                    </div>
                </div>

                <div class="text-right">

                    <div class="text-2xl font-bold">
                        R ${money(product.base_price).toFixed(2)}
                    </div>

                    <button
                        type="button"
                        onclick="removeProduct(${pi})"
                        class="text-red-600 text-sm mt-2"
                    >
                        Remove
                    </button>

                </div>

            </div>
        `;

        product.items.forEach((item, ii) => {

            const lineTotal =
                (!item.is_included && item.selected)
                    ? money(item.qty) * money(item.price)
                    : 0;

            productTotal += lineTotal;

            html += `
            <div class="grid grid-cols-12 gap-4 border-t py-4 items-center">

                <div class="col-span-4">

                    <div class="font-medium">
                        ${item.name}
                    </div>

                    <div class="text-xs text-gray-500">
                        ${item.description || ''}
                    </div>

                </div>

                <div class="col-span-2 text-center">

                    ${
                        item.is_included
                        ? `
                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">
                                Included
                            </span>
                        `
                        : `
                            <label class="inline-flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    ${item.selected ? 'checked' : ''}
                                    onchange="toggle(${pi}, ${ii}, this.checked)"
                                >

                                <span class="text-sm">
                                    Add
                                </span>
                            </label>
                        `
                    }

                </div>

                <div class="col-span-2">

                    <input
                        type="number"
                        min="1"
                        value="${item.qty}"
                        class="w-full border rounded-xl"
                        onchange="qty(${pi}, ${ii}, this.value)"
                    >

                </div>

                <div class="col-span-2">

                    <input
                        type="number"
                        step="0.01"
                        value="${item.price}"
                        class="w-full border rounded-xl"
                        onchange="price(${pi}, ${ii}, this.value)"
                    >

                </div>

                <div class="col-span-2 text-right font-bold">

                    R ${lineTotal.toFixed(2)}

                </div>

            </div>
            `;
        });

        html += `
            <div class="border-t pt-4 mt-4 text-right">

                <div class="text-sm text-gray-500">
                    Product Total
                </div>

                <div class="text-2xl font-bold">
                    R ${productTotal.toFixed(2)}
                </div>

            </div>

        </div>
        `;
    });

    document.getElementById('builder').innerHTML = html;
}

/*
|--------------------------------------------------------------------------
| CUSTOM ITEMS
|--------------------------------------------------------------------------
*/
function renderCustom()
{
    let html = '';

    state.custom.forEach((item, index) => {

        const total = money(item.qty) * money(item.unit_price);

        html += `
        <div class="grid grid-cols-12 gap-3 border rounded-xl p-4 mb-3">

            <div class="col-span-4">
                <div class="font-medium">
                    ${item.name}
                </div>

                <div class="text-xs text-gray-500">
                    ${item.description || ''}
                </div>
            </div>

            <div class="col-span-2">

                <input
                    type="number"
                    min="1"
                    value="${item.qty}"
                    class="w-full border rounded-xl"
                    onchange="customQty(${index}, this.value)"
                >

            </div>

            <div class="col-span-3">

                <input
                    type="number"
                    step="0.01"
                    value="${item.unit_price}"
                    class="w-full border rounded-xl"
                    onchange="customPrice(${index}, this.value)"
                >

            </div>

            <div class="col-span-2 text-right font-bold">
                R ${total.toFixed(2)}
            </div>

            <div class="col-span-1 text-right">

                <button
                    type="button"
                    onclick="removeCustom(${index})"
                    class="text-red-600"
                >
                    ✕
                </button>

            </div>

        </div>
        `;
    });

    document.getElementById('customList').innerHTML = html;
}

/*
|--------------------------------------------------------------------------
| TOTALS
|--------------------------------------------------------------------------
*/
function calcTotals()
{
    let subtotal = 0;

    state.products.forEach(product => {

        subtotal += money(product.base_price);

        product.items.forEach(item => {

            if (!item.is_included && item.selected) {

                subtotal +=
                    money(item.qty) * money(item.price);
            }
        });
    });

    state.custom.forEach(item => {

        subtotal +=
            money(item.qty) * money(item.unit_price);
    });

    const vat = subtotal * 0.15;

    const grand = subtotal + vat;

    document.getElementById('subtotal').innerText =
        subtotal.toFixed(2);

    document.getElementById('vat').innerText =
        vat.toFixed(2);

    document.getElementById('grandTotal').innerText =
        grand.toFixed(2);

    document.getElementById('total').innerText =
        grand.toFixed(2);
}

/*
|--------------------------------------------------------------------------
| PAYLOAD
|--------------------------------------------------------------------------
*/
function syncPayload()
{
    document.getElementById('payload').value =
        JSON.stringify(state);
}

/*
|--------------------------------------------------------------------------
| ACTIONS
|--------------------------------------------------------------------------
*/
function toggle(pi, ii, value)
{
    state.products[pi].items[ii].selected =
        value ? 1 : 0;

    render();
}

function qty(pi, ii, value)
{
    state.products[pi].items[ii].qty =
        money(value);

    render();
}

function price(pi, ii, value)
{
    state.products[pi].items[ii].price =
        money(value);

    render();
}

function customQty(index, value)
{
    state.custom[index].qty =
        money(value);

    render();
}

function customPrice(index, value)
{
    state.custom[index].unit_price =
        money(value);

    render();
}

/*
|--------------------------------------------------------------------------
| INIT
|--------------------------------------------------------------------------
*/
render();

</script>

</x-app-layout>