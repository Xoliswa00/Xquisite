<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-1">Administration</p>
                <h2 class="text-2xl font-bold text-slate-900">Staff Management</h2>
            </div>
            <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">
                + Add Staff Member
            </a>
        </div>
    </x-slot>

    <div class="py-12 px-4 max-w-6xl mx-auto">

        {{-- Active / Deleted tabs --}}
        <div class="flex gap-1 mb-5 border-b border-gray-200">
            <a href="{{ route('admin.users.index', request()->except('trashed')) }}"
               class="px-4 py-2 text-sm font-medium rounded-t-lg {{ !$showTrashed ? 'bg-white border border-b-white border-gray-200 text-indigo-700' : 'text-gray-500 hover:text-gray-700' }}">
                Active Staff
            </a>
            <a href="{{ route('admin.users.index', array_merge(request()->except('trashed'), ['trashed' => 1])) }}"
               class="px-4 py-2 text-sm font-medium rounded-t-lg flex items-center gap-2 {{ $showTrashed ? 'bg-white border border-b-white border-gray-200 text-red-700' : 'text-gray-500 hover:text-gray-700' }}">
                Deleted Staff
                @if ($trashedCount > 0)
                    <span class="text-xs bg-red-100 text-red-700 px-1.5 py-0.5 rounded-full font-semibold">{{ $trashedCount }}</span>
                @endif
            </a>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.users.index') }}" class="mb-6 flex gap-4">
            @if ($showTrashed)
                <input type="hidden" name="trashed" value="1">
            @endif
            <input type="text" name="search" placeholder="Search by name or email..." value="{{ request('search') }}"
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm flex-1">
            <select name="role" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">All Roles</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="staff" {{ request('role') === 'staff' ? 'selected' : '' }}>Staff</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 text-sm font-medium">
                Filter
            </button>
        </form>

        {{-- Users Table --}}
        <div class="overflow-x-auto bg-white rounded-xl shadow-sm border border-gray-200">
            <table class="w-full text-sm summary-on-mobile">
                <thead class="border-b border-gray-200 bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Name</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Email</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Role</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Status</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $user->email }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $user->isOwner() ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $user->role === 'admin' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $user->role === 'staff' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $user->isClient() ? 'bg-gray-100 text-gray-800' : '' }}
                                ">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($user->is_active)
                                    <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Active</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">Inactive</span>
                                @endif
                                @if($user->require_password_change)
                                    <span class="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded-full">⚠ Password change required</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 space-x-2">
                                @if ($showTrashed)
                                    {{-- Trashed user: restore or no action --}}
                                    <form method="POST" action="{{ route('admin.users.restore', $user->id) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-emerald-600 hover:text-emerald-900 text-xs font-medium">Restore</button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.users.show', $user) }}" class="text-indigo-600 hover:text-indigo-900 text-xs font-medium">View</a>
                                    @if(!$user->isOwner())
                                        <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:text-blue-900 text-xs font-medium">Edit</a>
                                        @if($user->is_active)
                                            <form method="POST" action="{{ route('admin.users.deactivate', $user) }}" class="inline">
                                                @csrf
                                                <button type="submit" onclick="return confirm('Deactivate this staff member?')" class="text-orange-600 hover:text-orange-900 text-xs font-medium">Deactivate</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.users.activate', $user) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-900 text-xs font-medium">Activate</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline"
                                              onsubmit="return confirm('Soft-delete {{ addslashes($user->name) }}? They can be restored from the Deleted tab.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-medium">Delete</button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                No staff members found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>
