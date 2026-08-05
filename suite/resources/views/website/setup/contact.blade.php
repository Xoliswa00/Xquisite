<x-app-layout>
    <x-slot name="header">Website Setup</x-slot>

    <div class="max-w-2xl space-y-6">

        <x-website-setup-progress :steps="$steps" :completed="$completed" :current-step="$step" :done-count="$doneCount" :total-count="$totalCount" />

        @if (session('success')) <div class="px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">{{ session('success') }}</div> @endif

        <div class="bg-panel-2 rounded-xl border border-line p-6 space-y-5">
            <div>
                <h2 class="text-lg font-semibold text-ink">How can customers reach you?</h2>
                <p class="text-sm text-ink-muted mt-1">Contact details and business hours shown on your site.</p>
            </div>

            <form method="POST" action="{{ route('website.branding.update') }}" class="space-y-5">
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-ink-muted mb-1">Contact Email</label>
                        <input type="email" name="contact_email" value="{{ old('contact_email', $branding->contact_email ?? '') }}"
                               placeholder="{{ $tenant->email ?: 'Uses your account email if left blank' }}"
                               class="w-full bg-panel border border-line-2 text-ink rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-muted mb-1">Contact Phone</label>
                        <input type="text" name="contact_phone" value="{{ old('contact_phone', $branding->contact_phone ?? '') }}"
                               placeholder="{{ $tenant->phone ?: 'Uses your account phone if left blank' }}"
                               class="w-full bg-panel border border-line-2 text-ink rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-ink-muted mb-1">WhatsApp Number</label>
                    <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $branding->whatsapp_number ?? '') }}"
                           placeholder="e.g. 27821234567"
                           class="w-full sm:w-1/2 bg-panel border border-line-2 text-ink rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                </div>

                <div>
                    <p class="block text-sm font-medium text-ink-muted mb-2">Business Hours</p>
                    <div class="space-y-2">
                        @php $days = ['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday']; @endphp
                        @foreach ($days as $key => $label)
                            @php $hours = $branding->business_hours[$key] ?? []; @endphp
                            <div class="flex items-center gap-3" x-data="{ closed: {{ old('business_hours.' . $key . '.closed', $hours['closed'] ?? false) ? 'true' : 'false' }} }">
                                <span class="text-xs text-ink-faint w-20 shrink-0">{{ $label }}</span>
                                <input type="time" name="business_hours[{{ $key }}][open]" :disabled="closed"
                                       value="{{ old('business_hours.' . $key . '.open', $hours['open'] ?? '09:00') }}"
                                       class="bg-panel border border-line-2 text-ink rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4] disabled:opacity-40">
                                <span class="text-ink-faint text-xs">to</span>
                                <input type="time" name="business_hours[{{ $key }}][close]" :disabled="closed"
                                       value="{{ old('business_hours.' . $key . '.close', $hours['close'] ?? '17:00') }}"
                                       class="bg-panel border border-line-2 text-ink rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4] disabled:opacity-40">
                                <label class="flex items-center gap-1.5 text-xs text-ink-faint cursor-pointer ml-2">
                                    <input type="hidden" name="business_hours[{{ $key }}][closed]" value="0">
                                    <input type="checkbox" name="business_hours[{{ $key }}][closed]" value="1" x-model="closed"
                                           class="w-3.5 h-3.5 rounded bg-panel border-line-2 text-[#0078D4] focus:ring-[#0078D4]">
                                    Closed
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="px-5 py-2.5 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-semibold rounded-lg transition-colors">
                    Continue
                </button>
            </form>
        </div>

        @include('website.setup._nav')
    </div>
</x-app-layout>
