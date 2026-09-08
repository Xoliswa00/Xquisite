<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Founding 20 Questionnaire — Xquisite Creations</title>
    <meta name="description" content="Help us understand how your business really operates — and be considered for the Xquisite Creations Founding 20 Programme: 3 months free, no setup fee.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800">

<header class="bg-white border-b border-slate-200 shadow-sm">
    <div class="max-w-3xl mx-auto px-4 py-4 flex items-center gap-3">
        <img src="/img/android-icon-96x96.png" alt="Xquisite Creations" class="w-9 h-9 rounded-lg object-contain shrink-0">
        <div class="min-w-0">
            <span class="block text-lg font-bold text-slate-900 truncate">Xquisite Creations</span>
            <span class="text-xs font-semibold uppercase tracking-wide text-[#D4AF37]">Founding 20 Programme</span>
        </div>
    </div>
</header>

<main class="max-w-3xl mx-auto px-4 py-10">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Help us understand your business</h1>
        <p class="text-slate-500 text-sm mt-2 leading-relaxed">
            Running a small business isn't simple. We want to understand how business owners actually manage bookings,
            customers, staff and payments — and use that to select 20 businesses for our Founding 20 Programme:
            <strong class="text-slate-700">3 months free, no setup fee.</strong>
        </p>
        <p class="text-slate-400 text-xs mt-2">Takes about 5–7 minutes. Every answer helps us build a better platform for your industry.</p>
    </div>

    @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    @php
        $painQuestions = [
            'pain_forgotten_appointments' => 'Clients forgetting appointments',
            'pain_late_cancellations' => 'Clients cancelling at the last minute',
            'pain_no_shows' => 'Clients not showing up at all',
            'pain_double_bookings' => 'Double bookings or scheduling conflicts',
            'pain_booking_enquiry_time' => 'Spending significant time responding to booking enquiries',
            'pain_staff_availability' => 'Struggling to know which staff member is available',
            'pain_tracking_balances' => 'Difficulty tracking what customers owe you',
            'pain_revenue_visibility' => 'Difficulty knowing how much revenue your business generated',
            'pain_customer_data_organisation' => 'Difficulty keeping customer information organised',
        ];

        $bookingMethodOptions = ['whatsapp' => 'WhatsApp', 'phone' => 'Phone', 'instagram_facebook' => 'Instagram/Facebook', 'website' => 'Website', 'walk_in' => 'Walk-in', 'booking_platform' => 'Booking platform', 'other' => 'Other'];
        $apptManagementOptions = ['paper_diary' => 'Paper diary', 'whatsapp' => 'WhatsApp', 'excel' => 'Excel/Google Sheets', 'google_calendar' => 'Google Calendar', 'booking_software' => 'Booking software', 'combination' => 'Combination of the above'];
        $customerDataOptions = ['notebook' => 'Notebook', 'whatsapp' => 'WhatsApp', 'excel' => 'Excel/spreadsheet', 'booking_software' => 'Booking software', 'memory' => "I just remember / don't track it"];
        $paymentTrackingOptions = ['notebook' => 'Notebook', 'excel' => 'Excel', 'banking_app' => 'Banking app', 'pos' => 'POS system', 'accounting_software' => 'Accounting software', 'booking_system' => 'Booking system', 'none' => "I don't formally track it"];
        $balanceTrackingOptions = ['notebook' => 'Notebook', 'excel' => 'Excel', 'banking_app' => 'Banking app', 'accounting_software' => 'Accounting software', 'booking_system' => 'Booking system', 'none' => "I don't track outstanding balances"];
        $adoptionBarrierOptions = ['cost' => 'Cost', 'dont_know_which' => 'Don\'t know which one to choose', 'too_complicated' => 'Too complicated', 'too_small' => 'My business is too small', 'customers_prefer_whatsapp' => 'My customers prefer WhatsApp', 'dont_need_one' => "I don't need one", 'bad_experience' => 'Previous bad experience', 'dont_know_how' => "I don't know how to use one", 'other' => 'Other'];
        $featureOptions = ['online_booking' => 'Online customer booking', 'automated_reminders' => 'Automated reminders', 'staff_calendars' => 'Staff calendars', 'customer_profiles' => 'Customer profiles', 'appointment_management' => 'Appointment management', 'payments' => 'Payments', 'outstanding_balances' => 'Outstanding balances', 'pos' => 'POS', 'stock_management' => 'Stock management', 'business_reporting' => 'Business reporting', 'revenue_tracking' => 'Revenue tracking', 'customer_history' => 'Customer history', 'marketing_retention' => 'Marketing/customer retention', 'other' => 'Other'];
        $bucketLabels = ['<1' => 'Less than 1 hour', '1-3' => '1–3 hours', '3-5' => '3–5 hours', '5-10' => '5–10 hours', '10+' => '10+ hours'];
    @endphp

    <form method="POST" action="{{ route('founding-twenty.store') }}" class="space-y-6">
        @csrf
        <input type="hidden" name="source" value="{{ old('source', $source) }}">

        {{-- Section 1 --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#0078D4]">Section 1 of 10</p>
            <h2 class="text-base font-semibold text-slate-800">Your business</h2>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">What type of business do you operate? *</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach(['salon' => 'Salon', 'beauty' => 'Beauty', 'wellness' => 'Wellness/Spa', 'fitness' => 'Fitness', 'service' => 'Other service business', 'other' => 'Other'] as $value => $label)
                        <label class="flex items-center gap-2 text-sm border border-slate-200 rounded-xl px-3 py-2 cursor-pointer has-[:checked]:border-[#0078D4] has-[:checked]:bg-blue-50">
                            <input type="radio" name="business_type" value="{{ $value }}" required @checked(old('business_type') === $value) class="text-[#0078D4] focus:ring-[#0078D4]">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                <input type="text" name="business_type_other" value="{{ old('business_type_other') }}" placeholder="If other, tell us what kind of business" class="mt-2 w-full border-slate-300 rounded-xl text-sm">
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Business name *</label>
                    <input type="text" name="business_name" value="{{ old('business_name') }}" required class="w-full border-slate-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Your name *</label>
                    <input type="text" name="owner_name" value="{{ old('owner_name') }}" required class="w-full border-slate-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Phone (WhatsApp) *</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required class="w-full border-slate-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full border-slate-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Town/area</label>
                    <input type="text" name="location" value="{{ old('location') }}" class="w-full border-slate-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">How long have you operated?</label>
                    <select name="years_operating" class="w-full border-slate-300 rounded-xl text-sm">
                        <option value="">Select…</option>
                        @foreach(['<1' => 'Less than 1 year', '1-3' => '1–3 years', '3-5' => '3–5 years', '5+' => '5+ years'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('years_operating') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Number of staff</label>
                    <select name="staff_count" class="w-full border-slate-300 rounded-xl text-sm">
                        <option value="">Select…</option>
                        @foreach(['1' => 'Just me', '2-5' => '2–5', '6-10' => '6–10', '10+' => '10+'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('staff_count') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Number of locations</label>
                    <input type="number" name="locations_count" value="{{ old('locations_count', 1) }}" min="1" class="w-full border-slate-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Monthly customers (approx.)</label>
                    <select name="monthly_customers" class="w-full border-slate-300 rounded-xl text-sm">
                        <option value="">Select…</option>
                        @foreach(['0-50' => '0–50', '51-150' => '51–150', '151-300' => '151–300', '300+' => '300+'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('monthly_customers') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Monthly appointments (approx.)</label>
                    <select name="monthly_appointments" class="w-full border-slate-300 rounded-xl text-sm">
                        <option value="">Select…</option>
                        @foreach(['0-50' => '0–50', '51-150' => '51–150', '151-300' => '151–300', '300+' => '300+'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('monthly_appointments') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Section 2 --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#0078D4]">Section 2 of 10</p>
            <h2 class="text-base font-semibold text-slate-800">How you run things today</h2>

            @foreach([
                ['name' => 'booking_methods', 'label' => 'How do customers book with you? (select all that apply)', 'options' => $bookingMethodOptions],
                ['name' => 'appointment_management_methods', 'label' => 'How do you manage appointments?', 'options' => $apptManagementOptions],
                ['name' => 'customer_data_methods', 'label' => 'How do you manage customer information?', 'options' => $customerDataOptions],
                ['name' => 'payment_tracking_methods', 'label' => 'How do you track payments?', 'options' => $paymentTrackingOptions],
                ['name' => 'balance_tracking_methods', 'label' => 'How do you track outstanding balances?', 'options' => $balanceTrackingOptions],
            ] as $group)
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">{{ $group['label'] }}</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @foreach($group['options'] as $value => $label)
                            <label class="flex items-center gap-2 text-sm border border-slate-200 rounded-xl px-3 py-2 cursor-pointer has-[:checked]:border-[#0078D4] has-[:checked]:bg-blue-50">
                                <input type="checkbox" name="{{ $group['name'] }}[]" value="{{ $value }}" class="rounded text-[#0078D4] focus:ring-[#0078D4]">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">What card payment device do you currently use, if any? (e.g. Yoco, SnapScan, a bank card machine)</label>
                <input type="text" name="card_payment_device" value="{{ old('card_payment_device') }}" placeholder="e.g. Yoco" class="w-full border-slate-300 rounded-xl text-sm">
            </div>
        </div>

        {{-- Section 3 --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#0078D4]">Section 3 of 10</p>
            <h2 class="text-base font-semibold text-slate-800">In the last 30 days, how often have you experienced…</h2>
            <div class="flex justify-end text-xs text-slate-400 pr-1"><span>Never</span><span class="ml-16">Very often</span></div>

            @foreach($painQuestions as $field => $label)
                <div class="border-t border-slate-100 pt-3 first:border-0 first:pt-0">
                    <p class="text-sm text-slate-700 mb-2">{{ $label }} *</p>
                    <div class="flex items-center gap-3">
                        @for($i = 1; $i <= 5; $i++)
                            <label class="flex flex-col items-center gap-1 text-xs text-slate-400 cursor-pointer">
                                <input type="radio" name="{{ $field }}" value="{{ $i }}" required @checked(old($field) == $i) class="text-[#0078D4] focus:ring-[#0078D4]">
                                {{ $i }}
                            </label>
                        @endfor
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Section 4 --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#0078D4]">Section 4 of 10</p>
            <h2 class="text-base font-semibold text-slate-800">Quantifying the impact</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">No-shows per month (approx.)</label>
                    <select name="no_shows_per_month" class="w-full border-slate-300 rounded-xl text-sm">
                        <option value="">Select…</option>
                        @foreach(['0' => '0', '1-2' => '1–2', '3-5' => '3–5', '6-10' => '6–10', '10+' => '10+'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('no_shows_per_month') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Average appointment value</label>
                    <select name="avg_appointment_value" class="w-full border-slate-300 rounded-xl text-sm">
                        <option value="">Select…</option>
                        @foreach(['0-100' => 'R0–R100', '101-250' => 'R101–R250', '251-500' => 'R251–R500', '501-1000' => 'R501–R1,000', '1000+' => 'R1,000+'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('avg_appointment_value') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Section 5 --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#0078D4]">Section 5 of 10</p>
            <h2 class="text-base font-semibold text-slate-800">Time cost</h2>
            @foreach([
                'hours_booking_admin' => 'How many hours per week do you spend managing appointments?',
                'hours_availability_messages' => 'How many hours per week answering "Are you available?" messages?',
                'hours_manual_reminders' => 'How many hours per week manually reminding clients?',
            ] as $field => $label)
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ $label }}</label>
                    <select name="{{ $field }}" class="w-full border-slate-300 rounded-xl text-sm">
                        <option value="">Select…</option>
                        @foreach($bucketLabels as $value => $bucketLabel)
                            <option value="{{ $value }}" @selected(old($field) === $value)>{{ $bucketLabel }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
        </div>

        {{-- Section 6 --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#0078D4]">Section 6 of 10</p>
            <h2 class="text-base font-semibold text-slate-800">What's stopped you before</h2>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">What prevents you from using a business management/booking platform?</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach($adoptionBarrierOptions as $value => $label)
                        <label class="flex items-center gap-2 text-sm border border-slate-200 rounded-xl px-3 py-2 cursor-pointer has-[:checked]:border-[#0078D4] has-[:checked]:bg-blue-50">
                            <input type="checkbox" name="adoption_barriers[]" value="{{ $value }}" class="rounded text-[#0078D4] focus:ring-[#0078D4]">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                <input type="text" name="adoption_barrier_other" value="{{ old('adoption_barrier_other') }}" placeholder="If other, tell us more" class="mt-2 w-full border-slate-300 rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">If you've used a booking/business system before, what frustrated you about it?</label>
                <textarea name="past_solution_frustration" rows="2" class="w-full border-slate-300 rounded-xl text-sm">{{ old('past_solution_frustration') }}</textarea>
            </div>
        </div>

        {{-- Section 7 --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#0078D4]">Section 7 of 10</p>
            <h2 class="text-base font-semibold text-slate-800">What would help most</h2>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Which would be most valuable to your business? (choose up to 5)</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach($featureOptions as $value => $label)
                        <label class="flex items-center gap-2 text-sm border border-slate-200 rounded-xl px-3 py-2 cursor-pointer has-[:checked]:border-[#0078D4] has-[:checked]:bg-blue-50">
                            <input type="checkbox" name="priority_features[]" value="{{ $value }}" class="rounded text-[#0078D4] focus:ring-[#0078D4]">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Which ONE would make the biggest difference to your business?</label>
                <input type="text" name="top_priority_feature" value="{{ old('top_priority_feature') }}" class="w-full border-slate-300 rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Is there one thing you wish your business could do automatically?</label>
                <textarea name="automation_wishlist" rows="2" class="w-full border-slate-300 rounded-xl text-sm">{{ old('automation_wishlist') }}</textarea>
            </div>
        </div>

        {{-- Section 8 --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#0078D4]">Section 8 of 10</p>
            <h2 class="text-base font-semibold text-slate-800">The value of solving this</h2>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">If Xquisite could solve your biggest operational problem, how valuable would that be? *</label>
                <div class="flex items-center gap-4">
                    <span class="text-xs text-slate-400">Not valuable</span>
                    @for($i = 1; $i <= 5; $i++)
                        <label class="flex flex-col items-center gap-1 text-xs text-slate-400 cursor-pointer">
                            <input type="radio" name="value_rating" value="{{ $i }}" required @checked(old('value_rating') == $i) class="text-[#0078D4] focus:ring-[#0078D4]">
                            {{ $i }}
                        </label>
                    @endfor
                    <span class="text-xs text-slate-400">Very valuable</span>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">What would Xquisite need to do for you to say "this is worth R200/month"?</label>
                <textarea name="value_open_text" rows="2" class="w-full border-slate-300 rounded-xl text-sm">{{ old('value_open_text') }}</textarea>
            </div>
        </div>

        {{-- Section 9 --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#0078D4]">Section 9 of 10</p>
            <h2 class="text-base font-semibold text-slate-800">After the free 3 months</h2>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">The standard subscription is R200/month after 3 months free. How would you feel about continuing? *</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach(['very_likely' => 'Very likely', 'likely' => 'Likely', 'unsure' => 'Unsure', 'unlikely' => 'Unlikely', 'very_unlikely' => 'Very unlikely'] as $value => $label)
                        <label class="flex items-center gap-2 text-sm border border-slate-200 rounded-xl px-3 py-2 cursor-pointer has-[:checked]:border-[#0078D4] has-[:checked]:bg-blue-50">
                            <input type="radio" name="continuation_likelihood" value="{{ $value }}" required @checked(old('continuation_likelihood') === $value) class="text-[#0078D4] focus:ring-[#0078D4]">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">What would make you continue?</label>
                    <textarea name="continuation_driver" rows="2" class="w-full border-slate-300 rounded-xl text-sm">{{ old('continuation_driver') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">What would make you cancel?</label>
                    <textarea name="churn_driver" rows="2" class="w-full border-slate-300 rounded-xl text-sm">{{ old('churn_driver') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Section 10 --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#D4AF37]">Section 10 of 10</p>
            <h2 class="text-base font-semibold text-slate-800">Founding 20 Programme</h2>

            <label class="flex items-start gap-3 text-sm text-slate-700 border border-slate-200 rounded-xl px-4 py-3 cursor-pointer has-[:checked]:border-[#D4AF37] has-[:checked]:bg-amber-50">
                <input type="checkbox" name="wants_founding_twenty" value="1" @checked(old('wants_founding_twenty', true)) class="mt-0.5 rounded text-[#D4AF37] focus:ring-[#D4AF37]">
                <span>Yes, I'd like to be considered for the Founding 20 Programme — 3 months free, no setup fee.</span>
            </label>

            <label class="flex items-start gap-3 text-sm text-slate-700 border border-slate-200 rounded-xl px-4 py-3 cursor-pointer has-[:checked]:border-[#D4AF37] has-[:checked]:bg-amber-50">
                <input type="checkbox" name="willing_to_give_feedback" value="1" @checked(old('willing_to_give_feedback')) class="mt-0.5 rounded text-[#D4AF37] focus:ring-[#D4AF37]">
                <span>I'm willing to actively use the platform and provide feedback during the 3-month programme.</span>
            </label>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Preferred contact method</label>
                    <select name="preferred_contact_method" class="w-full border-slate-300 rounded-xl text-sm">
                        @foreach(['whatsapp' => 'WhatsApp', 'call' => 'Phone call', 'email' => 'Email'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('preferred_contact_method', 'whatsapp') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Best time to contact you</label>
                    <input type="text" name="best_contact_time" value="{{ old('best_contact_time') }}" class="w-full border-slate-300 rounded-xl text-sm">
                </div>
            </div>

            <label class="flex items-start gap-3 text-sm text-slate-600 border border-slate-200 rounded-xl px-4 py-3 cursor-pointer has-[:checked]:border-[#0078D4] has-[:checked]:bg-blue-50">
                <input type="checkbox" name="privacy_consent" value="1" required @checked(old('privacy_consent')) class="mt-0.5 rounded text-[#0078D4] focus:ring-[#0078D4]">
                <span>I consent to Xquisite Creations storing and using my answers, contact details and business information to evaluate this application and, if selected, to operate the Founding 20 Programme, as described in the <a href="{{ route('privacy') }}" target="_blank" class="text-[#0078D4] underline hover:no-underline">Privacy Policy</a>. *</span>
            </label>
        </div>

        <button type="submit" class="w-full bg-[#0078D4] hover:bg-[#0065B8] text-white font-semibold rounded-xl py-3 text-sm transition">
            Submit my application
        </button>
    </form>
</main>

<footer class="border-t border-slate-200 mt-10 py-6 text-center text-xs text-slate-400">
    <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 hover:opacity-80 transition-opacity">
        <img src="/img/android-icon-96x96.png" alt="Xquisite Creations" class="h-5 w-5 object-contain rounded">
        <span>Powered by <span class="font-semibold text-slate-500">Xquisite Creations</span></span>
    </a>
</footer>
</body>
</html>
