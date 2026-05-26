<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Edit Product</h2>
            <a href="{{ route('products.show', $product) }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4">

        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-300 text-red-700 p-4 rounded-lg text-sm">
                <ul class="list-disc pl-4 space-y-1">
                    @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('products.update', $product) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-xl shadow p-6 space-y-4">
                <h3 class="font-semibold text-gray-700">Product Details</h3>

                <div class="grid md:grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Name *</label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                               class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm @error('name') border-red-500 @enderror">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Group *</label>
                        <select name="product_group_id" required
                                class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm" id="groupSelect">
                            @foreach($groups as $g)
                                <option value="{{ $g->id }}" {{ $product->product_group_id == $g->id ? 'selected' : '' }}
                                        data-categories="{{ $g->categories->pluck('name', 'id')->toJson() }}">
                                    {{ $g->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Category</label>
                        <select name="product_category_id"
                                class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm" id="categorySelect">
                            <option value="">— None —</option>
                            @foreach($groups->find($product->product_group_id)?->categories ?? collect() as $c)
                                <option value="{{ $c->id }}" {{ $product->product_category_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Billing Type *</label>
                        <select name="billing_type" required
                                class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                            <option value="once_off" {{ $product->billing_type === 'once_off' ? 'selected' : '' }}>Once Off</option>
                            <option value="recurring" {{ $product->billing_type === 'recurring' ? 'selected' : '' }}>Recurring</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Billing Cycle</label>
                        <select name="billing_cycle"
                                class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                            <option value="">— N/A —</option>
                            <option value="monthly" {{ $product->billing_cycle === 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="yearly" {{ $product->billing_cycle === 'yearly' ? 'selected' : '' }}>Yearly</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" rows="3"
                                  class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">{{ old('description', $product->description) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('products.show', $product) }}"
                   class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit"
                        class="px-6 py-2 bg-slate-900 text-white rounded-md text-sm hover:bg-slate-700">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
