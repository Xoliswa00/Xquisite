<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-[#D4AF37]">Account & Settings</h1>
    </x-slot>

    <div class="max-w-5xl space-y-6">

        {{-- Flash messages --}}
        @foreach(['profile-updated' => 'Account details saved.', 'business-updated' => 'Business profile saved.', 'password-updated' => 'Password updated.'] as $key => $msg)
            @if(session('status') === $key)
                <div class="flex items-center gap-3 rounded-xl bg-emerald-900/30 border border-emerald-700/50 px-4 py-3 text-sm text-emerald-400">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    {{ $msg }}
                </div>
            @endif
        @endforeach

        {{-- ── 1. YOUR ACCOUNT ─────────────────────────────────────────── --}}
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
            <div class="border-b border-slate-800 px-8 py-6">
                <h2 class="text-xl font-semibold text-slate-100 tracking-tight">Your Account</h2>
                <p class="mt-1 text-sm text-slate-400">Your personal login details for this platform.</p>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="px-8 py-8 space-y-8">
                @csrf
                @method('PATCH')
                <x-form-errors />

                {{-- Basic Details --}}
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-4">Basic Details</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <label for="name" class="flex items-center gap-2 text-sm font-medium text-slate-300">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                                Full Name
                            </label>
                            <input type="text" id="name" name="name"
                                   value="{{ old('name', $user->name) }}" required
                                   class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4]/50 focus:border-[#0078D4] transition @error('name') border-red-500 @enderror">
                            @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2 text-sm font-medium text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                Role
                            </label>
                            <div class="w-full bg-slate-800/50 border border-slate-700/50 text-slate-500 text-sm rounded-xl px-3.5 py-2.5">
                                {{ ucfirst(str_replace(['-', '_'], ' ', $user->roles->first()?->name ?? 'User')) }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Contact --}}
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-4">Contact</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <label for="email" class="flex items-center gap-2 text-sm font-medium text-slate-300">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                                Email Address
                            </label>
                            <input type="email" id="email" name="email"
                                   value="{{ old('email', $user->email) }}" required
                                   class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4]/50 focus:border-[#0078D4] transition @error('email') border-red-500 @enderror">
                            @error('email')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                            @if($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                                <p class="text-xs text-amber-400">
                                    Email unverified.
                                    <button form="send-verification" class="underline hover:text-amber-300">Resend</button>
                                </p>
                            @endif
                        </div>
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2 text-sm font-medium text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                Verified
                            </label>
                            @if($user->hasVerifiedEmail())
                                <div class="inline-flex items-center gap-1.5 text-xs text-emerald-400 bg-emerald-900/30 border border-emerald-700/40 rounded-xl px-3.5 py-2.5 w-full">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                                    Email verified
                                </div>
                            @else
                                <div class="inline-flex items-center gap-1.5 text-xs text-amber-400 bg-amber-900/20 border border-amber-700/40 rounded-xl px-3.5 py-2.5 w-full">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                                    Not verified
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-800 pt-6">
                    <button type="button" onclick="location.reload()"
                            class="px-4 py-2 text-sm font-medium text-slate-400 hover:text-white bg-slate-800 border border-slate-700 hover:border-slate-500 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-medium rounded-xl transition">
                        Save Account
                    </button>
                </div>
            </form>

            <form id="send-verification" method="POST" action="{{ route('verification.send') }}" class="hidden">@csrf</form>
        </div>

        {{-- ── 2. BUSINESS DETAILS ────────────────────────────────────────── --}}
        @if($tenant)
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden"
             x-data="businessForm(
                 '{{ old('business_name', $tenant->name ?? '') }}',
                 '{{ old('slug', $tenant->slug ?? '') }}'
             )">
            <div class="border-b border-slate-800 px-8 py-6">
                <h2 class="text-xl font-semibold text-slate-100 tracking-tight">Business Details</h2>
                <p class="mt-1 text-sm text-slate-400">What clients see on your booking portal and invoices.</p>
            </div>

            <form method="POST" action="{{ route('profile.business.update') }}" class="px-8 py-8 space-y-8">
                @csrf
                @method('PATCH')

                {{-- Business Identity --}}
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-4">Business Identity</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <label for="business_name" class="flex items-center gap-2 text-sm font-medium text-slate-300">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                                Business Name
                            </label>
                            <input type="text" id="business_name" name="business_name"
                                   x-model="businessName" @input="onNameInput()" required
                                   class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4]/50 focus:border-[#0078D4] transition @error('business_name') border-red-500 @enderror">
                            @error('business_name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="slug" class="flex items-center gap-2 text-sm font-medium text-slate-300">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/></svg>
                                Booking URL Slug
                            </label>
                            <input type="text" id="slug" name="slug"
                                   x-model="slug" @input="slug = toSlug(slug)" required
                                   placeholder="e.g. glam-by-xoliswa"
                                   class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4]/50 focus:border-[#0078D4] transition @error('slug') border-red-500 @enderror">
                            @error('slug')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror

                            {{-- URL preview --}}
                            <div class="flex items-center gap-2 bg-slate-800/60 border border-slate-700/50 rounded-lg px-3 py-1.5 text-xs font-mono text-slate-500">
                                <span class="shrink-0">{{ url('/book/') }}/</span>
                                <span class="text-[#0078D4] font-semibold" x-text="slug || '…'"></span>
                                <button type="button" @click="copyUrl()"
                                        class="ml-auto shrink-0 px-2 py-0.5 rounded border border-slate-700 hover:border-slate-500 text-slate-500 hover:text-slate-300 transition">
                                    Copy
                                </button>
                            </div>

                            <div x-show="slugChanged" x-cloak
                                 class="flex items-start gap-2 rounded-lg bg-amber-900/25 border border-amber-700/50 px-3 py-2.5">
                                <svg class="shrink-0 w-4 h-4 text-amber-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <p class="text-xs text-amber-400">
                                    <span class="font-semibold">URL will change.</span>
                                    Shared booking links will stop working.
                                    Old: <span class="font-mono">{{ url('/book/' . $tenant->slug) }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Contact --}}
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-4">Contact Information</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <label for="biz_email" class="flex items-center gap-2 text-sm font-medium text-slate-300">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                                Business Email
                            </label>
                            <input type="email" id="biz_email" name="email"
                                   value="{{ old('email', $tenant->email) }}"
                                   placeholder="hello@mybusiness.co.za"
                                   class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4]/50 focus:border-[#0078D4] transition">
                        </div>
                        <div class="space-y-1.5">
                            <label for="phone" class="flex items-center gap-2 text-sm font-medium text-slate-300">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.338c0-1.01.666-1.874 1.628-2.133l3.028-.78c.463-.12.94.136 1.11.58l1.308 3.39a1.125 1.125 0 0 1-.327 1.266l-1.29 1.072a10.53 10.53 0 0 0 4.53 4.53l1.072-1.29a1.125 1.125 0 0 1 1.266-.327l3.39 1.307c.445.172.7.65.58 1.112l-.78 3.027A2.25 2.25 0 0 1 17.663 21.75C9.226 21.75 2.25 14.774 2.25 6.338Z"/></svg>
                                Business Phone
                            </label>
                            <input type="tel" id="phone" name="phone"
                                   value="{{ old('phone', $tenant->phone) }}"
                                   placeholder="e.g. 011 123 4567"
                                   class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4]/50 focus:border-[#0078D4] transition">
                        </div>
                    </div>
                </div>

                {{-- Location --}}
                <div x-data="locationDetector()">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500">Location</h3>
                        <button type="button" @click="detect()" :disabled="locating"
                                class="flex items-center gap-1.5 text-xs text-[#0078D4] hover:text-[#B8D4F0] disabled:opacity-50 transition">
                            <svg class="w-3.5 h-3.5" :class="locating ? 'animate-spin' : ''"
                                 fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                            </svg>
                            <span x-text="locating ? 'Detecting…' : 'Use my location'"></span>
                        </button>
                    </div>
                    <div class="space-y-1.5">
                        <label for="address" class="flex items-center gap-2 text-sm font-medium text-slate-300">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                            Physical Address
                        </label>
                        <textarea id="address" name="address" x-ref="addressField" rows="2"
                                  placeholder="e.g. 15 Oak Avenue, Sandton, Johannesburg, 2196"
                                  class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4]/50 focus:border-[#0078D4] transition resize-none @error('address') border-red-500 @enderror">{{ old('address', $tenant->address) }}</textarea>
                        @error('address')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                        <p x-show="locationError" class="text-xs text-red-400" x-text="locationError"></p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-800 pt-6">
                    <button type="button" onclick="location.reload()"
                            class="px-4 py-2 text-sm font-medium text-slate-400 hover:text-white bg-slate-800 border border-slate-700 hover:border-slate-500 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-medium rounded-xl transition">
                        Save Business Details
                    </button>
                </div>
            </form>
        </div>

        {{-- ── 3. BANKING DETAILS ─────────────────────────────────────────── --}}
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden"
             x-data="bankForm('{{ old('bank_name', $tenant->bank_name ?? '') }}')">
            <div class="border-b border-slate-800 px-8 py-6">
                <h2 class="text-xl font-semibold text-slate-100 tracking-tight">Banking Details</h2>
                <p class="mt-1 text-sm text-slate-400">Used on invoices and proof-of-payment requests sent to clients.</p>
            </div>

            <form method="POST" action="{{ route('profile.business.update') }}" class="px-8 py-8 space-y-8">
                @csrf
                @method('PATCH')
                <input type="hidden" name="business_name" value="{{ $tenant->name }}">
                <input type="hidden" name="slug"          value="{{ $tenant->slug }}">
                <input type="hidden" name="email"         value="{{ $tenant->email }}">
                <input type="hidden" name="phone"         value="{{ $tenant->phone }}">
                <input type="hidden" name="address"       value="{{ $tenant->address }}">

                <div>
                    <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-4">Bank Account</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="space-y-1.5">
                            <label for="bank_name" class="flex items-center gap-2 text-sm font-medium text-slate-300">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z"/></svg>
                                Bank
                            </label>
                            <select id="bank_name" name="bank_name" x-model="bankName" @change="onBankChange()"
                                    class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4]/50 focus:border-[#0078D4] transition">
                                <option value="">— Select bank —</option>
                                @foreach(['ABSA','African Bank','Bidvest Bank','Capitec Bank','Discovery Bank','FNB','Investec','Nedbank','Standard Bank','TymeBank','Other'] as $bank)
                                    <option value="{{ $bank }}" @selected(old('bank_name', $tenant->bank_name) === $bank)>{{ $bank }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label for="bank_account_type" class="flex items-center gap-2 text-sm font-medium text-slate-300">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></svg>
                                Account Type
                            </label>
                            <select id="bank_account_type" name="bank_account_type"
                                    class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4]/50 focus:border-[#0078D4] transition">
                                <option value="">— Select —</option>
                                <option value="cheque"  @selected(old('bank_account_type', $tenant->bank_account_type) === 'cheque')>Cheque / Current</option>
                                <option value="savings" @selected(old('bank_account_type', $tenant->bank_account_type) === 'savings')>Savings</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label for="bank_account_number" class="flex items-center gap-2 text-sm font-medium text-slate-300">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h-.75A2.25 2.25 0 0 0 4.5 9.75v7.5a2.25 2.25 0 0 0 2.25 2.25h7.5a2.25 2.25 0 0 0 2.25-2.25v-7.5a2.25 2.25 0 0 0-2.25-2.25h-.75m0-3-3-3m0 0-3 3m3-3v11.25m6-2.25h.75a2.25 2.25 0 0 1 2.25 2.25v7.5a2.25 2.25 0 0 1-2.25 2.25h-7.5a2.25 2.25 0 0 1-2.25-2.25v-.75"/></svg>
                                Account Number
                            </label>
                            <input type="text" id="bank_account_number" name="bank_account_number"
                                   value="{{ old('bank_account_number', $tenant->bank_account_number) }}"
                                   class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4]/50 focus:border-[#0078D4] transition">
                        </div>

                        <div class="space-y-1.5">
                            <label for="bank_branch_code" class="flex items-center gap-2 text-sm font-medium text-slate-300">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5"/></svg>
                                Branch Code
                            </label>
                            <input type="text" id="bank_branch_code" name="bank_branch_code" x-model="branchCode"
                                   placeholder="Auto-filled"
                                   class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4]/50 focus:border-[#0078D4] transition">
                            <p x-show="bankName && bankName !== 'Other'" x-cloak class="text-xs text-slate-500">Universal code auto-filled.</p>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-4">Account Holder</h3>
                    <div class="max-w-sm space-y-1.5">
                        <label for="bank_account_holder" class="flex items-center gap-2 text-sm font-medium text-slate-300">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                            Account Holder Name
                        </label>
                        <input type="text" id="bank_account_holder" name="bank_account_holder"
                               value="{{ old('bank_account_holder', $tenant->bank_account_holder) }}"
                               placeholder="As it appears on the account"
                               class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4]/50 focus:border-[#0078D4] transition">
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-800 pt-6">
                    <button type="button" onclick="location.reload()"
                            class="px-4 py-2 text-sm font-medium text-slate-400 hover:text-white bg-slate-800 border border-slate-700 hover:border-slate-500 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-medium rounded-xl transition">
                        Save Banking Details
                    </button>
                </div>
            </form>
        </div>
        @endif

        {{-- ── 4. CHANGE PASSWORD ──────────────────────────────────────────── --}}
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
            <div class="border-b border-slate-800 px-8 py-6">
                <h2 class="text-xl font-semibold text-slate-100 tracking-tight">Change Password</h2>
                <p class="mt-1 text-sm text-slate-400">Use a strong password you don't use elsewhere.</p>
            </div>

            <form method="POST" action="{{ route('password.update') }}" class="px-8 py-8 space-y-8">
                @csrf
                @method('PUT')

                <div>
                    <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-4">Update Password</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="space-y-1.5">
                            <label for="current_password" class="flex items-center gap-2 text-sm font-medium text-slate-300">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                                Current Password
                            </label>
                            <input type="password" id="current_password" name="current_password"
                                   autocomplete="current-password"
                                   class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4]/50 focus:border-[#0078D4] transition @error('current_password', 'updatePassword') border-red-500 @enderror">
                            @error('current_password', 'updatePassword')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div class="space-y-1.5">
                            <label for="password" class="flex items-center gap-2 text-sm font-medium text-slate-300">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 0 1 21.75 8.25Z"/></svg>
                                New Password
                            </label>
                            <input type="password" id="password" name="password"
                                   autocomplete="new-password"
                                   class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4]/50 focus:border-[#0078D4] transition @error('password', 'updatePassword') border-red-500 @enderror">
                            @error('password', 'updatePassword')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div class="space-y-1.5">
                            <label for="password_confirmation" class="flex items-center gap-2 text-sm font-medium text-slate-300">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 0 1 21.75 8.25Z"/></svg>
                                Confirm Password
                            </label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                   autocomplete="new-password"
                                   class="w-full bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4]/50 focus:border-[#0078D4] transition">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-800 pt-6">
                    <button type="button" onclick="location.reload()"
                            class="px-4 py-2 text-sm font-medium text-slate-400 hover:text-white bg-slate-800 border border-slate-700 hover:border-slate-500 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-medium rounded-xl transition">
                        Update Password
                    </button>
                </div>
            </form>
        </div>

        {{-- ── 5. DANGER ZONE ──────────────────────────────────────────────── --}}
        <div class="bg-red-950/20 border border-red-900/40 rounded-2xl overflow-hidden"
             x-data="{ open: false, password: '' }">
            <div class="border-b border-red-900/30 px-8 py-6">
                <h2 class="text-xl font-semibold text-red-400 tracking-tight">Danger Zone</h2>
                <p class="mt-1 text-sm text-slate-400">Permanent actions that cannot be undone.</p>
            </div>

            <div class="px-8 py-8">
                <div class="flex items-start justify-between gap-6">
                    <div>
                        <p class="text-sm font-medium text-slate-300">Delete Account</p>
                        <p class="mt-0.5 text-sm text-slate-500">Once deleted, all your data is permanently removed. This cannot be undone.</p>
                    </div>
                    <button type="button" @click="open = !open"
                            class="shrink-0 px-4 py-2 border border-red-700/60 text-red-400 hover:bg-red-900/30 text-sm font-medium rounded-xl transition">
                        Delete Account
                    </button>
                </div>

                <div x-show="open" x-cloak x-transition
                     class="mt-6 rounded-xl bg-slate-900 border border-red-900/50 p-6 space-y-4">
                    <p class="text-sm text-slate-300">Enter your password to permanently delete your account.</p>
                    <form method="POST" action="{{ route('profile.destroy') }}" class="space-y-3">
                        @csrf
                        @method('DELETE')
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2 text-sm font-medium text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                                Password
                            </label>
                            <input type="password" name="password" x-model="password"
                                   placeholder="Your current password"
                                   class="w-full max-w-sm bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500 transition">
                            @error('password', 'userDeletion')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex gap-3">
                            <button type="submit" :disabled="!password"
                                    class="px-4 py-2 bg-red-700 hover:bg-red-600 disabled:opacity-40 text-white text-sm font-semibold rounded-xl transition">
                                Yes, delete permanently
                            </button>
                            <button type="button" @click="open = false; password = ''"
                                    class="px-4 py-2 text-slate-400 hover:text-white text-sm transition">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
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
            branchCode: "{{ old('bank_branch_code', optional($tenant)->bank_branch_code ?? '') }}",

            onBankChange() {
                const code = SA_BRANCH_CODES[this.bankName];
                if (code) this.branchCode = code;
            },
        };
    }
    </script>
</x-app-layout>
