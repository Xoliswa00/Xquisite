<x-app-layout>
    <x-slot name="header">Edit Staff Member</x-slot>

    <div class="max-w-xl space-y-4">
        <div class="bg-slate-800 rounded-xl p-6">
            <form method="POST" action="{{ route('staff.update', $staff) }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Full Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $staff->name) }}" required
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Role / Title</label>
                    <input type="text" name="role" value="{{ old('role', $staff->role) }}"
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $staff->email) }}"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $staff->phone) }}"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                </div>

                @if($services->count())
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Services offered</label>
                        @php $assignedIds = old('service_ids', $staff->services->pluck('id')->toArray()); @endphp
                        <div class="space-y-1.5 max-h-40 overflow-y-auto pr-1">
                            @foreach($services as $service)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="service_ids[]" value="{{ $service->id }}"
                                           {{ in_array($service->id, $assignedIds) ? 'checked' : '' }}
                                           class="rounded bg-slate-700 border-slate-600 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm text-slate-300">{{ $service->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $staff->is_active) ? 'checked' : '' }}
                           class="rounded bg-slate-700 border-slate-600 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_active" class="text-sm text-slate-300">Active</label>
                </div>

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 pt-2">
                    <button type="submit" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-6 py-2 rounded-lg">Save Changes</button>
                    <a href="{{ route('staff.show', $staff) }}" class="text-sm text-slate-400 hover:text-white">Cancel</a>
                </div>
            </form>
        </div>

        <div class="bg-slate-800 rounded-xl p-4 border border-red-900/50">
            <p class="text-sm text-slate-400 mb-3">Remove this staff member. Their appointments will be orphaned.</p>
            <form method="POST" action="{{ route('staff.destroy', $staff) }}"
                  onsubmit="return confirm('Delete {{ addslashes($staff->name) }}?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-700 hover:bg-red-600 text-white text-sm px-4 py-2 rounded-lg">Delete Staff Member</button>
            </form>
        </div>
    </div>
</x-app-layout>
