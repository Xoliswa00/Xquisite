<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between max-w-4xl mx-auto">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">CRM / Administration</p>
                <h2 class="font-black text-2xl text-slate-900 tracking-tighter leading-tight">
                    {{ __('Provision New Client') }}
                </h2>
            </div>

            <a href="{{ route('clients.index') }}"
               class="group flex items-center text-[10px] font-bold uppercase tracking-widest text-slate-500 hover:text-indigo-600 transition-all">
                <span class="mr-2 group-hover:-translate-x-1 transition-transform">&larr;</span> Back to Registry
            </a>
        </div>
    </x-slot>

    <div class="py-12 px-4">
        <div class="max-w-4xl mx-auto">

            {{-- ALERTS: SUCCESS --}}
            @if(session('success'))
                <div class="mb-8 rounded-2xl border border-emerald-100 bg-emerald-50/50 p-4 shadow-sm animate-in fade-in slide-in-from-top-2">
                    <div class="flex items-center gap-3">
                        <div class="bg-emerald-500 rounded-full p-1">
                            <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="4"><path d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <p class="text-xs font-bold text-emerald-800 uppercase tracking-tight">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            {{-- ALERTS: ERRORS --}}
            @if ($errors->any())
                <div class="mb-8 rounded-2xl border-l-4 border-rose-500 bg-white p-6 shadow-xl shadow-rose-100/20">
                    <div class="flex items-start gap-4">
                        <div class="bg-rose-100 rounded-lg p-2 text-rose-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86 c1.54 0 2.5-1.67 1.73-3L13.73 4 c-.77-1.33-2.69-1.33-3.46 0L3.34 16 c-.77 1.33.19 3 1.73 3z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-black uppercase tracking-widest text-rose-900 mb-2">Entry Validation Failed</h4>
                            <ul class="text-xs text-rose-700/80 space-y-1 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <script>window.scrollTo({top:0, behavior:'smooth'})</script>
            @endif

            {{-- FORM CARD --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200/60 overflow-hidden">
                <form method="POST" action="{{ route('clients.store') }}" class="divide-y divide-slate-100">
                    @csrf

                    {{-- SECTION: PRIMARY IDENTITY --}}
                    <div class="p-8 lg:p-12 bg-slate-50/30">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                            <div>
                                <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-900 mb-2">Primary Identity</h3>
                                <p class="text-xs text-slate-500 leading-relaxed">System-wide identifier and official naming for the client entity.</p>
                            </div>
                            
                            <div class="lg:col-span-2 space-y-6">
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Legal Client Name <span class="text-rose-500">*</span></label>
                                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Acme Corporation"
                                           class="w-full px-5 py-4 bg-white border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 rounded-2xl text-sm font-semibold transition-all">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION: COMMUNICATION --}}
                    <div class="p-8 lg:p-12">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                            <div>
                                <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-900 mb-2">Communication</h3>
                                <p class="text-xs text-slate-500 leading-relaxed">Direct channels for billing alerts and administrative contact.</p>
                            </div>

                            <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Email Protocol</label>
                                    <input type="email" name="email" value="{{ old('email') }}" placeholder="billing@acme.com"
                                           class="w-full px-5 py-4 bg-slate-50/50 border-slate-200 focus:border-indigo-500 focus:bg-white rounded-2xl text-sm transition-all">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Telephony</label>
                                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+27 00 000 0000"
                                           class="w-full px-5 py-4 bg-slate-50/50 border-slate-200 focus:border-indigo-500 focus:bg-white rounded-2xl text-sm transition-all">
                                </div>
                                <div class="md:col-span-2 space-y-1">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Primary Liaison / Contact Person</label>
                                    <input type="text" name="contact_person" value="{{ old('contact_person') }}" placeholder="Full Name"
                                           class="w-full px-5 py-4 bg-slate-50/50 border-slate-200 focus:border-indigo-500 focus:bg-white rounded-2xl text-sm transition-all">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION: SYSTEM PERMISSIONS --}}
                    <div class="p-8 lg:p-12 bg-indigo-50/30">
                        <div class="flex items-center gap-6 p-6 bg-white border border-indigo-100 rounded-3xl shadow-sm">
                            <div class="relative flex items-center">
                                <input type="checkbox" name="send_invitation" id="send_invitation" value="1"
                                       class="w-6 h-6 text-indigo-600 border-slate-300 rounded-lg focus:ring-indigo-500 cursor-pointer transition-all">
                            </div>
                            <div>
                                <label for="send_invitation" class="block text-xs font-black uppercase tracking-widest text-indigo-900 cursor-pointer">
                                    Automated Portal Invitation
                                </label>
                                <p class="text-xs text-indigo-700/60 mt-0.5">Initialize dashboard access and send security credentials via email.</p>
                            </div>
                        </div>
                    </div>

                    {{-- FOOTER: ACTIONS --}}
                    <div class="p-8 bg-slate-50/80 flex items-center justify-between border-t border-slate-100">
                        <button type="button" onclick="window.history.back()"
                                class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors">
                            Abort Process
                        </button>

                        <button type="submit"
                                class="px-10 py-4 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] 
                                       hover:bg-indigo-600 transition-all shadow-xl shadow-slate-900/10 active:scale-95">
                            Commit Client Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>