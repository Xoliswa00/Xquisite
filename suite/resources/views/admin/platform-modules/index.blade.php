<x-app-layout>
    <x-slot name="header">Platform Modules</x-slot>

    <div class="space-y-8">

        <div class="flex items-center justify-between gap-4 flex-wrap">
            <h1 class="text-xl font-bold text-[#D4AF37]">Platform Modules</h1>
            <a href="{{ route('admin.platform-modules.create') }}"
               class="shrink-0 px-4 py-2 bg-[#0078D4] hover:bg-[#0078D4] text-white text-sm rounded-lg font-medium transition-colors">
                + Add Module
            </a>
        </div>

        @if (session('success'))
            <div class="px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">{{ session('success') }}</div>
        @endif

        @foreach ([
            'active'      => ['label' => 'Live',        'colour' => 'emerald'],
            'beta'        => ['label' => 'In Testing',   'colour' => 'amber'],
            'coming_soon' => ['label' => 'Coming Soon',  'colour' => 'indigo'],
        ] as $status => $meta)

            @php $group = $modules->get($status, collect()); @endphp
            @if ($group->isNotEmpty())

            <div>
                <div class="flex items-center gap-3 mb-3">
                    <span class="px-2.5 py-1 rounded-full text-xs font-medium
                        {{ $meta['colour'] === 'emerald' ? 'bg-emerald-500/20 text-emerald-300' : '' }}
                        {{ $meta['colour'] === 'amber'   ? 'bg-amber-500/20 text-amber-300' : '' }}
                        {{ $meta['colour'] === 'indigo'  ? 'bg-[#0078D4]/20 text-[#B8D4F0]' : '' }}">
                        {{ $meta['label'] }}
                    </span>
                    <span class="text-sm text-slate-500">{{ $group->count() }} {{ Str::plural('module', $group->count()) }}</span>
                </div>

                <div class="bg-slate-800 rounded-xl border border-slate-700 divide-y divide-slate-700 overflow-hidden">
                    @foreach ($group as $module)
                    <div class="flex items-center gap-4 px-5 py-4 hover:bg-slate-700/30 transition-colors">

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="font-medium text-white text-sm">{{ $module->name }}</p>
                                @if (!$module->is_visible)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-700 text-slate-400 border border-slate-600">Hidden</span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5 truncate">{{ $module->description }}</p>
                        </div>

                        <div class="hidden sm:flex items-center gap-6 text-sm text-slate-400 shrink-0">
                            <span class="font-mono text-xs text-slate-500">{{ $module->key }}</span>
                            <span>R{{ number_format($module->price, 0) }}/mo</span>
                            @if ($module->launch_date)
                                <span class="text-xs">🗓 {{ $module->launch_date->format('d M Y') }}</span>
                            @endif
                        </div>

                        {{-- Quick status switch --}}
                        <form method="POST" action="{{ route('admin.platform-modules.status', $module) }}" class="shrink-0">
                            @csrf @method('PATCH')
                            <select name="status" onchange="this.form.submit()"
                                    class="bg-slate-700 border border-slate-600 text-slate-200 text-xs rounded-lg px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                                <option value="active"       {{ $module->status === 'active'       ? 'selected' : '' }}>Live</option>
                                <option value="beta"         {{ $module->status === 'beta'         ? 'selected' : '' }}>In Testing</option>
                                <option value="coming_soon"  {{ $module->status === 'coming_soon'  ? 'selected' : '' }}>Coming Soon</option>
                            </select>
                        </form>

                        <a href="{{ route('admin.platform-modules.edit', $module) }}"
                           class="text-xs text-[#0078D4] hover:text-[#B8D4F0] font-medium shrink-0 transition-colors">
                            Edit
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>

            @endif
        @endforeach

    </div>
</x-app-layout>
