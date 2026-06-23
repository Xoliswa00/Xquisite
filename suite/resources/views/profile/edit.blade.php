<x-app-layout>
    <x-slot name="header">Account & Business</x-slot>

    <div class="max-w-2xl space-y-6">

        {{-- Flash --}}
        @if(session('status') === 'profile-updated')
            <div class="rounded-xl bg-emerald-900/30 border border-emerald-700/50 px-4 py-3 text-sm text-emerald-400">
                Account details saved.
            </div>
        @endif
        @if(session('status') === 'business-updated')
            <div class="rounded-xl bg-emerald-900/30 border border-emerald-700/50 px-4 py-3 text-sm text-emerald-400">
                Business profile saved.
            </div>
        @endif
        @if(session('status') === 'password-updated')
            <div class="rounded-xl bg-emerald-900/30 border border-emerald-700/50 px-4 py-3 text-sm text-emerald-400">
                Password updated.
            </div>
        @endif

        {{-- ── 1. YOUR ACCOUNT ───────────────────────────────────────── --}}
        <div class="bg-slate-800 rounded-xl p-6">
            <h3 class="text-base font-semibold text-slate-100 mb-0.5">Your Account</h3>
            <p class="text-xs text-slate-400 mb-5">Your personal login details for this platform.</p>

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf
                @method('PATCH')
                <x-form-errors />

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('name') border-red-500 @enderror">
                    @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('email') border-red-500 @enderror">
                    @error('email')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    @if($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                        <p class="mt-1 text-xs text-amber-400">
                            Email unverified.
                            <button form="send-verification" class="underline hover:text-amber-300">Resend verification</button>
                        </p>
                    @endif
                </div>

                <div class="pt-1">
                    <button type="submit"
                            class="px-5 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-medium rounded-lg transition-colors">
                        Save account
                    </button>
                </div>
            </form>

            <form id="send-verification" method="POST" action="{{ route('verification.send') }}" class="hidden">
                @csrf
            </form>
        </div>

        {{-- ── 2. BUSINESS DETAILS ────────────────────────────────────── --}}
        @if($tenant)
        <div class="bg-slate-800 rounded-xl p-6"
             x-data="businessForm(
                 '{{ old('business_name', $tenant->name ?? '') }}',
                 '{{ old('slug', $tenant->slug ?? '') }}'
             )">
            <h3 class="text-base font-semibold text-slate-100 mb-0.5">Business Details</h3>
            <p class="text-xs text-slate-400 mb-5">What clients see on your booking portal and invoices.</p>

            <form method="POST" action="{{ route('profile.business.update') }}" class="space-y-4">
                @csrf
                @method('PATCH')

                {{-- Business name --}}
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Business Name</label>
                    <input type="text" name="business_name" x-model="businessName"
                           @input="onNameInput()" required
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('business_name') border-red-500 @enderror">
                    @error('business_name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                {{-- Booking URL / Slug --}}
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">
                        Booking Portal URL
                    </label>
                    <div class="flex items-center gap-2 p-2 rounded-lg bg-slate-900/60 border border-slate-700 text-sm font-mono text-slate-400 mb-2">
                        <span class="text-slate-500 shrink-0">{{ url('/book/') }}/</span>
                        <span class="text-[#0078D4] font-semibold" x-text="slug || '…'"></span>
                        <button type="button" @click="copyUrl()"
                                class="ml-auto shrink-0 text-xs text-slate-500 hover:text-slate-300 transition-colors px-2 py-0.5 rounded border border-slate-700 hover:border-slate-500">
                            Copy
                        </button>
                    </div>
                    <input type="text" name="slug" x-model="slug"
                           @input="slug = toSlug(slug)" required
                           placeholder="e.g. glam-by-xoliswa"
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('slug') border-red-500 @enderror">
                    @error('slug')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    <p class="mt-1 text-xs text-slate-500">Lowercase letters, numbers and hyphens only.</p>

                    {{-- Change warning --}}
                    <div x-show="slugChanged" x-cloak
                         class="mt-2 flex items-start gap-2 rounded-lg bg-amber-900/25 border border-amber-700/50 px-3 py-2.5">
                        <svg class="shrink-0 w-4 h-4 text-amber-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-xs text-amber-400">
                            <span class="font-semibold">URL will change.</span>
                            Any booking links you've shared with clients will stop working.
                            Old URL: <span class="font-mono">{{ url('/book/' . $tenant->slug) }}</span>
                        </p>
                    </div>
                </div>

                {{-- Contact email + phone --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Business Email</label>
                        <input type="email" name="email" value="{{ old('email', $tenant->email) }}"
                               placeholder="e.g. hello@mybusiness.co.za"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('email') border-red-500 @enderror">
                        @error('email')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Business Phone</label>
                        <input type="tel" name="phone" value="{{ old('phone', $tenant->phone) }}"
                               placeholder="e.g. 011 123 4567"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('phone') border-red-500 @enderror">
                        @error('phone')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Address with location detect --}}
                <div x-data="locationDetector()">
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-sm font-medium text-slate-300">Physical Address</label>
                        <button type="button" @click="detect()"
                                :disabled="locating"
                                class="flex items-center gap-1.5 text-xs text-[#0078D4] hover:text-[#B8D4F0] disabled:opacity-50 transition-colors">
                            <svg class="w-3.5 h-3.5" :class="locating ? 'animate-spin' : ''"
                                 fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span x-text="locating ? 'Detecting…' : 'Use my location'"></span>
                        </button>
                    </div>
                    <textarea name="address" x-ref="addressField" rows="2"
                              placeholder="e.g. 15 Oak Avenue, Sandton, Johannesburg, 2196"
                              class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] resize-none @error('address') border-red-500 @enderror">{{ old('address', $tenant->address) }}</textarea>
                    @error('address')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    <p x-show="locationError" class="mt-1 text-xs text-red-400" x-text="locationError"></p>
                </div>

                <div class="pt-1">
                    <button type="submit"
                            class="px-5 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-medium rounded-lg transition-colors">
                        Save business details
                    </button>
                </div>
            </form>
        </div>

        {{-- ── 3. BANKING DETAILS ─────────────────────────────────────── --}}
        <div class="bg-slate-800 rounded-xl p-6"
             x-data="bankForm('{{ old('bank_name', $tenant->bank_name ?? '') }}')">
            <h3 class="text-base font-semibold text-slate-100 mb-0.5">Banking Details</h3>
            <p class="text-xs text-slate-400 mb-5">Used on invoices and proof-of-payment requests for clients.</p>

            <form method="POST" action="{{ route('profile.business.update') }}" class="space-y-4">
                @csrf
                @method('PATCH')
                {{-- Keep other fields as-is so they're not cleared --}}
                <input type="hidden" name="business_name" value="{{ $tenant->name }}">
                <input type="hidden" name="slug" value="{{ $tenant->slug }}">
                <input type="hidden" name="email" value="{{ $tenant->email }}">
                <input type="hidden" name="phone" value="{{ $tenant->phone }}">
                <input type="hidden" name="address" value="{{ $tenant->address }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Bank</label>
                        <select name="bank_name" x-model="bankName" @change="onBankChange()"
                                class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                            <option value="">— Select bank —</option>
                            @foreach(['ABSA','African Bank','Bidvest Bank','Capitec Bank','Discovery Bank','FNB','Investec','Nedbank','Standard Bank','TymeBank','Other'] as $bank)
                                <option value="{{ $bank }}" @selected(old('bank_name', $tenant->bank_name) === $bank)>{{ $bank }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Account Type</label>
                        <select name="bank_account_type"
                                class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                            <option value="">— Select —</option>
                            <option value="cheque" @selected(old('bank_account_type', $tenant->bank_account_type) === 'cheque')>Cheque / Current</option>
                            <option value="savings" @selected(old('bank_account_type', $tenant->bank_account_type) === 'savings')>Savings</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Account Holder Name</label>
                    <input type="text" name="bank_account_holder"
                           value="{{ old('bank_account_holder', $tenant->bank_account_holder) }}"
                           placeholder="As it appears on the account"
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Account Number</label>
                        <input type="text" name="bank_account_number"
                               value="{{ old('bank_account_number', $tenant->bank_account_number) }}"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Branch Code</label>
                        <input type="text" name="bank_branch_code" x-model="branchCode"
                               placeholder="Auto-filled for known banks"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                    </div>
                </div>

                {{-- Branch code quick reference --}}
                <div x-show="bankName && bankName !== 'Other'" x-cloak
                     class="text-xs text-slate-500 -mt-2">
                    Universal branch code auto-filled — you may override if needed.
                </div>

                <div class="pt-1">
                    <button type="submit"
                            class="px-5 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-medium rounded-lg transition-colors">
                        Save banking details
                    </button>
                </div>
            </form>
        </div>
        @endif

        {{-- ── 4. CHANGE PASSWORD ─────────────────────────────────────── --}}
        <div class="bg-slate-800 rounded-xl p-6">
            <h3 class="text-base font-semibold text-slate-100 mb-0.5">Change Password</h3>
            <p class="text-xs text-slate-400 mb-5">Use a strong password that you don't use elsewhere.</p>

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Current Password</label>
                    <input type="password" name="current_password" autocomplete="current-password"
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('current_password', 'updatePassword') border-red-500 @enderror">
                    @error('current_password', 'updatePassword')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">New Password</label>
                    <input type="password" name="password" autocomplete="new-password"
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('password', 'updatePassword') border-red-500 @enderror">
                    @error('password', 'updatePassword')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Confirm New Password</label>
                    <input type="password" name="password_confirmation" autocomplete="new-password"
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                </div>

                <div class="pt-1">
                    <button type="submit"
                            class="px-5 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-medium rounded-lg transition-colors">
                        Update password
                    </button>
                </div>
            </form>
        </div>

        {{-- ── 5. DANGER ZONE ─────────────────────────────────────────── --}}
        <div class="bg-red-950/20 border border-red-800/30 rounded-xl p-6"
             x-data="{ open: false, password: '' }">
            <h3 class="text-base font-semibold text-red-400 mb-0.5">Danger Zone</h3>
            <p class="text-xs text-slate-400 mb-4">Once your account is deleted all data is permanently removed.</p>

            <button type="button" @click="open = true"
                    class="px-4 py-2 border border-red-700 text-red-400 hover:bg-red-900/30 text-sm rounded-lg transition-colors">
                Delete my account
            </button>

            {{-- Inline confirmation (no modal dependency) --}}
            <div x-show="open" x-cloak x-transition
                 class="mt-4 rounded-xl bg-slate-900 border border-red-800/50 p-5 space-y-4">
                <p class="text-sm text-slate-300">
                    Enter your password to confirm permanent deletion.
                </p>
                <form method="POST" action="{{ route('profile.destroy') }}" class="space-y-3">
                    @csrf
                    @method('DELETE')
                    <input type="password" name="password" x-model="password"
                           placeholder="Your password"
                           class="w-full bg-slate-800 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-red-500">
                    @error('password', 'userDeletion')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    <div class="flex gap-3">
                        <button type="submit" :disabled="!password"
                                class="px-4 py-2 bg-red-700 hover:bg-red-600 disabled:opacity-40 text-white text-sm font-semibold rounded-lg transition-colors">
                            Yes, delete permanently
                        </button>
                        <button type="button" @click="open = false; password = ''"
                                class="px-4 py-2 text-slate-400 hover:text-white text-sm transition-colors">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
    function businessForm(initialName, initialSlug) {
        return {
            businessName: initialName,
            slug: initialSlug,
            originalSlug: initialSlug,
            originalName: initialName,

            get slugChanged() {
                return this.slug !== this.originalSlug && this.originalSlug !== '';
            },

            onNameInput() {
                // Only auto-sync slug if it still matches the original auto-generated value
                const autoFromOriginal = this.toSlug(this.originalName);
                if (this.slug === autoFromOriginal || this.slug === this.originalSlug) {
                    this.slug = this.toSlug(this.businessName);
                }
            },

            toSlug(str) {
                return str.toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .trim()
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-+|-+$/g, '')
                    .substring(0, 60);
            },

            copyUrl() {
                const url = '{{ url('/book/') }}/' + this.slug;
                navigator.clipboard.writeText(url).then(() => {
                    // Brief visual feedback via slug colour change
                    const el = document.querySelector('[x-text="slug || \'…\'"]');
                    if (el) { el.style.color = '#34d399'; setTimeout(() => el.style.color = '', 1200); }
                });
            },
        };
    }

    function locationDetector() {
        return {
            locating: false,
            locationError: '',

            async detect() {
                this.locationError = '';
                if (!navigator.geolocation) {
                    this.locationError = 'Geolocation is not supported by your browser.';
                    return;
                }
                this.locating = true;
                try {
                    const pos = await new Promise((resolve, reject) =>
                        navigator.geolocation.getCurrentPosition(resolve, reject, { timeout: 10000 })
                    );
                    const { latitude, longitude } = pos.coords;
                    const res = await fetch(
                        `https://nominatim.openstreetmap.org/reverse?lat=${latitude}&lon=${longitude}&format=json&addressdetails=1`,
                        { headers: { 'Accept-Language': 'en' } }
                    );
                    const data = await res.json();
                    const a = data.address || {};
                    const parts = [
                        a.road || a.pedestrian || a.footway,
                        a.suburb || a.neighbourhood || a.quarter,
                        a.city || a.town || a.municipality || a.village,
                        a.state,
                        a.postcode,
                        a.country,
                    ].filter(Boolean);
                    this.$refs.addressField.value = parts.join(', ');
                } catch (e) {
                    this.locationError = 'Could not detect location — please enter your address manually.';
                } finally {
                    this.locating = false;
                }
            },
        };
    }

    const SA_BRANCH_CODES = {
        'ABSA':           '632005',
        'African Bank':   '430000',
        'Bidvest Bank':   '462005',
        'Capitec Bank':   '470010',
        'Discovery Bank': '679000',
        'FNB':            '250655',
        'Investec':       '580105',
        'Nedbank':        '198765',
        'Standard Bank':  '051001',
        'TymeBank':       '678910',
    };

    function bankForm(initialBank) {
        return {
            bankName: initialBank,
            branchCode: '{{ old('bank_branch_code', optional($tenant)->bank_branch_code ?? '') }}',

            onBankChange() {
                const code = SA_BRANCH_CODES[this.bankName];
                if (code) this.branchCode = code;
            },
        };
    }
    </script>
</x-app-layout>
