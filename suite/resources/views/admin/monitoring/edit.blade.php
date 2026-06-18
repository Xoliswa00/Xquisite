<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('monitoring.show', $instance) }}" class="text-[#0078D4] hover:text-[#B8D4F0] text-sm mb-2 inline-block">← Back to {{ $instance->name }}</a>
        <h2 class="text-2xl font-bold text-[#D4AF37]">Edit Instance</h2>
    </x-slot>

    <div class="max-w-2xl">
        <div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
            <form action="{{ route('monitoring.update', $instance) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-300 mb-2">Instance Name</label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $instance->name) }}"
                           class="w-full px-4 py-2 bg-slate-700 border border-slate-600 text-white rounded-lg focus:outline-none focus:border-[#0078D4] focus:ring-1 focus:ring-[#0078D4] @error('name') border-red-500 @enderror"
                           required>
                    @error('name')
                        <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="url" class="block text-sm font-medium text-slate-300 mb-2">Instance URL</label>
                    <input type="url" 
                           id="url" 
                           name="url" 
                           value="{{ old('url', $instance->url) }}"
                           class="w-full px-4 py-2 bg-slate-700 border border-slate-600 text-white rounded-lg focus:outline-none focus:border-[#0078D4] focus:ring-1 focus:ring-[#0078D4] @error('url') border-red-500 @enderror"
                           required>
                    @error('url')
                        <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="api_token" class="block text-sm font-medium text-slate-300 mb-2">API Token</label>
                    <input type="text" 
                           id="api_token" 
                           name="api_token" 
                           value="{{ old('api_token', $instance->api_token) }}"
                           class="w-full px-4 py-2 bg-slate-700 border border-slate-600 text-white rounded-lg font-mono text-sm focus:outline-none focus:border-[#0078D4] focus:ring-1 focus:ring-[#0078D4] @error('api_token') border-red-500 @enderror"
                           required>
                    @error('api_token')
                        <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-slate-400 mt-1">Update the token if you need to reset access credentials</p>
                </div>

                <div>
                    <label for="tenant_id" class="block text-sm font-medium text-slate-300 mb-2">Tenant ID</label>
                    <input type="text" 
                           id="tenant_id" 
                           name="tenant_id" 
                           value="{{ old('tenant_id', $instance->tenant_id) }}"
                           class="w-full px-4 py-2 bg-slate-700 border border-slate-600 text-white rounded-lg focus:outline-none focus:border-[#0078D4] focus:ring-1 focus:ring-[#0078D4]">
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" 
                           id="active" 
                           name="active" 
                           value="1"
                           {{ old('active', $instance->active) ? 'checked' : '' }}
                           class="w-4 h-4 bg-slate-700 border-slate-600 rounded focus:ring-[#0078D4]">
                    <label for="active" class="text-sm text-slate-300">Actively monitor this instance</label>
                </div>

                <div class="flex gap-3">
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-[#0078D4] hover:bg-[#0078D4] text-white rounded-lg font-medium transition">
                        Save Changes
                    </button>
                    <a href="{{ route('monitoring.show', $instance) }}"
                       class="flex-1 px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg font-medium transition text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
