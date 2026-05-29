@extends('layouts.booking')

@section('title', $tenant->name . ' â€” Book')

@section('content')
<div class="space-y-8">
    <div>
        <h1 class="text-3xl font-bold text-slate-900">Book an Appointment</h1>
        <p class="text-slate-500 mt-1">Choose a service to get started.</p>
    </div>

    @if($services->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-400">
            No services available yet. Check back soon.
        </div>
    @else
        <div class="grid sm:grid-cols-2 gap-4">
            @foreach($services as $service)
                <a href="{{ route('book.service', [$slug, $service]) }}"
                   class="group bg-white rounded-2xl border border-slate-200 hover:border-indigo-400 hover:shadow-md p-6 transition">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900 group-hover:text-indigo-600 transition">
                                {{ $service->name }}
                            </h2>
                            @if($service->description)
                                <p class="text-sm text-slate-500 mt-1 line-clamp-2">{{ $service->description }}</p>
                            @endif
                        </div>
                        <svg class="w-5 h-5 text-slate-300 group-hover:text-indigo-400 mt-1 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                    <div class="flex items-center gap-4 mt-4 text-sm text-slate-500">
                        <span>{{ $service->duration_minutes }} min</span>
                        <span class="text-slate-300">Â·</span>
                        <span class="font-semibold text-slate-700">R{{ number_format($service->price, 2) }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection

