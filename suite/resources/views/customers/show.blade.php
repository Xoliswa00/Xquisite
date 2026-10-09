<x-app-layout>
    <x-slot name="header">{{ $customer->name }}</x-slot>

    <div class="max-w-3xl space-y-4">

        <!-- Profile card -->
        <div class="bg-slate-800 rounded-xl p-5 space-y-4">
            {{-- Top row: name + status --}}
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-0.5 min-w-0">
                    <h2 class="text-lg font-semibold text-[#D4AF37] truncate">{{ $customer->name }}</h2>
                    @if($customer->email)
                        <p class="text-sm text-slate-400">{{ $customer->email }}</p>
                    @endif
                    @if($customer->phone)
                        <p class="text-sm text-slate-400"><x-whatsapp-link :phone="$customer->phone" :message="'Hi ' . $customer->name . ', this is ' . (Auth::user()->tenant?->name ?? config('app.name'))" /></p>
                    @endif
                    @if($customer->notes)
                        <p class="text-sm text-slate-500 pt-1">{{ $customer->notes }}</p>
                    @endif
                </div>
                @if($customer->is_active)
                    <span class="shrink-0 inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-900/50 text-emerald-400 border border-emerald-800">Active</span>
                @else
                    <span class="shrink-0 inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-slate-700 text-slate-400 border border-slate-600">Inactive</span>
                @endif
            </div>
            {{-- Action buttons on their own row — stack nicely on mobile --}}
            <div class="flex gap-2">
                <a href="{{ route('customers.edit', $customer) }}"
                   class="flex-1 sm:flex-none text-center bg-slate-700 hover:bg-slate-600 text-sm px-4 py-2 rounded-lg">Edit</a>
                <a href="{{ route('appointments.create') }}?customer_id={{ $customer->id }}"
                   class="flex-1 sm:flex-none text-center bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm px-4 py-2 rounded-lg font-medium">+ Book</a>
            </div>

            {{-- Online login: staff create a setup link on purpose and send it. The customer
                 chooses their own password. A link works for a short time and can be cancelled. --}}
            <div class="border-t border-slate-700 pt-4 space-y-3" x-data="{ copied: false }">
                @if($customer->hasActiveSetupLink())
                    <div>
                        <p class="text-sm font-medium text-slate-200">{{ $customer->password ? 'New login link ready' : 'Setup link ready' }}</p>
                        <p class="text-sm text-slate-400">
                            Send it to {{ $customer->name }} so they can choose {{ $customer->password ? 'a new password. Their current password keeps working until they use the link' : 'their own password' }}. It works until
                            {{ $customer->setup_link_expires_at->format('l j M, H:i') }} and only once.
                            @unless($customer->phone) There is no cell number on this profile, so copy the link and send it yourself. @endunless
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @if($customer->phone)
                            <x-whatsapp-link :phone="$customer->phone" :message="$customer->setupLinkMessage()"
                                class="flex-1 sm:flex-none justify-center bg-slate-700 hover:bg-slate-600 text-sm px-4 py-2 rounded-lg">Send on WhatsApp</x-whatsapp-link>
                        @endif
                        <button type="button" data-link="{{ $customer->setupLinkUrl() }}"
                                x-on:click="navigator.clipboard.writeText($el.dataset.link).then(() => { copied = true; setTimeout(() => copied = false, 2500) }).catch(() => window.prompt('Copy this link:', $el.dataset.link))"
                                class="flex-1 sm:flex-none text-center bg-slate-700 hover:bg-slate-600 text-sm px-4 py-2 rounded-lg">
                            <span x-show="!copied">Copy link</span>
                            <span x-show="copied" x-cloak class="text-emerald-400">Copied</span>
                        </button>
                        <form method="POST" action="{{ route('customers.setup-link.destroy', $customer) }}" class="flex-1 sm:flex-none">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full text-center text-sm px-4 py-2 rounded-lg text-slate-300 hover:text-white border border-slate-600 hover:border-slate-500"
                                    data-confirm="Cancel this link? If you already sent it, it will stop working."
                                    onclick="return confirm(this.dataset.confirm)">Cancel link</button>
                        </form>
                    </div>
                @elseif($customer->password)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-slate-200">Has an online login</p>
                            <p class="text-sm text-slate-400">
                                Signs in with {{ $customer->email && $customer->phone ? 'their email address or cell number' : ($customer->email ? 'their email address' : 'their cell number') }}.
                                @if(auth()->user()->isAdmin())
                                    Forgotten the password? Create a new login link. Their current password keeps working until they use it.
                                @else
                                    Forgotten the password? Ask a manager to send a new login link.
                                @endif
                            </p>
                        </div>
                        @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('customers.setup-link.store', $customer) }}" class="shrink-0">
                                @csrf
                                <button type="submit" class="w-full sm:w-auto text-center bg-slate-700 hover:bg-slate-600 text-sm px-4 py-2 rounded-lg">Create new login link</button>
                            </form>
                        @endif
                    </div>
                @else
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-slate-200">No online login yet</p>
                            <p class="text-sm text-slate-400">Create a setup link and send it to {{ $customer->name }}. They choose their own password. The link works for {{ intdiv(\App\Modules\Booking\Models\Customer::SETUP_LINK_HOURS, 24) }} days and only once.</p>
                        </div>
                        <form method="POST" action="{{ route('customers.setup-link.store', $customer) }}" class="shrink-0">
                            @csrf
                            <button type="submit" class="w-full sm:w-auto text-center bg-slate-700 hover:bg-slate-600 text-sm px-4 py-2 rounded-lg">Create setup link</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        {{-- Saved looks: past bookings staff saved as this client's look --}}
        @if($savedLooks->isNotEmpty())
        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-700">
                <h3 class="text-sm font-medium text-slate-300">Saved looks</h3>
            </div>
            <div class="divide-y divide-slate-700">
                @foreach($savedLooks as $look)
                    <div class="p-4 space-y-2">
                        <div class="flex items-center justify-between gap-3">
                            <a href="{{ route('appointments.show', $look) }}" class="text-sm text-slate-200 font-medium hover:text-[#0078D4] truncate">
                                {{ $look->services->pluck('name')->join(', ') ?: 'Booking #' . $look->id }}
                            </a>
                            <span class="text-xs text-slate-500 shrink-0">{{ $look->scheduled_at->format('d M Y') }}</span>
                        </div>
                        <p class="text-xs {{ $look->look_showcase_at ? 'text-emerald-400' : 'text-slate-500' }}">
                            {{ $look->look_showcase_at ? 'OK to share (client agreed ' . $look->look_showcase_at->format('d M Y') . ')' : 'Not cleared for sharing' }}
                        </p>
                        @if($look->resultPhotos->isNotEmpty())
                            <div class="max-w-sm">
                                <p class="text-xs text-slate-500 mb-1">How it turned out</p>
                                @include('appointments.partials.look-thumbs', ['photos' => $look->resultPhotos, 'label' => 'After photo'])
                            </div>
                        @endif
                        @if($look->inspirationPhotos->isNotEmpty())
                            <div class="max-w-sm">
                                <p class="text-xs text-slate-500 mb-1">What they asked for</p>
                                @include('appointments.partials.look-thumbs', ['photos' => $look->inspirationPhotos, 'label' => 'Inspiration photo'])
                            </div>
                        @endif
                        @if($look->inspiration_notes)
                            <p class="text-xs text-slate-400 whitespace-pre-line">{{ $look->inspiration_notes }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- What this client was told or agreed to (POPIA record, newest first) --}}
        @if($consents->isNotEmpty())
        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-700">
                <h3 class="text-sm font-medium text-slate-300">Consent history</h3>
            </div>
            <ul class="divide-y divide-slate-700">
                @foreach($consents as $consent)
                    <li class="px-4 py-2.5 flex items-start justify-between gap-3 text-xs">
                        <span class="text-slate-300">
                            {{ $consent->describe() }}
                            @if($consent->appointment)
                                <span class="text-slate-500">&middot; {{ $consent->appointment->services->pluck('name')->join(', ') ?: 'booking' }}, {{ $consent->appointment->scheduled_at->format('d M Y') }}</span>
                            @endif
                        </span>
                        <span class="text-slate-500 shrink-0">{{ $consent->created_at->format('d M Y, H:i') }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Appointment history -->
        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-700">
                <h3 class="text-sm font-medium text-slate-300">Appointment History</h3>
            </div>

            {{-- Mobile cards --}}
            <div class="sm:hidden divide-y divide-slate-700">
                @forelse($appointments as $appt)
                    @php $colors = ['pending'=>'yellow','confirmed'=>'emerald','completed'=>'blue','awaiting_payment'=>'amber','cancelled'=>'red','no_show'=>'slate']; $c = $colors[$appt->status] ?? 'slate'; @endphp
                    <a href="{{ route('appointments.show', $appt) }}" class="block px-4 py-3 hover:bg-slate-700/50 transition-colors">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm text-slate-300 font-medium">{{ $appt->scheduled_at->format('d M Y, H:i') }}</p>
                            <span class="shrink-0 inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $c }}-900/50 text-{{ $c }}-400 border border-{{ $c }}-800">
                                {{ ucfirst(str_replace('_', ' ', $appt->status)) }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5 truncate">{{ $appt->services->pluck('name')->join(', ') ?: '—' }}</p>
                        @if($appt->staff)
                            <p class="text-xs text-slate-500 mt-0.5">{{ $appt->staff->name }}</p>
                        @endif
                    </a>
                @empty
                    <div class="px-4 py-8 text-center text-slate-500 text-sm">No appointments yet.</div>
                @endforelse
            </div>

            {{-- Desktop table --}}
            <table class="hidden sm:table w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400 text-left">
                        <th class="px-4 py-3 font-medium">Date</th>
                        <th class="px-4 py-3 font-medium">Services</th>
                        <th class="px-4 py-3 font-medium">Staff</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($appointments as $appt)
                        <tr class="hover:bg-slate-700/50">
                            <td class="px-4 py-3 text-slate-300">{{ $appt->scheduled_at->format('d M Y, H:i') }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ $appt->services->pluck('name')->join(', ') ?: '—' }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ $appt->staff?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @php $colors = ['pending'=>'yellow','confirmed'=>'emerald','completed'=>'blue','awaiting_payment'=>'amber','cancelled'=>'red','no_show'=>'slate']; $c = $colors[$appt->status] ?? 'slate'; @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $c }}-900/50 text-{{ $c }}-400 border border-{{ $c }}-800">
                                    {{ ucfirst(str_replace('_', ' ', $appt->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('appointments.show', $appt) }}" class="text-slate-400 hover:text-white text-xs">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">No appointments yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($appointments->hasPages())
                <div class="px-4 py-3 border-t border-slate-700">{{ $appointments->links() }}</div>
            @endif
        </div>

        <a href="{{ route('customers.index') }}" class="inline-block text-sm text-slate-400 hover:text-white">← Back to customers</a>
    </div>
</x-app-layout>
