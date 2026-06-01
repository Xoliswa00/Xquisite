<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">Platform Modules</h2>
            <a href="{{ route('admin.platform-modules.create') }}"
               class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm rounded-lg font-medium">
                + Add Module
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

        @if (session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        @foreach ([
            'active'      => ['label' => 'Live',        'colour' => 'emerald'],
            'beta'        => ['label' => 'In Testing',   'colour' => 'amber'],
            'coming_soon' => ['label' => 'Coming Soon',  'colour' => 'indigo'],
        ] as $status => $meta)

            @php $group = $modules->get($status, collect()); @endphp
            @if ($group->isNotEmpty())

            <div>
                <div class="flex items-center gap-3 mb-4">
                    <span class="px-2.5 py-1 rounded-full text-xs font-medium
                        {{ $meta['colour'] === 'emerald' ? 'bg-emerald-100 text-emerald-700' : '' }}
                        {{ $meta['colour'] === 'amber'   ? 'bg-amber-100 text-amber-700' : '' }}
                        {{ $meta['colour'] === 'indigo'  ? 'bg-indigo-100 text-indigo-700' : '' }}">
                        {{ $meta['label'] }}
                    </span>
                    <span class="text-sm text-gray-500">{{ $group->count() }} {{ Str::plural('module', $group->count()) }}</span>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100 overflow-hidden">
                    @foreach ($group as $module)
                    <div class="flex items-center gap-4 px-5 py-4">

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="font-medium text-gray-900 text-sm">{{ $module->name }}</p>
                                @if (!$module->is_visible)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 text-gray-500 border border-gray-200">Hidden</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $module->description }}</p>
                        </div>

                        <div class="hidden sm:flex items-center gap-6 text-sm text-gray-500 shrink-0">
                            <span class="font-mono text-xs text-gray-400">{{ $module->key }}</span>
                            <span>R{{ number_format($module->price, 0) }}/mo</span>
                            @if ($module->launch_date)
                                <span class="text-xs">🗓 {{ $module->launch_date->format('d M Y') }}</span>
                            @endif
                        </div>

                        {{-- Quick status switch --}}
                        <form method="POST" action="{{ route('admin.platform-modules.status', $module) }}" class="shrink-0">
                            @csrf @method('PATCH')
                            <select name="status" onchange="this.form.submit()"
                                    class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="active"       {{ $module->status === 'active'       ? 'selected' : '' }}>Live</option>
                                <option value="beta"         {{ $module->status === 'beta'         ? 'selected' : '' }}>In Testing</option>
                                <option value="coming_soon"  {{ $module->status === 'coming_soon'  ? 'selected' : '' }}>Coming Soon</option>
                            </select>
                        </form>

                        <a href="{{ route('admin.platform-modules.edit', $module) }}"
                           class="text-xs text-indigo-600 hover:text-indigo-800 font-medium shrink-0">
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
