<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            Quote {{ $quote->quote_number }}
        </h2>
    </x-slot>

    <div class="py-6 max-w-6xl mx-auto px-4">

        <div class="bg-white rounded shadow p-6 mb-4">

            <div class="mb-4">
                <h3 class="font-bold text-lg">
                    Client
                </h3>

                <div>
                    {{ $quote->client->name ?? 'N/A' }}
                </div>
            </div>

            <table class="w-full border-collapse">

                <thead>
                    <tr class="border-b">
                        <th class="text-left p-2">Item</th>
                        <th class="text-right p-2">Qty</th>
                        <th class="text-right p-2">Price</th>
                        <th class="text-right p-2">Total</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($quote->items as $item)

                        <tr class="border-b">

                            <td class="p-2">
                                {{ $item->name }}
                            </td>

                            <td class="p-2 text-right">
                                {{ $item->quantity }}
                            </td>

                            <td class="p-2 text-right">
                                R {{ number_format($item->unit_price, 2) }}
                            </td>

                            <td class="p-2 text-right">
                                R {{ number_format($item->total_price, 2) }}
                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

            <div class="mt-6 text-right">

                <div class="text-lg">
                    Subtotal:
                    <strong>
                        R {{ number_format($quote->subtotal ?? 0, 2) }}
                    </strong>
                </div>

                <div class="text-lg">
                    VAT:
                    <strong>
                        R {{ number_format($quote->vat_amount ?? 0, 2) }}
                    </strong>
                </div>

                <div class="text-2xl font-bold">
                    Total:
                    R {{ number_format($quote->total ?? 0, 2) }}
                </div>

            </div>

        </div>

    </div>

</x-app-layout>