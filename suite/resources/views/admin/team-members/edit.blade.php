<x-app-layout>
    <x-slot name="header">Edit Team Member</x-slot>

    <div class="max-w-xl space-y-6">

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.team-members.index') }}" class="text-slate-400 hover:text-white text-sm transition-colors">&larr; Back</a>
            <h1 class="text-xl font-bold text-[#D4AF37]">Edit — {{ $teamMember->name }}</h1>
        </div>

        @if ($errors->any())
            <div class="px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm space-y-1">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.team-members.update', $teamMember) }}" enctype="multipart/form-data"
              class="bg-slate-800 rounded-xl border border-slate-700 p-6 space-y-5">
            @csrf @method('PUT')

            {{-- Current photo preview --}}
            @if ($teamMember->photoUrl())
            <div class="flex items-center gap-4">
                <img src="{{ $teamMember->photoUrl() }}" alt="{{ $teamMember->name }}"
                     class="w-16 h-16 rounded-full object-cover border-2 border-slate-600">
                <p class="text-xs text-slate-400">Upload a new photo to replace this one.</p>
            </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1.5">Full Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $teamMember->name) }}" required
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4]">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1.5">Role / Title <span class="text-red-400">*</span></label>
                    <input type="text" name="role" value="{{ old('role', $teamMember->role) }}" required
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4]">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1.5">Bio</label>
                <textarea name="bio" rows="3"
                          class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4] resize-none">{{ old('bio', $teamMember->bio) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1.5">Photo <span class="text-slate-500">(leave blank to keep current)</span></label>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp"
                       class="w-full bg-slate-700 border border-slate-600 text-slate-300 text-sm rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4] file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:bg-slate-600 file:text-slate-200">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1.5">LinkedIn URL</label>
                <input type="url" name="linkedin_url" value="{{ old('linkedin_url', $teamMember->linkedin_url) }}"
                       class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4] placeholder-slate-500"
                       placeholder="https://linkedin.com/in/...">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1.5">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $teamMember->sort_order) }}" min="0"
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4]">
                </div>
                <div class="flex items-end pb-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $teamMember->is_active) ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-slate-600 bg-slate-700 text-[#0078D4] focus:ring-[#0078D4]">
                        <span class="text-sm text-slate-300">Show on About page</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="px-5 py-2.5 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-semibold rounded-lg transition-colors">
                    Save Changes
                </button>
                <a href="{{ route('admin.team-members.index') }}" class="px-5 py-2.5 text-slate-400 hover:text-white text-sm transition-colors">Cancel</a>
            </div>
        </form>

    </div>
</x-app-layout>
