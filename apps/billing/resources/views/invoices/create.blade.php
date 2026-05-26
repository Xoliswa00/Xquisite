<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl">New Invoice</h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto px-4">

        <div class="bg-white rounded shadow p-6">

            <form method="POST" action="{{ route('invoices.store') }}">
                @csrf

                {{-- Client --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Client</label>
                    <select name="client_id"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                        <option value="">— Select client —</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}"
                                {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('client_id') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Due Date --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700">Due Date</label>
                    <input type="date" name="due_date"
                           value="{{ old('due_date', now()->addDays(30)->format('Y-m-d')) }}"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm" required>
                    @error('due_date') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Line Items --}}
                <div class="mb-4">
                    <h3 class="font-semibold text-gray-700 mb-2">Line Items</h3>

                    <table class="w-full text-sm mb-2" id="items-table">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left p-2">Description</th>
                                <th class="text-right p-2 w-20">Qty</th>
                                <th class="text-right p-2 w-28">Unit Price</th>
                                <th class="text-right p-2 w-24">Total (ex VAT)</th>
                                <th class="p-2 w-10"></th>
                            </tr>
                        </thead>
                        <tbody id="items-body">
                            <tr class="item-row border-t">
                                <td class="p-2">
                                    <input type="text" name="items[0][description]"
                                           class="w-full border-gray-300 rounded text-sm" required>
                                </td>
                                <td class="p-2">
                                    <input type="number" name="items[0][quantity]" value="1" min="0.01" step="0.01"
                                           class="w-full text-right border-gray-300 rounded text-sm item-qty" required>
                                </td>
                                <td class="p-2">
                                    <input type="number" name="items[0][unit_price]" value="0" min="0" step="0.01"
                                           class="w-full text-right border-gray-300 rounded text-sm item-price" required>
                                </td>
                                <td class="p-2 text-right item-total text-gray-600">R 0.00</td>
                                <td class="p-2 text-center">
                                    <button type="button" onclick="removeRow(this)"
                                            class="text-red-500 text-xs">✕</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <button type="button" onclick="addRow()"
                            class="text-blue-600 text-sm underline">+ Add Line</button>
                </div>

                {{-- Totals preview --}}
                <div class="text-right mb-6 text-sm space-y-1">
                    <div>Subtotal: <strong id="preview-subtotal">R 0.00</strong></div>
                    <div>VAT (15%): <strong id="preview-vat">R 0.00</strong></div>
                    <div class="text-lg font-bold">Total: <span id="preview-total">R 0.00</span></div>
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                            class="bg-blue-600 text-white px-5 py-2 rounded text-sm">
                        Create Invoice
                    </button>
                    <a href="{{ route('invoices.index') }}"
                       class="bg-gray-200 text-gray-700 px-5 py-2 rounded text-sm">
                        Cancel
                    </a>
                </div>

            </form>

        </div>
    </div>

    <script>
        let rowIndex = 1;

        function fmt(n) { return 'R ' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','); }

        function recalc() {
            let subtotal = 0;
            document.querySelectorAll('.item-row').forEach(row => {
                const qty   = parseFloat(row.querySelector('.item-qty').value)   || 0;
                const price = parseFloat(row.querySelector('.item-price').value) || 0;
                const line  = qty * price;
                row.querySelector('.item-total').textContent = fmt(line);
                subtotal += line;
            });
            const vat   = subtotal * 0.15;
            const total = subtotal + vat;
            document.getElementById('preview-subtotal').textContent = fmt(subtotal);
            document.getElementById('preview-vat').textContent      = fmt(vat);
            document.getElementById('preview-total').textContent    = fmt(total);
        }

        function addRow() {
            const tbody = document.getElementById('items-body');
            const row   = document.createElement('tr');
            row.className = 'item-row border-t';
            row.innerHTML = `
                <td class="p-2">
                    <input type="text" name="items[${rowIndex}][description]"
                           class="w-full border-gray-300 rounded text-sm" required>
                </td>
                <td class="p-2">
                    <input type="number" name="items[${rowIndex}][quantity]" value="1" min="0.01" step="0.01"
                           class="w-full text-right border-gray-300 rounded text-sm item-qty" required oninput="recalc()">
                </td>
                <td class="p-2">
                    <input type="number" name="items[${rowIndex}][unit_price]" value="0" min="0" step="0.01"
                           class="w-full text-right border-gray-300 rounded text-sm item-price" required oninput="recalc()">
                </td>
                <td class="p-2 text-right item-total text-gray-600">R 0.00</td>
                <td class="p-2 text-center">
                    <button type="button" onclick="removeRow(this)" class="text-red-500 text-xs">✕</button>
                </td>`;
            tbody.appendChild(row);
            rowIndex++;
        }

        function removeRow(btn) {
            const rows = document.querySelectorAll('.item-row');
            if (rows.length > 1) {
                btn.closest('tr').remove();
                recalc();
            }
        }

        document.getElementById('items-body').addEventListener('input', recalc);
    </script>

</x-app-layout>
