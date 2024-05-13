<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test.user@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '+1234567'
        ]);

        $this->assertAuthenticated();
        $response->assertNoContent();
    }
}
