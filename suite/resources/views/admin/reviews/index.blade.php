<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-[#D4AF37]">Customer Reviews</h2>
    </x-slot>

    <div class="space-y-8">

        @if (session('success'))
            <div class="px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">{{ session('success') }}</div>
        @endif

        @foreach ([
            'pending'  => ['label' => 'Pending Review', 'colour' => 'amber'],
            'approved' => ['label' => 'Approved',        'colour' => 'emerald'],
            'rejected' => ['label' => 'Rejected',        'colour' => 'red'],
        ] as $status => $meta)

        @php $group = $reviews->get($status, collect()); @endphp
        @if ($group->isNotEmpty())

        <div>
            <div class="flex items-center gap-3 mb-3">
                <span class="px-2.5 py-1 rounded-full text-xs font-medium
                    {{ $meta['colour'] === 'amber'   ? 'bg-amber-500/20 text-amber-300' : '' }}
                    {{ $meta['colour'] === 'emerald' ? 'bg-emerald-500/20 text-emerald-300' : '' }}
                    {{ $meta['colour'] === 'red'     ? 'bg-red-500/20 text-red-300' : '' }}">
                    {{ $meta['label'] }}
                </span>
                <span class="text-sm text-slate-500">{{ $group->count() }} {{ Str::plural('review', $group->count()) }}</span>
            </div>

            <div class="bg-slate-800 rounded-xl border border-slate-700 divide-y divide-slate-700 overflow-hidden">
                @foreach ($group as $review)
                <div class="p-5">
                    <div class="flex flex-col sm:flex-row sm:items-start gap-4">

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 flex-wrap">
                                <span class="text-amber-400 font-bold tracking-tight text-sm">
                                    {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                                </span>
                                @if ($review->title)
                                    <span class="font-semibold text-white text-sm">{{ $review->title }}</span>
                                @endif
                                @if ($review->is_featured)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-[#0078D4]/20 text-[#B8D4F0] border border-[#002B5B]">Featured</span>
                                @endif
                            </div>

                            <p class="text-sm text-slate-300 mt-1.5 leading-relaxed">{{ $review->body }}</p>

                            <div class="flex flex-wrap items-center gap-2 mt-2 text-xs text-slate-500">
                                <span>{{ $review->display_name ?? 'Anonymous' }}</span>
                                @if ($review->business_type)
                                    <span>·</span><span>{{ $review->business_type }}</span>
                                @endif
                                @if ($review->prompted_at_count)
                                    <span>·</span><span>Prompted at {{ number_format($review->prompted_at_count) }} actions</span>
                                @else
                                    <span>·</span><span>Voluntary</span>
                                @endif
                                <span>·</span><span>{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 flex-wrap shrink-0">
                            @if ($status === 'approved')
                            <form method="POST" action="{{ route('admin.reviews.featured', $review) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="text-xs px-2.5 py-1.5 rounded-lg border transition
                                    {{ $review->is_featured ? 'bg-[#0078D4] border-[#0078D4] text-white' : 'border-slate-600 text-slate-400 hover:border-[#0078D4] hover:text-[#B8D4F0]' }}">
                                    {{ $review->is_featured ? '★ Featured' : 'Feature' }}
                                </button>
                            </form>
                            @endif

                            <form method="POST" action="{{ route('admin.reviews.status', $review) }}">
                                @csrf @method('PATCH')
                                <select name="status" onchange="this.form.submit()"
                                        class="bg-slate-700 border border-slate-600 text-slate-200 text-xs rounded-lg px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                                    <option value="pending"  {{ $review->status === 'pending'  ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ $review->status === 'approved' ? 'selected' : '' }}>Approve</option>
                                    <option value="rejected" {{ $review->status === 'rejected' ? 'selected' : '' }}>Reject</option>
                                </select>
                            </form>
                        </div>

                    </div>
                </div>
                @endforeach
            </div>
        </div>

        @endif
        @endforeach

        @if ($reviews->isEmpty())
            <div class="text-center py-16 text-slate-500">
                <p>No reviews yet.</p>
                <p class="text-sm mt-1 text-slate-600">Reviews appear here after users submit them from the app.</p>
            </div>
        @endif

    </div>
</x-app-layout>
