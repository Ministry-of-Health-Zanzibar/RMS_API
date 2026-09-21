<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class LoginTest extends TestCase
{
    public function test_login_validation_errors_return_unprocessable_entity(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'not-an-email',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonStructure(['email', 'password']);
    }

    public function test_login_is_rate_limited_after_five_attempts(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->postJson('/api/login', [
                'email' => 'rate-limit-test@example.com',
            ])->assertUnprocessable();
        }

        $this->postJson('/api/login', [
            'email' => 'rate-limit-test@example.com',
        ])->assertTooManyRequests();
    }
}
