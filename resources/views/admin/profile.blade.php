@extends('layouts.admin')

@section('title', 'Perfil')

@section('content')
    <div class="page-intro"><div><p class="eyebrow">Cuenta</p><h2>Tu perfil</h2><p class="muted">Actualiza tus datos de acceso personales.</p></div></div>
    <section class="panel form-panel">
        <h2>Datos personales</h2>
        <dl class="profile-summary"><div><dt>Correo electrónico</dt><dd>{{ auth()->user()->email }}</dd></div><div><dt>Estado</dt><dd><span class="badge badge-success">Activo</span></dd></div></dl>
        <form method="POST" action="{{ route('admin.profile.update') }}" class="stack-form">
            @csrf @method('PUT')
            <div class="field"><label for="name">Nombre</label><input id="name" name="name" value="{{ old('name', auth()->user()->name) }}" maxlength="120" required><x-admin.field-error name="name" /></div>
            <button class="button button-primary" type="submit">Guardar perfil</button>
        </form>
    </section>
    <section class="panel form-panel">
        <h2>Cambiar contraseña</h2><p class="muted">Se cerrarán las demás sesiones.</p>
        <form method="POST" action="{{ route('admin.profile.password') }}" class="stack-form">
            @csrf @method('PUT')
            <div class="field"><label for="current_password">Contraseña actual</label><input id="current_password" name="current_password" type="password" autocomplete="current-password" required><x-admin.field-error name="current_password" /></div>
            <div class="field-grid">
                <div class="field"><label for="password">Nueva contraseña</label><input id="password" name="password" type="password" autocomplete="new-password" required><p class="field-hint">Mínimo 12 caracteres.</p><x-admin.field-error name="password" /></div>
                <div class="field"><label for="password_confirmation">Confirma la contraseña</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required></div>
            </div>
            <button class="button button-primary" type="submit">Actualizar contraseña</button>
        </form>
    </section>
@endsection
