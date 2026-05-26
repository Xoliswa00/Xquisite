<x-app-layout>
    <x-slot name="header">Analytics</x-slot>

    <div class="space-y-6">

        <!-- Revenue KPIs -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-slate-800 rounded-xl p-5">
                <p class="text-xs text-slate-400 uppercase tracking-wide">Today's Revenue</p>
                <p class="text-2xl font-bold text-white mt-1">R {{ number_format($todayRevenue, 2) }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ $todaySales }} {{ $todaySales === 1 ? 'sale' : 'sales' }}</p>
            </div>
            <div class="bg-slate-800 rounded-xl p-5">
                <p class="text-xs text-slate-400 uppercase tracking-wide">This Week</p>
                <p class="text-2xl font-bold text-white mt-1">R {{ number_format($weekRevenue, 2) }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ now()->startOfWeek()->format('d M') }} – {{ now()->format('d M') }}</p>
            </div>
            <div class="bg-slate-800 rounded-xl p-5">
                <p class="text-xs text-slate-400 uppercase tracking-wide">This Month</p>
                <p class="text-2xl font-bold text-white mt-1">R {{ number_format($monthRevenue, 2) }}</p>
                @if($monthGrowth !== null)
                    <p class="text-xs mt-1 {{ $monthGrowth >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                        {{ $monthGrowth >= 0 ? '+' : '' }}{{ $monthGrowth }}% vs last month
                    </p>
                @else
                    <p class="text-xs text-slate-500 mt-1">{{ $monthSales }} sales</p>
                @endif
            </div>
            <div class="bg-slate-800 rounded-xl p-5">
                <p class="text-xs text-slate-400 uppercase tracking-wide">Avg Sale Value</p>
                <p class="text-2xl font-bold text-white mt-1">R {{ number_format($avgSaleValue, 2) }}</p>
                <p class="text-xs text-slate-500 mt-1">based on {{ $monthSales }} sales this month</p>
            </div>
        </div>

        <!-- Revenue chart + Payment breakdown -->
        <div class="grid lg:grid-cols-3 gap-4">

            <!-- 14-day bar chart -->
            <div class="lg:col-span-2 bg-slate-800 rounded-xl p-5">
                <h3 class="text-sm font-medium text-slate-300 mb-4">Revenue — Last 14 Days</h3>
                @php $maxRev = $revenueChart->max('revenue') ?: 1; @endphp
                <div class="flex items-end gap-1 h-32">
                    @foreach($revenueChart as $day)
                        @php $heightPct = max(4, ($day['revenue'] / $maxRev) * 100); @endphp
                        <div class="flex-1 flex flex-col items-center gap-1 group relative">
                            <div class="w-full bg-indigo-600/80 hover:bg-indigo-500 rounded-sm transition-colors"
                                 style="height: {{ $heightPct }}%"
                                 title="R {{ number_format($day['revenue'], 2) }}">
                            </div>
                            @if($loop->index % 2 === 0)
                                <span class="text-xs text-slate-600 truncate w-full text-center">{{ \Carbon\Carbon::parse($day['date'])->format('d') }}</span>
                            @else
                                <span class="text-xs text-transparent">·</span>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="mt-2 flex justify-between text-xs text-slate-500">
                    <span>{{ $revenueChart->first()['label'] }}</span>
                    <span>{{ $revenueChart->last()['label'] }}</span>
                </div>
            </div>

            <!-- Payment method breakdown -->
            <div class="bg-slate-800 rounded-xl p-5">
                <h3 class="text-sm font-medium text-slate-300 mb-4">Payment Methods (This Month)</h3>
                @php $totalPayRev = $paymentBreakdown->sum('revenue') ?: 1; @endphp
                <div class="space-y-3">
                    @forelse($paymentBreakdown as $pm)
                        @php $pct = round(($pm->revenue / $totalPayRev) * 100); @endphp
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-slate-300 uppercase">{{ $pm->payment_method }}</span>
                                <span class="text-slate-400">{{ $pct }}% · R {{ number_format($pm->revenue, 0) }}</span>
                            </div>
                            <div class="w-full bg-slate-700 rounded-full h-1.5">
                                <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No sales this month.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Bookings + Services + Products -->
        <div class="grid lg:grid-cols-3 gap-4">

            <!-- Booking stats -->
            <div class="bg-slate-800 rounded-xl p-5 space-y-4">
                <h3 class="text-sm font-medium text-slate-300">Bookings — This Month</h3>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-slate-700/50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-white">{{ $monthBookings }}</p>
                        <p class="text-xs text-slate-400">Total</p>
                    </div>
                    <div class="bg-slate-700/50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-emerald-400">{{ $completionRate }}%</p>
                        <p class="text-xs text-slate-400">Completion</p>
                    </div>
                </div>

                <div class="space-y-2 text-sm">
                    @foreach(['pending'=>'Pending','confirmed'=>'Confirmed','completed'=>'Completed','cancelled'=>'Cancelled','no_show'=>'No Show'] as $status => $label)
                        @php $count = $bookingsByStatus[$status] ?? 0; @endphp
                        <div class="flex justify-between">
                            <span class="text-slate-400">{{ $label }}</span>
                            <span class="text-white font-medium">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="pt-2 border-t border-slate-700 flex items-center justify-between text-xs">
                    <span class="text-slate-400">Today</span>
                    <span class="text-white font-medium">{{ $todayBookings }} bookings</span>
                </div>
            </div>

            <!-- Top services -->
            <div class="bg-slate-800 rounded-xl p-5">
                <h3 class="text-sm font-medium text-slate-300 mb-3">Top Services (This Month)</h3>
                @if($topServices->isEmpty())
                    <p class="text-sm text-slate-500">No service sales recorded yet.</p>
                @else
                    @php $maxSvcRev = $topServices->max('revenue') ?: 1; @endphp
                    <div class="space-y-3">
                        @foreach($topServices as $svc)
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-slate-300 truncate flex-1 mr-2">{{ $svc->name }}</span>
                                    <span class="text-slate-400 shrink-0">{{ $svc->sold }}× · R {{ number_format($svc->revenue, 0) }}</span>
                                </div>
                                <div class="w-full bg-slate-700 rounded-full h-1.5">
                                    <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ round(($svc->revenue / $maxSvcRev) * 100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Top products -->
            <div class="bg-slate-800 rounded-xl p-5">
                <h3 class="text-sm font-medium text-slate-300 mb-3">Top Products (This Month)</h3>
                @if($topProducts->isEmpty())
                    <p class="text-sm text-slate-500">No product sales recorded yet.</p>
                @else
                    @php $maxPrdRev = $topProducts->max('revenue') ?: 1; @endphp
                    <div class="space-y-3">
                        @foreach($topProducts as $prd)
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-slate-300 truncate flex-1 mr-2">{{ $prd->name }}</span>
                                    <span class="text-slate-400 shrink-0">{{ $prd->sold }}× · R {{ number_format($prd->revenue, 0) }}</span>
                                </div>
                                <div class="w-full bg-slate-700 rounded-full h-1.5">
                                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ round(($prd->revenue / $maxPrdRev) * 100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Inventory + Customers + Recent sales -->
        <div class="grid lg:grid-cols-3 gap-4">

            <!-- Inventory snapshot -->
            <div class="bg-slate-800 rounded-xl p-5 space-y-3">
                <h3 class="text-sm font-medium text-slate-300">Inventory Snapshot</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Active Products</span>
                        <span class="text-white font-medium">{{ $totalProducts }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Reorder Needed</span>
                        <span class="{{ $reorderNeeded > 0 ? 'text-amber-400' : 'text-slate-500' }} font-medium">{{ $reorderNeeded }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Out of Stock</span>
                        <span class="{{ $outOfStock > 0 ? 'text-red-400' : 'text-slate-500' }} font-medium">{{ $outOfStock }}</span>
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-700 flex gap-2">
                    @if($reorderNeeded > 0)
                        <a href="{{ route('stock.reorder-alerts') }}" class="text-xs text-amber-400 hover:text-amber-300">
                            View reorder alerts →
                        </a>
                    @endif
                    <a href="{{ route('stock.take') }}" class="text-xs text-indigo-400 hover:text-indigo-300 {{ $reorderNeeded > 0 ? 'ml-auto' : '' }}">
                        Stock take →
                    </a>
                </div>
            </div>

            <!-- Customers -->
            <div class="bg-slate-800 rounded-xl p-5 space-y-3">
                <h3 class="text-sm font-medium text-slate-300">Customers</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Total Customers</span>
                        <span class="text-white font-medium">{{ $totalCustomers }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">New This Month</span>
                        <span class="text-emerald-400 font-medium">+{{ $newThisMonth }}</span>
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-700">
                    <a href="{{ route('customers.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300">
                        Manage customers →
                    </a>
                </div>
            </div>

            <!-- Recent sales -->
            <div class="bg-slate-800 rounded-xl overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-700 flex items-center justify-between">
                    <h3 class="text-sm font-medium text-slate-300">Recent Sales</h3>
                    <a href="{{ route('pos.sales.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300">All →</a>
                </div>
                @forelse($recentSales as $sale)
                    <a href="{{ route('pos.sales.show', $sale) }}"
                       class="px-4 py-2.5 border-b border-slate-700/50 flex items-center justify-between hover:bg-slate-700/30 block">
                        <div>
                            <p class="text-xs font-mono text-slate-400">{{ $sale->reference }}</p>
                            <p class="text-xs text-slate-500">{{ $sale->paid_at?->format('d M, H:i') }}</p>
                        </div>
                        <p class="text-sm font-bold text-white">R {{ number_format($sale->total, 2) }}</p>
                    </a>
                @empty
                    <div class="px-4 py-6 text-center text-slate-500 text-sm">No sales yet.</div>
                @endforelse
            </div>
        </div>

    </div>
</x-app-layout>
