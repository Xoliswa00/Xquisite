<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('website.templates.index') }}" class="text-slate-400 hover:text-white transition-colors">← Templates</a>
            <span class="text-slate-600">/</span>
            <h2 class="text-lg font-semibold text-slate-100">{{ $template->name }}</h2>
        </div>
    </x-slot>

    @php $isActive = $activeTemplateKey === $template->key; @endphp

    <div class="max-w-3xl space-y-6">

        @if (session('success'))
            <div class="px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm">{{ session('error') }}</div>
        @endif

        <div class="flex items-center gap-4">
            <x-template-preview :template="$template" ratio="aspect-video" class="border border-slate-700 flex-1" />
        </div>
        <div class="flex items-center justify-between -mt-2">
            <p class="text-xs text-slate-500">
                Live preview with sample content — your actual site will show your business name, logo, and colors once activated.
            </p>
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('template.preview', $template->key) }}" target="_blank" rel="noopener" class="text-xs text-[#0078D4] hover:text-[#B8D4F0] font-medium">Live Demo ↗</a>
                <x-template-preview-modal :template="$template" />
            </div>
        </div>

        <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
            <div class="flex items-center gap-2 flex-wrap mb-2">
                <h1 class="text-base font-semibold text-white">{{ $template->name }}</h1>
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-700 text-slate-400 border border-slate-600 font-mono">v{{ $template->version }}</span>
                @if ($template->is_featured)
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-[#D4AF37]/20 text-[#D4AF37] border border-[#D4AF37]/40">Featured</span>
                @endif
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-700 text-slate-300 border border-slate-600 capitalize">{{ str_replace('-', ' ', $template->category) }}</span>
            </div>

            <div class="flex items-center gap-2 text-xs text-slate-500 mb-3">
                <span class="text-amber-400 tracking-tight">{{ $template->starsHtml() }}</span>
                @if ($template->rating_count)
                    <span>{{ $template->displayRating() }} ({{ $template->rating_count }} {{ Str::plural('review', $template->rating_count) }})</span>
                @else
                    <span>No reviews yet</span>
                @endif
                <span>·</span>
                <span>{{ $template->activeCustomersCount() }} {{ Str::plural('business', $template->activeCustomersCount()) }} using this template</span>
                <span>·</span>
                <span>By {{ $template->author }}</span>
            </div>

            <p class="text-sm text-slate-400">{{ $template->description }}</p>

            {{-- Scores --}}
            @if ($template->speed_score || $template->seo_score || $template->accessibility_score)
                <div class="mt-4 flex flex-wrap gap-4 text-xs">
                    @foreach (['speed_score' => 'Speed', 'seo_score' => 'SEO', 'accessibility_score' => 'Accessibility'] as $field => $label)
                        @if ($template->$field !== null)
                            <div class="flex items-center gap-1.5">
                                <span class="text-slate-500">{{ $label }}</span>
                                <span class="font-semibold {{ $template->$field >= 90 ? 'text-emerald-400' : ($template->$field >= 50 ? 'text-amber-400' : 'text-red-400') }}">{{ $template->$field }}</span>
                            </div>
                        @endif
                    @endforeach
                    @if ($template->scores_checked_at)
                        <span class="text-slate-600">Checked {{ $template->scores_checked_at->diffForHumans() }}</span>
                    @endif
                </div>
            @endif

            {{-- Modules supported --}}
            @if (!empty($template->modules_supported))
                <div class="mt-4">
                    <p class="text-xs font-medium text-slate-400 mb-1.5">Pairs well with</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($template->modules_supported as $moduleKey)
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-slate-700 text-slate-300 capitalize">{{ str_replace('_', ' ', $moduleKey) }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Theme support --}}
            <div class="mt-4 flex items-center gap-1.5 text-xs text-slate-500">
                @if ($template->supports_theme_toggle)
                    <span class="text-emerald-400">●</span> Supports light &amp; dark mode
                @else
                    <span class="text-slate-500">●</span> Fixed dark design (no toggle)
                @endif
            </div>

            <div class="mt-5 flex items-center justify-between">
                <span class="text-sm font-medium {{ $template->isFree() ? 'text-emerald-400' : 'text-slate-500' }}">
                    @if ($template->isFree())
                        Free
                    @else
                        R{{ number_format($template->price ?? 0, 0) }} · {{ str_replace('_', ' ', $template->price_type) }}
                    @endif
                </span>

                @php $locked = !$template->isPlaceholder() && !$canActivateReal; @endphp

                @if ($isActive)
                    <button type="button" disabled class="px-5 py-2 bg-slate-700 text-slate-400 text-sm rounded-lg font-medium cursor-not-allowed">
                        Currently Active
                    </button>
                @elseif ($template->isFree())
                    <form method="POST" action="{{ route('website.templates.activate', $template) }}">
                        @csrf
                        <input type="hidden" name="from_wizard" value="{{ request()->boolean('from_wizard') ? 1 : 0 }}">
                        <button type="submit" class="px-5 py-2 {{ $locked ? 'bg-slate-700 hover:bg-slate-600 text-slate-200' : 'bg-[#0078D4] hover:bg-[#0078D4]/90 text-white' }} text-sm rounded-lg font-medium transition-colors">
                            {{ $locked ? 'Reserve — Unlocks on a Paid Plan' : 'Activate This Template' }}
                        </button>
                    </form>
                @elseif ($hasPurchased)
                    <form method="POST" action="{{ route('website.templates.activate', $template) }}">
                        @csrf
                        <input type="hidden" name="from_wizard" value="{{ request()->boolean('from_wizard') ? 1 : 0 }}">
                        <button type="submit" class="px-5 py-2 bg-[#0078D4] hover:bg-[#0078D4]/90 text-white text-sm rounded-lg font-medium transition-colors">
                            Activate (Already Purchased)
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('website.templates.checkout', $template) }}">
                        @csrf
                        <button type="submit" class="px-5 py-2 {{ $locked ? 'bg-slate-700 hover:bg-slate-600 text-slate-200' : 'bg-[#D4AF37] hover:bg-[#D4AF37]/90 text-slate-900 font-semibold' }} text-sm rounded-lg font-medium transition-colors">
                            {{ $locked ? 'Reserve — Unlocks on a Paid Plan' : 'Buy for R' . number_format($template->price ?? 0, 0) }}
                        </button>
                    </form>
                @endif
            </div>

            @if ($locked && $isPreferredPick)
                <p class="mt-2 text-xs text-[#0078D4]">✓ Saved as your pick — it'll be ready to activate the moment you're on a paid plan.</p>
            @elseif ($locked)
                <p class="mt-2 text-xs text-slate-500">Real industry templates unlock once you're off your free trial — Coming Soon stays free the whole time.</p>
            @endif
        </div>

        {{-- Update history --}}
        @if (!empty($template->changelog))
            <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
                <h3 class="text-sm font-semibold text-white mb-3">Update History</h3>
                <div class="space-y-3">
                    @foreach ($template->changelog as $entry)
                        <div class="text-sm">
                            <span class="font-mono text-xs text-slate-400">v{{ $entry['version'] ?? '?' }}</span>
                            <span class="text-xs text-slate-600">· {{ $entry['date'] ?? '' }}</span>
                            <p class="text-slate-300 mt-0.5">{{ $entry['notes'] ?? '' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Reviews --}}
        <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
            <h3 class="text-sm font-semibold text-white mb-4">Reviews</h3>

            @if ($reviews->isEmpty())
                <p class="text-sm text-slate-500">No reviews yet.</p>
            @else
                <div class="space-y-4 mb-6">
                    @foreach ($reviews as $review)
                        <div class="border-b border-slate-700 pb-4 last:border-0 last:pb-0">
                            <div class="flex items-center gap-2">
                                <span class="text-amber-400 text-sm tracking-tight">{{ $review->starsHtml() }}</span>
                                @if ($review->title)
                                    <span class="text-sm font-medium text-white">{{ $review->title }}</span>
                                @endif
                            </div>
                            @if ($review->body)
                                <p class="text-sm text-slate-400 mt-1">{{ $review->body }}</p>
                            @endif
                            <p class="text-xs text-slate-600 mt-1">{{ $review->tenant?->name ?? 'A tenant' }} · {{ $review->created_at->diffForHumans() }}</p>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($canReview)
                <div class="border-t border-slate-700 pt-4" x-data="{ rating: {{ old('rating', $myReview->rating ?? 5) }} }">
                    <p class="text-xs font-medium text-slate-400 mb-2">{{ $myReview ? 'Update your review' : 'Leave a review' }} — you've activated this template</p>
                    <form method="POST" action="{{ route('website.templates.reviews.store', $template) }}" class="space-y-3">
                        @csrf
                        <div class="flex items-center gap-1">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button" @click="rating = {{ $i }}" class="text-xl leading-none" :class="rating >= {{ $i }} ? 'text-amber-400' : 'text-slate-600'">★</button>
                            @endfor
                            <input type="hidden" name="rating" x-model="rating">
                        </div>
                        <input type="text" name="title" value="{{ old('title', $myReview->title ?? '') }}" maxlength="120" placeholder="Review title (optional)"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                        <textarea name="body" rows="2" maxlength="2000" placeholder="What did you think? (optional)"
                                  class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">{{ old('body', $myReview->body ?? '') }}</textarea>
                        <button type="submit" class="px-4 py-2 bg-[#0078D4] hover:bg-[#0078D4]/90 text-white text-sm rounded-lg font-medium transition-colors">
                            {{ $myReview ? 'Update Review' : 'Submit Review' }}
                        </button>
                        @if ($myReview && $myReview->status === 'pending')
                            <p class="text-xs text-slate-500">Your review is awaiting approval.</p>
                        @endif
                    </form>
                </div>
            @else
                <p class="text-xs text-slate-500 border-t border-slate-700 pt-4">Activate this template to leave a review.</p>
            @endif
        </div>

    </div>
</x-app-layout>
