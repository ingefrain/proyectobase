<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Resumen') · Proyecto Base</title>
    <link rel="stylesheet" href="{{ asset('assets/base-admin/admin.css') }}?v={{ filemtime(public_path('assets/base-admin/admin.css')) }}">
</head>
<body class="admin-body">
    <a class="skip-link" href="#main-content">Saltar al contenido</a>
    <div class="admin-shell">
        <button class="drawer-scrim" type="button" data-drawer-close aria-label="Cerrar menú" tabindex="-1"></button>
        <aside class="sidebar" id="admin-sidebar" aria-label="Navegación administrativa">
            <a class="brand" href="{{ auth()->user()->can('dashboard.view') ? route('admin.dashboard') : route('admin.settings.index') }}">
                <span class="brand-mark" aria-hidden="true">PB</span>
                <span><strong>Proyecto Base</strong><small>Administración</small></span>
            </a>
            <nav class="side-navigation" aria-label="Principal">
                @can('dashboard.view')
                    <a class="side-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}" href="{{ route('admin.dashboard') }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>Resumen</a>
                @endcan
                @canany(['users.view', 'roles.view', 'notifications.view', 'documents.view', 'system.audit.view'])
                    <a class="side-link {{ request()->routeIs('admin.settings.*', 'admin.users.*', 'admin.roles.*', 'admin.system-audit.*', 'admin.system-status.*', 'admin.login-security.*', 'admin.permissions.*', 'admin.audit.*', 'admin.notifications.*', 'admin.documents.*') ? 'is-active' : '' }}" href="{{ route('admin.settings.index') }}" @if(request()->routeIs('admin.settings.*', 'admin.users.*', 'admin.roles.*', 'admin.system-audit.*', 'admin.system-status.*', 'admin.login-security.*', 'admin.permissions.*', 'admin.audit.*', 'admin.notifications.*', 'admin.documents.*')) aria-current="page" @endif>Configuración</a>
                @endcanany
            </nav>
        </aside>

        <div class="workspace">
            <header class="topbar">
                <div class="topbar-start">
                    <button class="menu-toggle" type="button" data-menu-toggle aria-label="Abrir menú de navegación" aria-controls="admin-sidebar" aria-expanded="false">☰</button>
                    <div class="page-heading">
                        <p class="eyebrow">Proyecto Base · Administración</p>
                        <h1>@yield('title', 'Resumen')</h1>
                    </div>
                </div>
                <details class="profile-menu">
                    <summary aria-label="Abrir menú de perfil">
                        <span class="avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="profile-menu-name">{{ auth()->user()->name }}</span>
                        <span aria-hidden="true">⌄</span>
                    </summary>
                    <div class="profile-dropdown">
                        <a class="account-menu-link" href="{{ route('admin.profile.edit') }}">Mi perfil</a>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button class="account-menu-link account-menu-button" type="submit">Cerrar sesión</button>
                        </form>
                    </div>
                </details>
            </header>

            <main class="main-content" id="main-content" tabindex="-1">
                @foreach (['status' => 'success', 'success' => 'success', 'error' => 'error', 'warning' => 'warning', 'info' => 'info'] as $key => $variant)
                    @if (session($key))
                        <x-admin.alert :variant="$variant">{{ session($key) }}</x-admin.alert>
                    @endif
                @endforeach
                @if ($errors->any())
                    <x-admin.alert variant="error">
                        <p>Hay campos que necesitan atención.</p>
                        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </x-admin.alert>
                @endif
                @yield('content')
            </main>
            <footer class="workspace-footer"><span>Proyecto Base</span><span>Administración reutilizable</span></footer>
        </div>
    </div>
    <script src="{{ asset('assets/base-admin/admin.js') }}" defer></script>
</body>
</html>
