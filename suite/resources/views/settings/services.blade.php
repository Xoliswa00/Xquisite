<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Services & Add-ons</h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

        @if (session('success'))
            <div class="p-4 bg-emerald-900/50 border border-emerald-700 text-emerald-300 rounded-lg text-sm">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="p-4 bg-red-900/50 border border-red-700 text-red-300 rounded-lg text-sm">{{ session('error') }}</div>
        @endif

        {{-- ── Service Catalog ─────────────────────────────────────────────── --}}
        @php
            $categoryLabels = [
                'onboarding' => ['label' => 'Getting Started',    'desc' => 'Let us set everything up for you'],
                'training'   => ['label' => 'Training',           'desc' => 'Get your team confident on the platform'],
                'support'    => ['label' => 'Support Plans',      'desc' => 'Priority access when you need help fast'],
                'custom'     => ['label' => 'Custom Work',        'desc' => 'Tailored integrations and reports'],
            ];
        @endphp

        @foreach ($categoryLabels as $cat => $meta)
        @php $group = $services->get($cat); @endphp
        @if ($group && $group->isNotEmpty())

        <div>
            <div class="mb-5">
                <h3 class="text-base font-semibold text-[#D4AF37]">{{ $meta['label'] }}</h3>
                <p class="text-sm text-slate-500">{{ $meta['desc'] }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($group as $service)
                <div x-data="{ open: false }" class="bg-slate-800 rounded-xl border border-slate-700 hover:border-[#0078D4]/40 transition overflow-hidden">

                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-white text-sm">{{ $service->name }}</p>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $service->description }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-sm font-bold text-white">{{ $service->displayPrice() }}</p>
                                <p class="text-xs text-slate-500">{{ $service->billing_type === 'recurring' ? 'per month' : 'once-off' }}</p>
                            </div>
                        </div>

                        <button @click="open = !open"
                                class="mt-4 w-full py-2 text-sm font-medium rounded-lg border border-[#0078D4]/30 text-[#0078D4] hover:bg-[#F0F7FF] transition">
                            <span x-text="open ? 'Cancel' : 'Request this service'"></span>
                        </button>
                    </div>

                    {{-- Request form (inline expand) --}}
                    <div x-show="open" x-transition class="border-t border-slate-700/50 bg-slate-900 px-5 pb-5 pt-4">
                        <form method="POST" action="{{ route('settings.services.request') }}" class="space-y-3">
                            @csrf
                            <input type="hidden" name="platform_service_id" value="{{ $service->id }}">

                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-1">Preferred date <span class="text-slate-500">(optional)</span></label>
                                <input type="date" name="requested_date"
                                       min="{{ now()->toDateString() }}"
                                       class="w-full border border-slate-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0078D4]">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-1">Notes <span class="text-slate-500">(optional)</span></label>
                                <textarea name="client_notes" rows="2" maxlength="1000"
                                          placeholder="Any context that helps us prepare..."
                                          class="w-full border border-slate-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0078D4] resize-none"></textarea>
                            </div>

                            <button type="submit"
                                    class="w-full py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-semibold rounded-lg transition">
                                Submit request
                            </button>
                            <p class="text-xs text-slate-500 text-center">We'll confirm and send a quote within 1 business day.</p>
                        </form>
                    </div>

                </div>
                @endforeach
            </div>
        </div>

        @endif
        @endforeach

        {{-- ── My Requests ─────────────────────────────────────────────────── --}}
        @if ($myOrders->isNotEmpty())
        <div>
            <h3 class="text-base font-semibold text-[#D4AF37] mb-4">My Requests</h3>

            <div class="bg-slate-800 rounded-xl border border-slate-700 divide-y divide-slate-700/50 overflow-hidden">
                @foreach ($myOrders as $order)
                <div class="flex items-center gap-4 px-5 py-4">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-white text-sm">{{ $order->service->name }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $order->created_at->format('d M Y') }}</p>
                    </div>
                    @if ($order->quoted_price)
                        <span class="text-sm font-semibold text-white">R{{ number_format($order->quoted_price, 0) }}</span>
                    @endif
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium
                        {{ $order->statusColour() === 'amber'   ? 'bg-amber-100 text-amber-700' : '' }}
                        {{ $order->statusColour() === 'blue'    ? 'bg-blue-100 text-blue-700' : '' }}
                        {{ $order->statusColour() === 'indigo'  ? 'bg-[#E8F2FA] text-[#0078D4]' : '' }}
                        {{ $order->statusColour() === 'purple'  ? 'bg-teal-100 text-teal-700' : '' }}
                        {{ $order->statusColour() === 'emerald' ? 'bg-emerald-100 text-emerald-700' : '' }}
                        {{ $order->statusColour() === 'gray'    ? 'bg-slate-800/50 text-slate-400' : '' }}">
                        {{ $order->statusLabel() }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</x-app-layout>
