<x-app-layout>
    <x-slot name="header">Services & Add-ons</x-slot>

    <div class="space-y-8">

        <div class="flex items-center justify-between gap-4 flex-wrap">
            <h1 class="text-xl font-bold text-white">Services & Add-ons</h1>
            <a href="{{ route('admin.platform-services.create') }}"
               class="shrink-0 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm rounded-lg font-medium transition-colors">
                + Add Service
            </a>
        </div>

        @if (session('success'))
            <div class="px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">{{ session('success') }}</div>
        @endif

        {{-- Pending Orders --}}
        @if ($orders->isNotEmpty())
        <div>
            <h3 class="text-sm font-semibold text-white mb-3">
                Pending Orders
                <span class="ml-2 text-xs font-medium px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300">{{ $orders->count() }}</span>
            </h3>

            <div class="bg-slate-800 rounded-xl border border-slate-700 divide-y divide-slate-700 overflow-hidden">
                @foreach ($orders as $order)
                <div class="p-5">
                    <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 flex-wrap">
                                <span class="font-semibold text-white text-sm">{{ $order->service->name }}</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-medium
                                    {{ $order->statusColour() === 'amber'   ? 'bg-amber-500/20 text-amber-300' : '' }}
                                    {{ $order->statusColour() === 'blue'    ? 'bg-blue-500/20 text-blue-300' : '' }}
                                    {{ $order->statusColour() === 'indigo'  ? 'bg-indigo-500/20 text-indigo-300' : '' }}
                                    {{ $order->statusColour() === 'purple'  ? 'bg-purple-500/20 text-purple-300' : '' }}
                                    {{ $order->statusColour() === 'emerald' ? 'bg-emerald-500/20 text-emerald-300' : '' }}
                                    {{ $order->statusColour() === 'gray'    ? 'bg-slate-700 text-slate-400' : '' }}">
                                    {{ $order->statusLabel() }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-400 mt-1">
                                {{ $order->tenant->name }}
                                @if ($order->requester) · Requested by {{ $order->requester->name }} @endif
                                @if ($order->requested_date) · Preferred date: {{ $order->requested_date->format('d M Y') }} @endif
                                · {{ $order->created_at->diffForHumans() }}
                            </p>
                            @if ($order->client_notes)
                                <p class="text-sm text-slate-400 mt-1.5 italic">"{{ $order->client_notes }}"</p>
                            @endif
                        </div>

                        {{-- Inline update form --}}
                        <form method="POST" action="{{ route('admin.service-orders.update', $order) }}"
                              class="shrink-0 flex flex-wrap items-center gap-2">
                            @csrf @method('PATCH')
                            <input type="number" name="quoted_price" placeholder="Quote R"
                                   value="{{ $order->quoted_price }}"
                                   class="w-24 bg-slate-700 border border-slate-600 text-slate-100 text-xs rounded-lg px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <select name="status"
                                    class="bg-slate-700 border border-slate-600 text-slate-200 text-xs rounded-lg px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                @foreach (\App\Models\TenantServiceOrder::STATUS_LABELS as $val => $label)
                                    <option value="{{ $val }}" {{ $order->status === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button type="submit"
                                    class="text-xs bg-indigo-600 hover:bg-indigo-500 text-white px-3 py-1.5 rounded-lg transition-colors">
                                Save
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Service Catalog --}}
        <div>
            <h3 class="text-sm font-semibold text-white mb-4">Service Catalog</h3>

            @foreach ($services->groupBy('category') as $category => $group)
            <div class="mb-5">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500 mb-3">{{ ucfirst($category) }}</p>
                <div class="bg-slate-800 rounded-xl border border-slate-700 divide-y divide-slate-700 overflow-hidden">
                    @foreach ($group as $service)
                    <div class="flex items-center gap-4 px-5 py-4 hover:bg-slate-700/30 transition-colors">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="font-medium text-white text-sm">{{ $service->name }}</p>
                                @if (!$service->is_active)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-700 text-slate-400">Inactive</span>
                                @endif
                                @if (!$service->is_requestable)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-700 text-slate-400">Not requestable</span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5 truncate">{{ $service->description }}</p>
                        </div>
                        <div class="hidden sm:flex items-center gap-6 text-sm shrink-0">
                            <span class="text-slate-200 font-medium">{{ $service->displayPrice() }}</span>
                            <span class="text-xs text-slate-500">{{ $service->billing_type === 'recurring' ? 'Recurring' : 'Once-off' }}</span>
                            <span class="text-xs text-slate-500">{{ $service->orders_count }} {{ Str::plural('order', $service->orders_count) }}</span>
                        </div>
                        <a href="{{ route('admin.platform-services.edit', $service) }}"
                           class="text-xs text-indigo-400 hover:text-indigo-300 font-medium shrink-0 transition-colors">Edit</a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

    </div>
</x-app-layout>
