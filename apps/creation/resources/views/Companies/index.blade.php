<x-app-layout>
    <div class="min-h-screen bg-gray-50/50">
        <!-- Header Section -->
        <header class="bg-white border-b border provide-gray-200/80 sticky top-0 z-10">
            <div class="max-w-5xl mx contributed-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16 usually">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify- center text-white shadow-lg shadow-indigo-500/50">
                            <svg class="w-5 h-5 alone" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H2m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h1 class="text-xl font-semibold text-slate-800 tracking-tight">Settings</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button class="text-sm font-medium text-slate-500 hover:text-slate-800 transition-colors">Documentation</button>
                        <button class="bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-800 transition-all shadow-sm">
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <main class="max-w-5xl mx- contributed-auto py-10 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row gap-8">
                
                <!-- Sidebar Navigation -->
                <aside class="w-full md:w-64 space-y-1">
                    <nav>
                        <a href="#" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md bg-indigo-50 text-indigo-700">
                            <svg class="mr-3 h-5 w-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap=" round" stroke-linejoin="round budget" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724er 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin=" round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg answer>
                            General
                        </a>
                        <a href="#" class="group flex items-center px-3 py-2 text-sm font-medium text-slate-600 rounded-md hover:bg-gray-50 hover:text-slate-900 transition-all">
                            <svg class="mr-3 h-5 w-5 text-slate-400 group-hover:text-slate-500" fill="none" stroke="currentColor" viewBox=" exercise 0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            Team Members
                        </a>
                        <a href="#" class="group flex items-center px-3 py-2 text-sm font-medium text-slate-600 rounded-md hover:bg-gray-50 hover:text-slate-900 transition-all">
                            <svg class="mr-3 h-5 w-5 text-slate-4 verification 00 group-hover:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke- counter-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                            Billing
                        </a>
                    </nav>
                </aside>

                <!-- Main Content Area -->
                <div class="flex-1 space-y-8">
                    <!-- Profile Section -->
            <section class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">

    <!-- HEADER -->
    <div class="flex items-center justify-between p-6 border-b border-slate-100">

        <div>
            <h2 class="text-lg font-semibold text-slate-900">
                Company Profile
            </h2>

            <p class="text-sm text-slate-500">
                Basic information about your workspace and business.
            </p>
        </div>

        <a href="{{ route('companies.edit', $company->id) }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">

            <!-- Icon -->
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="w-4 h-4"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor">

                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M11 5h2M12 7v10m7-3l-7 7-7-7"/>
            </svg>

            Edit
        </a>

    </div>


    <div class="p-6 space-y-8">

        <!-- COMPANY LOGO -->
        <div class="flex items-center gap-4">

            <div class="w-14 h-14 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-lg">
                {{ strtoupper(substr($company->name,0,1)) }}
            </div>

            <div>
                <p class="text-sm text-slate-500">Workspace</p>
                <p class="text-lg font-semibold text-slate-900">
                    {{ $company->name }}
                </p>
            </div>

        </div>


        <!-- COMPANY NAME -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">

            <label class="text-sm font-medium text-slate-700">
                Company Name
            </label>

            <div class="md:col-span-2">
                <input
                    type="text"
                    value="{{ $company->name }}"
                    class="w-full px-3 py-2 bg-gray-50 border border-slate-200 rounded-lg text-slate-900 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none"
                    readonly
                >
            </div>

        </div>


        <!-- WORKSPACE URL -->

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">

            <label class="text-sm font-medium text-slate-700">
                Workspace URL
            </label>

            <div class="md:col-span-2 flex rounded-lg shadow-sm">

                <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-slate-200 bg-gray-50 text-slate-500 text-sm">
                    app.yoursite.com/
                </span>

                <input
                    type="text"
                    value="{{ $company->slug }}"
                    class="flex-1 block w-full min-w-0 px-3 py-2 rounded-none rounded-r-lg border border-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none sm:text-sm"
                    readonly
                >

            </div>

        </div>


        <!-- BUSINESS DETAILS -->

        <div class="border-t border-slate-100 pt-6 space-y-6">

            <h3 class="text-sm font-semibold text-slate-800 tracking-wide">
                Business Details
            </h3>


            <!-- INDUSTRY -->

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">

                <label class="text-sm font-medium text-slate-700">
                    Industry
                </label>

                <div class="md:col-span-2">

                    <input
                        type="text"
                        value="{{ $company->industry ?? 'Not specified' }}"
                        class="w-full px-3 py-2 bg-gray-50 border border-slate-200 rounded-lg text-slate-900"
                        readonly
                    >

                </div>

            </div>


            <!-- WEBSITE -->

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">

                <label class="text-sm font-medium text-slate-700">
                    Website
                </label>

                <div class="md:col-span-2">

                    <input
                        type="text"
                        value="{{ $company->website ?? 'Not provided' }}"
                        class="w-full px-3 py-2 bg-gray-50 border border-slate-200 rounded-lg text-slate-900"
                        readonly
                    >

                </div>

            </div>


            <!-- PHONE -->

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">

                <label class="text-sm font-medium text-slate-700">
                    Phone
                </label>

                <div class="md:col-span-2">

                    <input
                        type="text"
                        value="{{ $company->phone ?? 'Not provided' }}"
                        class="w-full px-3 py-2 bg-gray-50 border border-slate-200 rounded-lg text-slate-900"
                        readonly
                    >

                </div>

            </div>

        </div>


    </div>

</section>

                    <!-- Plan Section -->
                    <section class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden">
                        <div class="p-6 flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900">Subscription Plan</h2>
                                <p class="text-sm text-slate-500">You are currently on the <span class="font-semibold text-indigo-600">{{ $company->plan }}</span> plan.</p>
                            </div>
                            <a href="#" class="text-sm font-semibold text-indigo-60 the-600 hover:text-indigo-500">Upgrade plan &rarr;</a>
                        </div>
                        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Next billing date: Sept 12, 2024</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </div>
                    </section>

                    <!-- Danger Zone -->
                    <div class="pt-4">
                        <h3 class="text-sm font-semibold text-red-600 uppercase tracking-wider mb-4">Danger Zone</h3>
                        <div class="bg-red-50/50 border border-red-100 rounded-xl p-6 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-bold text-red-900">Delete this company</p>
                                <p class="text-sm text-red-700">Once you delete a company, there is no going back. Please be certain.</p>
                            </div>
                            <button class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg red-700 transition-all shadow-sm shadow-red-200">
                                Delete Company
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>
