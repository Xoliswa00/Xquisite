<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Overview</p>
                <h2 class="font-bold text-2xl text-gray-900 mt-0.5">
                    {{ auth()->user()->currentCompany?->name ?? 'Dashboard' }}
                </h2>
            </div>
            <div class="flex gap-2 text-sm">
                <a href="{{ route('invoices.create') }}"
                   class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-700 font-medium">
                    + New Invoice
                </a>
                <a href="{{ route('quotes.create') }}"
                   class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
                    + New Quote
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        @php
            $company = auth()->user()->currentCompany;
            if ($company) {
                $cid = $company->id;
                $totalInvoiced   = \App\Models\Invoice::where('company_id', $cid)->sum('total');
                $totalPaid       = \App\Models\Payment::where('company_id', $cid)->sum('amount');
                $outstanding     = \App\Models\Invoice::where('company_id', $cid)->whereIn('status', ['draft','sent'])->sum('total');
                $overdue         = \App\Models\Invoice::where('company_id', $cid)->where('status','overdue')->sum('total');
                $recentInvoices  = \App\Models\Invoice::where('company_id', $cid)->with('client')->latest()->limit(5)->get();
                $recentPayments  = \App\Models\Payment::where('company_id', $cid)->with('invoice.client')->orderByDesc('payment_date')->limit(5)->get();
                $clientCount     = \App\Models\Client::where('company_id', $cid)->count();
                $draftInvoices   = \App\Models\Invoice::where('company_id', $cid)->where('status','draft')->count();
            }
        @endphp

        @if(!$company)
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded-lg text-sm">
                No company linked to your account. <a href="{{ route('companies.create') }}" class="underline font-medium">Create a company</a> to get started.
            </div>
        @else

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Total Invoiced</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">R {{ number_format($totalInvoiced, 2) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Total Received</p>
                <p class="text-2xl font-bold text-green-600 mt-2">R {{ number_format($totalPaid, 2) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Outstanding</p>
                <p class="text-2xl font-bold text-yellow-600 mt-2">R {{ number_format($outstanding, 2) }}</p>
                @if($draftInvoices > 0)
                    <p class="text-xs text-gray-400 mt-1">{{ $draftInvoices }} draft{{ $draftInvoices > 1 ? 's' : '' }}</p>
                @endif
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Overdue</p>
                <p class="text-2xl font-bold {{ $overdue > 0 ? 'text-red-600' : 'text-gray-400' }} mt-2">
                    R {{ number_format($overdue, 2) }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('clients.index') }}" class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition group">
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Clients</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">{{ $clientCount }}</p>
                <p class="text-xs text-indigo-600 mt-2 group-hover:underline">View all &rarr;</p>
            </a>
            <a href="{{ route('invoices.index') }}" class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition group">
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Invoices</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">{{ \App\Models\Invoice::where('company_id', $cid)->count() }}</p>
                <p class="text-xs text-indigo-600 mt-2 group-hover:underline">View all &rarr;</p>
            </a>
            <a href="{{ route('quotes.index') }}" class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition group">
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Quotes</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">{{ \App\Models\Quote::where('company_id', $cid)->count() }}</p>
                <p class="text-xs text-indigo-600 mt-2 group-hover:underline">View all &rarr;</p>
            </a>
            <a href="{{ route('payments.index') }}" class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition group">
                <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Payments</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">{{ \App\Models\Payment::where('company_id', $cid)->count() }}</p>
                <p class="text-xs text-indigo-600 mt-2 group-hover:underline">View all &rarr;</p>
            </a>
        </div>

        <div class="grid grid-cols-2 gap-6">

            {{-- Recent Invoices --}}
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold text-gray-700">Recent Invoices</h3>
                    <a href="{{ route('invoices.index') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
                </div>
                @forelse($recentInvoices as $inv)
                    <div class="flex items-center justify-between py-2.5 border-b last:border-0 text-sm">
                        <div>
                            <a href="{{ route('invoices.show', $inv) }}" class="font-medium text-gray-900 hover:text-indigo-600">
                                {{ $inv->invoice_number }}
                            </a>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $inv->client->name ?? '-' }}</p>
                        </div>
                        <div class="text-right">
                            <span class="block text-sm font-medium text-gray-900">R {{ number_format($inv->total, 2) }}</span>
                            <span class="text-xs px-2 py-0.5 rounded mt-0.5 inline-block
                                @if($inv->status === 'paid') bg-green-100 text-green-700
                                @elseif($inv->status === 'overdue') bg-red-100 text-red-700
                                @elseif($inv->status === 'sent') bg-blue-100 text-blue-700
                                @else bg-gray-100 text-gray-500 @endif">
                                {{ ucfirst($inv->status) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 py-4 text-center">No invoices yet. <a href="{{ route('invoices.create') }}" class="text-indigo-600 hover:underline">Create one</a>.</p>
                @endforelse
            </div>

            {{-- Recent Payments --}}
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold text-gray-700">Recent Payments</h3>
                    <a href="{{ route('payments.index') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
                </div>
                @forelse($recentPayments as $pmt)
                    <div class="flex items-center justify-between py-2.5 border-b last:border-0 text-sm">
                        <div>
                            <p class="font-medium text-gray-900">{{ $pmt->invoice->client->name ?? '-' }}</p>
                            <p class="text-xs text-gray-400 mt-0.5 capitalize">{{ $pmt->method }} &bull; {{ $pmt->payment_date->format('d M Y') }}</p>
                        </div>
                        <span class="font-semibold text-green-600">R {{ number_format($pmt->amount, 2) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 py-4 text-center">No payments recorded yet.</p>
                @endforelse
            </div>
        </div>

        @endif
    </div>
</x-app-layout>
