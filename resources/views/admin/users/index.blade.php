@extends('layouts.admin')

@section('title', 'Usuarios')

@section('content')
    <div class="page-intro">
        <div><p class="eyebrow">Administración</p><h2>Usuarios</h2><p class="muted">Cuentas con acceso a este panel.</p></div>
        @can('users.create')<a class="button button-primary" href="{{ route('admin.users.create') }}">Crear usuario</a>@endcan
    </div>
    <section class="panel">
        <form method="GET" action="{{ route('admin.users.index') }}" class="filter-bar" aria-label="Filtrar usuarios">
            <label class="filter-search" for="q">Buscar<input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Nombre o correo"></label>
            <label for="status">Estado<select id="status" name="status"><option value="">Todos</option><option value="active" @selected(($filters['status'] ?? '') === 'active')>Activos</option><option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactivos</option></select></label>
            <label for="role">Rol<select id="role" name="role"><option value="">Todos</option>@foreach($roles as $role)<option value="{{ $role->id }}" @selected((string)($filters['role'] ?? '') === (string)$role->id)>{{ $role->name }}{{ $role->is_active ? '' : ' · inactivo' }}</option>@endforeach</select></label>
            <div class="filter-actions"><button class="button button-primary button-small" type="submit">Aplicar</button><a class="button button-secondary button-small" href="{{ route('admin.users.index') }}">Limpiar</a></div>
        </form>
        @if($users->isEmpty())
            <x-admin.empty-state title="No hay usuarios para mostrar" description="Prueba otros filtros o crea una cuenta." />
        @else
            <div class="table-wrap"><table>
                <thead><tr><th>Usuario</th><th>Correo</th><th>Roles</th><th>Estado</th><th>Actualizado</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td><strong>{{ $user->name }}</strong></td><td>{{ $user->email }}</td>
                        <td><div class="badge-list">@forelse($user->roles as $role)<span class="badge badge-neutral">{{ $role->name }}</span>@empty<span class="muted">Sin rol</span>@endforelse</div></td>
                        <td><span class="badge {{ $user->is_active ? 'badge-success' : 'badge-muted' }}">{{ $user->is_active ? 'Activo' : 'Inactivo' }}</span></td>
                        <td><time datetime="{{ $user->updated_at->toIso8601String() }}">{{ $user->updated_at->format('d/m/Y') }}</time></td>
                        <td class="row-actions">
                            @can('users.update')<a class="button button-secondary button-small" href="{{ route('admin.users.edit', $user) }}">Editar</a>@endcan
                            @can('users.disable')
                                <form method="POST" action="{{ route('admin.users.status', $user) }}">
                                    @csrf @method('PATCH')
                                    <button class="button {{ $user->is_active ? 'button-danger' : 'button-secondary' }} button-small" type="submit">{{ $user->is_active ? 'Desactivar' : 'Activar' }}</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
            <div class="pagination-wrap">{{ $users->links() }}</div>
        @endif
    </section>
@endsection
