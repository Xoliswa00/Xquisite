<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Customer Reviews</h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

        @if (session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">{{ session('success') }}</div>
        @endif

        @foreach ([
            'pending'  => ['label' => 'Pending Review', 'colour' => 'amber'],
            'approved' => ['label' => 'Approved',        'colour' => 'emerald'],
            'rejected' => ['label' => 'Rejected',        'colour' => 'red'],
        ] as $status => $meta)

        @php $group = $reviews->get($status, collect()); @endphp
        @if ($group->isNotEmpty())

        <div>
            <div class="flex items-center gap-3 mb-4">
                <span class="px-2.5 py-1 rounded-full text-xs font-medium
                    {{ $meta['colour'] === 'amber'   ? 'bg-amber-100 text-amber-700' : '' }}
                    {{ $meta['colour'] === 'emerald' ? 'bg-emerald-100 text-emerald-700' : '' }}
                    {{ $meta['colour'] === 'red'     ? 'bg-red-100 text-red-700' : '' }}">
                    {{ $meta['label'] }}
                </span>
                <span class="text-sm text-gray-500">{{ $group->count() }} {{ Str::plural('review', $group->count()) }}</span>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100 overflow-hidden">
                @foreach ($group as $review)
                <div class="p-5">
                    <div class="flex items-start justify-between gap-4">

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 flex-wrap">
                                <span class="text-amber-400 font-bold tracking-tight text-sm">
                                    {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                                </span>
                                @if ($review->title)
                                    <span class="font-semibold text-gray-900 text-sm">{{ $review->title }}</span>
                                @endif
                                @if ($review->is_featured)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-700 border border-indigo-200">Featured</span>
                                @endif
                            </div>

                            <p class="text-sm text-gray-600 mt-1.5 leading-relaxed">{{ $review->body }}</p>

                            <div class="flex items-center gap-3 mt-2 text-xs text-gray-400">
                                <span>{{ $review->display_name ?? 'Anonymous' }}</span>
                                @if ($review->business_type)
                                    <span>·</span>
                                    <span>{{ $review->business_type }}</span>
                                @endif
                                @if ($review->prompted_at_count)
                                    <span>·</span>
                                    <span>Prompted at {{ number_format($review->prompted_at_count) }} actions</span>
                                @else
                                    <span>·</span>
                                    <span>Voluntary</span>
                                @endif
                                <span>·</span>
                                <span>{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            {{-- Feature toggle --}}
                            @if ($status === 'approved')
                            <form method="POST" action="{{ route('admin.reviews.featured', $review) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="text-xs px-2.5 py-1.5 rounded-lg border transition
                                    {{ $review->is_featured ? 'bg-indigo-50 border-indigo-200 text-indigo-700' : 'border-gray-200 text-gray-500 hover:border-indigo-300' }}">
                                    {{ $review->is_featured ? '★ Featured' : 'Feature' }}
                                </button>
                            </form>
                            @endif

                            {{-- Status switcher --}}
                            <form method="POST" action="{{ route('admin.reviews.status', $review) }}">
                                @csrf @method('PATCH')
                                <select name="status" onchange="this.form.submit()"
                                        class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
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
            <div class="text-center py-16 text-gray-400">
                <p>No reviews yet.</p>
                <p class="text-sm mt-1">Reviews appear here after users submit them from the app.</p>
            </div>
        @endif

    </div>
</x-app-layout>
