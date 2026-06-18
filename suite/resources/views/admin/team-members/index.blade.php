<x-app-layout>
    <x-slot name="header">Team Members</x-slot>

    <div class="space-y-6">

        <div class="flex items-center justify-between gap-4 flex-wrap">
            <h1 class="text-xl font-bold text-[#D4AF37]">Team Members</h1>
            <a href="{{ route('admin.team-members.create') }}"
               class="shrink-0 px-4 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm rounded-lg font-medium transition-colors">
                + Add Member
            </a>
        </div>

        @if (session('success'))
            <div class="px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">{{ session('success') }}</div>
        @endif

        @if ($members->isEmpty())
            <div class="bg-slate-800 rounded-xl border border-slate-700 px-6 py-12 text-center">
                <p class="text-slate-400 text-sm">No team members yet. Add your first one.</p>
            </div>
        @else
            <div class="bg-slate-800 rounded-xl border border-slate-700 divide-y divide-slate-700 overflow-hidden">
                @foreach ($members as $member)
                <div class="flex items-center gap-4 px-5 py-4 hover:bg-slate-700/30 transition-colors">

                    {{-- Avatar --}}
                    <div class="w-10 h-10 rounded-full overflow-hidden shrink-0 bg-[#002B5B] flex items-center justify-center text-white text-sm font-bold">
                        @if ($member->photoUrl())
                            <img src="{{ $member->photoUrl() }}" alt="{{ $member->name }}" class="w-full h-full object-cover">
                        @else
                            {{ $member->initials() }}
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="font-medium text-white text-sm">{{ $member->name }}</p>
                            @if (!$member->is_active)
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-700 text-slate-400 border border-slate-600">Hidden</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $member->role }}</p>
                    </div>

                    {{-- Sort order --}}
                    <span class="hidden sm:block text-xs text-slate-500 shrink-0">Order: {{ $member->sort_order }}</span>

                    {{-- LinkedIn --}}
                    @if ($member->linkedin_url)
                        <a href="{{ $member->linkedin_url }}" target="_blank" rel="noopener"
                           class="hidden sm:block text-xs text-[#0078D4] hover:text-[#0065B8] shrink-0">LinkedIn</a>
                    @endif

                    {{-- Actions --}}
                    <div class="flex items-center gap-3 shrink-0">
                        <a href="{{ route('admin.team-members.edit', $member) }}"
                           class="text-xs text-[#0078D4] hover:text-[#0065B8] font-medium transition-colors">Edit</a>
                        <form method="POST" action="{{ route('admin.team-members.destroy', $member) }}"
                              onsubmit="return confirm('Remove {{ $member->name }} from the team?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-400 hover:text-red-300 font-medium transition-colors">Remove</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        @endif

    </div>
</x-app-layout>
