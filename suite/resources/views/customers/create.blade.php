<x-app-layout>
    <x-slot name="header">New Customer</x-slot>

    <div class="max-w-xl">
        <div class="bg-slate-800 rounded-xl p-6"
             x-data="{
                 supported: false,
                 loading: false,
                 init() {
                     this.supported = ('contacts' in navigator) && ('ContactsManager' in window);
                 },
                 async importContact() {
                     if (!this.supported) return;
                     this.loading = true;
                     try {
                         const results = await navigator.contacts.select(['name', 'tel', 'email'], { multiple: false });
                         if (!results.length) return;
                         const c = results[0];
                         if (c.name?.length)  this.$refs.nameField.value  = c.name[0];
                         if (c.tel?.length)   this.$refs.phoneField.value = c.tel[0];
                         if (c.email?.length) this.$refs.emailField.value = c.email[0];
                     } catch (e) {
                         // user cancelled — do nothing
                     } finally {
                         this.loading = false;
                     }
                 }
             }">

            {{-- Import from contacts (mobile only — hidden on desktop where API unavailable) --}}
            <div x-show="supported" x-cloak class="mb-5">
                <button type="button" @click="importContact()" :disabled="loading"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border border-dashed border-slate-500 text-slate-300 hover:border-[#0078D4] hover:text-[#0078D4] hover:bg-[#0078D4]/5 transition-colors text-sm font-medium disabled:opacity-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span x-text="loading ? 'Opening contacts…' : 'Import from contacts'"></span>
                </button>
                <p class="text-xs text-slate-500 text-center mt-1.5">Opens your phone's contact list — fills name, phone &amp; email</p>
            </div>

            <form method="POST" action="{{ route('customers.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Full Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           x-ref="nameField"
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('name') border-red-500 @enderror">
                    @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           x-ref="emailField"
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('email') border-red-500 @enderror">
                    @error('email')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           x-ref="phoneField"
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Notes</label>
                    <textarea name="notes" rows="3"
                              class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]"
                              placeholder="Allergies, preferences…">{{ old('notes') }}</textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                           class="rounded bg-slate-700 border-slate-600 text-[#0078D4] focus:ring-[#0078D4]">
                    <label for="is_active" class="text-sm text-slate-300">Active</label>
                </div>

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 pt-2">
                    <button type="submit" class="w-full sm:w-auto bg-[#0078D4] hover:bg-[#0078D4] text-white text-sm px-6 py-2 rounded-lg">
                        Add Customer
                    </button>
                    <a href="{{ route('customers.index') }}" class="text-sm text-slate-400 hover:text-white">Cancel</a>
                </div>
            </form>
        </div>{{-- end x-data --}}
    </div>
</x-app-layout>
