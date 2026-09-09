<x-app-layout>
    <x-slot name="header">Staff roles</x-slot>

    <div class="max-w-3xl space-y-5">
        <a href="{{ route('admin.users.index') }}" class="inline-block text-sm text-slate-400 hover:text-white transition-colors">&larr; Back to staff</a>

        <div class="bg-slate-800 rounded-xl border border-slate-700 p-6 space-y-4">
            <p class="text-sm text-slate-300">Every staff account has one role. You can also tick extra permissions on top of the role when someone needs a little more.</p>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="bg-slate-900/50 border border-slate-700 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-emerald-300 mb-2">Employee</h3>
                    <p class="text-xs text-slate-400 mb-3">Day-to-day work on the floor.</p>
                    <ul class="text-sm text-slate-300 space-y-1.5">
                        <li>Take and manage bookings, use the calendar</li>
                        <li>Add and update customers</li>
                        <li>Ring up sales and orders</li>
                        <li>See reports and revenue</li>
                    </ul>
                    <p class="text-xs text-slate-500 mt-3">Can't manage staff, edit products or pricing, manage stock, change settings, or open Property Management.</p>
                </div>

                <div class="bg-slate-900/50 border border-slate-700 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-blue-300 mb-2">Manager</h3>
                    <p class="text-xs text-slate-400 mb-3">Everything an Employee can do, plus running the business.</p>
                    <ul class="text-sm text-slate-300 space-y-1.5">
                        <li>Add, edit and remove staff accounts</li>
                        <li>Manage products, services and pricing</li>
                        <li>Manage stock, suppliers and purchase orders</li>
                        <li>Manage properties, leases and rent</li>
                        <li>Change store and business settings</li>
                    </ul>
                    <p class="text-xs text-slate-500 mt-3">Only the owner can add or change another Manager's account.</p>
                </div>
            </div>

            <div class="bg-slate-900/50 border border-slate-700 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-slate-200 mb-1">Signing in</h3>
                <p class="text-sm text-slate-400">You set a temporary password (or we generate one) and hand it over. There's no email link to click. They're asked to choose their own password the first time they sign in.</p>
            </div>
        </div>
    </div>
</x-app-layout>
