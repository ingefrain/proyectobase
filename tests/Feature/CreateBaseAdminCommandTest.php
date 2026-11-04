<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateBaseAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_the_first_administrator_without_persisting_a_fixture(): void
    {
        $this->artisan('base:admin')
            ->expectsQuestion('Nombre', 'Administradora Inicial')
            ->expectsQuestion('Correo', 'inicio@example.test')
            ->expectsQuestion('Contraseña (mínimo 12 caracteres)', 'Clave-segura-inicial-123!')
            ->expectsQuestion('Confirma la contraseña', 'Clave-segura-inicial-123!')
            ->expectsOutputToContain('Cuenta administrativa creada: inicio@example.test')
            ->assertExitCode(0);

        $user = User::query()->where('email', 'inicio@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('Clave-segura-inicial-123!', $user->password));
        $this->assertTrue($user->is_active);
        $this->assertSame(['administrator'], $user->roles()->pluck('key')->all());
        $this->assertTrue($user->can('permissions.assign'));
        $this->assertSame(1, Role::query()->where('key', 'administrator')->count());
    }
}
