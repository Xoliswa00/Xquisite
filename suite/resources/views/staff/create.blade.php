<x-app-layout>
    <x-slot name="header">Add Staff Member</x-slot>

    <div class="max-w-xl">
        <div class="bg-slate-800 rounded-xl p-6">
            <form method="POST" action="{{ route('staff.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Full Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('name') border-red-500 @enderror">
                    @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Role / Title</label>
                    <input type="text" name="role" value="{{ old('role', 'Therapist') }}"
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]"
                           placeholder="e.g. Therapist, Nail Tech, Stylist">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                    </div>
                </div>

                @if($services->count())
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Services offered</label>
                        <div class="space-y-1.5 max-h-40 overflow-y-auto pr-1">
                            @foreach($services as $service)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="service_ids[]" value="{{ $service->id }}"
                                           {{ in_array($service->id, old('service_ids', [])) ? 'checked' : '' }}
                                           class="rounded bg-slate-700 border-slate-600 text-[#0078D4] focus:ring-[#0078D4]">
                                    <span class="text-sm text-slate-300">{{ $service->name }}</span>
                                    <span class="text-xs text-slate-500">{{ $service->duration_minutes }}m</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                           class="rounded bg-slate-700 border-slate-600 text-[#0078D4] focus:ring-[#0078D4]">
                    <label for="is_active" class="text-sm text-slate-300">Active</label>
                </div>

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 pt-2">
                    <button type="submit" class="w-full sm:w-auto bg-[#0078D4] hover:bg-[#0078D4] text-white text-sm px-6 py-2 rounded-lg">Add Staff Member</button>
                    <a href="{{ route('staff.index') }}" class="text-sm text-slate-400 hover:text-white">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
