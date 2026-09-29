<?php

namespace Tests\Feature\FoundingTwenty;

use App\Models\FoundingTwentyApplication;
use App\Models\FoundingTwentyDepositEntry;
use App\Models\PlatformInvoice;
use App\Models\Tenant;
use App\Models\User;
use App\Services\FoundingTwentyDepositLedger;
use App\Services\FoundingTwentyProgrammeStats;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * The deposit journal: every movement is a balanced debit and credit, entries are never
 * edited, mistakes are corrected with a reversal, and the dashboard reads the same numbers.
 */
class FoundingTwentyDepositJournalTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('manage-tenants', 'web');
        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('manage-tenants');
    }

    /** An application whose deposit proof is in and waiting to be confirmed. */
    private function awaitingConfirmation(array $overrides = []): FoundingTwentyApplication
    {
        static $n = 0;
        $n++;

        return FoundingTwentyApplication::create(array_merge([
            'owner_name' => 'Thandi Mokoena', 'business_name' => 'Salon ' . $n, 'applicant_role' => 'owner',
            'phone' => '08412345' . str_pad((string) $n, 2, '0', STR_PAD_LEFT), 'preferred_contact_method' => 'whatsapp',
            'business_type' => 'salon', 'submitted_at' => now(), 'status' => 'selected',
            'deposit_amount' => 100, 'deposit_reference' => 'F20-' . str_pad((string) $n, 4, '0', STR_PAD_LEFT),
            'deposit_submitted_at' => now(),
        ], $overrides));
    }

    private function confirmed(array $overrides = []): FoundingTwentyApplication
    {
        $a = $this->awaitingConfirmation($overrides);
        $this->actingAs($this->admin)->post(route('admin.founding-twenty.deposit.confirm', $a));

        return $a->fresh();
    }

    private function tenantWithInvoice(float $amount = 200, string $status = 'unpaid'): array
    {
        static $i = 0;
        $i++;
        $tenant = Tenant::create(['name' => 'T' . $i, 'slug' => 'tj-' . uniqid(), 'email' => 'tj' . uniqid() . '@example.com', 'is_active' => true]);
        $invoice = PlatformInvoice::create([
            'tenant_id' => $tenant->id, 'invoice_number' => 'INV-J-' . $i, 'plan' => 'founding', 'amount' => $amount, 'status' => $status,
            'due_date' => now()->addDays(5), 'billing_period_start' => now(), 'billing_period_end' => now()->addMonth(),
        ]);

        return [$tenant, $invoice];
    }

    private function totals(): array
    {
        return app(FoundingTwentyDepositLedger::class)->totals();
    }

    // ── Every movement is a balanced pair ────────────────────────────────────

    public function test_confirming_a_deposit_journals_money_in_and_a_liability_to_pay_it_back(): void
    {
        $a = $this->confirmed();

        $entry = $a->depositEntries()->sole();
        $this->assertSame('received', $entry->type);
        $this->assertSame('bank', $entry->debit_account);
        $this->assertSame('deposits_held', $entry->credit_account);
        $this->assertSame('100.00', $entry->amount);
        $this->assertSame($this->admin->id, $entry->recorded_by);
        $this->assertSame(['received' => 100.0, 'refunded' => 0.0, 'credited' => 0.0, 'held' => 100.0], $this->totals());
    }

    public function test_confirming_twice_does_not_journal_twice(): void
    {
        $a = $this->confirmed();

        $this->actingAs($this->admin)->post(route('admin.founding-twenty.deposit.confirm', $a));

        $this->assertSame(1, $a->depositEntries()->count());
    }

    public function test_paying_back_clears_the_liability_against_the_bank(): void
    {
        $a = $this->confirmed();

        $this->actingAs($this->admin)->post(route('admin.founding-twenty.deposit.refund', $a), ['deposit_refund_reference' => 'EFT-9']);

        $entry = $a->depositEntries()->where('type', 'refunded')->sole();
        $this->assertSame('deposits_held', $entry->debit_account);
        $this->assertSame('bank', $entry->credit_account);
        $this->assertSame('EFT-9', $entry->reference);
        $this->assertSame(0.0, $this->totals()['held']);
    }

    public function test_crediting_clears_the_liability_against_the_invoice_it_reduced(): void
    {
        [$tenant, $invoice] = $this->tenantWithInvoice();
        $a = $this->confirmed(['tenant_id' => $tenant->id]);

        $this->actingAs($this->admin)->post(route('admin.founding-twenty.deposit.credit', $a), ['invoice_id' => $invoice->id]);

        $entry = $a->depositEntries()->where('type', 'credited')->sole();
        $this->assertSame('deposits_held', $entry->debit_account);
        $this->assertSame('accounts_receivable', $entry->credit_account);
        $this->assertSame($invoice->id, $entry->invoice_id);
        $this->assertSame(['received' => 100.0, 'refunded' => 0.0, 'credited' => 100.0, 'held' => 0.0], $this->totals());
    }

    public function test_a_refused_credit_journals_nothing(): void
    {
        [$tenant, $invoice] = $this->tenantWithInvoice(100);
        $a = $this->confirmed(['tenant_id' => $tenant->id]);

        $this->actingAs($this->admin)->post(route('admin.founding-twenty.deposit.credit', $a), ['invoice_id' => $invoice->id])->assertSessionHas('error');

        $this->assertSame(['received'], $a->depositEntries()->pluck('type')->all());
    }

    // ── Never edited, never deleted ──────────────────────────────────────────

    public function test_journal_entries_cannot_be_edited_or_deleted(): void
    {
        $entry = $this->confirmed()->depositEntries()->sole();

        try {
            $entry->update(['amount' => 5]);
            $this->fail('An entry was edited.');
        } catch (\LogicException $e) {
            $this->assertStringContainsString('cannot be edited', $e->getMessage());
        }
        try {
            $entry->delete();
            $this->fail('An entry was deleted.');
        } catch (\LogicException $e) {
            $this->assertStringContainsString('cannot be deleted', $e->getMessage());
        }
        $this->assertSame('100.00', $entry->fresh()->amount);
    }

    public function test_the_journal_write_is_audited(): void
    {
        $a = $this->confirmed();

        $this->assertDatabaseHas('audit_logs', ['action' => 'FoundingTwentyDepositEntry.created', 'entity_id' => $a->depositEntries()->sole()->id]);
    }

    // ── Corrections are reversals ────────────────────────────────────────────

    public function test_reversing_a_payback_swaps_the_accounts_puts_the_deposit_back_on_hold_and_keeps_both_rows(): void
    {
        $a = $this->confirmed();
        $this->actingAs($this->admin)->post(route('admin.founding-twenty.deposit.refund', $a), ['deposit_refund_reference' => 'EFT-WRONG']);
        $refund = $a->depositEntries()->where('type', 'refunded')->sole();

        $this->actingAs($this->admin)->post(route('admin.founding-twenty.deposits.reverse', $refund), ['reason' => 'Paid the wrong account'])
            ->assertSessionHas('success');

        $reversal = $refund->fresh()->reversal;
        $this->assertSame('reversal', $reversal->type);
        $this->assertSame('bank', $reversal->debit_account);
        $this->assertSame('deposits_held', $reversal->credit_account);
        $this->assertSame('Paid the wrong account', $reversal->reason);
        $this->assertSame(3, $a->depositEntries()->count());
        $this->assertFalse($a->fresh()->isDepositSettled());
        $this->assertNull($a->fresh()->deposit_refund_reference);
        $this->assertSame(['received' => 100.0, 'refunded' => 0.0, 'credited' => 0.0, 'held' => 100.0], $this->totals());
    }

    public function test_reversing_a_credit_puts_the_amount_back_on_the_invoice(): void
    {
        [$tenant, $invoice] = $this->tenantWithInvoice(200);
        $a = $this->confirmed(['tenant_id' => $tenant->id]);
        $this->actingAs($this->admin)->post(route('admin.founding-twenty.deposit.credit', $a), ['invoice_id' => $invoice->id]);
        $credit = $a->depositEntries()->where('type', 'credited')->sole();

        $this->actingAs($this->admin)->post(route('admin.founding-twenty.deposits.reverse', $credit), ['reason' => 'They want it paid back instead']);

        $this->assertSame('200.00', $invoice->fresh()->amount);
        $this->assertStringContainsString('reversed: They want it paid back instead', $invoice->fresh()->notes);
        $this->assertNull($a->fresh()->deposit_credited_at);
        $this->assertSame(100.0, $this->totals()['held']);
    }

    public function test_a_credit_on_an_invoice_that_has_since_been_paid_cannot_be_reversed(): void
    {
        [$tenant, $invoice] = $this->tenantWithInvoice(200);
        $a = $this->confirmed(['tenant_id' => $tenant->id]);
        $this->actingAs($this->admin)->post(route('admin.founding-twenty.deposit.credit', $a), ['invoice_id' => $invoice->id]);
        $invoice->update(['status' => 'paid']);
        $credit = $a->depositEntries()->where('type', 'credited')->sole();

        $this->actingAs($this->admin)->post(route('admin.founding-twenty.deposits.reverse', $credit), ['reason' => 'Changed our minds'])
            ->assertSessionHas('error');

        $this->assertNull($credit->fresh()->reversal);
        $this->assertSame('100.00', $invoice->fresh()->amount);
    }

    public function test_an_entry_can_only_be_reversed_once_and_a_reversal_cannot_be_reversed(): void
    {
        $a = $this->confirmed();
        $this->actingAs($this->admin)->post(route('admin.founding-twenty.deposit.refund', $a));
        $refund = $a->depositEntries()->where('type', 'refunded')->sole();
        $this->actingAs($this->admin)->post(route('admin.founding-twenty.deposits.reverse', $refund), ['reason' => 'First reversal']);

        $this->actingAs($this->admin)->post(route('admin.founding-twenty.deposits.reverse', $refund), ['reason' => 'Second reversal'])->assertSessionHas('error');
        $this->actingAs($this->admin)->post(route('admin.founding-twenty.deposits.reverse', $refund->fresh()->reversal), ['reason' => 'Undo the undo'])->assertSessionHas('error');

        $this->assertSame(3, $a->depositEntries()->count());
    }

    public function test_a_reason_is_required_to_reverse(): void
    {
        $a = $this->confirmed();
        $received = $a->depositEntries()->sole();

        $this->actingAs($this->admin)->post(route('admin.founding-twenty.deposits.reverse', $received), ['reason' => 'no'])->assertSessionHasErrors('reason');

        $this->assertNull($received->fresh()->reversal);
    }

    public function test_a_received_deposit_cannot_be_reversed_while_it_has_been_settled(): void
    {
        $a = $this->confirmed();
        $this->actingAs($this->admin)->post(route('admin.founding-twenty.deposit.refund', $a));

        $this->actingAs($this->admin)->post(route('admin.founding-twenty.deposits.reverse', $a->depositEntries()->where('type', 'received')->sole()), ['reason' => 'Never really arrived'])
            ->assertSessionHas('error');

        $this->assertNotNull($a->fresh()->deposit_confirmed_at);
    }

    public function test_reversing_a_received_deposit_unconfirms_it_and_the_totals_drop(): void
    {
        $a = $this->confirmed();

        $this->actingAs($this->admin)->post(route('admin.founding-twenty.deposits.reverse', $a->depositEntries()->sole()), ['reason' => 'POP was for the wrong amount']);

        $this->assertNull($a->fresh()->deposit_confirmed_at);
        $this->assertSame(['received' => 0.0, 'refunded' => 0.0, 'credited' => 0.0, 'held' => 0.0], $this->totals());
    }

    // ── The journal and the applications must agree ──────────────────────────

    public function test_a_deposit_changed_outside_the_ledger_is_flagged(): void
    {
        $this->confirmed();
        $sneaky = $this->awaitingConfirmation();
        $sneaky->update(['deposit_confirmed_at' => now()]); // confirmed without the ledger

        $flagged = app(FoundingTwentyDepositLedger::class)->discrepancies();

        $this->assertSame([$sneaky->id], $flagged->pluck('id')->all());
    }

    public function test_a_clean_journal_has_no_discrepancies(): void
    {
        $a = $this->confirmed();
        $b = $this->confirmed();
        $this->actingAs($this->admin)->post(route('admin.founding-twenty.deposit.refund', $b));

        $this->assertTrue(app(FoundingTwentyDepositLedger::class)->discrepancies()->isEmpty());
    }

    // ── What you see ─────────────────────────────────────────────────────────

    public function test_the_journal_page_shows_each_entry_with_its_debit_and_credit_and_a_reverse_option(): void
    {
        $a = $this->confirmed();

        $response = $this->actingAs($this->admin)->get(route('admin.founding-twenty.deposits'));

        $response->assertOk();
        $response->assertSee('Deposit received');
        $response->assertSee('Deposits held (owed back)');
        $response->assertSee($a->business_name);
        $response->assertSee('Reverse entry');
        $response->assertSee('The journal agrees with every application');
    }

    public function test_the_journal_page_shows_reversals_and_greys_the_entry_they_undo(): void
    {
        $a = $this->confirmed();
        $this->actingAs($this->admin)->post(route('admin.founding-twenty.deposit.refund', $a));
        $refund = $a->depositEntries()->where('type', 'refunded')->sole();
        $this->actingAs($this->admin)->post(route('admin.founding-twenty.deposits.reverse', $refund), ['reason' => 'Wrong account']);

        $response = $this->actingAs($this->admin)->get(route('admin.founding-twenty.deposits'));

        $response->assertSee('Reversal of #' . $refund->id);
        $response->assertSee('reversed by #' . $refund->fresh()->reversal->id);
        $response->assertSee('Wrong account');
    }

    public function test_the_journal_page_warns_when_the_books_disagree(): void
    {
        $sneaky = $this->awaitingConfirmation();
        $sneaky->update(['deposit_confirmed_at' => now()]);

        $this->actingAs($this->admin)->get(route('admin.founding-twenty.deposits'))
            ->assertSee('The journal and these applications disagree')
            ->assertSee($sneaky->business_name);
    }

    public function test_the_csv_is_the_journal_with_both_accounts_and_the_reason_for_reversals(): void
    {
        $a = $this->confirmed();
        $this->actingAs($this->admin)->post(route('admin.founding-twenty.deposit.refund', $a), ['deposit_refund_reference' => 'EFT-1']);
        $refund = $a->depositEntries()->where('type', 'refunded')->sole();
        $this->actingAs($this->admin)->post(route('admin.founding-twenty.deposits.reverse', $refund), ['reason' => 'Wrong account']);

        $response = $this->actingAs($this->admin)->get(route('admin.founding-twenty.deposits', ['format' => 'csv']));

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Entry,Date,Type,Reference,Business,Debit,Credit,Amount,Invoice,"Reverses entry",Reason,"Recorded by"', $csv);
        $this->assertStringContainsString(',received,', $csv);
        $this->assertStringContainsString(',refunded,', $csv);
        $this->assertStringContainsString('EFT-1', $csv);
        $this->assertStringContainsString(',reversal,', $csv);
        $this->assertStringContainsString('"Wrong account"', str_replace('Wrong account', '"Wrong account"', $csv));
        $this->assertSame(3, substr_count($csv, "\n") - 1);
    }

    public function test_the_journal_page_is_empty_safe(): void
    {
        $this->actingAs($this->admin)->get(route('admin.founding-twenty.deposits'))->assertOk()->assertSee('No deposits confirmed yet.');
    }

    // ── Existing deposits carried into the journal ───────────────────────────

    public function test_deposits_confirmed_before_the_journal_existed_are_carried_over_by_the_migration(): void
    {
        $migration = require database_path('migrations/2026_09_27_100000_create_founding_twenty_deposit_entries_table.php');
        \Illuminate\Support\Facades\Schema::drop('founding_twenty_deposit_entries');
        $held = $this->awaitingConfirmation(['deposit_confirmed_at' => now()->subDays(10)]);
        $paidBack = $this->awaitingConfirmation(['deposit_confirmed_at' => now()->subDays(20), 'deposit_refunded_at' => now()->subDays(2)]);
        $unconfirmed = $this->awaitingConfirmation();

        $migration->up();

        $this->assertSame(['received'], FoundingTwentyDepositEntry::where('founding_twenty_application_id', $held->id)->pluck('type')->all());
        $this->assertSame(['received', 'refunded'], FoundingTwentyDepositEntry::where('founding_twenty_application_id', $paidBack->id)->orderBy('id')->pluck('type')->all());
        $this->assertSame(0, FoundingTwentyDepositEntry::where('founding_twenty_application_id', $unconfirmed->id)->count());
        $this->assertSame(100.0, $this->totals()['held']);
        $this->assertTrue(app(FoundingTwentyDepositLedger::class)->discrepancies()->isEmpty());
    }

    // ── The dashboard reads the same numbers ─────────────────────────────────

    public function test_dashboard_numbers_come_from_the_journal_and_match_it(): void
    {
        $held = $this->confirmed();
        $paidBack = $this->confirmed();
        $this->actingAs($this->admin)->post(route('admin.founding-twenty.deposit.refund', $paidBack));
        [$tenant, $invoice] = $this->tenantWithInvoice(200);
        $credited = $this->confirmed(['tenant_id' => $tenant->id]);
        $this->actingAs($this->admin)->post(route('admin.founding-twenty.deposit.credit', $credited), ['invoice_id' => $invoice->id]);
        FoundingTwentyApplication::whereKey($credited->id)->update(['status' => 'converted', 'tenant_linked_at' => now()->subDays(100), 'first_value_milestone_at' => now()->subDays(90)]);

        $stats = app(FoundingTwentyProgrammeStats::class)->dashboard();

        $this->assertSame(3, $stats['applications']);
        $this->assertSame(3, $stats['selected']);
        $this->assertSame(1, $stats['paying']);
        $this->assertSame(1, $stats['activated']);
        $this->assertSame(200.0, $stats['committedMonthly']);
        $this->assertSame($this->totals(), $stats['deposits']);
        $this->assertSame(['received' => 300.0, 'refunded' => 100.0, 'credited' => 100.0, 'held' => 100.0], $stats['deposits']);
        $this->assertSame(0, $stats['discrepancies']);
    }

    public function test_the_dashboard_panel_shows_those_numbers_and_flags_a_disagreement(): void
    {
        $this->confirmed();
        $stats = app(FoundingTwentyProgrammeStats::class)->dashboard();

        $html = view('admin.founding-twenty._dashboard-panel', ['founding20' => $stats])->render();

        $this->assertStringContainsString('Deposits held', $html);
        $this->assertStringContainsString('R100', $html);
        $this->assertStringContainsString('R100 received', $html);
        $this->assertStringNotContainsString('disagree with the journal', $html);

        $sneaky = $this->awaitingConfirmation();
        $sneaky->update(['deposit_confirmed_at' => now()]);
        $html = view('admin.founding-twenty._dashboard-panel', ['founding20' => app(FoundingTwentyProgrammeStats::class)->dashboard()])->render();
        $this->assertStringContainsString('1 deposit(s) disagree with the journal', $html);
    }

    public function test_the_action_count_on_the_dashboard_matches_the_action_queue(): void
    {
        $this->awaitingConfirmation(['status' => 'rejected', 'reviewed_at' => now()]); // decided, not told
        $this->awaitingConfirmation(['status' => 'pending', 'submitted_at' => now()->subDays(10), 'deposit_amount' => null, 'deposit_submitted_at' => null]); // overdue + unacknowledged

        $stats = app(FoundingTwentyProgrammeStats::class);
        $queue = $stats->queue();

        $this->assertSame(1, $queue['toTellDecision']->count());
        $this->assertSame(1, $queue['overduePromise']->count());
        $this->assertSame($stats->actionCount($queue), $stats->dashboard()['actionCount']);
        $this->assertGreaterThanOrEqual(3, $stats->actionCount());
    }

    public function test_settled_by_choice_deposits_wait_in_the_queue_until_done(): void
    {
        $a = $this->confirmed();
        $this->actingAs($this->admin)->post(route('admin.founding-twenty.deposit.outcome', $a), ['deposit_outcome' => 'refund']);

        $this->actingAs($this->admin)->get(route('admin.founding-twenty.action-queue'))
            ->assertSee('Deposits to pay back or credit (1)')
            ->assertSee('wants it paid back');

        $this->actingAs($this->admin)->post(route('admin.founding-twenty.deposit.refund', $a));

        $this->actingAs($this->admin)->get(route('admin.founding-twenty.action-queue'))->assertSee('Deposits to pay back or credit (0)');
    }

    public function test_the_purge_never_deletes_a_business_with_a_deposit_on_the_books(): void
    {
        $a = $this->confirmed(['status' => 'rejected']);
        $a->forceFill(['created_at' => now()->subMonths(20)])->saveQuietly();

        $this->artisan('founding-twenty:purge-stale', ['--force' => true])->assertSuccessful();

        $this->assertNotNull(FoundingTwentyApplication::find($a->id));
    }
}
