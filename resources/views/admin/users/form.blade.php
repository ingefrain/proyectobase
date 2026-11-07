@extends('layouts.admin')

@section('title', $creating ? 'Crear usuario' : 'Editar usuario')

@section('content')
    @php($selectedRoleIds = array_map('intval', old('role_ids', $selectedRoles)))
    <div class="page-intro">
        <div><p class="eyebrow">Usuarios</p><p class="muted">{{ $creating ? 'Agrega una cuenta al sistema.' : 'Actualiza la cuenta y sus accesos.' }}</p></div>
        <a class="button button-secondary" href="{{ route('admin.users.index') }}">Volver a usuarios</a>
    </div>
    <section class="panel form-panel admin-form-surface">
        <form method="POST" action="{{ $creating ? route('admin.users.store') : route('admin.users.update', $user) }}" class="stack-form" data-password-policy data-password-optional="{{ $creating ? 'false' : 'true' }}" data-minimum="{{ \App\Support\PasswordPolicy::minimumLength() }}" data-maximum="{{ \App\Support\PasswordPolicy::maximumLength() }}" data-require-mixed-case="{{ config('password_policy.require_mixed_case') ? 'true' : 'false' }}" data-require-number="{{ config('password_policy.require_number') ? 'true' : 'false' }}">
            @csrf
            @unless($creating) @method('PUT') @endunless
            <div class="field-grid admin-form-grid">
                <div class="field"><label for="name">Nombre</label><input id="name" name="name" value="{{ old('name', $user->name) }}" maxlength="120" autocomplete="name" required><x-admin.field-error name="name" /></div>
                <div class="field"><label for="email">Correo electrónico</label><input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" maxlength="255" autocomplete="email" required><x-admin.field-error name="email" /></div>
            </div>
            <div class="field-grid admin-form-grid password-fields">
                <div class="field">
                    <label for="password">{{ $creating ? 'Contraseña' : 'Nueva contraseña (opcional)' }}</label>
                    <div class="password-input-wrap"><input id="password" name="password" type="password" autocomplete="new-password" @required($creating) data-password-value><button class="password-visibility-toggle" type="button" aria-label="Mostrar contraseña" aria-controls="password" aria-pressed="false" data-password-toggle hidden><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M2.1 12s3.5-6.5 9.9-6.5 9.9 6.5 9.9 6.5-3.5 6.5-9.9 6.5S2.1 12 2.1 12Zm9.9 3.4a3.4 3.4 0 1 0 0-6.8 3.4 3.4 0 0 0 0 6.8Z"/><path data-eye-slash d="m3 3 18 18"/></svg></button></div>
                    @unless($creating)<p class="field-hint">Déjalo vacío si no quieres cambiarla.</p>@endunless
                    <x-admin.field-error name="password" />
                </div>
                <div class="field">
                    <label for="password_confirmation">Confirmar contraseña</label>
                    <div class="password-input-wrap"><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" @required($creating) data-password-confirm><button class="password-visibility-toggle" type="button" aria-label="Mostrar contraseña" aria-controls="password_confirmation" aria-pressed="false" data-password-toggle hidden><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M2.1 12s3.5-6.5 9.9-6.5 9.9 6.5 9.9 6.5-3.5 6.5-9.9 6.5S2.1 12 2.1 12Zm9.9 3.4a3.4 3.4 0 1 0 0-6.8 3.4 3.4 0 0 0 0 6.8Z"/><path data-eye-slash d="m3 3 18 18"/></svg></button></div>
                    <x-admin.field-error name="password_confirmation" />
                </div>
            </div>
            <p class="password-limit-warning" data-password-limit role="alert" aria-live="assertive" hidden>La contraseña no puede superar {{ \App\Support\PasswordPolicy::maximumLength() }} caracteres.</p>
            <ul class="password-checklist" aria-label="Requisitos de la contraseña" data-password-checklist @unless($creating) hidden @endunless>
                    <li data-rule="minimum"><span aria-hidden="true">✗</span><span>Mínimo {{ \App\Support\PasswordPolicy::minimumLength() }} caracteres</span><span class="sr-only" data-rule-status>Falta</span></li>
                    @if(config('password_policy.require_mixed_case'))
                        <li data-rule="uppercase"><span aria-hidden="true">✗</span><span>Una letra mayúscula</span><span class="sr-only" data-rule-status>Falta</span></li>
                        <li data-rule="lowercase"><span aria-hidden="true">✗</span><span>Una letra minúscula</span><span class="sr-only" data-rule-status>Falta</span></li>
                    @endif
                    @if(config('password_policy.require_number'))
                        <li data-rule="number"><span aria-hidden="true">✗</span><span>Un número</span><span class="sr-only" data-rule-status>Falta</span></li>
                    @endif
                    <li data-rule="match"><span aria-hidden="true">✗</span><span>Las contraseñas coinciden</span><span class="sr-only" data-rule-status>Falta</span></li>
                </ul>
            @can('roles.assign')
                <fieldset class="choice-fieldset role-assignment" data-role-selector>
                    <legend>Roles</legend>
                    <input type="hidden" name="role_assignment_present" value="1">
                    <p class="field-hint">Selecciona uno o más roles para este usuario.</p>
                    <label class="role-native-label" for="role_ids">Roles asignados</label>
                    <select id="role_ids" name="role_ids[]" multiple size="{{ min(max($roles->count(), 3), 6) }}" aria-describedby="role-help role_ids-error role_ids-item-error">
                        @foreach($roles as $role)
                            @if($role->is_active || in_array($role->id, $selectedRoles, true))
                                <option value="{{ $role->id }}" @selected(in_array($role->id, $selectedRoleIds, true))>{{ $role->name }}{{ $role->is_active ? '' : ' (inactivo)' }}</option>
                            @endif
                        @endforeach
                    </select>
                    <div class="role-enhanced" data-role-enhanced hidden>
                        <div class="role-chips" data-role-chips aria-live="polite"></div>
                        <button class="role-picker-toggle" type="button" aria-expanded="false" aria-controls="role-dropdown-panel" data-role-toggle>Elegir roles</button>
                        <div class="role-dropdown-panel" id="role-dropdown-panel" data-role-dropdown hidden>
                            <label class="sr-only" for="role-search">Buscar roles</label>
                            <input id="role-search" type="search" placeholder="Buscar roles…" autocomplete="off" data-role-search>
                            <div class="role-options" data-role-options role="group" aria-label="Roles disponibles"></div>
                        </div>
                    </div>
                    <p class="field-hint" id="role-help">{{ $roles->isEmpty() ? 'No hay roles disponibles.' : 'Puedes seleccionar varios roles.' }}</p>
                    <x-admin.field-error name="role_ids" />
                    <x-admin.field-error name="role_ids.*" id="role_ids-item-error" />
                </fieldset>
            @endcan
            <div class="form-actions"><button class="button button-primary" type="submit">{{ $creating ? 'Crear usuario' : 'Guardar cambios' }}</button><a class="button button-secondary" href="{{ route('admin.users.index') }}">Cancelar</a></div>
        </form>
    </section>
@endsection
