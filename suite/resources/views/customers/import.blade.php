<x-app-layout>
    <x-slot name="header">Import Customers</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">

        <div class="flex items-center justify-between">
            <a href="{{ route('customers.index') }}" class="text-sm text-slate-400 hover:text-white">&larr; Back to Customers</a>
        </div>

        <div class="bg-slate-800 rounded-xl p-6 space-y-5">
            <div>
                <h2 class="text-white font-semibold text-lg">Import from your contacts</h2>
                <p class="text-slate-400 text-sm mt-1">
                    Instead of adding customers one by one, upload a file exported from your phone or computer's
                    contacts app. Both <strong class="text-slate-300">CSV</strong> and
                    <strong class="text-slate-300">vCard (.vcf)</strong> files are supported — whichever your export
                    gives you.
                </p>
            </div>

            <details class="text-xs text-slate-400 bg-slate-900/50 rounded-lg p-3">
                <summary class="cursor-pointer font-semibold text-slate-300">How do I export my contacts?</summary>
                <ul class="mt-2 space-y-1 list-disc list-inside">
                    <li><strong class="text-slate-300">Google Contacts</strong> (Android, or Gmail on desktop): contacts.google.com &rarr; Export &rarr; Google CSV or vCard</li>
                    <li><strong class="text-slate-300">iPhone</strong>: usually easiest via iCloud.com &rarr; Contacts &rarr; select all &rarr; Export vCard</li>
                    <li><strong class="text-slate-300">Outlook</strong>: File &rarr; Open &amp; Export &rarr; Import/Export &rarr; Export to a file (CSV)</li>
                </ul>
            </details>

            @if($errors->any())
                <div class="bg-red-900/30 border border-red-800 text-red-300 text-sm rounded-lg p-3">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('customers.import.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Contacts file</label>
                    <input type="file" name="file" accept=".csv,.vcf,text/csv" required
                           class="w-full text-sm text-slate-300 bg-slate-900 border border-slate-700 rounded-lg cursor-pointer focus:outline-none focus:ring-1 focus:ring-[#0078D4] file:mr-4 file:py-2 file:px-4 file:border-0 file:bg-slate-700 file:text-slate-200 file:text-sm file:font-medium hover:file:bg-slate-600">
                    <p class="text-xs text-slate-500 mt-1">Max 5MB. Only a name and phone number are required per contact — anything without either is skipped.</p>
                </div>

                <div class="flex items-center gap-3 text-xs text-slate-400 bg-slate-900/50 rounded-lg p-3">
                    <svg class="w-4 h-4 shrink-0 text-[#0078D4]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Contacts already in your customer list (matched by phone number) are skipped automatically — safe to re-upload the same file.
                </div>

                <button type="submit"
                        class="w-full bg-[#0078D4] hover:bg-[#0065B8] text-white font-semibold py-2.5 rounded-lg transition-colors">
                    Import Contacts
                </button>
            </form>
        </div>

    </div>
</x-app-layout>
