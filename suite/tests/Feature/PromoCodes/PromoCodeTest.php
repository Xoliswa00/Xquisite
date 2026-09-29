<?php

namespace Tests\Feature\PromoCodes;

use App\Models\PromoCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoCodeTest extends TestCase
{
    use RefreshDatabase;

    private function code(array $overrides = []): PromoCode
    {
        return PromoCode::create(array_merge([
            'code' => 'TEST' . uniqid(),
            'type' => 'free_months',
            'value' => 3,
            'is_active' => true,
        ], $overrides));
    }

    public function test_code_under_its_cap_is_redeemable_and_not_flagged(): void
    {
        $code = $this->code(['max_redemptions' => 20, 'times_redeemed' => 5]);

        $this->assertTrue($code->isRedeemable());
        $this->assertFalse($code->isExhausted());
    }

    public function test_code_past_its_cap_is_still_redeemable_but_flagged(): void
    {
        // The cap is a soft limit for visibility (e.g. "Founding 20"), not a hard
        // stop — regression guard for that explicit product decision (2026-09-05).
        $code = $this->code(['max_redemptions' => 20, 'times_redeemed' => 24]);

        $this->assertTrue($code->isExhausted());
        $this->assertTrue($code->isRedeemable(), 'A code past its redemption cap must still be redeemable — the cap is a highlight, not a block.');
    }

    public function test_deactivated_code_is_not_redeemable_even_under_cap(): void
    {
        $code = $this->code(['max_redemptions' => 20, 'times_redeemed' => 1, 'is_active' => false]);

        $this->assertFalse($code->isRedeemable());
    }

    public function test_expired_code_is_not_redeemable_even_under_cap(): void
    {
        $code = $this->code(['max_redemptions' => 20, 'times_redeemed' => 1, 'expires_at' => now()->subDay()]);

        $this->assertFalse($code->isRedeemable());
    }

    public function test_code_with_no_max_redemptions_is_never_exhausted(): void
    {
        $code = $this->code(['max_redemptions' => null, 'times_redeemed' => 500]);

        $this->assertFalse($code->isExhausted());
        $this->assertTrue($code->isRedeemable());
    }
}
