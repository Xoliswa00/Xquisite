<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <h1 class="text-lg font-semibold text-slate-100">Website Branding</h1>
            <a href="{{ $tenant->website_url }}" target="_blank" rel="noopener"
               class="inline-flex items-center gap-1.5 text-xs text-[#0078D4] hover:text-[#B8D4F0] font-medium transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                Preview site
            </a>
        </div>
    </x-slot>

    <div class="max-w-3xl space-y-6" x-data="{ step: 1 }">

        @if (session('success'))
            <div class="px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="p-3 bg-red-500/10 border border-red-500/30 text-red-400 rounded-lg text-sm space-y-1">
                @foreach ($errors->all() as $error) <p>{{ $error }}</p> @endforeach
            </div>
        @endif

        {{-- Step tabs --}}
        <div class="flex flex-wrap gap-2">
            @foreach (['1' => 'Business Basics', '2' => 'Look & Feel', '3' => 'Media', '4' => 'Contact & Social'] as $n => $label)
                <button type="button" @click="step = {{ $n }}"
                        class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors"
                        :class="step === {{ $n }} ? 'bg-[#0078D4] border-[#0078D4] text-white' : 'border-slate-700 text-slate-400 hover:text-white hover:border-slate-500'">
                    {{ $n }}. {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Step 1: Business Basics --}}
        <div x-show="step === 1" x-cloak class="bg-slate-800 rounded-xl border border-slate-700 p-6 space-y-4">
            <h3 class="text-sm font-semibold text-slate-100">Business Basics</h3>
            <p class="text-xs text-slate-400">
                Your business name, logo, and address are managed in Account &amp; Settings so they stay
                consistent everywhere they appear — invoices, the dashboard, and your website.
            </p>
            <div class="flex items-center gap-4 p-4 rounded-lg bg-slate-900 border border-slate-700">
                @if($tenant->logo_url)
                    <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}" class="w-12 h-12 rounded-lg object-cover border border-slate-700">
                @else
                    <div class="w-12 h-12 rounded-lg bg-[#0078D4]/20 flex items-center justify-center">
                        <span class="text-sm font-bold text-[#0078D4]">{{ strtoupper(substr($tenant->name, 0, 1)) }}</span>
                    </div>
                @endif
                <div class="flex-1">
                    <p class="text-sm font-medium text-white">{{ $tenant->name }}</p>
                    <p class="text-xs text-slate-500">{{ $tenant->address ?: 'No address set' }}</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="text-xs text-[#0078D4] hover:text-[#B8D4F0] font-medium shrink-0">
                    Edit in Account & Settings →
                </a>
            </div>
            <button type="button" @click="step = 2" class="px-4 py-2 bg-[#0078D4] hover:bg-[#0078D4]/90 text-white text-sm rounded-lg font-medium transition-colors">
                Next: Look & Feel
            </button>
        </div>

        {{-- Main branding form (steps 2-4 share one submit) --}}
        <form method="POST" action="{{ route('website.branding.update') }}"
              x-data="{
                  primary: '{{ old('primary_color', $branding->primary_color ?? '#0078D4') }}',
                  secondary: '{{ old('secondary_color', $branding->secondary_color ?? '#002B5B') }}',
                  accent: '{{ old('accent_color', $branding->accent_color ?? '#D4AF37') }}',
              }">
            @csrf
            @method('PATCH')

            {{-- Step 2: Look & Feel --}}
            <div x-show="step === 2" x-cloak class="bg-slate-800 rounded-xl border border-slate-700 p-6 space-y-5">
                <h3 class="text-sm font-semibold text-slate-100">Look & Feel</h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Primary Color</label>
                        <div class="flex items-center gap-2">
                            <input type="color" x-model="primary" class="w-10 h-10 rounded-lg bg-slate-700 border border-slate-600 cursor-pointer">
                            <input type="text" name="primary_color" x-model="primary" maxlength="7"
                                   class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Secondary Color</label>
                        <div class="flex items-center gap-2">
                            <input type="color" x-model="secondary" class="w-10 h-10 rounded-lg bg-slate-700 border border-slate-600 cursor-pointer">
                            <input type="text" name="secondary_color" x-model="secondary" maxlength="7"
                                   class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Accent Color</label>
                        <div class="flex items-center gap-2">
                            <input type="color" x-model="accent" class="w-10 h-10 rounded-lg bg-slate-700 border border-slate-600 cursor-pointer">
                            <input type="text" name="accent_color" x-model="accent" maxlength="7"
                                   class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Heading Font</label>
                        <select name="heading_font" class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                            @foreach ($fonts as $key => $font)
                                <option value="{{ $key }}" {{ old('heading_font', $branding->heading_font ?? 'inter') === $key ? 'selected' : '' }}>{{ $font['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Body Font</label>
                        <select name="body_font" class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                            @foreach ($fonts as $key => $font)
                                <option value="{{ $key }}" {{ old('body_font', $branding->body_font ?? 'inter') === $key ? 'selected' : '' }}>{{ $font['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex justify-between pt-2">
                    <button type="button" @click="step = 1" class="px-4 py-2 text-sm text-slate-400 hover:text-white transition-colors">← Back</button>
                    <button type="button" @click="step = 3" class="px-4 py-2 bg-[#0078D4] hover:bg-[#0078D4]/90 text-white text-sm rounded-lg font-medium transition-colors">Next: Media</button>
                </div>
            </div>

            {{-- Step 3: Media --}}
            <div x-show="step === 3" x-cloak class="bg-slate-800 rounded-xl border border-slate-700 p-6 space-y-5">
                <h3 class="text-sm font-semibold text-slate-100">Media</h3>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Business Description</label>
                    <textarea name="description" rows="4" maxlength="1000"
                              placeholder="Tell your customers about your business."
                              class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">{{ old('description', $branding->description ?? '') }}</textarea>
                    <p class="mt-1 text-xs text-slate-500">Shown as your site's hero/about text. Max 1000 characters.</p>
                </div>

                <div class="flex justify-between pt-2">
                    <button type="button" @click="step = 2" class="px-4 py-2 text-sm text-slate-400 hover:text-white transition-colors">← Back</button>
                    <button type="button" @click="step = 4" class="px-4 py-2 bg-[#0078D4] hover:bg-[#0078D4]/90 text-white text-sm rounded-lg font-medium transition-colors">Next: Contact & Social</button>
                </div>
            </div>

            {{-- Step 4: Contact & Social --}}
            <div x-show="step === 4" x-cloak class="bg-slate-800 rounded-xl border border-slate-700 p-6 space-y-5">
                <h3 class="text-sm font-semibold text-slate-100">Contact & Social</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Contact Email</label>
                        <input type="email" name="contact_email" value="{{ old('contact_email', $branding->contact_email ?? '') }}"
                               placeholder="{{ $tenant->email ?: 'Uses your account email if left blank' }}"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Contact Phone</label>
                        <input type="text" name="contact_phone" value="{{ old('contact_phone', $branding->contact_phone ?? '') }}"
                               placeholder="{{ $tenant->phone ?: 'Uses your account phone if left blank' }}"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">WhatsApp Number</label>
                    <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $branding->whatsapp_number ?? '') }}"
                           placeholder="e.g. 27821234567"
                           class="w-full sm:w-1/2 bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                </div>

                <div>
                    <p class="block text-sm font-medium text-slate-300 mb-2">Social Links</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach (['facebook', 'instagram', 'twitter', 'linkedin', 'tiktok', 'youtube'] as $platform)
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-slate-400 w-16 shrink-0 capitalize">{{ $platform }}</span>
                                <input type="url" name="socials[{{ $platform }}]"
                                       value="{{ old('socials.' . $platform, $branding->socials[$platform] ?? '') }}"
                                       placeholder="https://…"
                                       class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                            </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <p class="block text-sm font-medium text-slate-300 mb-2">Business Hours</p>
                    <div class="space-y-2">
                        @php $days = ['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday']; @endphp
                        @foreach ($days as $key => $label)
                            @php $hours = $branding->business_hours[$key] ?? []; @endphp
                            <div class="flex items-center gap-3" x-data="{ closed: {{ old('business_hours.' . $key . '.closed', $hours['closed'] ?? false) ? 'true' : 'false' }} }">
                                <span class="text-xs text-slate-400 w-20 shrink-0">{{ $label }}</span>
                                <input type="time" name="business_hours[{{ $key }}][open]" :disabled="closed"
                                       value="{{ old('business_hours.' . $key . '.open', $hours['open'] ?? '09:00') }}"
                                       class="bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4] disabled:opacity-40">
                                <span class="text-slate-500 text-xs">to</span>
                                <input type="time" name="business_hours[{{ $key }}][close]" :disabled="closed"
                                       value="{{ old('business_hours.' . $key . '.close', $hours['close'] ?? '17:00') }}"
                                       class="bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4] disabled:opacity-40">
                                <label class="flex items-center gap-1.5 text-xs text-slate-400 cursor-pointer ml-2">
                                    <input type="hidden" name="business_hours[{{ $key }}][closed]" value="0">
                                    <input type="checkbox" name="business_hours[{{ $key }}][closed]" value="1" x-model="closed"
                                           class="w-3.5 h-3.5 rounded bg-slate-700 border-slate-600 text-[#0078D4] focus:ring-[#0078D4]">
                                    Closed
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-between pt-2">
                    <button type="button" @click="step = 3" class="px-4 py-2 text-sm text-slate-400 hover:text-white transition-colors">← Back</button>
                    <button type="submit" class="px-5 py-2 bg-[#0078D4] hover:bg-[#0078D4]/90 text-white text-sm rounded-lg font-medium transition-colors">
                        Save Branding
                    </button>
                </div>
            </div>
        </form>

        {{-- Favicon & hero image uploads live alongside step 2/3 visually, but are independent forms --}}
        <div x-show="step === 2" x-cloak class="bg-slate-800 rounded-xl border border-slate-700 p-6">
            <h3 class="text-sm font-semibold text-slate-100 mb-4">Favicon</h3>
            <form method="POST" action="{{ route('website.branding.favicon') }}" enctype="multipart/form-data" class="flex items-center gap-6">
                @csrf
                <div class="shrink-0">
                    @if($branding->favicon_url)
                        <img src="{{ $branding->favicon_url }}" alt="Favicon" class="w-12 h-12 rounded-lg object-cover border border-slate-700 bg-slate-900">
                    @else
                        <div class="w-12 h-12 rounded-lg border-2 border-dashed border-slate-700 bg-slate-900/60"></div>
                    @endif
                </div>
                <div class="flex-1 space-y-2" x-data="{ fileName: '' }">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 border border-slate-600 text-sm text-slate-300 transition">
                            Choose file
                        </span>
                        <span class="text-sm text-slate-400" x-text="fileName || 'No file selected'"></span>
                        <input type="file" name="favicon" accept="image/*" class="sr-only" @change="fileName = $event.target.files[0]?.name || ''">
                    </label>
                    <p class="text-xs text-slate-500">JPEG, PNG, WebP or ICO · Max 2 MB</p>
                    @error('favicon')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="shrink-0 px-4 py-2 bg-[#0078D4] hover:bg-[#0078D4]/90 text-white text-sm rounded-lg font-medium transition-colors">Upload</button>
            </form>
        </div>

        <div x-show="step === 3" x-cloak class="bg-slate-800 rounded-xl border border-slate-700 p-6">
            <h3 class="text-sm font-semibold text-slate-100 mb-4">Hero Image</h3>
            <form method="POST" action="{{ route('website.branding.hero-image') }}" enctype="multipart/form-data" class="flex items-center gap-6">
                @csrf
                <div class="shrink-0">
                    @if($branding->hero_image_url)
                        <img src="{{ $branding->hero_image_url }}" alt="Hero image" class="w-24 h-16 rounded-lg object-cover border border-slate-700 bg-slate-900">
                    @else
                        <div class="w-24 h-16 rounded-lg border-2 border-dashed border-slate-700 bg-slate-900/60"></div>
                    @endif
                </div>
                <div class="flex-1 space-y-2" x-data="{ fileName: '' }">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 border border-slate-600 text-sm text-slate-300 transition">
                            Choose file
                        </span>
                        <span class="text-sm text-slate-400" x-text="fileName || 'No file selected'"></span>
                        <input type="file" name="hero_image" accept="image/*" class="sr-only" @change="fileName = $event.target.files[0]?.name || ''">
                    </label>
                    <p class="text-xs text-slate-500">JPEG, PNG or WebP · Max 4 MB. Falls back to the template's default photo if not set.</p>
                    @error('hero_image')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="shrink-0 px-4 py-2 bg-[#0078D4] hover:bg-[#0078D4]/90 text-white text-sm rounded-lg font-medium transition-colors">Upload</button>
            </form>
        </div>

        <div x-show="step === 3" x-cloak class="bg-slate-800 rounded-xl border border-slate-700 p-6">
            <h3 class="text-sm font-semibold text-slate-100 mb-4">About Photo</h3>
            <form method="POST" action="{{ route('website.branding.about-image') }}" enctype="multipart/form-data" class="flex items-center gap-6">
                @csrf
                <div class="shrink-0">
                    @if($branding->about_image_url)
                        <img src="{{ $branding->about_image_url }}" alt="About photo" class="w-24 h-16 rounded-lg object-cover border border-slate-700 bg-slate-900">
                    @else
                        <div class="w-24 h-16 rounded-lg border-2 border-dashed border-slate-700 bg-slate-900/60"></div>
                    @endif
                </div>
                <div class="flex-1 space-y-2" x-data="{ fileName: '' }">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 border border-slate-600 text-sm text-slate-300 transition">
                            Choose file
                        </span>
                        <span class="text-sm text-slate-400" x-text="fileName || 'No file selected'"></span>
                        <input type="file" name="about_image" accept="image/*" class="sr-only" @change="fileName = $event.target.files[0]?.name || ''">
                    </label>
                    <p class="text-xs text-slate-500">JPEG, PNG or WebP · Max 4 MB. Shown next to your business description on the About section.</p>
                    @error('about_image')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="shrink-0 px-4 py-2 bg-[#0078D4] hover:bg-[#0078D4]/90 text-white text-sm rounded-lg font-medium transition-colors">Upload</button>
            </form>
        </div>

        <div x-show="step === 3" x-cloak class="bg-slate-800 rounded-xl border border-slate-700 p-6">
            <h3 class="text-sm font-semibold text-slate-100 mb-1">Gallery Photos</h3>
            <p class="text-xs text-slate-500 mb-4">Add up to 8 of your own photos to replace the template's stock gallery.</p>

            @php $gallery = $branding->gallery_images ?? []; @endphp
            @if(count($gallery))
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
                    @foreach($gallery as $i => $url)
                        <div class="relative group">
                            <img src="{{ $url }}" alt="Gallery photo" class="w-full h-24 rounded-lg object-cover border border-slate-700 bg-slate-900">
                            <form method="POST" action="{{ route('website.branding.gallery-image.destroy', $i) }}" class="absolute top-1 right-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit" aria-label="Remove photo"
                                        class="w-6 h-6 rounded-full bg-slate-950/80 text-slate-300 hover:text-red-400 hover:bg-slate-950 flex items-center justify-center text-xs transition-colors">
                                    &times;
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif

            @if(count($gallery) < 8)
                <form method="POST" action="{{ route('website.branding.gallery-image.store') }}" enctype="multipart/form-data" class="flex items-center gap-6">
                    @csrf
                    <div class="flex-1 space-y-2" x-data="{ fileName: '' }">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 border border-slate-600 text-sm text-slate-300 transition">
                                Choose file
                            </span>
                            <span class="text-sm text-slate-400" x-text="fileName || 'No file selected'"></span>
                            <input type="file" name="gallery_image" accept="image/*" class="sr-only" @change="fileName = $event.target.files[0]?.name || ''">
                        </label>
                        <p class="text-xs text-slate-500">JPEG, PNG or WebP · Max 4 MB · {{ 8 - count($gallery) }} slot{{ 8 - count($gallery) === 1 ? '' : 's' }} left.</p>
                        @error('gallery_image')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="shrink-0 px-4 py-2 bg-[#0078D4] hover:bg-[#0078D4]/90 text-white text-sm rounded-lg font-medium transition-colors">Add photo</button>
                </form>
            @endif
        </div>

    </div>
</x-app-layout>
