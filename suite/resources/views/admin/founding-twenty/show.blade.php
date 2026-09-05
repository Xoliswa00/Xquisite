@php
    $a = $application;
    $tierColor = match($a->tier) {
        'high' => 'text-emerald-400',
        'good' => 'text-[#0078D4]',
        'potential' => 'text-amber-400',
        default => 'text-slate-400',
    };
    $painQuestions = [
        'pain_forgotten_appointments' => 'Clients forgetting appointments',
        'pain_late_cancellations' => 'Clients cancelling at the last minute',
        'pain_no_shows' => 'Clients not showing up at all',
        'pain_double_bookings' => 'Double bookings or scheduling conflicts',
        'pain_booking_enquiry_time' => 'Time spent on booking enquiries',
        'pain_staff_availability' => 'Knowing which staff member is available',
        'pain_tracking_balances' => 'Tracking what customers owe',
        'pain_revenue_visibility' => 'Knowing how much revenue was generated',
        'pain_customer_data_organisation' => 'Keeping customer information organised',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">Founding 20 Application</x-slot>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white">{{ $a->business_name }}</h2>
                <p class="text-slate-400 text-sm mt-1">{{ $a->owner_name }} · {{ $a->phone }} @if($a->email) · {{ $a->email }} @endif</p>
            </div>
            <a href="{{ route('admin.founding-twenty.index') }}" class="text-sm text-slate-400 hover:text-slate-200">&larr; Back to list</a>
        </div>

        @php
            $isSelected = in_array($a->status, ['selected', 'converted']);
            $steps = [
                ['label' => 'Selected for the programme', 'done' => $isSelected],
                ['label' => 'Deposit confirmed', 'done' => $a->deposit_confirmed_at !== null],
                ['label' => 'Tenant account linked', 'done' => $a->tenant_id !== null],
                ['label' => 'Promo code issued', 'done' => $a->promoCodeRedemption !== null],
                ['label' => 'First-value milestone hit', 'done' => $a->first_value_milestone_at !== null],
            ];
        @endphp
        <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
            <h3 class="text-sm font-semibold text-slate-300 mb-4">Onboarding checklist</h3>
            <div class="grid grid-cols-1 sm:grid-cols-5 gap-3 mb-5">
                @foreach($steps as $step)
                    <div class="flex items-center gap-2 text-sm">
                        <span class="w-5 h-5 rounded-full flex items-center justify-center shrink-0 {{ $step['done'] ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-700 text-slate-500' }}">
                            @if($step['done'])
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            @else
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                            @endif
                        </span>
                        <span class="{{ $step['done'] ? 'text-slate-300' : 'text-slate-500' }}">{{ $step['label'] }}</span>
                    </div>
                @endforeach
            </div>

            <div class="grid sm:grid-cols-2 gap-4 pt-4 border-t border-slate-700">
                <div>
                    @if($a->tenant)
                        <p class="text-sm text-slate-300">Linked tenant: <span class="text-white font-medium">{{ $a->tenant->name }}</span></p>
                    @else
                        <div class="space-y-2">
                            <form method="POST" action="{{ route('admin.founding-twenty.tenant', $a) }}" class="flex items-end gap-2">
                                @csrf
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-slate-400 mb-1">Link to an existing tenant</label>
                                    <select name="tenant_id" required class="w-full bg-slate-900 border-slate-700 text-white rounded-lg text-sm">
                                        <option value="">Select…</option>
                                        @foreach($tenants as $t)
                                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="px-3 py-2 text-sm bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg transition">Link</button>
                            </form>
                            <a href="{{ route('admin.tenants.create', ['founding_twenty' => $a->id]) }}" class="text-xs text-[#0078D4] hover:underline">or create a new tenant from this application &rarr;</a>
                        </div>
                    @endif
                </div>
                <div>
                    @if($a->first_value_milestone_at)
                        <p class="text-sm text-slate-300">First win: <span class="text-white">{{ $a->first_value_milestone_note }}</span> <span class="text-slate-500">({{ $a->first_value_milestone_at->diffForHumans() }})</span></p>
                    @else
                        <form method="POST" action="{{ route('admin.founding-twenty.milestone', $a) }}" class="flex items-end gap-2">
                            @csrf
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-slate-400 mb-1">Log first-value win (day 1-7 goal)</label>
                                <input type="text" name="first_value_milestone_note" required placeholder="e.g. sent first automated reminder" class="w-full bg-slate-900 border-slate-700 text-white placeholder-slate-500 rounded-lg text-sm">
                            </div>
                            <button type="submit" class="px-3 py-2 text-sm bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg transition">Log</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">

                <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
                    <h3 class="text-sm font-semibold text-slate-300 mb-3">Business profile</h3>
                    <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                        <dt class="text-slate-400">Type</dt><dd class="text-white capitalize">{{ $a->business_type }}{{ $a->business_type_other ? " ({$a->business_type_other})" : '' }}</dd>
                        <dt class="text-slate-400">Location</dt><dd class="text-white">{{ $a->location ?? '—' }}</dd>
                        <dt class="text-slate-400">Years operating</dt><dd class="text-white">{{ $a->years_operating ?? '—' }}</dd>
                        <dt class="text-slate-400">Staff</dt><dd class="text-white">{{ $a->staff_count ?? '—' }}</dd>
                        <dt class="text-slate-400">Locations</dt><dd class="text-white">{{ $a->locations_count ?? '—' }}</dd>
                        <dt class="text-slate-400">Monthly customers</dt><dd class="text-white">{{ $a->monthly_customers ?? '—' }}</dd>
                        <dt class="text-slate-400">Monthly appointments</dt><dd class="text-white">{{ $a->monthly_appointments ?? '—' }}</dd>
                    </dl>
                </div>

                <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
                    <h3 class="text-sm font-semibold text-slate-300 mb-3">Current operations</h3>
                    <dl class="space-y-2 text-sm">
                        <div><dt class="text-slate-400 inline">Booking:</dt> <dd class="text-white inline">{{ implode(', ', $a->booking_methods ?? []) ?: '—' }}</dd></div>
                        <div><dt class="text-slate-400 inline">Appointment mgmt:</dt> <dd class="text-white inline">{{ implode(', ', $a->appointment_management_methods ?? []) ?: '—' }}</dd></div>
                        <div><dt class="text-slate-400 inline">Customer data:</dt> <dd class="text-white inline">{{ implode(', ', $a->customer_data_methods ?? []) ?: '—' }}</dd></div>
                        <div><dt class="text-slate-400 inline">Payment tracking:</dt> <dd class="text-white inline">{{ implode(', ', $a->payment_tracking_methods ?? []) ?: '—' }}</dd></div>
                        <div><dt class="text-slate-400 inline">Balance tracking:</dt> <dd class="text-white inline">{{ implode(', ', $a->balance_tracking_methods ?? []) ?: '—' }}</dd></div>
                        <div><dt class="text-slate-400 inline">Card payment device:</dt> <dd class="text-white inline">{{ $a->card_payment_device ?: '—' }}</dd></div>
                    </dl>
                </div>

                <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
                    <h3 class="text-sm font-semibold text-slate-300 mb-3">Pain frequency <span class="text-slate-500 font-normal">(1 = never, 5 = very often)</span></h3>
                    <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                        @foreach($painQuestions as $field => $label)
                            <dt class="text-slate-400">{{ $label }}</dt><dd class="text-white">{{ $a->{$field} ?? '—' }}</dd>
                        @endforeach
                    </dl>
                </div>

                <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
                    <h3 class="text-sm font-semibold text-slate-300 mb-3">Impact &amp; time cost</h3>
                    <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                        <dt class="text-slate-400">No-shows/month</dt><dd class="text-white">{{ $a->no_shows_per_month ?? '—' }}</dd>
                        <dt class="text-slate-400">Avg. appointment value</dt><dd class="text-white">{{ $a->avg_appointment_value ?? '—' }}</dd>
                        <dt class="text-slate-400">Hours on booking admin/week</dt><dd class="text-white">{{ $a->hours_booking_admin ?? '—' }}</dd>
                        <dt class="text-slate-400">Hours on availability msgs/week</dt><dd class="text-white">{{ $a->hours_availability_messages ?? '—' }}</dd>
                        <dt class="text-slate-400">Hours on manual reminders/week</dt><dd class="text-white">{{ $a->hours_manual_reminders ?? '—' }}</dd>
                    </dl>
                </div>

                <div class="bg-slate-800 rounded-xl border border-slate-700 p-6 space-y-3">
                    <h3 class="text-sm font-semibold text-slate-300">Alternatives &amp; priorities</h3>
                    <p class="text-sm"><span class="text-slate-400">Adoption barriers:</span> <span class="text-white">{{ implode(', ', $a->adoption_barriers ?? []) ?: '—' }}{{ $a->adoption_barrier_other ? " ({$a->adoption_barrier_other})" : '' }}</span></p>
                    @if($a->past_solution_frustration)
                        <p class="text-sm"><span class="text-slate-400">Past frustration:</span> <span class="text-white">{{ $a->past_solution_frustration }}</span></p>
                    @endif
                    <p class="text-sm"><span class="text-slate-400">Priority features:</span> <span class="text-white">{{ implode(', ', $a->priority_features ?? []) ?: '—' }}</span></p>
                    <p class="text-sm"><span class="text-slate-400">Biggest single difference:</span> <span class="text-white">{{ $a->top_priority_feature ?? '—' }}</span></p>
                    @if($a->automation_wishlist)
                        <p class="text-sm"><span class="text-slate-400">Automation wishlist:</span> <span class="text-white">{{ $a->automation_wishlist }}</span></p>
                    @endif
                </div>

                <div class="bg-slate-800 rounded-xl border border-slate-700 p-6 space-y-3">
                    <h3 class="text-sm font-semibold text-slate-300">Value &amp; commercial signal</h3>
                    <p class="text-sm"><span class="text-slate-400">Value rating:</span> <span class="text-white">{{ $a->value_rating ?? '—' }}/5</span></p>
                    @if($a->value_open_text)
                        <p class="text-sm"><span class="text-slate-400">What would justify R200/month:</span> <span class="text-white">{{ $a->value_open_text }}</span></p>
                    @endif
                    <p class="text-sm"><span class="text-slate-400">Continuation likelihood:</span> <span class="text-white capitalize">{{ str_replace('_', ' ', $a->continuation_likelihood ?? '—') }}</span></p>
                    @if($a->continuation_driver)
                        <p class="text-sm"><span class="text-slate-400">Would continue if:</span> <span class="text-white">{{ $a->continuation_driver }}</span></p>
                    @endif
                    @if($a->churn_driver)
                        <p class="text-sm"><span class="text-slate-400">Would cancel if:</span> <span class="text-white">{{ $a->churn_driver }}</span></p>
                    @endif
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-slate-800 rounded-xl border border-slate-700 p-6 text-center">
                    <p class="text-slate-400 text-sm">Score</p>
                    <p class="text-4xl font-bold {{ $tierColor }} mt-1">{{ $a->score ?? '—' }}<span class="text-lg text-slate-500">/100</span></p>
                    <p class="text-xs uppercase tracking-wide {{ $tierColor }} mt-1">{{ $a->tier ?? 'unscored' }}</p>
                </div>

                <div class="bg-slate-800 rounded-xl border border-slate-700 p-6 space-y-3">
                    <h3 class="text-sm font-semibold text-slate-300">Founding 20 opt-in</h3>
                    <p class="text-sm text-slate-300">Wants to join: <span class="text-white">{{ $a->wants_founding_twenty ? 'Yes' : 'No' }}</span></p>
                    <p class="text-sm text-slate-300">Willing to give feedback: <span class="text-white">{{ $a->willing_to_give_feedback ? 'Yes' : 'No' }}</span></p>
                    <p class="text-sm text-slate-300">Preferred contact: <span class="text-white capitalize">{{ $a->preferred_contact_method }}</span></p>
                    @if($a->best_contact_time)
                        <p class="text-sm text-slate-300">Best time: <span class="text-white">{{ $a->best_contact_time }}</span></p>
                    @endif
                    @if($a->source)
                        <p class="text-sm text-slate-300">Source: <span class="text-white">{{ $a->source }}</span></p>
                    @endif
                </div>

                @if($a->deposit_amount !== null)
                    @php
                        $reserveUrl = route('founding-twenty.reserve', [$a, $a->reservationToken()]);
                        $depositStatus = match(true) {
                            $a->deposit_refunded_at !== null => ['label' => 'Refunded', 'color' => 'text-slate-400'],
                            $a->deposit_confirmed_at !== null => ['label' => 'Confirmed', 'color' => 'text-emerald-400'],
                            $a->deposit_submitted_at !== null => ['label' => 'POP submitted — awaiting review', 'color' => 'text-amber-400'],
                            default => ['label' => 'Awaiting payment', 'color' => 'text-slate-400'],
                        };
                    @endphp
                    <div class="bg-slate-800 rounded-xl border border-slate-700 p-6 space-y-3">
                        <h3 class="text-sm font-semibold text-slate-300">Reservation deposit</h3>
                        <p class="text-sm text-slate-300">Amount: <span class="text-white">R{{ number_format($a->deposit_amount, 2) }}</span></p>
                        <p class="text-sm text-slate-300">Reference: <span class="text-white font-mono">{{ $a->deposit_reference }}</span></p>
                        <p class="text-sm text-slate-300">Status: <span class="{{ $depositStatus['color'] }} font-medium">{{ $depositStatus['label'] }}</span></p>

                        <div class="pt-1">
                            <label class="block text-xs font-medium text-slate-400 mb-1">Reservation link — send via {{ $a->preferred_contact_method }}</label>
                            <input type="text" readonly value="{{ $reserveUrl }}" onclick="this.select()" class="w-full bg-slate-900 border-slate-700 text-slate-300 rounded-lg text-xs">
                        </div>

                        @if($a->deposit_pop_path)
                            <a href="{{ route('admin.founding-twenty.deposit.pop', $a) }}"
                               class="inline-flex items-center px-3 py-1.5 text-xs bg-slate-700 hover:bg-slate-600 text-slate-300 rounded transition">
                                Download proof of payment
                            </a>
                        @endif

                        <div class="flex gap-2 pt-1">
                            @if($a->deposit_submitted_at && !$a->deposit_confirmed_at)
                                <form method="POST" action="{{ route('admin.founding-twenty.deposit.confirm', $a) }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 text-xs bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-400 rounded transition">
                                        Confirm deposit
                                    </button>
                                </form>
                            @endif
                            @if($a->deposit_confirmed_at && !$a->deposit_refunded_at)
                                <form method="POST" action="{{ route('admin.founding-twenty.deposit.refund', $a) }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 text-xs bg-slate-700 hover:bg-slate-600 text-slate-300 rounded transition">
                                        Mark refunded
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="bg-slate-800 rounded-xl border border-slate-700 p-6 space-y-3">
                    <h3 class="text-sm font-semibold text-slate-300">Promo code</h3>
                    @if($a->promoCodeRedemption)
                        <p class="text-sm text-slate-300">Code: <span class="text-white font-mono">{{ $a->promoCodeRedemption->promoCode->code }}</span></p>
                        <p class="text-sm text-slate-300">Value given: <span class="text-[#D4AF37] font-medium">R{{ number_format($a->promoCodeRedemption->financial_value, 2) }}</span></p>
                        <a href="{{ route('admin.promo-codes.show', $a->promoCodeRedemption->promoCode) }}" class="text-xs text-[#0078D4] hover:underline">View code details &rarr;</a>
                    @else
                        <p class="text-sm text-slate-400">No promo code issued yet.</p>
                        <a href="{{ route('admin.promo-codes.index') }}" class="text-xs text-[#0078D4] hover:underline">Go to promo codes &rarr;</a>
                    @endif
                </div>

                <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
                    <h3 class="text-sm font-semibold text-slate-300 mb-3">Review</h3>
                    <form method="POST" action="{{ route('admin.founding-twenty.status', $a) }}" class="space-y-3">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Status</label>
                            <select name="status" class="w-full bg-slate-900 border-slate-700 text-white rounded-lg text-sm">
                                @foreach(['pending', 'reviewing', 'selected', 'waitlisted', 'rejected', 'converted'] as $status)
                                    <option value="{{ $status }}" @selected($a->status === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Notes</label>
                            <textarea name="admin_notes" rows="3" class="w-full bg-slate-900 border-slate-700 text-white rounded-lg text-sm">{{ $a->admin_notes }}</textarea>
                        </div>
                        <button type="submit" class="w-full bg-[#0078D4] hover:bg-[#0065B8] text-white rounded-lg py-2 text-sm font-medium transition">
                            Save
                        </button>
                        @if($a->reviewer)
                            <p class="text-xs text-slate-500">Last reviewed by {{ $a->reviewer->name }} {{ $a->reviewed_at?->diffForHumans() }}</p>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
