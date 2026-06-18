<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.index') }}" class="text-slate-400 hover:text-white transition-colors">← Staff</a>
            <span class="text-slate-600">/</span>
            <h2 class="text-xl font-bold text-[#D4AF37]">Add Staff Member</h2>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-300 mb-1">
                        Full Name <span class="text-red-400">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-1">
                        Email Address <span class="text-red-400">*</span>
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-slate-500">A temporary password will be emailed — they'll be required to change it on first login.</p>
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium text-slate-300 mb-1">
                        Role <span class="text-red-400">*</span>
                    </label>
                    <select id="role" name="role" required
                            class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('role') border-red-500 @enderror">
                        <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                    @error('role')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                @if(isset($permissions) && $permissions->isNotEmpty())
                    <div class="bg-slate-900/50 border border-slate-700 rounded-lg p-4">
                        <p class="text-sm font-medium text-slate-300 mb-3">Assign Permissions</p>
                        <div class="grid gap-2 sm:grid-cols-2">
                            @foreach($permissions as $permission)
                                <label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                           class="rounded border-slate-600 bg-slate-700 text-[#0078D4] focus:ring-[#0078D4]"
                                           {{ in_array($permission->name, old('permissions', [])) ? 'checked' : '' }}>
                                    {{ ucfirst($permission->name) }}
                                </label>
                            @endforeach
                        </div>
                        @error('permissions')
                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div class="bg-[#001A3A]/20 border border-[#002B5B]/40 rounded-lg p-4">
                    <p class="text-sm text-[#B8D4F0]">
                        <strong>How it works:</strong> A unique temporary password is generated and emailed to the staff member. They must set a new password on first login.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 pt-2">
                    <button type="submit" class="px-5 py-2 bg-[#0078D4] hover:bg-[#0078D4] text-white rounded-lg text-sm font-medium transition-colors">
                        Create Staff Account
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="px-5 py-2 border border-slate-700 text-slate-300 hover:bg-slate-700 rounded-lg text-sm font-medium text-center transition-colors">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
