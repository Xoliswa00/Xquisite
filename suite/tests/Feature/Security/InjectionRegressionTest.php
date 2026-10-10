<?php

namespace Tests\Feature\Security;

use App\Models\FoundingTwentyApplication;
use App\Models\Promotion;
use App\Models\ServiceCategory;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Booking\Models\Customer;
use App\Modules\Booking\Models\Service;
use App\Notifications\NewTenantRegistered;
use App\Services\Tenant\TenantContext;
use App\Support\Csv;
use App\Support\MailText;
use Carbon\Carbon;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * One test per injection hole found in the 2026-10-09 security audit. Each
 * sends the payload that used to work and asserts it no longer does.
 *
 * The JS payload is `'-alert(1)-'`: inside '{{ }}' it used to end the string
 * and run. Rendered through @js() it arrives as an escaped JS string instead, so the
 * tell-tale `'&#039;` (a quote, then an HTML-encoded quote) never appears.
 */
class InjectionRegressionTest extends TestCase
{
    use RefreshDatabase;

    private const JS_PAYLOAD = "'-alert(1)-'";

    /** What the payload looked like on the page while the hole was open. */
    private const BROKEN_OUT = "'&#039;-alert(1)-&#039;'";

    /** The same payload through @js(): the quotes are unicode escapes inside one JS string. */
    private const SAFE_COPY = "writeText('\\u0027-alert(1)-\\u0027')";

