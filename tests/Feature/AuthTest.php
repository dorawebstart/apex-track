<?php

namespace Tests\Feature;

use App\Models\TrackSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Реєстрація, вхід, вихід і захист сторінок від гостей.
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_user_with_role_user_even_if_role_is_sent(): void
    {
        $this->post(route('register'), [
            'name' => 'Новий Пілот',
            'email' => 'pilot@example.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'admin',
        ])->assertRedirect(route('cabinet'));

        $user = User::where('email', 'pilot@example.test')->firstOrFail();
        $this->assertSame('user', $user->role);
        $this->assertNotSame('secret123', $user->password);
        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_validates_input(): void
    {
        $this->post(route('register'), ['name' => '', 'email' => 'not-an-email', 'password' => '1', 'password_confirmation' => '2'])
            ->assertSessionHasErrors(['name', 'email', 'password']);

        $this->assertGuest();
    }

    public function test_user_can_log_in_and_out(): void
    {
        $user = User::factory()->create(['password' => 'password']);

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('cabinet'));
        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create(['password' => 'password']);

        $this->post(route('login'), ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_login_from_protected_pages(): void
    {
        $session = TrackSession::factory()->create();

        $this->get(route('cabinet'))->assertRedirect(route('login'));
        $this->post(route('bookings.store', $session))->assertRedirect(route('login'));
    }

    public function test_guest_book_button_returns_to_session_after_login(): void
    {
        $session = TrackSession::factory()->create();
        $user = User::factory()->create(['password' => 'password']);

        $this->get(route('bookings.start', $session))->assertRedirect(route('login'));

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('bookings.start', $session));
    }
}
