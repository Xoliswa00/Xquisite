@extends('property.portal.layout')

@section('content')
<div class="space-y-8">

    <div>
        <h1 class="text-2xl font-bold text-slate-900">Welcome, {{ $renter->name }}</h1>
        @if($activeLease)
            <p class="text-slate-500 mt-1">
                {{ $activeLease->unit->unit_number }} &mdash; {{ $activeLease->property->name }}
            </p>
        @endif
    </div>

    @if(!$activeLease)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-amber-800">
            <p class="font-semibold">No active lease found.</p>
            <p class="text-sm mt-1">Contact your property manager for assistance.</p>
        </div>
    @else
        <div class="grid sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
                <p class="text-xs text-slate-400 uppercase font-semibold">Monthly Rent</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">R{{ number_format($activeLease->monthly_rent, 2) }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-5">
                <p class="text-xs text-slate-400 uppercase font-semibold">Lease Ends</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">
                    {{ $activeLease->end_date ? $activeLease->end_date->format('d M Y') : 'Open' }}
                </p>
            </div>
            <a href="{{ route('rent.maintenance', $slug) }}"
               class="bg-white rounded-2xl border border-slate-200 p-5 hover:border-[#B8D4F0] transition group">
                <p class="text-xs text-slate-400 uppercase font-semibold">Open Maintenance</p>
                <p class="text-2xl font-bold {{ $openMaintenance > 0 ? 'text-orange-500' : 'text-slate-900' }} mt-1">
                    {{ $openMaintenance }}
                </p>
                <p class="text-xs text-[#0078D4] mt-2 group-hover:underline">View &rarr;</p>
            </a>
        </div>

        {{-- Latest payments --}}
        @if($activeLease->rentPayments->count())
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center">
                <h2 class="text-sm font-semibold text-slate-800">Recent Payments</h2>
                <a href="{{ route('rent.payments', $slug) }}" class="text-xs text-[#0078D4] hover:underline">View all</a>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($activeLease->rentPayments as $payment)
                <div class="px-5 py-3 flex items-center justify-between text-sm">
                    <div>
                        <p class="font-medium text-slate-800">{{ \Carbon\Carbon::parse($payment->period . '-01')->format('F Y') }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">Due {{ $payment->due_date->format('d M Y') }}</p>
                    </div>
                    <div class="text-right">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            @if($payment->status === 'paid') bg-emerald-100 text-emerald-700
                            @elseif($payment->status === 'partial') bg-yellow-100 text-yellow-700
                            @elseif($payment->status === 'overdue') bg-red-100 text-red-700
                            @else bg-slate-100 text-slate-600 @endif">
                            {{ ucfirst($payment->status) }}
                        </span>
                        <p class="text-xs text-slate-400 mt-0.5">R{{ number_format($payment->amount_due, 2) }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    @endif

</div>
@endsection
