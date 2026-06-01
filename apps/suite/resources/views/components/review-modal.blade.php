@props(['threshold' => null, 'autoOpen' => false])

<div x-data="{
        open: {{ $autoOpen ? 'true' : 'false' }},
        rating: 0,
        hovered: 0,
        submitted: false,
        dismiss() {
            this.open = false;
            @if($threshold)
            fetch('{{ route('reviews.dismiss') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Content-Type': 'application/json' },
                body: JSON.stringify({ threshold: {{ $threshold }} })
            });
            @endif
        }
     }"
     @open-review-modal.window="open = true">

    {{-- Floating trigger button (always visible) --}}
    @unless($autoOpen)
    <button @click="open = true"
            class="fixed bottom-24 right-6 z-40 flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-medium px-3.5 py-2 rounded-full shadow-lg transition">
        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
        </svg>
        Rate Xquisite
    </button>
    @endunless

    {{-- Backdrop --}}
    <div x-show="open" x-transition.opacity
         class="fixed inset-0 bg-black/50 z-50"
         @click="dismiss()">
    </div>

    {{-- Modal --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @click.stop>

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-7 relative">

            <button @click="dismiss()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            @if($threshold)
            <div class="mb-4 inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs font-medium">
                🎉 {{ number_format($threshold) }} actions — you're a power user!
            </div>
            @endif

            <h3 class="text-lg font-bold text-gray-900">How's Xquisite working for you?</h3>
            <p class="text-sm text-gray-500 mt-1">Takes 30 seconds. Helps us improve and shows others what to expect.</p>

            @if (session('success') && str_contains(session('success'), 'feedback'))
                <div class="mt-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @else
            <form method="POST" action="{{ route('reviews.store') }}" class="mt-5 space-y-4">
                @csrf
                @if($threshold)
                    <input type="hidden" name="threshold" value="{{ $threshold }}">
                @endif

                {{-- Star rating --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Your rating</label>
                    <div class="flex gap-1">
                        @for ($i = 1; $i <= 5; $i++)
                        <button type="button"
                                @mouseenter="hovered = {{ $i }}"
                                @mouseleave="hovered = 0"
                                @click="rating = {{ $i }}"
                                class="text-3xl transition focus:outline-none"
                                :class="(hovered >= {{ $i }} || rating >= {{ $i }}) ? 'text-amber-400' : 'text-gray-200'">
                            ★
                        </button>
                        @endfor
                    </div>
                    <input type="hidden" name="rating" :value="rating">
                    <p x-show="rating === 0" class="text-xs text-red-500 mt-1" style="display:none">Please select a rating</p>
                </div>

                {{-- Display name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Display name <span class="text-gray-400 font-normal">(shown publicly)</span>
                    </label>
                    <input type="text" name="display_name"
                           value="{{ auth()->user()->tenant?->name }}"
                           placeholder="Your business name"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                {{-- Title --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Headline <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <input type="text" name="title" maxlength="120"
                           placeholder="e.g. Game changer for my salon"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                {{-- Body --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Your review <span class="text-red-500">*</span></label>
                    <textarea name="body" rows="3" required minlength="10" maxlength="1000"
                              placeholder="What do you love? What could be better?"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="submit" :disabled="rating === 0"
                            class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-lg transition">
                        Submit review
                    </button>
                    <button type="button" @click="dismiss()"
                            class="px-4 py-2.5 text-sm text-gray-500 hover:text-gray-700">
                        Maybe later
                    </button>
                </div>

                <p class="text-xs text-gray-400 text-center">Reviews are moderated before being published.</p>
            </form>
            @endif

        </div>
    </div>
</div>
