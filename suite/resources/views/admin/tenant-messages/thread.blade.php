<x-app-layout>
    <x-slot name="header">Messages · {{ $tenant->name }}</x-slot>

    <div class="max-w-3xl mx-auto space-y-5"
         x-data="adminChatPoller({{ $messages->last()->id ?? 0 }})" x-init="start()">

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-[#D4AF37]">{{ $tenant->name }}</h2>
                <p class="text-sm text-slate-400 mt-0.5">{{ $tenant->email }} · Platform support thread</p>
            </div>
            <a href="{{ route('admin.tenants.show', $tenant) }}" class="text-sm text-slate-400 hover:text-white">← Tenant</a>
        </div>

        {{-- Thread --}}
        <div id="admin-messages" class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-4 min-h-64 max-h-[60vh] overflow-y-auto">
            @forelse($messages as $msg)
                @if($msg->is_from_owner)
                    {{-- From platform / us --}}
                    <div class="flex justify-end">
                        <div class="max-w-[75%]">
                            @if($msg->subject)
                                <p class="text-xs text-slate-400 text-right mb-1 font-medium">{{ $msg->subject }}</p>
                            @endif
                            <div class="bg-[#002B5B] text-white rounded-2xl rounded-tr-none px-4 py-3 text-sm">
                                {!! nl2br(e($msg->body)) !!}
                            </div>
                            <p class="text-[10px] text-slate-500 text-right mt-1">{{ $msg->created_at->diffForHumans() }} · You (Xquisite)</p>
                        </div>
                    </div>
                @else
                    {{-- From tenant --}}
                    <div class="flex justify-start">
                        <div class="max-w-[75%]">
                            <div class="bg-slate-700 text-slate-100 rounded-2xl rounded-tl-none px-4 py-3 text-sm">
                                {!! nl2br(e($msg->body)) !!}
                            </div>
                            <p class="text-[10px] text-slate-500 mt-1">
                                {{ $msg->created_at->diffForHumans() }} · {{ $msg->fromUser?->name ?? $tenant->name }}
                                @if($msg->read_at)
                                    · <span class="text-emerald-600">Read</span>
                                @endif
                            </p>
                        </div>
                    </div>
                @endif
            @empty
                <p id="admin-empty-state" class="text-center text-sm text-slate-500 py-8">No messages yet. Send the first message below.</p>
            @endforelse
        </div>

        {{-- Compose form --}}
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
            <form method="POST" action="{{ route('admin.tenants.messages.store', $tenant) }}" class="space-y-3">
                @csrf
                <div>
                    <x-input-label value="Subject (optional)" />
                    <x-text-input name="subject" class="mt-1 w-full" placeholder="e.g. Action required · Your account" />
                </div>
                <div>
                    <x-input-label value="Message" />
                    <textarea name="body" rows="4" required
                              class="mt-1 w-full rounded-lg bg-slate-800 border-slate-700 text-slate-200 text-sm focus:ring-[#0078D4] focus:border-[#0078D4]"
                              placeholder="Write your message to {{ $tenant->name }}…"></textarea>
                    <x-input-error :messages="$errors->get('body')" />
                </div>
                <button type="submit" class="px-5 py-2.5 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-medium rounded-lg">
                    Send to Tenant
                </button>
            </form>
        </div>

    </div>

    {{-- Polls for new messages every few seconds — same pattern as the tenant-
         and client-side thread views. --}}
    <script>
        function adminChatPoller(lastId) {
            return {
                lastId: lastId,
                pollUrl: '{{ route('admin.tenants.messages.poll', $tenant) }}',
                timer: null,
                start() {
                    this.timer = setInterval(() => this.poll(), 4000);
                    document.addEventListener('visibilitychange', () => {
                        if (!document.hidden) this.poll();
                    });
                },
                async poll() {
                    if (document.hidden) return;
                    try {
                        const res = await fetch(this.pollUrl + '?after_id=' + this.lastId, { headers: { 'Accept': 'application/json' } });
                        if (!res.ok) return;
                        const data = await res.json();
                        if (!data.messages || !data.messages.length) return;

                        const container = document.getElementById('admin-messages');
                        const wasNearBottom = container.scrollHeight - container.scrollTop - container.clientHeight < 100;

                        const emptyState = document.getElementById('admin-empty-state');
                        if (emptyState) emptyState.remove();

                        data.messages.forEach((m) => {
                            const row = document.createElement('div');
                            row.className = m.is_from_owner ? 'flex justify-end' : 'flex justify-start';

                            const inner = document.createElement('div');
                            inner.className = 'max-w-[75%]';

                            let html = '';
                            if (m.is_from_owner) {
                                if (m.subject_html) {
                                    html += `<p class="text-xs text-slate-400 text-right mb-1 font-medium">${m.subject_html}</p>`;
                                }
                                html += `<div class="bg-[#002B5B] text-white rounded-2xl rounded-tr-none px-4 py-3 text-sm">${m.body_html}</div>`;
                                html += `<p class="text-[10px] text-slate-500 text-right mt-1">${m.created_human} · You (Xquisite)</p>`;
                            } else {
                                html += `<div class="bg-slate-700 text-slate-100 rounded-2xl rounded-tl-none px-4 py-3 text-sm">${m.body_html}</div>`;
                                html += `<p class="text-[10px] text-slate-500 mt-1">${m.created_human} · ${m.from_name_html}</p>`;
                            }
                            inner.innerHTML = html;
                            row.appendChild(inner);
                            container.appendChild(row);
                        });

                        this.lastId = data.last_id;
                        if (wasNearBottom) container.scrollTop = container.scrollHeight;
                    } catch (e) {
                        // Network hiccup — next interval tries again.
                    }
                },
            };
        }
    </script>
</x-app-layout>
