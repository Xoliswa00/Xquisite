@extends('property.portal.layout')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-slate-900">Payment History</h1>

    @if($payments->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 p-10 text-center text-slate-400">
            No payment records yet.
        </div>
    @else
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="divide-y divide-slate-100">
                @foreach($payments as $payment)
                <div class="px-5 py-4 flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-slate-900">
                            {{ \Carbon\Carbon::parse($payment->period . '-01')->format('F Y') }}
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $payment->unit->property->name ?? '' }} &mdash; Unit {{ $payment->unit->unit_number ?? '' }}
                        </p>
                        @if($payment->paid_date)
                            <p class="text-xs text-slate-400">Paid {{ $payment->paid_date->format('d M Y') }}
                                @if($payment->payment_method) &middot; {{ strtoupper($payment->payment_method) }} @endif
                                @if($payment->reference) &middot; Ref: {{ $payment->reference }} @endif
                            </p>
                        @else
                            <p class="text-xs text-slate-400">Due {{ $payment->due_date->format('d M Y') }}</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-slate-900">R{{ number_format($payment->amount_due, 2) }}</p>
                        @if($payment->status === 'partial')
                            <p class="text-xs text-slate-500">R{{ number_format($payment->amount_paid, 2) }} paid</p>
                        @endif
                        <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-xs font-medium
                            @if($payment->status === 'paid') bg-emerald-100 text-emerald-700
                            @elseif($payment->status === 'partial') bg-yellow-100 text-yellow-700
                            @elseif($payment->status === 'overdue') bg-red-100 text-red-700
                            @else bg-slate-100 text-slate-600 @endif">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div>{{ $payments->links() }}</div>
    @endif
</div>
@endsection
