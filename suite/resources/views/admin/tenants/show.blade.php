<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.tenants.index') }}" class="text-slate-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            {{ $tenant->name }}
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm">{{ session('error') }}</div>
    @endif

    <div class="grid lg:grid-cols-3 gap-6">

        <!-- Left: Info + Subdomain -->
        <div class="space-y-4">

            <!-- Info card -->
            <div class="bg-slate-800 rounded-2xl border border-slate-700 p-5">
                <h2 class="text-sm font-semibold text-[#D4AF37] mb-4">Tenant Info</h2>
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-xs text-slate-400 mb-0.5">Name</dt><dd class="text-white font-medium">{{ $tenant->name }}</dd></div>
                    <div><dt class="text-xs text-slate-400 mb-0.5">Email</dt><dd class="text-slate-300">{{ $tenant->email }}</dd></div>
                    @if($tenant->phone)
                        <div><dt class="text-xs text-slate-400 mb-0.5">Phone</dt><dd class="text-slate-300">{{ $tenant->phone }}</dd></div>
                    @endif
                    @if($tenant->address)
                        <div><dt class="text-xs text-slate-400 mb-0.5">Address</dt><dd class="text-slate-300 text-xs leading-relaxed">{{ $tenant->address }}</dd></div>
                    @endif
                    @if($tenant->vat_number)
                        <div><dt class="text-xs text-slate-400 mb-0.5">VAT Number</dt><dd class="text-slate-300">{{ $tenant->vat_number }}</dd></div>
                    @endif
                    @if($tenant->industry)
                        <div><dt class="text-xs text-slate-400 mb-0.5">Industry</dt><dd class="text-slate-300 capitalize">{{ $tenant->industry }}</dd></div>
                    @endif
                    <div><dt class="text-xs text-slate-400 mb-0.5">Slug</dt><dd class="font-mono text-xs text-[#0078D4]">{{ $tenant->slug }}</dd></div>
                    <div>
                        <dt class="text-xs text-slate-400 mb-0.5">Status</dt>
                        <dd class="{{ $tenant->is_active ? 'text-emerald-400' : 'text-red-400' }} font-medium">
                            {{ $tenant->is_active ? 'Active' : 'Inactive' }}
                            @if($tenant->isOnTrial())
                                <span class="ml-2 text-amber-400 font-normal">(Trial until {{ $tenant->trial_ends_at->format('d M Y') }})</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400 mb-0.5">Monthly Total</dt>
                        <dd class="text-xl font-bold text-[#0078D4]">R{{ number_format($tenant->monthlyTotal(), 2) }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Subdomain -->
            <div class="bg-slate-800 rounded-2xl border border-slate-700 p-5">
                <h2 class="text-sm font-semibold text-[#D4AF37] mb-4">Domain Settings</h2>
                <form action="{{ route('admin.tenants.subdomain', $tenant) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="flex gap-2 items-center mb-2">
                        <input type="text" name="subdomain" value="{{ $tenant->subdomain ?? $tenant->slug }}"
                               class="flex-1 bg-slate-700 border border-slate-600 text-white text-sm rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#0078D4]"
                               placeholder="slug">
                        <span class="text-xs text-slate-400 shrink-0">.{{ config('app.domain', 'xquisite.co.za') }}</span>
                    </div>
                    <button type="submit" class="w-full bg-[#0078D4] hover:bg-[#002B5B] text-white text-sm font-medium py-2 rounded-xl transition-colors">
                        Update Subdomain
                    </button>
                </form>

                <div class="mt-3 pt-3 border-t border-slate-700 text-xs text-slate-400">
                    <p class="mb-1">Storefront URL:</p>
                    <a href="{{ $tenant->storefront_url }}" target="_blank" class="text-[#0078D4] break-all">{{ $tenant->storefront_url }}</a>
                </div>

                @if($tenant->custom_domain)
                    <div class="mt-3 pt-3 border-t border-slate-700">
                        <p class="text-xs text-slate-400 mb-1">Custom Domain</p>
                        <p class="text-sm text-white font-mono">{{ $tenant->custom_domain }}</p>
                        <span class="text-xs {{ $tenant->custom_domain_verified ? 'text-emerald-400' : 'text-amber-400' }}">
                            {{ $tenant->custom_domain_verified ? '✓ Verified' : '⚠ Pending verification' }}
                        </span>
                    </div>
                @endif
            </div>

            <!-- Tenant Actions -->
            <div class="bg-slate-800 rounded-2xl border border-slate-700 p-5">
                <h2 class="text-sm font-semibold text-[#D4AF37] mb-3">Account Control</h2>
                <div class="flex gap-2">
                    @if($tenant->is_active)
                        <form action="{{ route('admin.tenants.suspend', $tenant) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" onclick="return confirm('Suspend {{ addslashes($tenant->name) }}? They will lose access immediately.')"
                                    class="w-full text-xs font-semibold px-3 py-2 rounded-lg bg-red-500/15 text-red-400 hover:bg-red-500/25 transition-colors">
                                Suspend Tenant
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.tenants.activate', $tenant) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full text-xs font-semibold px-3 py-2 rounded-lg bg-emerald-500/15 text-emerald-400 hover:bg-emerald-500/25 transition-colors">
                                Reactivate Tenant
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('admin.tenants.messages', $tenant) }}"
                       class="flex-1 text-center text-xs font-semibold px-3 py-2 rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600 transition-colors">
                        Message Tenant
                    </a>
                </div>
            </div>

            <!-- Users + IT Support -->
            <div class="bg-slate-800 rounded-2xl border border-slate-700 p-5">
                <h2 class="text-sm font-semibold text-[#D4AF37] mb-3">Users ({{ $tenant->users->count() }}) — IT Support</h2>
                <div class="space-y-3">
                    @foreach($tenant->users as $user)
                        <div class="rounded-xl border border-slate-700 p-3 space-y-2">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white shrink-0
                                    {{ $user->is_active ? 'bg-[#0078D4]' : 'bg-slate-600' }}">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm text-white font-medium truncate">{{ $user->name }}</p>
                                    <p class="text-xs text-slate-400 truncate">{{ $user->email }}</p>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    @php $userRole = $user->getRoleNames()->first(); @endphp
                                    <span class="text-xs capitalize {{ $userRole === 'tenant-owner' ? 'text-[#D4AF37]' : 'text-slate-500' }}">{{ $userRole ? str_replace('-', ' ', $userRole) : '—' }}</span>
                                    <span class="text-xs {{ $user->is_active ? 'text-emerald-400' : 'text-red-400' }}">
                                        {{ $user->is_active ? '● Active' : '● Inactive' }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex gap-2 pt-1">
                                <form action="{{ route('admin.tenants.users.reset-password', [$tenant, $user]) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Reset password for {{ addslashes($user->name) }}? They will receive an email with a temporary password.')"
                                            class="w-full text-xs px-2 py-1.5 rounded-lg bg-amber-500/15 text-amber-400 hover:bg-amber-500/25 transition-colors">
                                        Reset Password
                                    </button>
                                </form>
                                <form action="{{ route('admin.tenants.users.toggle', [$tenant, $user]) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit"
                                            class="w-full text-xs px-2 py-1.5 rounded-lg transition-colors
                                            {{ $user->is_active ? 'bg-red-500/15 text-red-400 hover:bg-red-500/25' : 'bg-emerald-500/15 text-emerald-400 hover:bg-emerald-500/25' }}">
                                        {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            @if($tenant->moduleRequests->where('status', 'pending')->isNotEmpty())
                <div class="bg-slate-800 rounded-2xl border border-slate-700 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-sm font-semibold text-[#D4AF37]">Pending Module Requests</h2>
                            <p class="text-xs text-slate-400">Review requests for this tenant before approving or rejecting.</p>
                        </div>
                        <a href="{{ route('admin.module-requests.index') }}" class="text-xs text-[#0078D4] hover:text-white">View all requests</a>
                    </div>

                    <div class="space-y-3">
                        @foreach($tenant->moduleRequests->where('status', 'pending') as $request)
                            <div class="rounded-xl border border-amber-500/20 bg-amber-500/5 p-4 text-sm text-slate-200">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-white">{{ $request->module_name }}</p>
                                        <p class="text-xs text-slate-400">{{ ucfirst($request->type) }} requested by {{ $request->user->name }}</p>
                                    </div>
                                    <span class="text-xs text-amber-300 uppercase tracking-wide">Pending</span>
                                </div>
                                @if($request->notes)
                                    <p class="mt-3 text-xs text-slate-400">"{{ $request->notes }}"</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Right: Module Management -->
        <div class="lg:col-span-2">
            <div class="bg-slate-800 rounded-2xl border border-slate-700 p-5">
                <h2 class="text-sm font-semibold text-[#D4AF37] mb-1">Modules</h2>
                <p class="text-xs text-slate-400 mb-5">Toggle modules on or off. Changes take effect immediately.</p>

                <div class="space-y-3">
                    @foreach($allModules as $key => $module)
                        @php
                            $tenantModule = $tenant->tenantModules->firstWhere('module', $key);
                            $isActive = $tenantModule?->is_active ?? false;
                            $price    = $tenantModule?->price_override ?? $module['price'];
                        @endphp

                        <div class="flex items-start gap-4 p-4 rounded-xl border {{ $isActive ? 'border-[#0078D4]/40 bg-[#0078D4]/5' : 'border-slate-700 bg-slate-900/30' }}">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <p class="text-sm font-medium text-white">{{ $module['name'] }}</p>
                                    @if($isActive)
                                        <span class="text-xs bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded-full">Active</span>
                                    @else
                                        <span class="text-xs bg-slate-700 text-slate-400 px-2 py-0.5 rounded-full">Inactive</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-400">{{ $module['description'] }}</p>
                                @if($isActive && $tenantModule?->activated_at)
                                    <p class="text-xs text-slate-500 mt-1">Active since {{ $tenantModule->activated_at->format('d M Y') }}</p>
                                @endif
                            </div>

                            <div class="shrink-0 text-right">
                                <p class="text-sm font-bold text-white mb-2">R{{ number_format($price, 0) }}<span class="text-xs text-slate-400 font-normal">/mo</span></p>

                                <form action="{{ route('admin.tenants.module', $tenant) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="module" value="{{ $key }}">
                                    <input type="hidden" name="active" value="{{ $isActive ? '0' : '1' }}">
                                    <button type="submit"
                                            class="text-xs font-semibold px-4 py-1.5 rounded-lg transition-colors {{ $isActive ? 'bg-red-500/20 text-red-400 hover:bg-red-500/30' : 'bg-[#0078D4] hover:bg-[#002B5B] text-white' }}">
                                        {{ $isActive ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-5 pt-4 border-t border-slate-700 flex justify-between items-center">
                    <p class="text-xs text-slate-400">{{ $tenant->activeModules->count() }} active modules</p>
                    <p class="text-sm font-bold text-white">
                        Monthly total: <span class="text-[#0078D4]">R{{ number_format($tenant->monthlyTotal(), 2) }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
