<x-guest-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-slate-900">Set Your Password</h2>
        <p class="text-sm text-slate-600 mt-1">Welcome! Please create a strong password for your account.</p>
    </x-slot>

    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <p class="text-sm text-blue-800">
            Your account has been created. To proceed, you must set a secure password for your account.
        </p>
    </div>

    <form method="POST" action="{{ route('password.update-first') }}" class="space-y-6">
        @csrf

        {{-- Password --}}
        <div>
            <x-input-label for="password" value="{{ __('Password') }}" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        {{-- Confirm Password --}}
        <div>
            <x-input-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        {{-- Password Requirements --}}
        <div class="bg-gray-50 rounded-lg p-4 text-xs">
            <p class="font-semibold text-gray-900 mb-2">Password must contain:</p>
            <ul class="space-y-1 text-gray-600">
                <li>✓ At least 8 characters</li>
                <li>✓ At least one uppercase letter</li>
                <li>✓ At least one number</li>
                <li>✓ At least one special character</li>
            </ul>
        </div>

        <div class="flex items-center justify-end gap-4">
            <x-primary-button>
                {{ __('Set Password & Continue') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
