<x-app-layout>
    <x-slot name="header">Bundle Plans</x-slot>

    <div class="space-y-6">

        <div class="flex items-center justify-between gap-4 flex-wrap">
            <h1 class="text-xl font-bold text-[#D4AF37]">Bundle Plans</h1>
            <a href="{{ route('admin.plans.create') }}"
               class="shrink-0 px-4 py-2 bg-[#0078D4] hover:bg-[#0078D4] text-white text-sm rounded-lg font-medium transition-colors">
                + New Plan
            </a>
        </div>

        @if (session('success'))
            <div class="px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @forelse ($plans as $plan)
            <div class="bg-slate-800 rounded-xl border {{ $plan->is_featured ? 'border-[#0078D4] ring-2 ring-[#0078D4]/20' : 'border-slate-700' }} overflow-hidden flex flex-col">

                {{-- Header --}}
                <div class="px-5 py-4 border-b border-slate-700 flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-semibold text-[#D4AF37]">{{ $plan->name }}</h3>
                            @if ($plan->is_featured)
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-[#0078D4]/20 text-[#B8D4F0] font-medium border border-[#002B5B]">Featured</span>
                            @endif
                            @if (!$plan->is_active)
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-700 text-slate-400 border border-slate-600">Inactive</span>
                            @endif
                        </div>
                        @if ($plan->tagline)
                            <p class="text-xs text-slate-400 mt-0.5">{{ $plan->tagline }}</p>
                        @endif
                    </div>
                    <a href="{{ route('admin.plans.edit', $plan) }}"
                       class="text-xs text-[#0078D4] hover:text-[#B8D4F0] font-medium shrink-0 transition-colors">Edit</a>
                </div>

                {{-- Pricing --}}
                <div class="px-5 py-3 bg-slate-900/50 border-b border-slate-700">
                    <p class="text-2xl font-bold text-white">
                        R{{ number_format($plan->price_monthly, 0) }}
                        <span class="text-sm font-normal text-slate-400">/mo</span>
                    </p>
                    @if ($plan->price_annual)
                        <p class="text-xs text-emerald-400 mt-0.5">
                            R{{ number_format($plan->price_annual, 0) }}/mo billed annually
                            · save {{ $plan->annualDiscountPercent() }}%
                        </p>
                    @endif
                </div>

                {{-- Modules --}}
                <div class="px-5 py-4 flex-1">
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-3">Includes</p>
                    @if ($plan->planModules->isEmpty())
                        <p class="text-xs text-slate-600 italic">No modules assigned</p>
                    @else
                        <ul class="space-y-1.5">
                            @foreach ($plan->planModules as $pm)
                            <li class="flex items-center gap-2 text-sm text-slate-300">
                                <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ $pm->platformModule?->name ?? $pm->module_key }}
                            </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                {{-- Delete --}}
                <div class="px-5 pb-4 border-t border-slate-700 pt-3">
                    <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}"
                          onsubmit="return confirm('Delete {{ $plan->name }} plan?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-400 hover:text-red-300 transition-colors">Delete plan</button>
                    </form>
                </div>

            </div>
            @empty
                <div class="col-span-3 text-center py-12 text-slate-500">
                    No plans yet. <a href="{{ route('admin.plans.create') }}" class="text-[#0078D4] hover:underline">Create your first plan →</a>
                </div>
            @endforelse
        </div>

    </div>
</x-app-layout>
