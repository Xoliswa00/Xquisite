<x-app-layout>
    <x-slot name="header">Staff Management</x-slot>

    <div class="space-y-5">

        <div class="flex items-center justify-between gap-4 flex-wrap">
            <h1 class="text-xl font-bold text-[#D4AF37]">Staff Management</h1>
            <a href="{{ route('admin.users.create') }}"
               class="shrink-0 px-4 py-2 bg-[#0078D4] hover:bg-[#0078D4] text-white text-sm font-medium rounded-lg transition-colors">
                + Add Staff Member
            </a>
        </div>

        {{-- Active / Deleted tabs --}}
        <div class="flex gap-1 bg-slate-800 p-1 rounded-xl overflow-x-auto">
            <a href="{{ route('admin.users.index', request()->except('trashed')) }}"
               class="px-4 py-1.5 rounded-lg text-sm font-medium transition-colors {{ !$showTrashed ? 'bg-[#0078D4] text-white' : 'text-slate-400 hover:text-white' }}">
                Active Staff
            </a>
            <a href="{{ route('admin.users.index', array_merge(request()->except('trashed'), ['trashed' => 1])) }}"
               class="px-4 py-1.5 rounded-lg text-sm font-medium transition-colors flex items-center gap-2 {{ $showTrashed ? 'bg-red-700 text-white' : 'text-slate-400 hover:text-white' }}">
                Deleted Staff
                @if ($trashedCount > 0)
                    <span class="text-xs bg-red-500/30 text-red-300 px-1.5 py-0.5 rounded-full font-semibold">{{ $trashedCount }}</span>
                @endif
            </a>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap gap-3">
            @if ($showTrashed)
                <input type="hidden" name="trashed" value="1">
            @endif
            <input type="text" name="search" placeholder="Search by name or email…" value="{{ request('search') }}"
                   class="flex-1 min-w-48 bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-lg px-3 py-2 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
            <select name="role" class="bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                <option value="">All Roles</option>
                <option value="tenant-owner" {{ request('role') === 'tenant-owner' ? 'selected' : '' }}>Owner</option>
                <option value="manager" {{ request('role') === 'manager' ? 'selected' : '' }}>Manager</option>
                <option value="employee" {{ request('role') === 'employee' ? 'selected' : '' }}>Employee</option>
                <option value="client" {{ request('role') === 'client' ? 'selected' : '' }}>Client</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white text-sm rounded-lg transition-colors">Filter</button>
            @if(request()->hasAny(['search', 'role']))
                <a href="{{ route('admin.users.index', request()->only('trashed') ?: []) }}"
                   class="px-4 py-2 text-sm text-slate-400 hover:text-white">Clear</a>
            @endif
        </form>

        {{-- Mobile cards --}}
        <div class="sm:hidden bg-slate-800 rounded-xl overflow-hidden divide-y divide-slate-700">
            @forelse($users as $user)
                <div class="px-4 py-4">
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <div class="min-w-0">
                            <p class="font-medium text-white truncate">{{ $user->name }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ $user->email }}</p>
                        </div>
                        <div class="flex flex-wrap gap-1 shrink-0">
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
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $user->is_active ? 'bg-emerald-500/20 text-emerald-300' : 'bg-red-500/20 text-red-300' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                    @if($user->require_password_change)
                        <p class="text-xs text-amber-400 mb-2">⚠ Password change required</p>
                    @endif
                    <div class="flex flex-wrap gap-2 mt-2">
                        @if ($showTrashed)
                            <form method="POST" action="{{ route('admin.users.restore', $user->id) }}">
                                @csrf @method('PATCH')
                                <button class="text-xs px-3 py-1.5 rounded-lg border border-emerald-700 text-emerald-400 hover:bg-emerald-900/30">Restore</button>
                            </form>
                        @else
                            <a href="{{ route('admin.users.show', $user) }}" class="text-xs px-3 py-1.5 rounded-lg border border-slate-700 text-slate-300 hover:bg-slate-700">View</a>
                            @if(!$user->isOwner())
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-xs px-3 py-1.5 rounded-lg border border-slate-700 text-slate-300 hover:bg-slate-700">Edit</a>
                                @if($user->is_active)
                                    <form method="POST" action="{{ route('admin.users.deactivate', $user) }}">
                                        @csrf
                                        <button onclick="return confirm('Deactivate this staff member?')" class="text-xs px-3 py-1.5 rounded-lg border border-amber-800 text-amber-400 hover:bg-amber-900/30">Deactivate</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.users.activate', $user) }}">
                                        @csrf
                                        <button class="text-xs px-3 py-1.5 rounded-lg border border-emerald-800 text-emerald-400 hover:bg-emerald-900/30">Activate</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                      onsubmit="return confirm('Soft-delete {{ addslashes($user->name) }}? They can be restored later.')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs px-3 py-1.5 rounded-lg border border-red-800 text-red-400 hover:bg-red-900/30">Delete</button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-4 py-10 text-center text-slate-500 text-sm">No staff members found.</div>
            @endforelse
        </div>

        {{-- Desktop table --}}
        <div class="hidden sm:block bg-slate-800 rounded-xl border border-slate-700 overflow-hidden overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-900/50 border-b border-slate-700">
                    <tr>
                        <th class="px-5 py-3 text-left font-medium text-slate-400">Name</th>
                        <th class="px-5 py-3 text-left font-medium text-slate-400">Email</th>
                        <th class="px-5 py-3 text-left font-medium text-slate-400">Role</th>
                        <th class="px-5 py-3 text-left font-medium text-slate-400">Status</th>
                        <th class="px-5 py-3 text-right font-medium text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-700/30 transition-colors">
                            <td class="px-5 py-3.5 font-medium text-white">{{ $user->name }}</td>
                            <td class="px-5 py-3.5 text-slate-400">{{ $user->email }}</td>
                            <td class="px-5 py-3.5">
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
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $user->is_active ? 'bg-emerald-500/20 text-emerald-300' : 'bg-red-500/20 text-red-300' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                @if($user->require_password_change)
                                    <span class="ml-1 text-xs text-amber-400">⚠ pw change</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-2 flex-wrap">
                                    @if ($showTrashed)
                                        <form method="POST" action="{{ route('admin.users.restore', $user->id) }}">
                                            @csrf @method('PATCH')
                                            <button class="text-xs text-emerald-400 hover:text-emerald-300 font-medium">Restore</button>
                                        </form>
                                    @else
                                        <a href="{{ route('admin.users.show', $user) }}" class="text-xs text-slate-400 hover:text-white font-medium">View</a>
                                        @if(!$user->isOwner())
                                            <a href="{{ route('admin.users.edit', $user) }}" class="text-xs text-[#0078D4] hover:text-[#B8D4F0] font-medium">Edit</a>
                                            @if($user->is_active)
                                                <form method="POST" action="{{ route('admin.users.deactivate', $user) }}">
                                                    @csrf
                                                    <button onclick="return confirm('Deactivate this staff member?')" class="text-xs text-amber-400 hover:text-amber-300 font-medium">Deactivate</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.users.activate', $user) }}">
                                                    @csrf
                                                    <button class="text-xs text-emerald-400 hover:text-emerald-300 font-medium">Activate</button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                                  onsubmit="return confirm('Soft-delete {{ addslashes($user->name) }}?')">
                                                @csrf @method('DELETE')
                                                <button class="text-xs text-red-400 hover:text-red-300 font-medium">Delete</button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-slate-500">No staff members found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $users->links() }}
    </div>
</x-app-layout>
