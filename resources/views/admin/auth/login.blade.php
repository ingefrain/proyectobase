@extends('layouts.guest')

@section('title', 'Iniciar sesión')

@section('content')
    <p class="eyebrow">Acceso seguro</p>
    <h1>Iniciar sesión</h1>
    <p class="muted">Ingresa con tu cuenta administrativa.</p>
    <form method="POST" action="{{ route('admin.login.store') }}" class="stack-form">
        @csrf
        <label for="email">Correo electrónico</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
        <x-admin.field-error name="email" />
        <label for="password">Contraseña</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        <x-admin.field-error name="password" />
        <label class="check-row"><input type="checkbox" name="remember" value="1"> Mantener sesión iniciada</label>
        <button class="button button-primary button-wide" type="submit">Entrar</button>
    </form>
    <p class="form-footnote"><a href="{{ route('admin.password.request') }}">¿Olvidaste tu contraseña?</a></p>
@endsection
