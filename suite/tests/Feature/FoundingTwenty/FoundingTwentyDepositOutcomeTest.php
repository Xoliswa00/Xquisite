<?php

namespace Tests\Feature\FoundingTwenty;

use App\Models\FoundingTwentyApplication;
use App\Models\PlatformInvoice;
use App\Models\Tenant;
use App\Models\User;
use App\Services\FoundingTwentyMessages;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * The R100 deposit comes back either way. The business chooses: paid back to their
 * bank account, or credited to their account. Either way it is recorded for the books.
 */
class FoundingTwentyDepositOutcomeTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(): Tenant
    {
        return Tenant::create(['name' => 'Thandi Hair', 'slug' => 'thandi-' . uniqid(), 'email' => 't' . uniqid() . '@example.com', 'is_active' => true]);
    }

    private function application(array $overrides = []): FoundingTwentyApplication
    {
        static $n = 0;
        $n++;

        return FoundingTwentyApplication::create(array_merge([
            'owner_name' => 'Thandi Mokoena', 'business_name' => 'Thandi Hair Studio', 'applicant_role' => 'owner',
            'phone' => '08312345' . str_pad((string) $n, 2, '0', STR_PAD_LEFT), 'preferred_contact_method' => 'whatsapp',
            'business_type' => 'salon', 'submitted_at' => now(), 'status' => 'selected',
            'deposit_amount' => 100, 'deposit_reference' => 'F20-' . str_pad((string) $n, 4, '0', STR_PAD_LEFT),
            'deposit_submitted_at' => now()->subDays(80), 'deposit_confirmed_at' => now()->subDays(80),
        ], $overrides));
    }

    private function invoice(Tenant $tenant, float $amount = 200, string $status = 'unpaid'): PlatformInvoice
    {
        static $i = 0;
        $i++;

        return PlatformInvoice::create([
            'tenant_id' => $tenant->id, 'invoice_number' => 'INV-T-' . $i, 'plan' => 'founding',
            'amount' => $amount, 'status' => $status, 'due_date' => now()->addDays(7),
            'billing_period_start' => now(), 'billing_period_end' => now()->addMonth(),
        ]);
    }

    private function admin(): User
    {
        Permission::findOrCreate('manage-tenants', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo('manage-tenants');

        return $user;
    }

    // ── What we promise ──────────────────────────────────────────────────────

    public function test_the_conversion_message_locks_the_price_for_24_months_and_offers_refund_or_credit(): void
    {
        $body = FoundingTwentyMessages::conversion($this->application())['body'];

        $this->assertStringContainsString('R200 a month, and that price is locked for 24 months from the day your free period ends', $body);
        $this->assertStringContainsString('"refund" and we pay it back to your bank account', $body);
        $this->assertStringContainsString('"credit" and we take it off your first invoice', $body);
        $this->assertStringContainsString('If you stop, we pay it back', $body);
        $this->assertStringContainsString('We take care of the accounting', $body);
    }

    public function test_the_selected_message_explains_the_deposit_is_not_a_fee(): void
    {
        $body = FoundingTwentyMessages::decision($this->application())['body'];

        $this->assertStringContainsString('The deposit is not a fee', $body);
        $this->assertStringContainsString('paid back to you or credited to your account', $body);
    }

    public function test_the_deposit_page_tells_them_they_can_choose(): void
    {
        $a = $this->application(['deposit_confirmed_at' => null, 'deposit_submitted_at' => null]);

        $this->get(route('founding-twenty.reserve', [$a, $a->reservationToken()]))
            ->assertOk()
            ->assertSee('paid back to you or credited to your account');
    }

    public function test_price_lock_runs_24_months_after_the_free_period_ends(): void
    {
        $a = $this->application(['tenant_linked_at' => now()->setDate(2026, 10, 1)->startOfDay()]);

        $this->assertSame('2029-01-01', $a->priceLockedUntil()->toDateString());
        $this->assertNull($this->application()->priceLockedUntil());
    }

    // ── Recording their choice and paying back ───────────────────────────────

    public function test_their_choice_is_recorded_before_anything_is_settled(): void
    {
        $a = $this->application();

        $this->actingAs($this->admin())->post(route('admin.founding-twenty.deposit.outcome', $a), ['deposit_outcome' => 'credit'])
            ->assertSessionHas('success');

        $this->assertSame('credit', $a->fresh()->deposit_outcome);
        $this->assertFalse($a->fresh()->isDepositSettled());
    }

    public function test_only_refund_or_credit_are_valid_choices(): void
    {
        $a = $this->application();

        $this->actingAs($this->admin())->post(route('admin.founding-twenty.deposit.outcome', $a), ['deposit_outcome' => 'keep'])
            ->assertSessionHasErrors('deposit_outcome');
    }

    public function test_paying_the_deposit_back_records_the_outcome_and_the_eft_reference(): void
    {
        $a = $this->application();

        $this->actingAs($this->admin())->post(route('admin.founding-twenty.deposit.refund', $a), ['deposit_refund_reference' => 'EFT-8841']);

        $a->refresh();
        $this->assertNotNull($a->deposit_refunded_at);
        $this->assertSame('refund', $a->deposit_outcome);
        $this->assertSame('EFT-8841', $a->deposit_refund_reference);
        $this->assertTrue($a->isDepositSettled());
    }

    public function test_a_deposit_that_was_never_confirmed_cannot_be_settled(): void
    {
        $a = $this->application(['deposit_confirmed_at' => null]);
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.founding-twenty.deposit.refund', $a))->assertStatus(422);
        $this->actingAs($admin)->post(route('admin.founding-twenty.deposit.outcome', $a), ['deposit_outcome' => 'refund'])->assertStatus(422);
    }

    public function test_a_deposit_cannot_be_settled_twice(): void
    {
        $tenant = $this->tenant();
        $a = $this->application(['tenant_id' => $tenant->id, 'deposit_refunded_at' => now()]);
        $invoice = $this->invoice($tenant);
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.founding-twenty.deposit.refund', $a))->assertStatus(422);
        $this->actingAs($admin)->post(route('admin.founding-twenty.deposit.credit', $a), ['invoice_id' => $invoice->id])->assertStatus(422);
        $this->assertSame('200.00', $invoice->fresh()->amount);
    }

    // ── Crediting to their account ───────────────────────────────────────────

    public function test_crediting_takes_the_deposit_off_the_invoice_and_leaves_a_note_for_the_books(): void
    {
        $tenant = $this->tenant();
        $a = $this->application(['tenant_id' => $tenant->id, 'deposit_reference' => 'F20-0007']);
        $invoice = $this->invoice($tenant, 200);

        $this->actingAs($this->admin())->post(route('admin.founding-twenty.deposit.credit', $a), ['invoice_id' => $invoice->id])
            ->assertSessionHas('success');

        $invoice->refresh();
        $a->refresh();
        $this->assertSame('100.00', $invoice->amount);
        $this->assertStringContainsString('Founding 20 deposit credit of R100.00 applied (ref F20-0007)', $invoice->notes);
        $this->assertStringContainsString('Invoiced amount was R200.00', $invoice->notes);
        $this->assertSame('credit', $a->deposit_outcome);
        $this->assertNotNull($a->deposit_credited_at);
        $this->assertSame($invoice->id, $a->deposit_credit_invoice_id);
        $this->assertTrue($a->isDepositSettled());
    }

    public function test_credit_cannot_go_on_another_businesss_invoice_or_a_paid_one(): void
    {
        $tenant = $this->tenant();
        $other = $this->tenant();
        $a = $this->application(['tenant_id' => $tenant->id]);
        $admin = $this->admin();

        $othersInvoice = $this->invoice($other, 200);
        $paidInvoice = $this->invoice($tenant, 200, 'paid');

        foreach ([$othersInvoice, $paidInvoice] as $invoice) {
            $this->actingAs($admin)->post(route('admin.founding-twenty.deposit.credit', $a), ['invoice_id' => $invoice->id])
                ->assertSessionHas('error');
            $this->assertSame('200.00', $invoice->fresh()->amount);
        }
        $this->assertFalse($a->fresh()->isDepositSettled());
    }

    public function test_credit_is_refused_when_the_invoice_is_not_larger_than_the_credit(): void
    {
        $tenant = $this->tenant();
        $a = $this->application(['tenant_id' => $tenant->id]);
        $small = $this->invoice($tenant, 100);

        $this->actingAs($this->admin())->post(route('admin.founding-twenty.deposit.credit', $a), ['invoice_id' => $small->id])
            ->assertSessionHas('error');

        $this->assertSame('100.00', $small->fresh()->amount);
        $this->assertFalse($a->fresh()->isDepositSettled());
    }

    public function test_credit_needs_a_linked_tenant(): void
    {
        $a = $this->application(['tenant_id' => null]);

        $this->actingAs($this->admin())->post(route('admin.founding-twenty.deposit.credit', $a), ['invoice_id' => 1])->assertStatus(422);
    }

    // ── The application page ─────────────────────────────────────────────────

    public function test_the_application_page_shows_the_settle_options_and_the_price_lock(): void
    {
        $tenant = $this->tenant();
        $a = $this->application(['tenant_id' => $tenant->id, 'tenant_linked_at' => now()->subDays(80)]);
        $invoice = $this->invoice($tenant);

        $response = $this->actingAs($this->admin())->get(route('admin.founding-twenty.show', $a));

        $response->assertOk();
        $response->assertSee('Settle the deposit');
        $response->assertSee('Credit to account');
        $response->assertSee('Mark paid back');
        $response->assertSee($invoice->invoice_number);
        $response->assertSee('Monthly price locked until');
    }

    public function test_a_settled_deposit_hides_the_settle_forms_and_says_how_it_was_settled(): void
    {
        $tenant = $this->tenant();
        $invoice = $this->invoice($tenant, 100);
        $a = $this->application(['tenant_id' => $tenant->id, 'deposit_credited_at' => now(), 'deposit_outcome' => 'credit', 'deposit_credit_invoice_id' => $invoice->id]);

        $response = $this->actingAs($this->admin())->get(route('admin.founding-twenty.show', $a));

        $response->assertDontSee('Settle the deposit');
        $response->assertSee('Credited to invoice ' . $invoice->invoice_number);
    }

    // ── The books ────────────────────────────────────────────────────────────

    public function test_the_ledger_reconciles_received_paid_back_credited_and_still_held(): void
    {
        $tenant = $this->tenant();
        $invoice = $this->invoice($tenant, 100);
        $this->application(['business_name' => 'Held Salon']);
        $this->application(['business_name' => 'Paid Back Salon', 'deposit_refunded_at' => now(), 'deposit_outcome' => 'refund']);
        $this->application(['business_name' => 'Credited Salon', 'deposit_credited_at' => now(), 'deposit_outcome' => 'credit', 'deposit_credit_invoice_id' => $invoice->id]);
        $this->application(['business_name' => 'Never Paid', 'deposit_confirmed_at' => null]);

        $response = $this->actingAs($this->admin())->get(route('admin.founding-twenty.deposits'));

        $response->assertOk();
        $this->assertSame(['received' => 300.0, 'refunded' => 100.0, 'credited' => 100.0, 'held' => 100.0], $response->viewData('totals'));
        $response->assertSee('Held Salon');
        $response->assertSee('Credited ' . now()->format('j M Y') . ' to ' . $invoice->invoice_number);
        $response->assertDontSee('Never Paid');
    }

    public function test_the_ledger_downloads_as_csv_with_the_references_an_accountant_needs(): void
    {
        $tenant = $this->tenant();
        $invoice = $this->invoice($tenant, 100);
        $this->application(['business_name' => 'Paid Back Salon', 'deposit_refunded_at' => now(), 'deposit_outcome' => 'refund', 'deposit_refund_reference' => 'EFT-1', 'deposit_reference' => 'F20-0101']);
        $this->application(['business_name' => 'Credited Salon', 'deposit_credited_at' => now(), 'deposit_outcome' => 'credit', 'deposit_credit_invoice_id' => $invoice->id, 'deposit_reference' => 'F20-0102']);

        $response = $this->actingAs($this->admin())->get(route('admin.founding-twenty.deposits', ['format' => 'csv']));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $csv = $response->streamedContent();
        $this->assertStringContainsString('Reference,Business,Amount,Received,Outcome,Settled,"Refund reference","Credited to invoice"', $csv);
        $this->assertStringContainsString('F20-0101,"Paid Back Salon",100.00,', $csv);
        $this->assertStringContainsString('EFT-1', $csv);
        $this->assertStringContainsString($invoice->invoice_number, $csv);
    }

    public function test_the_ledger_is_empty_safe(): void
    {
        $this->actingAs($this->admin())->get(route('admin.founding-twenty.deposits'))->assertOk()->assertSee('No deposits confirmed yet.');
    }
}
