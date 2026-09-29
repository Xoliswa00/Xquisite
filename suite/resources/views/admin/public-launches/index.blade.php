<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-[#D4AF37]">Public Launch Pages</h2>
    </x-slot>

    <div class="space-y-6">
        <p class="text-sm text-slate-400">Coming-soon pages for Founding 20 and any future module launches. Countdown, benefits, and public Q&amp;A, each independently on/off.</p>

        <div class="bg-slate-800 rounded-xl border border-slate-700 divide-y divide-slate-700 overflow-hidden">
            @foreach ($launches as $launch)
                <div class="p-5 flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-semibold text-white text-sm">{{ $launch->title }}</span>
                            <span class="text-xs px-1.5 py-0.5 rounded bg-slate-700 text-slate-400 font-mono">{{ $launch->key }}</span>
                            @if ($launch->is_active)
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300">Live</span>
                            @else
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-700 text-slate-400">Withheld</span>
                            @endif
                            @if (!$launch->qa_enabled)
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-700 text-slate-400">Q&amp;A off</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-1">
                            {{ $launch->launch_at ? 'Launches ' . $launch->launch_at->format('d M Y, H:i') : 'No countdown set' }}
                            &middot; {{ $launch->unanswered_count }} unanswered / {{ $launch->questions_count }} questions
                        </p>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('public-launch.show', $launch->key) }}" target="_blank"
                           class="text-xs px-3 py-1.5 rounded-lg border border-slate-600 text-slate-400 hover:border-[#0078D4] hover:text-[#B8D4F0] transition">
                            View page
                        </a>
                        <a href="{{ route('admin.public-launches.questions', $launch) }}"
                           class="text-xs px-3 py-1.5 rounded-lg border border-slate-600 text-slate-400 hover:border-[#0078D4] hover:text-[#B8D4F0] transition">
                            Q&amp;A inbox
                        </a>
                        <a href="{{ route('admin.public-launches.edit', $launch) }}"
                           class="text-xs px-3 py-1.5 rounded-lg bg-[#0078D4] hover:bg-[#0065B8] text-white transition">
                            Edit
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
