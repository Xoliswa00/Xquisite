<x-guest-layout>

    {{-- Logo (visible on mobile where the left panel is hidden) --}}
    <div class="flex items-center gap-2.5 mb-6 lg:hidden">
        <img src="/img/android-icon-96x96.png" alt="Xquisite" class="h-9 w-9 object-contain rounded-lg shrink-0">
        <div class="leading-none">
            <p class="font-bold text-sm tracking-wide text-[#002B5B]" style="font-family:'Montserrat',sans-serif">XQUISITE</p>
            <p class="font-semibold text-[10px] tracking-widest text-[#D4AF37]" style="font-family:'Montserrat',sans-serif">CREATIONS</p>
        </div>
    </div>

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-[#D4AF37]" style="font-family:'Montserrat',sans-serif">Create your account</h1>
        <p class="text-sm text-[#2D3748]/60 mt-1">14-day free trial &middot; No credit card required</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label for="business_name" class="block text-sm font-medium text-[#002B5B] mb-1">Business Name <span class="text-red-500">*</span></label>
            <input id="business_name" type="text" name="business_name" value="{{ old('business_name') }}" required autofocus placeholder="e.g. Glam Studio"
                   class="w-full bg-white border border-gray-200 text-[#2D3748] text-sm rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4] focus:border-[#0078D4] placeholder-gray-400 @error('business_name') border-red-400 @enderror">
            @error('business_name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="industry" class="block text-sm font-medium text-[#002B5B] mb-1">Industry</label>
            <select id="industry" name="industry"
                    class="w-full bg-white border border-gray-200 text-[#2D3748] text-sm rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4] focus:border-[#0078D4]">
                <option value="">— Select your industry —</option>
                <option value="salon"       {{ old('industry') === 'salon'       ? 'selected' : '' }}>Hair Salon</option>
                <option value="spa"         {{ old('industry') === 'spa'         ? 'selected' : '' }}>Spa & Wellness</option>
                <option value="barbershop"  {{ old('industry') === 'barbershop'  ? 'selected' : '' }}>Barbershop</option>
                <option value="beauty"      {{ old('industry') === 'beauty'      ? 'selected' : '' }}>Beauty Studio</option>
                <option value="nail"        {{ old('industry') === 'nail'        ? 'selected' : '' }}>Nail Salon</option>
                <option value="massage"     {{ old('industry') === 'massage'     ? 'selected' : '' }}>Massage Therapy</option>
                <option value="technology"  {{ old('industry') === 'technology'  ? 'selected' : '' }}>Technology</option>
                <option value="retail"      {{ old('industry') === 'retail'      ? 'selected' : '' }}>Retail</option>
                <option value="hospitality" {{ old('industry') === 'hospitality' ? 'selected' : '' }}>Hospitality</option>
                <option value="property"    {{ old('industry') === 'property'    ? 'selected' : '' }}>Property Management</option>
                <option value="other"       {{ old('industry') === 'other'       ? 'selected' : '' }}>Other</option>
            </select>
        </div>

        <div id="industry_other_field" class="hidden">
            <label for="industry_other" class="block text-sm font-medium text-[#002B5B] mb-1">Other industry</label>
            <input id="industry_other" type="text" name="industry_other" value="{{ old('industry_other') }}" placeholder="Please specify your industry"
                   class="w-full bg-white border border-gray-200 text-[#2D3748] text-sm rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4] focus:border-[#0078D4] @error('industry_other') border-red-400 @enderror">
            @error('industry_other')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="name" class="block text-sm font-medium text-[#002B5B] mb-1">Your Name <span class="text-red-500">*</span></label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="Full name"
                       class="w-full bg-white border border-gray-200 text-[#2D3748] text-sm rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4] focus:border-[#0078D4] placeholder-gray-400 @error('name') border-red-400 @enderror">
                @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-[#002B5B] mb-1">Phone</label>
                <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" placeholder="+27 ..."
                       class="w-full bg-white border border-gray-200 text-[#2D3748] text-sm rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4] focus:border-[#0078D4] placeholder-gray-400">
            </div>
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-[#002B5B] mb-1">Email Address <span class="text-red-500">*</span></label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="you@yourbusiness.co.za"
                   class="w-full bg-white border border-gray-200 text-[#2D3748] text-sm rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#0078D4] focus:border-[#0078D4] placeholder-gray-400 @error('email') border-red-400 @enderror">
            @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="password" class="block text-sm font-medium text-[#002B5B] mb-1">Password <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                           class="w-full bg-white border border-gray-200 text-[#2D3748] text-sm rounded-lg px-3 py-2.5 pr-9 focus:outline-none focus:ring-2 focus:ring-[#0078D4] focus:border-[#0078D4] @error('password') border-red-400 @enderror">
                    <button type="button" onclick="togglePwd('password',this)" class="absolute inset-y-0 right-0 px-2.5 flex items-center text-gray-400 hover:text-[#0078D4]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
                @error('password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-[#002B5B] mb-1">Confirm</label>
                <div class="relative">
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                           class="w-full bg-white border border-gray-200 text-[#2D3748] text-sm rounded-lg px-3 py-2.5 pr-9 focus:outline-none focus:ring-2 focus:ring-[#0078D4] focus:border-[#0078D4]">
                    <button type="button" onclick="togglePwd('password_confirmation',this)" class="absolute inset-y-0 right-0 px-2.5 flex items-center text-gray-400 hover:text-[#0078D4]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <button type="submit" class="w-full bg-[#0078D4] hover:bg-[#0065B8] text-white font-semibold py-3 rounded-xl text-sm transition-colors mt-2">
            Start Free Trial
        </button>

        <p class="text-center text-xs text-[#2D3748]/50">
            By creating an account you agree to our
            <a href="{{ route('terms') }}" target="_blank" class="text-[#0078D4] hover:text-[#0065B8]">Terms of Service</a> and
            <a href="{{ route('privacy') }}" target="_blank" class="text-[#0078D4] hover:text-[#0065B8]">Privacy Policy</a>.
        </p>
    </form>

    <script>
        function togglePwd(id, btn) {
            var input = document.getElementById(id);
            input.type = input.type === 'password' ? 'text' : 'password';
            btn.querySelector('svg').style.opacity = input.type === 'text' ? '0.5' : '1';
        }
        document.addEventListener('DOMContentLoaded', function () {
            const industrySelect = document.getElementById('industry');
            const otherField = document.getElementById('industry_other_field');
            function toggleOtherField() {
                if (!industrySelect) return;
                otherField.classList.toggle('hidden', industrySelect.value !== 'other');
            }
            if (industrySelect && otherField) {
                industrySelect.addEventListener('change', toggleOtherField);
                toggleOtherField();
            }
        });
    </script>

    <p class="mt-6 text-center text-sm text-[#2D3748]/60">
        Already have an account? <a href="{{ route('login') }}" class="text-[#0078D4] hover:text-[#0065B8] font-medium">Sign in</a>
    </p>

</x-guest-layout>