    private Tenant $tenant;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionRoleSeeder::class);

        $this->tenant = Tenant::create(['name' => 'Secure Salon', 'slug' => 'secure-salon', 'email' => 's@example.com', 'is_active' => true]);
        $this->tenant->activateModule('booking');
        $this->owner = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->owner->assignRole('tenant-owner');
    }

    private function asOwner(): static
    {
        TenantContext::clear();

        return $this->actingAs($this->owner);
    }

    // ── Reflected XSS: /services?tab= ───────────────────────────────────────

    public function test_services_tab_from_the_url_cannot_inject_script(): void
    {
        $response = $this->asOwner()->get('/services?tab=' . urlencode(self::JS_PAYLOAD));

        $response->assertOk();
        $response->assertDontSee('alert(1)', false);          // unknown tab is dropped entirely
        $response->assertSee("tab: 'services'", false);         // and the page falls back to the default tab
    }

    public function test_a_real_tab_still_opens(): void
    {
        $this->asOwner()->get('/services?tab=combos')->assertOk()->assertSee('combos', false);
    }

    // ── Stored XSS: promotion code on the public booking page ───────────────

    public function test_a_promotion_code_must_be_plain_letters_and_digits(): void
    {
        $this->asOwner()->post(route('promotions.store'), [
            'name' => 'Spring', 'code' => self::JS_PAYLOAD, 'discount_type' => 'percentage',
            'discount_value' => 10, 'applies_to' => 'all', 'is_active' => 1,
        ])->assertSessionHasErrors('code');

        $this->assertSame(0, Promotion::withoutGlobalScopes()->count());
    }

    public function test_a_legacy_promotion_code_is_rendered_safely_on_the_public_booking_page(): void
    {
        // Rows saved before the validation rule existed must still be harmless.
        Promotion::create([
            'tenant_id' => $this->tenant->id, 'name' => 'Old promo', 'code' => self::JS_PAYLOAD,
            'discount_type' => 'percentage', 'discount_value' => 10, 'applies_to' => 'all', 'is_active' => true,
        ]);

        $response = $this->get(route('book.index', $this->tenant->slug));

        $response->assertOk();
        $response->assertDontSee('writeText(' . self::BROKEN_OUT, false);
        $response->assertSee(self::SAFE_COPY, false);
    }

    // ── Stored XSS: bank account number on the booking confirm page ─────────

    public function test_a_bank_account_number_must_be_digits(): void
    {
        $this->asOwner()->patch(route('profile.business.update'), [
            'business_name' => 'Secure Salon', 'slug' => 'secure-salon', 'email' => 's@example.com',
            'bank_name' => 'ABSA', 'bank_account_number' => self::JS_PAYLOAD,
        ])->assertSessionHasErrors('bank_account_number');

        $this->assertNull($this->tenant->fresh()->bank_account_number);
    }

    public function test_a_legacy_bank_account_number_is_rendered_safely_on_the_confirm_page(): void
    {
        $this->tenant->forceFill(['bank_name' => 'ABSA', 'bank_account_number' => self::JS_PAYLOAD])->save();
        TenantContext::set($this->tenant->id);
        // A multi-day duration skips the slot-availability check.
        $service = Service::create([
            'tenant_id' => $this->tenant->id, 'name' => 'Retreat', 'duration_minutes' => 1440,
            'price' => 500, 'pricing_type' => 'flat', 'is_active' => true,
        ]);
        $customer = Customer::create(['tenant_id' => $this->tenant->id, 'name' => 'Jane', 'email' => 'jane@example.com', 'is_active' => true]);

        $response = $this->actingAs($customer, 'customer')->get(route('book.confirm', [
            'slug' => $this->tenant->slug, 'service_ids' => [$service->id],
            'scheduled_at' => Carbon::tomorrow()->format('Y-m-d H:i'),
        ]));

        $response->assertOk();
        $response->assertDontSee('writeText(' . self::BROKEN_OUT, false);
        $response->assertSee(self::SAFE_COPY, false);
    }

    // ── Stored XSS: service category icon ───────────────────────────────────

    public function test_a_category_icon_with_a_quote_cannot_break_out_of_the_edit_form(): void
    {
        $category = ServiceCategory::create([
            'tenant_id' => $this->tenant->id, 'name' => 'Nails', 'icon' => "'-alert()-'", 'color' => 'indigo', 'is_active' => true,
        ]);

        $response = $this->asOwner()->get(route('service-categories.edit', $category));

        $response->assertOk();
        $response->assertDontSee("icon: '&#039;", false);
        $response->assertSee("icon: '\\u0027-alert()-\\u0027'", false);
    }

    // ── CSV formula injection: Founding 20 deposit journal ──────────────────

    public function test_a_business_name_cannot_inject_a_formula_into_the_deposit_csv(): void
    {
        Permission::findOrCreate('manage-tenants', 'web');
        $admin = User::factory()->create();
        $admin->givePermissionTo('manage-tenants');

        $application = FoundingTwentyApplication::create([
            'owner_name' => 'Mallory', 'business_name' => '=HYPERLINK("https://evil.example","Click")',
            'applicant_role' => 'owner', 'phone' => '0841234501', 'preferred_contact_method' => 'whatsapp',
            'business_type' => 'salon', 'submitted_at' => now(), 'status' => 'selected',
            'deposit_amount' => 100, 'deposit_reference' => 'F20-0001', 'deposit_submitted_at' => now(),
        ]);
        $this->actingAs($admin)->post(route('admin.founding-twenty.deposit.confirm', $application));

        $csv = $this->actingAs($admin)->get(route('admin.founding-twenty.deposits', ['format' => 'csv']))
            ->assertOk()->streamedContent();

        $rows = array_map(fn ($line) => str_getcsv($line, ',', '"', ''), array_filter(explode("\n", trim($csv))));
        $this->assertGreaterThan(1, count($rows), 'the journal has the confirmed deposit');

        foreach (array_slice($rows, 1) as $row) {
            foreach ($row as $cell) {
                $this->assertDoesNotMatchRegularExpression('/^[=+@]/', (string) $cell, "cell would run as a formula: {$cell}");
            }
        }
        $this->assertStringContainsString("'=HYPERLINK", $csv);
    }

    public function test_the_csv_guard_neutralises_every_formula_trigger_but_leaves_numbers_alone(): void
    {
        foreach (['=1+1', '+27821234567x', '-cmd', '@SUM(A1)', "\tx", "\rx"] as $value) {
            $this->assertSame("'" . $value, Csv::cell($value));
        }

        $this->assertSame('Thandi', Csv::cell('Thandi'));
        $this->assertSame('-100.50', Csv::cell('-100.50'), 'a negative amount stays a number');
        $this->assertSame(42, Csv::cell(42));
        $this->assertNull(Csv::cell(null));
    }

    // ── Markdown injection: admin notification email ────────────────────────

    public function test_a_signup_name_cannot_put_a_live_link_or_image_in_the_admin_email(): void
    {
        $owner = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name'      => '[Approve this signup](https://evil.example/login) ![](https://evil.example/pixel.png)',
        ]);
        $this->tenant->forceFill(['name' => '[Click here](https://evil.example/phish)'])->save();

        // Compile the mail template the way `php artisan view:cache` does at deploy.
        // Markdown::withSecuredEncoding() is skipped when a compiled copy already
        // exists, so the protection has to hold in this state too.
        $template = app('view')->getFinder()->find('notifications::email');
        app('blade.compiler')->compile($template);

        $html = (string) (new NewTenantRegistered($this->tenant->fresh(), $owner))
            ->toMail(User::factory()->make())
            ->render();

        $this->assertStringNotContainsString('href="https://evil.example', $html);
        $this->assertStringNotContainsString('src="https://evil.example', $html);
        // The app's own formatting in that same line still renders.
        $this->assertStringContainsString('<strong', $html);
    }

    public function test_every_notification_email_is_protected_even_when_views_were_pre_compiled(): void
    {
        $compiler = app('blade.compiler');
        $template = app('view')->getFinder()->find('notifications::email');
        $normalCompiledPath = $compiler->getCompiledPath($template);

        // The state after `php artisan view:cache` / `optimize` at deploy.
        $compiler->compile($template);

        // A line with user text that nobody wrapped in MailText::plain(), as in
        // most of the app's notifications.
        $html = (string) (new \Illuminate\Notifications\Messages\MailMessage)
            ->line('[Pay your invoice](https://evil.example/pay) uploaded proof of payment for **INV-001**.')
            ->render();

        $this->assertInstanceOf(\App\Support\SecureMarkdown::class, app(\Illuminate\Mail\Markdown::class));
        $this->assertStringNotContainsString('href="https://evil.example', $html);
        $this->assertStringContainsString('<strong', $html, 'the app\'s own bold still renders');
        // The normal view cache is back in place for the rest of the request.
        $this->assertSame($normalCompiledPath, $compiler->getCompiledPath($template));
    }

    public function test_mail_text_escapes_markdown_but_leaves_ordinary_names_readable(): void
    {
        $this->assertSame('Thandi', MailText::plain('Thandi'));
        $this->assertSame("O'Brien", MailText::plain("O'Brien"));
        $this->assertSame('\[x\]\(y\)', MailText::plain('[x](y)'));
        $this->assertSame('\!\[x\]\(y\)', MailText::plain('![x](y)'));
        $this->assertSame('', MailText::plain(null));
    }
}
