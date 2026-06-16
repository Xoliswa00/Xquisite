<x-app-layout>
    <x-slot name="header">Give Feedback</x-slot>

    <div class="max-w-xl mx-auto space-y-6">

        <div>
            <h1 class="text-2xl font-bold text-slate-900">Share your feedback</h1>
            <p class="text-slate-500 mt-1 text-sm">Tell us how Xquisite is working for you. Takes 30 seconds.</p>
        </div>

        @if(session('success'))
            <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-5 flex items-start gap-3">
                <svg class="w-5 h-5 text-emerald-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-emerald-700 text-sm font-medium">{{ session('success') }}</p>
            </div>
        @endif

        @if($existing)
            <div class="rounded-2xl bg-white border border-slate-200 p-6 space-y-3">
                <div class="flex items-center gap-0.5 text-2xl leading-none">
                    @for($i = 1; $i <= 5; $i++)
                        <span class="{{ $i <= $existing->rating ? 'text-amber-400' : 'text-slate-200' }}">★</span>
                    @endfor
                </div>
                @if($existing->title)
                    <p class="font-semibold text-slate-900">{{ $existing->title }}</p>
                @endif
                <p class="text-slate-600 text-sm">{{ $existing->body }}</p>
                <p class="text-xs text-slate-400">
                    Submitted {{ $existing->created_at->diffForHumans() }} &middot;
                    <span class="{{ $existing->status === 'approved' ? 'text-emerald-600' : 'text-amber-600' }} font-medium">
                        {{ ucfirst($existing->status) }}
                    </span>
                </p>
            </div>
        @else
            <div class="rounded-2xl bg-white border border-slate-200 p-6"
                 x-data="{ rating: 0, hovered: 0 }">
                <form method="POST" action="{{ route('reviews.store') }}" class="space-y-5">
                    @csrf

                    {{-- Star rating --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Your rating</label>
                        <div class="flex gap-1">
                            @for ($i = 1; $i <= 5; $i++)
                            <button type="button"
                                    @mouseenter="hovered = {{ $i }}"
                                    @mouseleave="hovered = 0"
                                    @click="rating = {{ $i }}"
                                    class="text-4xl transition focus:outline-none leading-none"
                                    :class="(hovered >= {{ $i }} || rating >= {{ $i }}) ? 'text-amber-400' : 'text-slate-200'">★</button>
                            @endfor
                        </div>
                        <input type="hidden" name="rating" :value="rating">
                        @error('rating') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Display name --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Display name <span class="text-slate-400 font-normal text-xs">(shown publicly)</span>
                        </label>
                        <input type="text" name="display_name"
                               value="{{ old('display_name', auth()->user()->tenant?->name) }}"
                               placeholder="Your business name"
                               class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    {{-- Headline --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Headline <span class="text-slate-400 font-normal text-xs">(optional)</span>
                        </label>
                        <input type="text" name="title" maxlength="120"
                               value="{{ old('title') }}"
                               placeholder="e.g. Game changer for my salon"
                               class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    {{-- Body --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Your review <span class="text-red-500">*</span>
                        </label>
                        <textarea name="body" rows="4" required minlength="10" maxlength="1000"
                                  placeholder="What do you love? What could be better?"
                                  class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none">{{ old('body') }}</textarea>
                        @error('body') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit"
                            :disabled="rating === 0"
                            class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl transition">
                        Submit feedback
                    </button>

                    <p class="text-xs text-slate-400 text-center">Reviews are moderated before being published.</p>
                </form>
            </div>
        @endif

    </div>
</x-app-layout>
