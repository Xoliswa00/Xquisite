<x-app-layout>
    <x-slot name="header">{{ isset($client) ? 'Edit Client' : 'Add Client' }}</x-slot>

    <div class="max-w-xl mx-auto">
        <form method="POST" action="{{ isset($client) ? route('clients.update', $client) : route('clients.store') }}" class="space-y-5">
            @csrf
            @if(isset($client)) @method('PUT') @endif
            <x-form-errors />

            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                <div>
                    <x-input-label value="Full Name" />
                    <x-text-input name="name" class="mt-1 w-full" value="{{ old('name', $client->name ?? '') }}" required />
                    <x-input-error :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label value="Email" />
                    <x-text-input name="email" type="email" class="mt-1 w-full" value="{{ old('email', $client->email ?? '') }}" />
                    <x-input-error :messages="$errors->get('email')" />
                </div>
                <div>
                    <x-input-label value="Phone" />
                    <x-text-input name="phone" class="mt-1 w-full" value="{{ old('phone', $client->phone ?? '') }}" />
                    <x-input-error :messages="$errors->get('phone')" />
                </div>
                <div>
                    <x-input-label value="Notes" />
                    <textarea name="notes" rows="3" class="mt-1 w-full rounded-lg bg-slate-800 border-slate-700 text-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes', $client->notes ?? '') }}</textarea>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-medium rounded-lg text-sm">
                    {{ isset($client) ? 'Update Client' : 'Add Client' }}
                </button>
                <a href="{{ route('clients.index') }}" class="px-6 py-2.5 border border-slate-700 text-slate-300 hover:bg-slate-800 rounded-lg text-sm text-center">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
