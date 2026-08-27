<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-[#D4AF37]">IP Reputation</h2>
        <p class="text-slate-400 text-sm mt-1">IP addresses used to log into more than one account</p>
    </x-slot>

    <div class="space-y-6">

        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
                <p class="text-slate-400 text-sm">IPs with shared accounts</p>
                <p class="text-2xl font-bold text-white mt-1">{{ $ips->count() }}</p>
            </div>
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
                <p class="text-slate-400 text-sm">Flagged (unverified)</p>
                <p class="text-2xl font-bold text-red-400 mt-1">{{ $ips->filter(fn($r) => $r['is_flagged'] && !$r['is_verified'])->count() }}</p>
            </div>
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
                <p class="text-slate-400 text-sm">Verified</p>
                <p class="text-2xl font-bold text-emerald-400 mt-1">{{ $ips->where('is_verified', true)->count() }}</p>
            </div>
        </div>

        <p class="text-xs text-slate-500">
            Looking at the last 90 days of successful logins. An IP is <span class="text-red-400 font-medium">flagged</span>
            when accounts from more than 3 different businesses have logged in from it — that's unusual and worth a look.
            Accounts from the <em>same</em> business sharing one IP (an office or shop WiFi) is normal and shown, but never flagged.
        </p>

        {{-- List --}}
        <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
            @if($ips->isEmpty())
                <div class="px-6 py-12 text-center text-slate-500 text-sm">No IP has logged into more than one account in the last 90 days.</div>
            @else
                <div class="divide-y divide-slate-800">
                    @foreach($ips as $row)
                        <div class="p-6" x-data="{ open: false }">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-white text-base">{{ $row['ip_address'] }}</span>
                                        @if($row['is_flagged'] && !$row['is_verified'])
                                            <span class="px-2 py-0.5 rounded-full bg-red-900/50 border border-red-700/60 text-red-300 text-xs font-medium">Flagged</span>
                                        @elseif($row['is_verified'])
                                            <span class="px-2 py-0.5 rounded-full bg-emerald-900/50 border border-emerald-700/60 text-emerald-300 text-xs font-medium">Verified</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full bg-slate-800 border border-slate-700 text-slate-400 text-xs font-medium">Normal</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-500 mt-1">
                                        {{ $row['total_accounts'] }} accounts &middot;
                                        {{ $row['distinct_tenants'] }} {{ Str::plural('business', $row['distinct_tenants']) }} &middot;
                                        up to {{ $row['same_tenant_max'] }} on one business &middot;
                                        last seen {{ \Illuminate\Support\Carbon::parse($row['last_seen'])->diffForHumans() }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="button" @click="open = !open" class="text-xs text-[#0078D4] hover:text-blue-300">
                                        <span x-show="!open">Show accounts</span>
                                        <span x-show="open" x-cloak>Hide accounts</span>
                                    </button>
                                    @if($row['is_verified'])
                                        <form action="{{ route('admin.ip-reputation.unverify', $verifiedRecords[$row['ip_address']]) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-xs text-slate-400 hover:text-white">Unverify</button>
                                        </form>
                                    @else
                                        <button type="button" @click="$refs.note_{{ Str::slug($row['ip_address']) }}.classList.toggle('hidden')"
                                                class="px-3 py-1.5 rounded-lg bg-emerald-700/80 hover:bg-emerald-700 text-white text-xs font-medium">
                                            Verify
                                        </button>
                                    @endif
                                </div>
                            </div>

                            @if(!$row['is_verified'])
                                <form x-ref="note_{{ Str::slug($row['ip_address']) }}" action="{{ route('admin.ip-reputation.verify') }}" method="POST"
                                      class="hidden mt-3 flex flex-wrap gap-2 items-center">
                                    @csrf
                                    <input type="hidden" name="ip_address" value="{{ $row['ip_address'] }}">
                                    <input type="text" name="note" placeholder="Why is this IP okay? (e.g. shared coworking space)"
                                           class="flex-1 min-w-[240px] px-3 py-2 bg-slate-800 border border-slate-700 text-white rounded-lg text-sm">
                                    <button type="submit" class="px-3 py-2 rounded-lg bg-emerald-700 hover:bg-emerald-600 text-white text-sm font-medium">
                                        Confirm verified
                                    </button>
                                </form>
                            @endif

                            <div x-show="open" x-cloak class="mt-4 overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="text-left text-slate-500 text-xs uppercase tracking-wide">
                                            <th class="pr-4 py-1">Account</th>
                                            <th class="pr-4 py-1">Type</th>
                                            <th class="pr-4 py-1">Business</th>
                                            <th class="pr-4 py-1">Last seen</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-800/60">
                                        @foreach($row['accounts'] as $account)
                                            <tr>
                                                <td class="pr-4 py-1.5 text-slate-200">{{ $account['label'] }}</td>
                                                <td class="pr-4 py-1.5 text-slate-400">{{ $account['type'] }}</td>
                                                <td class="pr-4 py-1.5 text-slate-400">{{ $account['tenant_name'] ?? '—' }}</td>
                                                <td class="pr-4 py-1.5 text-slate-500">{{ \Illuminate\Support\Carbon::parse($account['last_seen'])->format('d M Y H:i') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
