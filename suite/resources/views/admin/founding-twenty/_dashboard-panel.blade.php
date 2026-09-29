    {{-- ── Founding 20 programme ── --}}
    <div>
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold uppercase tracking-widest text-[#D4AF37]">Founding 20</p>
            <div class="flex gap-4 text-xs">
                <a href="{{ route('admin.founding-twenty.funnel') }}" class="text-[#0078D4] hover:underline">Funnel</a>
                <a href="{{ route('admin.founding-twenty.deposits') }}" class="text-[#0078D4] hover:underline">Deposit journal</a>
                <a href="{{ route('admin.founding-twenty.action-queue') }}" class="text-[#0078D4] hover:underline">Action queue</a>
            </div>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                <p class="text-xs text-slate-400 uppercase tracking-wide">Applications</p>
                <p class="text-3xl font-bold text-white mt-2">{{ $founding20['applications'] }}</p>
                <p class="text-xs text-slate-500 mt-1.5">{{ $founding20['selected'] }} of {{ $founding20['targets']['selected'] }} selected @if($founding20['unfinished']) &middot; {{ $founding20['unfinished'] }} unfinished @endif</p>
            </div>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                <p class="text-xs text-slate-400 uppercase tracking-wide">Deposits held</p>
                <p class="text-3xl font-bold text-[#D4AF37] mt-2">R{{ number_format($founding20['deposits']['held'], 0) }}</p>
                <p class="text-xs text-slate-500 mt-1.5">R{{ number_format($founding20['deposits']['received'], 0) }} received &middot; R{{ number_format($founding20['deposits']['refunded'], 0) }} paid back &middot; R{{ number_format($founding20['deposits']['credited'], 0) }} credited</p>
                @if($founding20['discrepancies'] > 0)
                    <a href="{{ route('admin.founding-twenty.deposits') }}" class="text-xs text-red-400 mt-1 block">{{ $founding20['discrepancies'] }} deposit(s) disagree with the journal</a>
                @endif
            </div>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                <p class="text-xs text-slate-400 uppercase tracking-wide">Onboarded and paying</p>
                <p class="text-3xl font-bold text-emerald-400 mt-2">{{ $founding20['paying'] }} <span class="text-sm text-slate-500 font-normal">/ {{ $founding20['targets']['paying'] }}</span></p>
                <p class="text-xs text-slate-500 mt-1.5">{{ $founding20['onboarded'] }} onboarded &middot; {{ $founding20['activated'] }} with a first win &middot; R{{ number_format($founding20['committedMonthly'], 0) }} a month committed</p>
            </div>
            <div class="{{ $founding20['actionCount'] > 0 ? 'bg-amber-900/20 border-amber-800/40' : 'bg-slate-900 border-slate-800' }} border rounded-2xl p-5">
                <p class="text-xs {{ $founding20['actionCount'] > 0 ? 'text-amber-400' : 'text-slate-400' }} uppercase tracking-wide">Waiting on you</p>
                <p class="text-3xl font-bold {{ $founding20['actionCount'] > 0 ? 'text-amber-300' : 'text-slate-500' }} mt-2">{{ $founding20['actionCount'] }}</p>
                <p class="text-xs text-slate-500 mt-1.5">R{{ number_format($founding20['valueGiven'], 0) }} in free months and rewards given</p>
            </div>
        </div>
    </div>
