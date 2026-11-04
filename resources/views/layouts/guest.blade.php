<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Acceso') · Proyecto Base</title>
    <link rel="stylesheet" href="{{ asset('assets/base-admin/admin.css') }}">
</head>
<body class="guest-body">
    <main class="guest-card" id="main-content">
        <a class="guest-brand" href="{{ route('admin.login') }}">
            <span class="brand-mark" aria-hidden="true">PB</span>
            <span><strong>Proyecto Base</strong><small>Administración</small></span>
        </a>
        @yield('content')
    </main>
</body>
</html>
