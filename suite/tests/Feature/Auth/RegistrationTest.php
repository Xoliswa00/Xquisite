<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        // Registration on this app creates a tenant business, not a bare user —
        // business_name is required (see RegisteredUserController::store()).
        // This test predates that and was only ever exercising the validation
        // failure path silently, since a stale Breeze scaffold test doesn't
        // assert on validation errors.
        $response = $this->post('/register', [
            'business_name' => 'Test Salon',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }
}
