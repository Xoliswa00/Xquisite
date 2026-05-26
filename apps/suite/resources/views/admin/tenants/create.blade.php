<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.tenants.index') }}" class="text-slate-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            New Tenant
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <form action="{{ route('admin.tenants.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Business Details -->
            <div class="bg-slate-800 rounded-2xl border border-slate-700 p-5">
                <h2 class="text-sm font-semibold text-white mb-4">Business Details</h2>
                <div class="space-y-4">
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Business Name *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   class="w-full bg-slate-700 border border-slate-600 text-white text-sm rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                            @error('name')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Industry</label>
                            <select name="industry" class="w-full bg-slate-700 border border-slate-600 text-white text-sm rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">Select…</option>
                                @foreach(['salon', 'spa', 'barbershop', 'beauty', 'nail', 'massage', 'retail', 'other'] as $ind)
                                    <option value="{{ $ind }}" @selected(old('industry') === $ind)>{{ ucfirst($ind) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Business Email *</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   class="w-full bg-slate-700 border border-slate-600 text-white text-sm rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-500 @enderror">
                            @error('email')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Phone</label>
                            <input type="tel" name="phone" value="{{ old('phone') }}"
                                   class="w-full bg-slate-700 border border-slate-600 text-white text-sm rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Trial Period (days)</label>
                            <input type="number" name="trial_days" value="{{ old('trial_days', 14) }}" min="0" max="365"
                                   class="w-full bg-slate-700 border border-slate-600 text-white text-sm rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <p class="text-xs text-slate-500 mt-1">Set to 0 for no trial</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Subdomain</label>
                            <div class="flex items-center gap-1">
                                <input type="text" name="subdomain" value="{{ old('subdomain') }}"
                                       placeholder="auto-generated from name"
                                       class="flex-1 bg-slate-700 border border-slate-600 text-white text-sm rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <span class="text-xs text-slate-500 shrink-0">.{{ config('app.domain', 'xquisite.co.za') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Owner Account -->
            <div class="bg-slate-800 rounded-2xl border border-slate-700 p-5">
                <h2 class="text-sm font-semibold text-white mb-4">Owner Account</h2>
                <div class="space-y-4">
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Full Name *</label>
                            <input type="text" name="owner_name" value="{{ old('owner_name') }}" required
                                   class="w-full bg-slate-700 border border-slate-600 text-white text-sm rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('owner_name') border-red-500 @enderror">
                            @error('owner_name')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Login Email *</label>
                            <input type="email" name="owner_email" value="{{ old('owner_email') }}" required
                                   class="w-full bg-slate-700 border border-slate-600 text-white text-sm rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('owner_email') border-red-500 @enderror">
                            @error('owner_email')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Temporary Password *</label>
                        <input type="text" name="owner_password" value="{{ old('owner_password') }}" required
                               class="w-full bg-slate-700 border border-slate-600 text-white text-sm rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                               placeholder="Min 8 characters">
                        <p class="text-xs text-slate-500 mt-1">The owner should change this on first login</p>
                    </div>
                </div>
            </div>

            <!-- Module Selection -->
            <div class="bg-slate-800 rounded-2xl border border-slate-700 p-5">
                <h2 class="text-sm font-semibold text-white mb-1">Activate Modules</h2>
                <p class="text-xs text-slate-400 mb-4">Select which modules to enable for this tenant from day one.</p>

                <div class="space-y-3">
                    @foreach($allModules as $key => $module)
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-700 hover:border-slate-600 cursor-pointer transition-colors">
                            <input type="checkbox" name="modules[]" value="{{ $key }}"
                                   {{ old('modules') && in_array($key, old('modules')) ? 'checked' : '' }}
                                   class="mt-0.5 accent-indigo-600">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-white">{{ $module['name'] }}</p>
                                <p class="text-xs text-slate-400">{{ $module['description'] }}</p>
                            </div>
                            <span class="shrink-0 text-sm font-bold text-white">R{{ number_format($module['price'], 0) }}<span class="text-xs text-slate-400 font-normal">/mo</span></span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-6 py-3 rounded-xl transition-colors">
                    Create Tenant
                </button>
                <a href="{{ route('admin.tenants.index') }}" class="text-sm text-slate-400 hover:text-white">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
