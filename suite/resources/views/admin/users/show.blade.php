<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.index') }}" class="text-slate-400 hover:text-white transition-colors">← Staff</a>
            <span class="text-slate-600">/</span>
            <h2 class="text-xl font-bold text-[#D4AF37]">{{ $user->name }}</h2>
        </div>
    </x-slot>

    <div class="space-y-5">

        @if(session('success'))
            <div class="px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

            {{-- Basic Info --}}
            <div class="bg-slate-800 rounded-xl border border-slate-700 p-5">
                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Basic Information</h3>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs text-slate-500 mb-0.5">Name</dt>
                        <dd class="font-medium text-white">{{ $user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500 mb-0.5">Email</dt>
                        <dd class="text-slate-300">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500 mb-0.5">Role</dt>
                        <dd>
                            @php
                                $roleColors = [
                                    'super-admin'  => 'bg-amber-500/20 text-amber-300',
                                    'tenant-owner' => 'bg-purple-500/20 text-purple-300',
                                    'manager'      => 'bg-blue-500/20 text-blue-300',
                                    'employee'     => 'bg-emerald-500/20 text-emerald-300',
                                    'client'       => 'bg-slate-500/20 text-slate-300',
                                ];
                                $roleName = $user->getRoleNames()->first();
                            @endphp
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $roleColors[$roleName] ?? 'bg-slate-700 text-slate-300' }}">
                                {{ $roleName ? ucfirst(str_replace('-', ' ', $roleName)) : '—' }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500 mb-0.5">Joined</dt>
                        <dd class="text-slate-300">{{ $user->created_at->format('d M Y') }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Status --}}
            <div class="bg-slate-800 rounded-xl border border-slate-700 p-5">
                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Status</h3>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs text-slate-500 mb-0.5">Account Status</dt>
                        <dd>
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $user->is_active ? 'bg-emerald-500/20 text-emerald-300' : 'bg-red-500/20 text-red-300' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500 mb-0.5">Password</dt>
                        <dd>
                            @if($user->require_password_change)
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium bg-amber-500/20 text-amber-300">⚠ Change Required</span>
                            @else
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium bg-emerald-500/20 text-emerald-300">Set</span>
                            @endif
                        </dd>
                    </div>
                    @if($user->getPermissionNames()->isNotEmpty())
                        <div>
                            <dt class="text-xs text-slate-500 mb-1">Permissions</dt>
                            <dd class="flex flex-wrap gap-1">
                                @foreach($user->getPermissionNames() as $permission)
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-slate-700 text-slate-300">{{ $permission }}</span>
                                @endforeach
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Actions --}}
            @if(!$user->isOwner())
            <div class="bg-slate-800 rounded-xl border border-slate-700 p-5">
                <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-4">Actions</h3>
                <div class="space-y-2">
                    <a href="{{ route('admin.users.edit', $user) }}"
                       class="block w-full text-center px-3 py-2 bg-[#0078D4] hover:bg-[#0078D4] text-white rounded-lg text-sm font-medium transition-colors">
                        Edit Account
                    </a>

                    <form method="POST" action="{{ route('admin.users.reset-password', $user) }}">
                        @csrf
                        <button type="submit" onclick="return confirm('Reset password for {{ $user->name }}?')"
                                class="w-full px-3 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg text-sm font-medium transition-colors">
                            Reset Password
                        </button>
                    </form>

                    @if($user->is_active)
                        <form method="POST" action="{{ route('admin.users.deactivate', $user) }}">
                            @csrf
                            <button type="submit" onclick="return confirm('Deactivate {{ $user->name }}?')"
                                    class="w-full px-3 py-2 border border-amber-800 text-amber-400 hover:bg-amber-900/20 rounded-lg text-sm font-medium transition-colors">
                                Deactivate
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.users.activate', $user) }}">
                            @csrf
                            <button type="submit"
                                    class="w-full px-3 py-2 border border-emerald-800 text-emerald-400 hover:bg-emerald-900/20 rounded-lg text-sm font-medium transition-colors">
                                Reactivate
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
