@php
    $sortedSections = $sections->sortBy('sort_order')->values();
@endphp
<x-app-layout>
    <x-slot name="header">Website Editor</x-slot>

    <div x-data="editorPage()" x-init="init()">
        <div class="flex flex-col gap-4" style="height: calc(100vh - 9rem);">
            @if (session('success'))
                <div class="px-4 py-2.5 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">{{ session('success') }}</div>
            @endif

            <div class="flex flex-1 gap-4 overflow-hidden">
                {{-- Live preview --}}
                <div class="flex-1 overflow-hidden rounded-xl border border-line bg-panel">
                    <iframe id="xq-editor-iframe" src="{{ route('website.editor.preview') }}" class="h-full w-full border-0"></iframe>
                </div>

                {{-- Section list --}}
                <div class="flex w-80 shrink-0 flex-col gap-3 overflow-y-auto">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-ink">Sections</h2>
                        <button type="button" @click="$dispatch('open-modal', 'add-section')"
                                class="rounded-lg bg-[#0078D4] hover:bg-[#0078D4]/90 px-3 py-1.5 text-xs font-medium text-white transition-colors">
                            + Add Section
                        </button>
                    </div>

                    <ul class="space-y-2" x-sort="reorderSections">
                        @forelse($sortedSections as $section)
                            <li x-sort:item="{{ $section->id }}" class="rounded-lg border border-line bg-panel-2 p-3">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex min-w-0 items-center gap-2">
                                        <span x-sort:handle class="shrink-0 cursor-grab text-ink-faint hover:text-ink-muted" title="Drag to reorder">
                                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-medium text-ink">{{ $section->label() }}</p>
                                            @unless($section->is_visible)
                                                <span class="text-xs text-ink-faint">Hidden</span>
                                            @endunless
                                        </div>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-1">
                                        <button type="button" @click="openId = {{ $section->id }}" title="Edit"
                                                class="rounded p-1.5 text-ink-muted hover:bg-panel hover:text-ink">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button type="submit" form="visibility-form-{{ $section->id }}" title="{{ $section->is_visible ? 'Hide' : 'Show' }}"
                                                class="rounded p-1.5 text-ink-muted hover:bg-panel hover:text-ink">
                                            @if($section->is_visible)
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            @else
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                            @endif
                                        </button>
                                        <button type="submit" form="duplicate-form-{{ $section->id }}" title="Duplicate"
                                                class="rounded p-1.5 text-ink-muted hover:bg-panel hover:text-ink">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        </button>
                                        <button type="submit" form="delete-form-{{ $section->id }}" title="Delete"
                                                onclick="return confirm('Delete this section? This can\'t be undone.')"
                                                class="rounded p-1.5 text-ink-muted hover:bg-red-500/10 hover:text-red-400">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </div>

                                <form id="visibility-form-{{ $section->id }}" method="POST" action="{{ route('website.editor.sections.visibility', $section) }}" class="hidden">
                                    @csrf @method('PATCH')
                                </form>
                                <form id="duplicate-form-{{ $section->id }}" method="POST" action="{{ route('website.editor.sections.duplicate', $section) }}" class="hidden">
                                    @csrf
                                </form>
                                <form id="delete-form-{{ $section->id }}" method="POST" action="{{ route('website.editor.sections.destroy', $section) }}" class="hidden">
                                    @csrf @method('DELETE')
                                </form>
                            </li>
                        @empty
                            <li class="rounded-lg border border-dashed border-line-2 p-4 text-center text-sm text-ink-faint">
                                No sections yet — add your first one above.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- Side panels — one per section, toggled by the Alpine store above --}}
        @foreach($sortedSections as $section)
            <div
                x-cloak x-show="openId === {{ $section->id }}"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-4 opacity-0" x-transition:enter-end="translate-x-0 opacity-100"
                class="fixed inset-y-0 right-0 z-40 w-96 overflow-y-auto border-l border-line bg-panel p-5 shadow-2xl"
                @click.outside="openId = null"
            >
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-ink">Edit {{ $section->label() }}</h3>
                    <button type="button" @click="openId = null" class="text-ink-faint hover:text-ink">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <x-editor.panel-form :section="$section" />
            </div>
        @endforeach

        <x-modal name="add-section" max-width="2xl">
            <div class="p-6">
                <h2 class="mb-4 text-lg font-semibold text-ink">Add a Section</h2>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach($registry as $type => $def)
                        <form method="POST" action="{{ route('website.editor.sections.store') }}">
                            @csrf
                            <input type="hidden" name="type" value="{{ $type }}">
                            <button type="submit" class="w-full rounded-lg border border-line-2 bg-panel-2 p-3 text-left text-sm font-medium text-ink transition-colors hover:border-[#0078D4]">
                                {{ $def['label'] }}
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        </x-modal>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('editorPage', () => ({
                openId: null,
                init() {
                    window.addEventListener('message', (event) => {
                        if (event.origin !== window.location.origin) return;
                        if (!event.data || event.data.type !== 'xq-section-action') return;

                        const { action, sectionId } = event.data;

                        if (action === 'edit') {
                            this.openId = sectionId;
                            return;
                        }

                        if (action === 'delete') {
                            if (!confirm("Delete this section? This can't be undone.")) return;
                            document.getElementById('delete-form-' + sectionId)?.submit();
                            return;
                        }

                        if (action === 'duplicate') {
                            document.getElementById('duplicate-form-' + sectionId)?.submit();
                            return;
                        }

                        if (action === 'toggle-visibility') {
                            document.getElementById('visibility-form-' + sectionId)?.submit();
                        }
                    });
                },
                reorderSections(order) {
                    window.axios.patch('{{ route('website.editor.sections.reorder') }}', { order })
                        .then(() => {
                            document.getElementById('xq-editor-iframe').contentWindow.location.reload();
                        });
                },
            }));
        });
    </script>
</x-app-layout>
