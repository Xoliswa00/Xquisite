<x-app-layout>
    <x-slot name="header">Add Staff Member</x-slot>

    <div class="max-w-2xl space-y-4">
        <a href="{{ route('admin.users.index') }}" class="inline-block text-sm text-slate-400 hover:text-white transition-colors">&larr; Back to staff</a>

        <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-300 mb-1">
                        Full Name <span class="text-red-400">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           @error('name') aria-invalid="true" aria-describedby="name-error" @enderror
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#0078D4] @error('name') border-red-500 @enderror">
                    @error('name')
                        <p id="name-error" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-1">
                        Email Address <span class="text-red-400">*</span>
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                           aria-describedby="email-help @error('email') email-error @enderror"
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#0078D4] @error('email') border-red-500 @enderror">
                    @error('email')
                        <p id="email-error" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                    <p id="email-help" class="mt-1 text-xs text-slate-400">Used as their login name. If they have no email of their own, use one you control. No verification link is sent.</p>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-300 mb-1">
                        Temporary Password <span class="text-slate-500 font-normal">(optional)</span>
                    </label>
                    <input type="text" id="password" name="password" value="{{ old('password') }}" autocomplete="off"
                           aria-describedby="password-help @error('password') password-error @enderror"
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#0078D4] @error('password') border-red-500 @enderror">
                    @error('password')
                        <p id="password-error" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                    <p id="password-help" class="mt-1 text-xs text-slate-400">At least 8 characters. Leave blank and we'll generate one for you. Either way it's shown on the next screen for you to hand over, and they must change it on first sign-in.</p>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-1">
                        Confirm Password
                    </label>
                    <input type="text" id="password_confirmation" name="password_confirmation" value="{{ old('password_confirmation') }}" autocomplete="off"
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#0078D4]">
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium text-slate-300 mb-1">
                        Role <span class="text-red-400">*</span>
                    </label>
                    <select id="role" name="role" required aria-describedby="role-help"
                            class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#0078D4] @error('role') border-red-500 @enderror">
                        <option value="employee" {{ old('role') === 'employee' ? 'selected' : '' }}>Employee</option>
                        <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Manager</option>
                    </select>
                    @error('role')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                    <p id="role-help" class="mt-1 text-xs text-slate-400"><strong class="text-slate-300">Employee</strong> handles day-to-day work: bookings, customers and sales. <strong class="text-slate-300">Manager</strong> can also manage staff, products and pricing, reports and settings.</p>
                </div>

                @if(isset($permissions) && $permissions->isNotEmpty())
                    <fieldset class="bg-slate-900/50 border border-slate-700 rounded-lg p-4">
                        <legend class="text-sm font-medium text-slate-300 px-1">Extra permissions <span class="text-slate-500 font-normal">(optional)</span></legend>
                        <p class="text-xs text-slate-400 mb-3">Grant an Employee something beyond their normal access. Leave all unticked to use the plain role.</p>
                        <div class="grid gap-2 sm:grid-cols-2">
                            @foreach($permissions as $permission)
                                <label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                           class="rounded border-slate-600 bg-slate-700 text-[#0078D4] focus:ring-[#0078D4]"
                                           {{ in_array($permission->name, old('permissions', [])) ? 'checked' : '' }}>
                                    {{ \App\Support\PermissionLabels::for($permission->name) }}
                                </label>
                            @endforeach
                        </div>
                        @error('permissions')
                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                        @error('permissions.*')
                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </fieldset>
                @endif

                <div class="flex flex-col sm:flex-row gap-3 pt-2">
                    <button type="submit" class="px-5 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white rounded-lg text-sm font-medium transition-colors">
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
