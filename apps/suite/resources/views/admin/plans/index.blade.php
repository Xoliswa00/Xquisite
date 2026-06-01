<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">Bundle Plans</h2>
            <a href="{{ route('admin.plans.create') }}"
               class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm rounded-lg font-medium">
                + New Plan
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse ($plans as $plan)
            <div class="bg-white rounded-xl border {{ $plan->is_featured ? 'border-indigo-400 ring-2 ring-indigo-100' : 'border-gray-200' }} overflow-hidden">

                {{-- Header --}}
                <div class="px-5 py-4 border-b border-gray-100 flex items-start justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-semibold text-gray-900">{{ $plan->name }}</h3>
                            @if ($plan->is_featured)
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-700 font-medium border border-indigo-200">Featured</span>
                            @endif
                            @if (!$plan->is_active)
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 text-gray-500 border border-gray-200">Inactive</span>
                            @endif
                        </div>
                        @if ($plan->tagline)
                            <p class="text-xs text-gray-500 mt-0.5">{{ $plan->tagline }}</p>
                        @endif
                    </div>
                    <a href="{{ route('admin.plans.edit', $plan) }}"
                       class="text-xs text-indigo-600 hover:text-indigo-800 font-medium shrink-0">Edit</a>
                </div>

                {{-- Pricing --}}
                <div class="px-5 py-3 bg-gray-50 border-b border-gray-100">
                    <p class="text-2xl font-bold text-gray-900">
                        R{{ number_format($plan->price_monthly, 0) }}
                        <span class="text-sm font-normal text-gray-500">/mo</span>
                    </p>
                    @if ($plan->price_annual)
                        <p class="text-xs text-emerald-600 mt-0.5">
                            R{{ number_format($plan->price_annual, 0) }}/mo billed annually
                            · save {{ $plan->annualDiscountPercent() }}%
                        </p>
                    @endif
                </div>

                {{-- Modules --}}
                <div class="px-5 py-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Includes</p>
                    @if ($plan->planModules->isEmpty())
                        <p class="text-xs text-gray-400 italic">No modules assigned</p>
                    @else
                        <ul class="space-y-1.5">
                            @foreach ($plan->planModules as $pm)
                            <li class="flex items-center gap-2 text-sm text-gray-700">
                                <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ $pm->platformModule?->name ?? $pm->module_key }}
                            </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                {{-- Delete --}}
                <div class="px-5 pb-4">
                    <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}"
                          onsubmit="return confirm('Delete {{ $plan->name }} plan?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 hover:text-red-700">Delete plan</button>
                    </form>
                </div>

            </div>
            @empty
                <div class="col-span-3 text-center py-12 text-gray-400">
                    No plans yet. <a href="{{ route('admin.plans.create') }}" class="text-indigo-600">Create your first plan →</a>
                </div>
            @endforelse
        </div>

    </div>
</x-app-layout>
