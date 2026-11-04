@extends('layouts.admin')

@section('title', $creating ? 'Crear usuario' : 'Editar usuario')

@section('content')
    <div class="page-intro"><div><p class="eyebrow">Administración / Usuarios</p><h2>{{ $creating ? 'Crear usuario' : 'Editar usuario' }}</h2><p class="muted">{{ $creating ? 'Agrega una cuenta administrativa.' : 'Actualiza la cuenta y sus accesos.' }}</p></div><a class="button button-secondary" href="{{ route('admin.users.index') }}">Volver a usuarios</a></div>
    <section class="panel form-panel">
        <form method="POST" action="{{ $creating ? route('admin.users.store') : route('admin.users.update', $user) }}" class="stack-form">
            @csrf
            @unless($creating) @method('PUT') @endunless
            <div class="field-grid">
                <div class="field"><label for="name">Nombre</label><input id="name" name="name" value="{{ old('name', $user->name) }}" maxlength="120" autocomplete="name" required><x-admin.field-error name="name" /></div>
                <div class="field"><label for="email">Correo electrónico</label><input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" maxlength="255" autocomplete="email" required><x-admin.field-error name="email" /></div>
            </div>
            <div class="field-grid">
                <div class="field"><label for="password">{{ $creating ? 'Contraseña' : 'Nueva contraseña (opcional)' }}</label><input id="password" name="password" type="password" autocomplete="new-password" @required($creating)><p class="field-hint">Mínimo 12 caracteres. Déjalo vacío si no quieres cambiarla.</p><x-admin.field-error name="password" /></div>
                <div class="field"><label for="password_confirmation">Confirmar contraseña</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" @required($creating)></div>
            </div>
            @can('roles.assign')
                <fieldset class="choice-fieldset">
                    <legend>Roles</legend>
                    <input type="hidden" name="role_assignment_present" value="1">
                    <p class="field-hint">El acceso final depende de los permisos asociados a cada rol.</p>
                    <div class="choice-grid">
                        @forelse($roles as $role)
                            @if($role->is_active || in_array($role->id, $selectedRoles, true))
                                <label class="check-card"><input type="checkbox" name="role_ids[]" value="{{ $role->id }}" @checked(in_array($role->id, old('role_ids', $selectedRoles)))><span><strong>{{ $role->name }}</strong><small>{{ $role->description ?: $role->key }}</small></span></label>
                            @endif
                        @empty
                            <p class="muted">No hay roles disponibles.</p>
                        @endforelse
                    </div>
                    <x-admin.field-error name="role_ids" />
                </fieldset>
            @endcan
            <div class="form-actions"><button class="button button-primary" type="submit">{{ $creating ? 'Crear usuario' : 'Guardar cambios' }}</button><a class="button button-secondary" href="{{ route('admin.users.index') }}">Cancelar</a></div>
        </form>
    </section>
@endsection
