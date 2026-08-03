<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_borrower_account_registration_is_disabled(): void
    {
        $this->get('/register')->assertNotFound();

        $this->post('/register', [
            'email' => 'guest@example.com',
        ])->assertNotFound();
    }

    public function test_guest_borrowing_form_is_publicly_available(): void
    {
        $this->get('/guest-borrow')
            ->assertOk()
            ->assertSee('Guest Borrowing Request')
            ->assertSee('No account required');
    }
}
