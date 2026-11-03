<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Create only the users table needed by these authentication tests.
     *
     * This deliberately avoids running the application's full migration
     * history, which currently contains a duplicate residency_type column.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('username', 50)->nullable()->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->nullable();
            $table->string('status')->nullable();
            $table->boolean('document_processor')->default(false);
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->string('google_id')->nullable()->unique();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Guests should be redirected to the login page.
     */
    public function test_the_application_redirects_guests_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /**
     * Invalid credentials must not authenticate a user.
     */
    public function test_invalid_credentials_do_not_authenticate_a_user(): void
    {
        $response = $this
            ->from(route('login'))
            ->post(route('login.authenticate'), [
                'username' => 'nonexistent-security-test-user',
                'password' => 'Incorrect-Test-Password-123!',
                'role' => 'secretary',
            ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors();
        $this->assertGuest();
    }
}
