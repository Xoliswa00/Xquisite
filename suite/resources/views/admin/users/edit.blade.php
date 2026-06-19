<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.show', $user) }}" class="text-slate-400 hover:text-white transition-colors">← {{ $user->name }}</a>
            <span class="text-slate-600">/</span>
            <h2 class="text-xl font-bold text-[#D4AF37]">Edit</h2>
        </div>
    </x-slot>

    <div class="max-w-2xl space-y-4">
        <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-5">
                @csrf
                @method('PATCH')

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-300 mb-1">
                        Full Name <span class="text-red-400">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-1">
                        Email Address <span class="text-red-400">*</span>
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium text-slate-300 mb-1">
                        Role <span class="text-red-400">*</span>
                    </label>
                    <select id="role" name="role" required
                            class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('role') border-red-500 @enderror">
                        @php $currentRole = old('role', $user->getRoleNames()->first()); @endphp
                        <option value="employee" {{ $currentRole === 'employee' ? 'selected' : '' }}>Employee</option>
                        <option value="manager" {{ $currentRole === 'manager' ? 'selected' : '' }}>Manager</option>
                    </select>
                    @error('role')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                @if(isset($permissions) && $permissions->isNotEmpty())
                    <div class="bg-slate-900/50 border border-slate-700 rounded-lg p-4">
                        <p class="text-sm font-medium text-slate-300 mb-3">Permissions</p>
                        <div class="grid gap-2 sm:grid-cols-2">
                            @foreach($permissions as $permission)
                                <label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                           class="rounded border-slate-600 bg-slate-700 text-[#0078D4] focus:ring-[#0078D4]"
                                           {{ in_array($permission->name, old('permissions', $user->getPermissionNames()->toArray())) ? 'checked' : '' }}>
                                    {{ ucfirst($permission->name) }}
                                </label>
                            @endforeach
                        </div>
                        @error('permissions')
                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" id="is_active" name="is_active" value="1"
                               {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                               class="rounded border-slate-600 bg-slate-700 text-[#0078D4] focus:ring-[#0078D4]">
                        <span class="text-sm font-medium text-slate-300">Account is active</span>
                    </label>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 pt-2 border-t border-slate-700">
                    <button type="submit" class="px-5 py-2 bg-[#0078D4] hover:bg-[#0078D4] text-white rounded-lg text-sm font-medium transition-colors">
                        Save Changes
                    </button>
                    <a href="{{ route('admin.users.show', $user) }}" class="px-5 py-2 border border-slate-700 text-slate-300 hover:bg-slate-700 rounded-lg text-sm font-medium text-center transition-colors">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
