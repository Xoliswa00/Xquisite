@extends('property.portal.layout')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-slate-900">My Lease</h1>

    @if(!$activeLease)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-amber-800">
            No active lease found. Contact your property manager.
        </div>
    @else
        <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-slate-400 text-xs uppercase font-semibold">Property</p>
                    <p class="font-semibold text-slate-900 mt-1">{{ $activeLease->property->name }}</p>
                    <p class="text-slate-500 text-xs mt-0.5">{{ $activeLease->property->address_line_1 }}, {{ $activeLease->property->city }}</p>
                </div>
                <div>
                    <p class="text-slate-400 text-xs uppercase font-semibold">Unit</p>
                    <p class="font-semibold text-slate-900 mt-1">{{ $activeLease->unit->unit_number }}</p>
                    <p class="text-slate-500 text-xs mt-0.5">{{ ucfirst($activeLease->unit->type) }}</p>
                </div>
                <div>
                    <p class="text-slate-400 text-xs uppercase font-semibold">Monthly Rent</p>
                    <p class="font-semibold text-slate-900 mt-1">R{{ number_format($activeLease->monthly_rent, 2) }}</p>
                </div>
                <div>
                    <p class="text-slate-400 text-xs uppercase font-semibold">Deposit</p>
                    <p class="font-semibold text-slate-900 mt-1">R{{ number_format($activeLease->deposit_amount, 2) }}</p>
                    <p class="text-xs mt-0.5 {{ $activeLease->deposit_paid ? 'text-emerald-600' : 'text-red-500' }}">
                        {{ $activeLease->deposit_paid ? 'Paid' : 'Outstanding' }}
                    </p>
                </div>
                <div>
                    <p class="text-slate-400 text-xs uppercase font-semibold">Start Date</p>
                    <p class="font-semibold text-slate-900 mt-1">{{ $activeLease->start_date->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-slate-400 text-xs uppercase font-semibold">End Date</p>
                    <p class="font-semibold text-slate-900 mt-1">{{ $activeLease->end_date?->format('d M Y') ?? 'Open-ended' }}</p>
                </div>
            </div>

            @if($activeLease->notes)
                <div class="pt-3 border-t border-slate-100">
                    <p class="text-xs text-slate-400 font-semibold uppercase mb-1">Notes</p>
                    <p class="text-sm text-slate-600">{{ $activeLease->notes }}</p>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
