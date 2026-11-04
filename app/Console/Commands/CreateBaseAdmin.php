<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\AdminAuditLogger;
use App\Support\BasePermissions;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateBaseAdmin extends Command
{
    protected $signature = 'base:admin';

    protected $description = 'Crea interactivamente la primera cuenta administrativa de Proyecto Base';

    public function handle(AdminAuditLogger $audit): int
    {
        $name = trim((string) $this->ask('Nombre'));
        $email = mb_strtolower(trim((string) $this->ask('Correo')));
        $password = (string) $this->secret('Contraseña (mínimo 12 caracteres)');
        $confirmation = (string) $this->secret('Confirma la contraseña');

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password, 'password_confirmation' => $confirmation],
            [
                'name' => ['required', 'string', 'max:120'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'confirmed', Password::min(12)],
            ],
        );
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }
        $user = DB::transaction(function () use ($name, $email, $password, $audit): User {
            foreach (BasePermissions::DEFINITIONS as $key => $description) {
                Permission::query()->firstOrCreate(['key' => $key], ['description' => $description]);
            }

            $role = Role::query()->firstOrCreate(
                ['key' => 'administrator'],
                ['name' => 'Administrador', 'description' => 'Acceso administrativo completo.', 'is_active' => true],
            );
            $role->forceFill(['is_active' => true])->save();
            $role->permissions()->sync(Permission::query()->pluck('id')->all());

            $user = User::query()->create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'is_active' => true,
            ]);
            $user->roles()->attach($role);
            $audit->record('user.created', 'user', $user->id, null, [
                'name' => $user->name,
                'email' => $user->email,
                'is_active' => true,
                'roles' => ['administrator'],
            ], ['source' => 'base:admin']);

            return $user;
        });

        $this->components->info("Cuenta administrativa creada: {$user->email}");

        return self::SUCCESS;
    }
}
