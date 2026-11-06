@extends('layouts.admin')

@section('title', 'Actividad')

@section('content')
    <div class="page-intro"><p class="muted">Registro de cambios administrativos y acciones sensibles.</p></div>
    <section class="panel">
        <form method="GET" action="{{ route('admin.audit.index') }}" class="filter-bar" aria-label="Filtrar actividad">
            <label for="entity">Tipo<select id="entity" name="entity"><option value="">Todos</option><option value="user" @selected(($filters['entity'] ?? '') === 'user')>Usuario</option><option value="role" @selected(($filters['entity'] ?? '') === 'role')>Rol</option></select></label>
            <label for="action">Acción<select id="action" name="action"><option value="">Todas</option>
                @foreach(['user.created', 'user.updated', 'user.activated', 'user.deactivated', 'user.roles_assigned', 'user.password_changed', 'user.password_reset', 'user.profile_updated', 'role.created', 'role.updated', 'role.activated', 'role.deactivated', 'role.permissions_changed'] as $action)
                    <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ str($action)->replace('.', ' ')->headline() }}</option>
                @endforeach
            </select></label>
            <div class="filter-actions"><button class="button button-primary button-small" type="submit">Aplicar</button><a class="button button-secondary button-small" href="{{ route('admin.audit.index') }}">Limpiar</a></div>
        </form>
        @if($events->isEmpty())
            <x-admin.empty-state title="Sin eventos para mostrar" description="Los cambios administrativos aparecerán aquí." />
        @else
            <div class="table-wrap"><table><thead><tr><th>Fecha</th><th>Actor</th><th>Acción</th><th>Elemento</th><th>Cambios</th></tr></thead><tbody>
                @foreach($events as $event)
                    <tr><td><time datetime="{{ $event->created_at->toIso8601String() }}">{{ $event->created_at->format('d/m/Y H:i:s') }}</time></td><td>{{ $event->actor?->email ?? 'Sistema' }}</td><td>{{ str($event->action)->replace('.', ' ')->headline() }}</td><td>{{ ucfirst($event->entity_type) }} #{{ $event->entity_id }}</td><td><details><summary>Ver valores</summary><div class="audit-values"><strong>Anterior</strong><pre>{{ json_encode($event->before, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '—' }}</pre><strong>Nuevo</strong><pre>{{ json_encode($event->after, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '—' }}</pre></div></details></td></tr>
                @endforeach
            </tbody></table></div>
            <div class="pagination-wrap">{{ $events->links() }}</div>
        @endif
    </section>
@endsection
