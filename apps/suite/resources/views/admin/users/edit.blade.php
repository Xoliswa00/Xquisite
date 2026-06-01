<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-1">Administration</p>
                <h2 class="text-2xl font-bold text-slate-900">Edit Staff Member</h2>
            </div>
            <a href="{{ route('admin.users.show', $user) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                ← Back to Profile
            </a>
        </div>
    </x-slot>

    <div class="py-12 px-4 max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
                @csrf
                @method('PATCH')

                {{-- Name --}}
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-900 mb-1">
                        Full Name <span class="text-red-600">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-900 mb-1">
                        Email Address <span class="text-red-600">*</span>
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Role --}}
                <div>
                    <label for="role" class="block text-sm font-semibold text-gray-900 mb-1">
                        Role <span class="text-red-600">*</span>
                    </label>
                    <select id="role" name="role" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('role') border-red-500 @enderror">
                        <option value="staff" {{ old('role', $user->role) === 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                    @error('role')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @if(isset($permissions) && $permissions->isNotEmpty())
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <p class="text-sm font-semibold text-gray-900 mb-3">Permissions</p>
                        <div class="grid gap-2 sm:grid-cols-2">
                            @foreach($permissions as $permission)
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                           {{ in_array($permission->name, old('permissions', $user->getPermissionNames()->toArray())) ? 'checked' : '' }}>
                                    {{ ucfirst($permission->name) }}
                                </label>
                            @endforeach
                        </div>
                        @error('permissions')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                {{-- Active Status --}}
                <div>
                    <label for="is_active" class="flex items-center gap-2 mt-2">
                        <input type="checkbox" id="is_active" name="is_active" value="1" 
                               {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm font-medium text-gray-900">Account is active</span>
                    </label>
                </div>

                {{-- Actions --}}
                <div class="flex gap-4 pt-6 border-t border-gray-200">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">
                        Save Changes
                    </button>
                    <a href="{{ route('admin.users.show', $user) }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
