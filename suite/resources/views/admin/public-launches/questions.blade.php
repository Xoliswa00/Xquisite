<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-[#D4AF37]">Q&amp;A — {{ $launch->title }}</h2>
    </x-slot>

    <div class="space-y-8">
        <a href="{{ route('admin.public-launches.index') }}" class="text-sm text-slate-400 hover:text-white transition">&larr; Back to launch pages</a>

        @foreach ([
            'pending'  => ['label' => 'Awaiting an answer', 'colour' => 'yellow'],
            'answered' => ['label' => 'Answered',            'colour' => 'emerald'],
        ] as $status => $meta)

        @php $group = $questions->get($status, collect()); @endphp
        @if ($group->isNotEmpty())

        <div>
            <div class="flex items-center gap-3 mb-3">
                <span class="px-2.5 py-1 rounded-full text-xs font-medium
                    {{ $meta['colour'] === 'yellow' ? 'bg-yellow-500/20 text-yellow-300' : 'bg-emerald-500/20 text-emerald-300' }}">
                    {{ $meta['label'] }}
                </span>
                <span class="text-sm text-slate-500">{{ $group->count() }} {{ Str::plural('question', $group->count()) }}</span>
            </div>

            <div class="bg-slate-800 rounded-xl border border-slate-700 divide-y divide-slate-700 overflow-hidden">
                @foreach ($group as $question)
                <div class="p-5 space-y-3">
                    <div>
                        <p class="text-sm text-white">{{ $question->question }}</p>
                        <div class="flex flex-wrap items-center gap-2 mt-1.5 text-xs text-slate-500">
                            <span>{{ $question->asker_name ?: 'Anonymous' }}</span>
                            @if ($question->asker_email)
                                <span>&middot;</span><span>{{ $question->asker_email }}</span>
                            @endif
                            <span>&middot;</span><span>{{ $question->created_at->diffForHumans() }}</span>
                        </div>
                    </div>

                    @if ($question->isAnswered())
                        <div class="bg-slate-900/60 rounded-lg p-3 text-sm text-slate-300">
                            {{ $question->answer }}
                        </div>
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('admin.public-launches.questions.toggle', [$launch, $question]) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="text-xs px-2.5 py-1.5 rounded-lg border transition
                                    {{ $question->is_published ? 'bg-[#0078D4] border-[#0078D4] text-white' : 'border-slate-600 text-slate-400 hover:border-[#0078D4] hover:text-[#B8D4F0]' }}">
                                    {{ $question->is_published ? 'Published' : 'Publish' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.public-launches.questions.destroy', [$launch, $question]) }}"
                                  onsubmit="return confirm('Remove this question?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-600 text-slate-400 hover:border-red-500 hover:text-red-400 transition">
                                    Remove
                                </button>
                            </form>
                        </div>
                    @else
                        <form method="POST" action="{{ route('admin.public-launches.questions.answer', [$launch, $question]) }}" class="space-y-2">
                            @csrf @method('PATCH')
                            <textarea name="answer" rows="3" required maxlength="4000" placeholder="Write an answer…"
                                      class="w-full bg-slate-900 border border-slate-700 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]"></textarea>
                            <div class="flex items-center justify-between">
                                <label class="flex items-center gap-2 text-xs text-slate-400">
                                    <input type="checkbox" name="is_published" value="1" checked
                                           class="w-3.5 h-3.5 rounded border-slate-600 bg-slate-800 text-[#0078D4] focus:ring-[#0078D4]/50">
                                    Publish immediately
                                </label>
                                <button type="submit" class="text-xs px-3 py-1.5 rounded-lg bg-[#0078D4] hover:bg-[#0065B8] text-white transition">
                                    Save answer
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        @endif
        @endforeach

        @if ($questions->isEmpty())
            <div class="text-center py-16 text-slate-500">
                <p>No questions yet.</p>
                <p class="text-sm mt-1 text-slate-600">Questions appear here as visitors submit them from the public page.</p>
            </div>
        @endif
    </div>
</x-app-layout>
