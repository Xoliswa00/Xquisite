<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Edit Invoice {{ $invoice->invoice_number }}</h2>
            <a href="{{ route('invoices.show', $invoice) }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4">

        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-300 text-red-700 p-4 rounded-lg text-sm">
                <ul class="list-disc pl-4 space-y-1">
                    @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('invoices.update', $invoice) }}" id="invoiceForm">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-xl shadow p-6 mb-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Client *</label>
                        <select name="client_id" required
                                class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" {{ $invoice->client_id == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Due Date *</label>
                        <input type="date" name="due_date"
                               value="{{ old('due_date', $invoice->due_date?->format('Y-m-d')) }}" required
                               class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                            @foreach(['draft','sent','paid','overdue','cancelled'] as $s)
                                <option value="{{ $s }}" {{ $invoice->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Line Items --}}
            <div class="bg-white rounded-xl shadow p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold text-gray-700">Line Items</h3>
                    <button type="button" onclick="addRow()"
                            class="text-sm text-indigo-600 hover:text-indigo-800">+ Add Line</button>
                </div>

                <table class="w-full text-sm" id="itemsTable">
                    <thead>
                        <tr class="border-b text-gray-500">
                            <th class="text-left py-2">Description</th>
                            <th class="text-right py-2 w-20">Qty</th>
                            <th class="text-right py-2 w-28">Unit Price</th>
                            <th class="text-right py-2 w-24">VAT (15%)</th>
                            <th class="text-right py-2 w-28">Total</th>
                            <th class="w-8"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        @foreach($invoice->items as $i => $item)
                            <tr class="item-row border-b">
                                <td class="py-2 pr-2">
                                    <input type="text" name="items[{{ $i }}][description]"
                                           value="{{ $item->description }}" required
                                           class="w-full border-gray-300 rounded text-sm px-2 py-1">
                                </td>
                                <td class="py-2 px-1">
                                    <input type="number" name="items[{{ $i }}][quantity]"
                                           value="{{ $item->quantity }}" min="0.01" step="0.01" required
                                           class="w-full border-gray-300 rounded text-sm px-2 py-1 text-right qty-input">
                                </td>
                                <td class="py-2 px-1">
                                    <input type="number" name="items[{{ $i }}][unit_price]"
                                           value="{{ $item->unit_price }}" min="0" step="0.01" required
                                           class="w-full border-gray-300 rounded text-sm px-2 py-1 text-right price-input">
                                </td>
                                <td class="py-2 px-1 text-right text-gray-500 vat-cell">
                                    R {{ number_format($item->vat_amount, 2) }}
                                </td>
                                <td class="py-2 px-1 text-right font-medium total-cell">
                                    R {{ number_format($item->total + $item->vat_amount, 2) }}
                                </td>
                                <td class="py-2 text-center">
                                    <button type="button" onclick="removeRow(this)"
                                            class="text-red-400 hover:text-red-600 text-lg">&times;</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="text-right mt-4 space-y-1 text-sm">
                    <div>VAT (15%): <strong id="totalVat">R 0.00</strong></div>
                    <div class="text-lg font-bold">Total: <span id="grandTotal">R 0.00</span></div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('invoices.show', $invoice) }}"
                   class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit"
                        class="px-6 py-2 bg-slate-900 text-white rounded-md text-sm hover:bg-slate-700">
                    Save Invoice
                </button>
            </div>
        </form>
    </div>

    <template id="rowTemplate">
        <tr class="item-row border-b">
            <td class="py-2 pr-2">
                <input type="text" name="items[__IDX__][description]" required placeholder="Description"
                       class="w-full border-gray-300 rounded text-sm px-2 py-1">
            </td>
            <td class="py-2 px-1">
                <input type="number" name="items[__IDX__][quantity]" value="1" min="0.01" step="0.01" required
                       class="w-full border-gray-300 rounded text-sm px-2 py-1 text-right qty-input">
            </td>
            <td class="py-2 px-1">
                <input type="number" name="items[__IDX__][unit_price]" value="0" min="0" step="0.01" required
                       class="w-full border-gray-300 rounded text-sm px-2 py-1 text-right price-input">
            </td>
            <td class="py-2 px-1 text-right text-gray-500 vat-cell">R 0.00</td>
            <td class="py-2 px-1 text-right font-medium total-cell">R 0.00</td>
            <td class="py-2 text-center">
                <button type="button" onclick="removeRow(this)" class="text-red-400 hover:text-red-600 text-lg">&times;</button>
            </td>
        </tr>
    </template>

    <script>
        let rowIndex = {{ $invoice->items->count() }};

        function addRow() {
            const tpl = document.getElementById('rowTemplate').innerHTML.replaceAll('__IDX__', rowIndex++);
            document.getElementById('itemsBody').insertAdjacentHTML('beforeend', tpl);
            bindCalculation();
        }

        function removeRow(btn) {
            if (document.querySelectorAll('.item-row').length > 1) {
                btn.closest('tr').remove();
                recalculate();
            }
        }

        function bindCalculation() {
            document.querySelectorAll('.qty-input, .price-input').forEach(el => {
                el.oninput = recalculate;
            });
        }

        function recalculate() {
            let totalVat = 0, grandTotal = 0;
            document.querySelectorAll('.item-row').forEach(row => {
                const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                const price = parseFloat(row.querySelector('.price-input').value) || 0;
                const lineTotal = qty * price;
                const vat = lineTotal * 0.15;
                totalVat += vat;
                grandTotal += lineTotal + vat;
                row.querySelector('.vat-cell').textContent = 'R ' + vat.toFixed(2);
                row.querySelector('.total-cell').textContent = 'R ' + (lineTotal + vat).toFixed(2);
            });
            document.getElementById('totalVat').textContent = 'R ' + totalVat.toFixed(2);
            document.getElementById('grandTotal').textContent = 'R ' + grandTotal.toFixed(2);
        }

        bindCalculation();
        recalculate();
    </script>
</x-app-layout>
