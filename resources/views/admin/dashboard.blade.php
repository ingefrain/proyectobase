@extends('layouts.admin')

@section('title', 'Resumen')

@section('content')
    <div class="page-intro">
        <div><p class="eyebrow">Vista general</p><h2>Administración</h2><p class="muted">Estado actual de las cuentas y los accesos.</p></div>
    </div>
    <section class="metric-grid" aria-label="Resumen administrativo">
        <article class="metric-card"><span>Usuarios activos</span><strong>{{ number_format($activeUsers) }}</strong><small>Con acceso vigente</small></article>
        <article class="metric-card"><span>Usuarios inactivos</span><strong>{{ number_format($inactiveUsers) }}</strong><small>Sin acceso al panel</small></article>
        <article class="metric-card"><span>Roles activos</span><strong>{{ number_format($activeRoles) }}</strong><small>Disponibles para asignación</small></article>
    </section>
    @can('audit.view')
        <section class="panel">
            <div class="panel-heading"><div><h2>Actividad reciente</h2><p class="muted">Cambios administrativos registrados.</p></div><a class="button button-secondary button-small" href="{{ route('admin.audit.index') }}">Ver auditoría</a></div>
            @if($recentEvents->isEmpty())
                <x-admin.empty-state title="Sin actividad todavía" description="Los cambios administrativos aparecerán aquí." />
            @else
                <div class="table-wrap"><table><thead><tr><th>Acción</th><th>Elemento</th><th>Actor</th><th>Fecha</th></tr></thead><tbody>
                    @foreach($recentEvents as $event)
                        <tr><td>{{ str($event->action)->replace('.', ' ')->headline() }}</td><td>{{ ucfirst($event->entity_type) }} #{{ $event->entity_id }}</td><td>{{ $event->actor?->name ?? 'Sistema' }}</td><td><time datetime="{{ $event->created_at->toIso8601String() }}">{{ $event->created_at->format('d/m/Y H:i') }}</time></td></tr>
                    @endforeach
                </tbody></table></div>
            @endif
        </section>
    @endcan
@endsection
