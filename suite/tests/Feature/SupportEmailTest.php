<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportEmailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * One support address, from one config key. A hardcoded address in a view
     * is how four different ones ended up in front of users.
     */
    public function test_views_do_not_hardcode_a_support_address(): void
    {
        $offenders = [];

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views'))) as $file) {
            if ($file->isFile() && preg_match('/support@[a-z0-9.-]+/i', file_get_contents($file->getPathname()), $m)) {
                $offenders[] = str_replace(resource_path('views') . DIRECTORY_SEPARATOR, '', $file->getPathname()) . " ({$m[0]})";
            }
        }

        $this->assertSame([], $offenders, "Use config('contact.support_email') instead of a hardcoded support address.");
    }

    public function test_the_support_address_does_not_follow_the_send_from_address(): void
    {
        $this->assertStringNotContainsString("env('MAIL_FROM_ADDRESS'", file_get_contents(config_path('contact.php')));
    }

    public function test_the_terms_page_shows_the_configured_support_address(): void
    {
        config(['contact.support_email' => 'help@example.test', 'mail.from.address' => 'no-reply@example.test']);

        $this->get('/terms')
            ->assertOk()
            ->assertSee('mailto:help@example.test', false)
            ->assertDontSee('no-reply@example.test');
    }
}
