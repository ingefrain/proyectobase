<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\InteractsWithBaseAdmin;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use InteractsWithBaseAdmin;
    use RefreshDatabase;

    public function test_guest_can_open_login_and_password_recovery(): void
    {
        $response = $this->get(route('admin.login'));
        $response->assertOk()->assertSee('Iniciar sesión');
        $this->get(route('admin.password.request'))
            ->assertOk()
            ->assertSee('Restablecer contraseña')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
        $this->get('/')->assertRedirect(route('admin.login'));
    }

    public function test_password_reset_form_is_not_cached_or_indexed(): void
    {
        $user = $this->adminUser();
        $response = $this->get(route('admin.password.reset', [
            'token' => 'sample-token',
            'email' => $user->email,
        ]));

        $response->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->assertSee('name="password_confirmation"', false);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_active_user_can_login_with_remember_and_session_version(): void
    {
        $user = $this->adminUser(['password' => 'Una-clave-segura-123!']);

        $this->post(route('admin.login.store'), [
            'email' => strtoupper($user->email),
            'password' => 'Una-clave-segura-123!',
            'remember' => '1',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotEmpty($user->fresh()->remember_token);
        $this->assertSame($user->session_version, session('auth_session_version'));
    }

    public function test_authenticated_user_is_redirected_to_profile_from_guest_routes(): void
    {
        $admin = $this->adminUser();

        $this->actingAsAdmin($admin)->get(route('admin.login'))
            ->assertRedirect(route('admin.profile.edit'));
    }

    public function test_invalid_credentials_are_generic_and_inactive_users_cannot_login(): void
    {
        $active = $this->adminUser(['password' => 'Una-clave-segura-123!']);
        $inactive = User::factory()->create(['is_active' => false, 'password' => 'Una-clave-segura-123!']);

        foreach ([$active, $inactive] as $user) {
            $this->from(route('admin.login'))->post(route('admin.login.store'), [
                'email' => $user->email,
                'password' => $user === $active ? 'incorrecta' : 'Una-clave-segura-123!',
            ])->assertRedirect(route('admin.login'))->assertSessionHasErrors('email');
            $this->assertGuest();
        }
    }

    public function test_login_is_limited_by_account_and_ip(): void
    {
        $email = 'rate+'.Str::uuid().'@example.test';
        RateLimiter::clear('admin-login:account:'.hash('sha256', $email));

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('admin.login.store'), ['email' => $email, 'password' => 'incorrecta'])
                ->assertSessionHasErrors('email');
        }

        $this->post(route('admin.login.store'), ['email' => $email, 'password' => 'incorrecta'])
            ->assertTooManyRequests();
    }

    public function test_logout_invalidates_the_session(): void
    {
        $this->actingAsAdmin();
        $token = session()->token();

        $this->post(route('admin.logout'))->assertRedirect(route('admin.login'));
        $this->assertGuest();
        $this->assertNotSame($token, session()->token());
    }

    public function test_password_recovery_response_is_generic_and_notifies_only_active_users(): void
    {
        Notification::fake();
        $active = $this->adminUser();
        $inactive = User::factory()->create(['is_active' => false]);
        $message = 'Si la dirección corresponde a una cuenta activa, recibirás instrucciones para continuar.';

        $this->post(route('admin.password.email'), ['email' => 'missing@example.test'])
            ->assertSessionHas('status', $message);
        $this->post(route('admin.password.email'), ['email' => $active->email])
            ->assertSessionHas('status', $message);
        $this->post(route('admin.password.email'), ['email' => $inactive->email])
            ->assertSessionHas('status', $message);

        Notification::assertSentTo($active, ResetPassword::class);
        Notification::assertNotSentTo($inactive, ResetPassword::class);
    }

    public function test_valid_password_reset_changes_password_and_invalidates_sessions(): void
    {
        $user = $this->adminUser(['password' => 'Clave-anterior-segura-123!']);
        DB::table('sessions')->insert([
            'id' => 'reset-session',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => 'test',
            'last_activity' => time(),
        ]);
        $token = Password::createToken($user);
        $oldRememberToken = $user->remember_token;
        $oldVersion = $user->session_version;

        $this->post(route('admin.password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'Clave-nueva-segura-456!',
            'password_confirmation' => 'Clave-nueva-segura-456!',
        ])->assertRedirect(route('admin.login'));

        $user->refresh();
        $this->assertTrue(Hash::check('Clave-nueva-segura-456!', $user->password));
        $this->assertNotSame($oldRememberToken, $user->remember_token);
        $this->assertSame($oldVersion + 1, $user->session_version);
        $this->assertDatabaseMissing('sessions', ['id' => 'reset-session']);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
        $this->assertDatabaseHas('admin_audit_events', [
            'action' => 'user.password_reset',
            'entity_id' => (string) $user->id,
            'actor_id' => null,
        ]);
    }

    public function test_password_reset_rejects_invalid_and_reused_tokens(): void
    {
        $user = $this->adminUser();
        $this->from(route('admin.password.reset', ['token' => 'invalid', 'email' => $user->email]))
            ->post(route('admin.password.update'), [
                'token' => 'invalid',
                'email' => $user->email,
                'password' => 'Clave-nueva-segura-456!',
                'password_confirmation' => 'Clave-nueva-segura-456!',
            ])->assertRedirect()->assertSessionHasErrors('email');

        $token = Password::createToken($user);
        $payload = [
            'token' => $token,
            'email' => $user->email,
            'password' => 'Clave-nueva-segura-456!',
            'password_confirmation' => 'Clave-nueva-segura-456!',
        ];
        $this->post(route('admin.password.update'), $payload)->assertRedirect(route('admin.login'));
        $this->from(route('admin.password.reset', ['token' => $token, 'email' => $user->email]))
            ->post(route('admin.password.update'), $payload)->assertRedirect()->assertSessionHasErrors('email');
    }

    public function test_expired_password_reset_token_is_rejected(): void
    {
        $user = $this->adminUser();
        $token = Password::createToken($user);
        Carbon::setTestNow(now()->addMinutes(61));

        try {
            $this->post(route('admin.password.update'), [
                'token' => $token,
                'email' => $user->email,
                'password' => 'Clave-nueva-segura-456!',
                'password_confirmation' => 'Clave-nueva-segura-456!',
            ])->assertSessionHasErrors('email');
        } finally {
            Carbon::setTestNow();
        }
    }
}
