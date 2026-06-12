<x-app-layout>
<div class="max-w-6xl mx-auto py-8 space-y-8">

    <h2 class="text-2xl font-bold">Edit Product</h2>

    {{-- PRODUCT INFO --}}
    <div class="bg-white p-6 shadow rounded">
        <form method="POST" action="{{ route('products.update', $product) }}">
            @csrf
            @method('PUT')

            <input type="text" name="name" value="{{ $product->name }}" class="border p-2 w-full mb-3">
            <textarea name="description" class="border p-2 w-full mb-3">{{ $product->description }}</textarea>

            <button class="bg-blue-600 text-white px-4 py-2 rounded">Update</button>
        </form>
    </div>

    {{-- PRODUCT ITEMS --}}
    <div class="bg-white p-6 shadow rounded">
        <h3 class="font-bold mb-3">Product Items</h3>

        <ul class="mb-4">
            @foreach($product->items as $item)
                <li class="flex justify-between border-b py-2">
                    {{ $item->name }}

                    <form method="POST" action="{{ route('items.destroy', $item) }}">
                        @csrf
                        @method('DELETE')
                        <button class="text-red-600">Remove</button>
                    </form>
                </li>
            @endforeach
        </ul>

        <form method="POST" action="{{ route('items.store', $product) }}">
            @csrf
            <input type="text" name="name" placeholder="New item" class="border p-2 w-full mb-2">
            <button class="bg-green-600 text-white px-3 py-1 rounded">Add Item</button>
        </form>
    </div>

    {{-- PRODUCT PRICES --}}
    <div class="bg-white p-6 shadow rounded">
        <h3 class="font-bold mb-3">Pricing</h3>

        <table class="w-full mb-4">
            <thead>
                <tr>
                    <th>Price</th>
                    <th>VAT</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($product->prices as $price)
                    <tr>
                        <td>R{{ $price->price }}</td>
                        <td>{{ $price->vat_rate }}%</td>
                        <td>
                            @if($price->is_active)
                                <span class="text-green-600">Active</span>
                            @else
                                <span class="text-gray-500">Old</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <form method="POST" action="/products/{{ $product->id }}/prices">
            @csrf

            <input type="number" step="0.01" name="price" placeholder="Price" class="border p-2 w-full mb-2">
            <input type="number" step="0.01" name="vat_rate" value="15" class="border p-2 w-full mb-2">

            <button class="bg-blue-600 text-white px-3 py-2 rounded">
                Set New Price
            </button>
        </form>
    </div>

</div>
</x-app-layout>