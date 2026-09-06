<x-app-layout>
    <x-slot name="header">Action Queue</x-slot>

    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-[#D4AF37]">Action Queue</h2>
            <p class="text-slate-400 text-sm mt-1">Everything that needs a human decision — 30/60/90 check-ins auto-issue themselves, this is what's left for you.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                <p class="text-slate-400 text-sm">Deposits awaiting confirmation</p>
                <p class="text-2xl font-bold text-amber-400 mt-1">{{ $depositsAwaitingConfirmation->count() }}</p>
            </div>
            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                <p class="text-slate-400 text-sm">Check-ins awaiting response</p>
                <p class="text-2xl font-bold text-[#0078D4] mt-1">{{ $checkinsAwaitingResponse->count() }}</p>
            </div>
            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                <p class="text-slate-400 text-sm">Check-ins overdue (7+ days)</p>
                <p class="text-2xl font-bold text-red-400 mt-1">{{ $checkinsOverdue->count() }}</p>
            </div>
        </div>

        <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-700">
                <h3 class="text-sm font-semibold text-slate-300">Deposits awaiting confirmation</h3>
            </div>
            @if($depositsAwaitingConfirmation->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm summary-on-mobile">
                        <thead class="bg-slate-900/50 border-b border-slate-700">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Business</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Reference</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Submitted</th>
                                <th class="px-6 py-3 text-right font-semibold text-slate-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            @foreach($depositsAwaitingConfirmation as $app)
                                <tr class="hover:bg-slate-700/50 transition">
                                    <td class="px-6 py-4 text-white">{{ $app->business_name }}</td>
                                    <td class="px-6 py-4 text-slate-300 font-mono">{{ $app->deposit_reference }}</td>
                                    <td class="px-6 py-4 text-xs text-slate-400">{{ $app->deposit_submitted_at->diffForHumans() }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('admin.founding-twenty.show', $app) }}" class="inline-flex items-center px-3 py-1.5 text-xs bg-slate-700 hover:bg-slate-600 text-slate-300 rounded transition">Review</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="px-6 py-8 text-sm text-slate-400 text-center">Nothing waiting here.</p>
            @endif
        </div>

        <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-700">
                <h3 class="text-sm font-semibold text-red-400">Check-ins overdue (issued 7+ days ago, no response)</h3>
            </div>
            @if($checkinsOverdue->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm summary-on-mobile">
                        <thead class="bg-slate-900/50 border-b border-slate-700">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Business</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Check-in</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Issued</th>
                                <th class="px-6 py-3 text-right font-semibold text-slate-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            @foreach($checkinsOverdue as $checkin)
                                <tr class="hover:bg-slate-700/50 transition">
                                    <td class="px-6 py-4 text-white">{{ $checkin->application->business_name }}</td>
                                    <td class="px-6 py-4 text-slate-300">{{ $checkin->label() }}</td>
                                    <td class="px-6 py-4 text-xs text-red-400">{{ $checkin->created_at->diffForHumans() }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('admin.founding-twenty.show', $checkin->application) }}" class="inline-flex items-center px-3 py-1.5 text-xs bg-slate-700 hover:bg-slate-600 text-slate-300 rounded transition">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="px-6 py-8 text-sm text-slate-400 text-center">Nothing overdue.</p>
            @endif
        </div>

        <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-700">
                <h3 class="text-sm font-semibold text-slate-300">Check-ins awaiting response (issued recently)</h3>
            </div>
            @if($checkinsAwaitingResponse->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm summary-on-mobile">
                        <thead class="bg-slate-900/50 border-b border-slate-700">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Business</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Check-in</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Issued</th>
                                <th class="px-6 py-3 text-right font-semibold text-slate-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            @foreach($checkinsAwaitingResponse as $checkin)
                                <tr class="hover:bg-slate-700/50 transition">
                                    <td class="px-6 py-4 text-white">{{ $checkin->application->business_name }}</td>
                                    <td class="px-6 py-4 text-slate-300">{{ $checkin->label() }}</td>
                                    <td class="px-6 py-4 text-xs text-slate-400">{{ $checkin->created_at->diffForHumans() }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('admin.founding-twenty.show', $checkin->application) }}" class="inline-flex items-center px-3 py-1.5 text-xs bg-slate-700 hover:bg-slate-600 text-slate-300 rounded transition">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="px-6 py-8 text-sm text-slate-400 text-center">Nothing awaiting a response.</p>
            @endif
        </div>
    </div>
</x-app-layout>
