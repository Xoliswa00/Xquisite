<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">Services & Add-ons</h2>
            <a href="{{ route('admin.platform-services.create') }}"
               class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm rounded-lg font-medium">
                + Add Service
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

        @if (session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">{{ session('success') }}</div>
        @endif

        {{-- ── Pending Orders ──────────────────────────────────────────────── --}}
        @if ($orders->isNotEmpty())
        <div>
            <h3 class="text-base font-semibold text-gray-900 mb-4">
                Pending Orders
                <span class="ml-2 text-xs font-medium px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">{{ $orders->count() }}</span>
            </h3>

            <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100 overflow-hidden">
                @foreach ($orders as $order)
                <div class="p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 flex-wrap">
                                <span class="font-semibold text-gray-900 text-sm">{{ $order->service->name }}</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-medium
                                    {{ $order->statusColour() === 'amber'   ? 'bg-amber-100 text-amber-700' : '' }}
                                    {{ $order->statusColour() === 'blue'    ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ $order->statusColour() === 'indigo'  ? 'bg-indigo-100 text-indigo-700' : '' }}
                                    {{ $order->statusColour() === 'purple'  ? 'bg-purple-100 text-purple-700' : '' }}
                                    {{ $order->statusColour() === 'emerald' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                    {{ $order->statusColour() === 'gray'    ? 'bg-gray-100 text-gray-600' : '' }}">
                                    {{ $order->statusLabel() }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $order->tenant->name }}
                                @if ($order->requester) · Requested by {{ $order->requester->name }} @endif
                                @if ($order->requested_date) · Preferred date: {{ $order->requested_date->format('d M Y') }} @endif
                                · {{ $order->created_at->diffForHumans() }}
                            </p>
                            @if ($order->client_notes)
                                <p class="text-sm text-gray-600 mt-1.5 italic">"{{ $order->client_notes }}"</p>
                            @endif
                        </div>

                        {{-- Inline update form --}}
                        <form method="POST" action="{{ route('admin.service-orders.update', $order) }}"
                              class="shrink-0 flex items-center gap-2">
                            @csrf @method('PATCH')
                            <input type="number" name="quoted_price" placeholder="Quote R"
                                   value="{{ $order->quoted_price }}"
                                   class="w-24 text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <select name="status"
                                    class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                @foreach (\App\Models\TenantServiceOrder::STATUS_LABELS as $val => $label)
                                    <option value="{{ $val }}" {{ $order->status === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button type="submit"
                                    class="text-xs bg-indigo-600 hover:bg-indigo-500 text-white px-3 py-1.5 rounded-lg">
                                Save
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ── Service Catalog ─────────────────────────────────────────────── --}}
        <div>
            <h3 class="text-base font-semibold text-gray-900 mb-4">Service Catalog</h3>

            @foreach ($services->groupBy('category') as $category => $group)
            <div class="mb-6">
                <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">{{ ucfirst($category) }}</p>
                <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100 overflow-hidden">
                    @foreach ($group as $service)
                    <div class="flex items-center gap-4 px-5 py-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="font-medium text-gray-900 text-sm">{{ $service->name }}</p>
                                @if (!$service->is_active)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 text-gray-500">Inactive</span>
                                @endif
                                @if (!$service->is_requestable)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 text-gray-500">Not requestable</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $service->description }}</p>
                        </div>
                        <div class="hidden sm:flex items-center gap-6 text-sm shrink-0">
                            <span class="text-gray-700 font-medium">{{ $service->displayPrice() }}</span>
                            <span class="text-xs text-gray-400">{{ $service->billing_type === 'recurring' ? 'Recurring' : 'Once-off' }}</span>
                            <span class="text-xs text-gray-400">{{ $service->orders_count }} {{ Str::plural('order', $service->orders_count) }}</span>
                        </div>
                        <a href="{{ route('admin.platform-services.edit', $service) }}"
                           class="text-xs text-indigo-600 hover:text-indigo-800 font-medium shrink-0">Edit</a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

    </div>
</x-app-layout>
