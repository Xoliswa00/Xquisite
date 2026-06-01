<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-1">Administration</p>
                <h2 class="text-2xl font-bold text-slate-900">{{ $user->name }}</h2>
            </div>
            <a href="{{ route('admin.users.index') }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                ← Back to Staff
            </a>
        </div>
    </x-slot>

    <div class="py-12 px-4 max-w-4xl mx-auto">
        {{-- Alert Messages --}}
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-sm text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        {{-- User Details Card --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            {{-- Basic Info --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Basic Information</h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-gray-600 uppercase tracking-wider mb-1">Name</p>
                        <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-600 uppercase tracking-wider mb-1">Email</p>
                        <p class="text-sm font-medium text-gray-900">{{ $user->email }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-600 uppercase tracking-wider mb-1">Role</p>
                        <p class="text-sm font-medium">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                {{ $user->isOwner() ? 'bg-purple-100 text-purple-800' : '' }}
                                {{ $user->role === 'admin' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $user->role === 'staff' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $user->isClient() ? 'bg-gray-100 text-gray-800' : '' }}
                            ">
                                {{ ucfirst($user->role) }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Status --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Status</h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-gray-600 uppercase tracking-wider mb-1">Account Status</p>
                        <p>
                            @if($user->is_active)
                                <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Active</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">Inactive</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-600 uppercase tracking-wider mb-1">Password Status</p>
                        <p>
                            @if($user->require_password_change)
                                <span class="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded-full">⚠ Change Required</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Set</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-600 uppercase tracking-wider mb-1">Joined</p>
                        <p class="text-sm font-medium text-gray-900">{{ $user->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>

            {{-- Permissions --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Assigned Permissions</h3>
                <div class="space-y-2">
                    @forelse($user->getPermissionNames() as $permission)
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-slate-100 text-slate-800 text-xs font-medium">
                            {{ ucfirst($permission) }}
                        </span>
                    @empty
                        <p class="text-sm text-gray-600">No custom permissions assigned for this user.</p>
                    @endforelse
                </div>
            </div>

            {{-- Actions --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Actions</h3>
                <div class="space-y-2">
                    @if(!$user->isOwner())
                        <a href="{{ route('admin.users.edit', $user) }}" class="block px-3 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 text-sm font-medium text-center">
                            Edit
                        </a>
                        
                        <form method="POST" action="{{ route('admin.users.reset-password', $user) }}">
                            @csrf
                            <button type="submit" onclick="return confirm('Reset password for {{ $user->name }}?')" class="w-full px-3 py-2 bg-orange-50 text-orange-700 rounded-lg hover:bg-orange-100 text-sm font-medium">
                                Reset Password
                            </button>
                        </form>

                        @if($user->is_active)
                            <form method="POST" action="{{ route('admin.users.deactivate', $user) }}">
                                @csrf
                                <button type="submit" onclick="return confirm('Deactivate {{ $user->name }}?')" class="w-full px-3 py-2 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 text-sm font-medium">
                                    Deactivate
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.users.activate', $user) }}">
                                @csrf
                                <button type="submit" class="w-full px-3 py-2 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 text-sm font-medium">
                                    Reactivate
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
