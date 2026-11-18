@extends('admin.components.demo', ['demoTitle' => 'Utilidades'])

@section('usage', 'Controles y feedback reutilizables para interacciones administrativas. Los ejemplos se ejecutan en esta página.')

@section('example')
    <section class="utility-section" aria-labelledby="utility-actions">
    <h3 id="utility-actions">Jerarquía de acciones</h3>
    <p class="muted">Una acción principal por contexto. Eliminar usa peligro; una confirmación afirmativa puede usar éxito.</p>
    <div class="page-actions">
        <x-admin.button>Primario</x-admin.button>
        <x-admin.button variant="secondary">Secundario</x-admin.button>
        <x-admin.button variant="tertiary">Terciario</x-admin.button>
        <x-admin.button variant="danger">Destructivo</x-admin.button>
        <x-admin.button variant="confirmation">Confirmación</x-admin.button>
        <x-admin.button :disabled="true">Deshabilitado</x-admin.button>
        <x-admin.button :loading="true">Guardar cambios</x-admin.button>
        <x-admin.button variant="secondary" data-button-loading-demo>Probar guardado</x-admin.button>
    </div>
    </section>
    <section class="utility-section" aria-labelledby="utility-filters">
    <h3 id="utility-filters">Barra de filtros</h3>
    <p class="muted">Demostración de distribución. Estos campos no consultan ni modifican registros.</p>
    <form method="GET" action="{{ route('admin.components.utilities') }}" class="admin-filter-bar">
        <label class="admin-filter-field filter-search">Búsqueda<input name="search" type="search" value="{{ is_string(request('search')) ? request('search') : '' }}"></label>
        <label class="admin-filter-field">Estado<select name="status"><option value="">Todos</option><option value="active" @selected(request('status') === 'active')>Activo</option><option value="inactive" @selected(request('status') === 'inactive')>Inactivo</option></select></label>
        <label class="admin-filter-field">Rol<select name="role"><option value="">Todos</option><option value="editor" @selected(request('role') === 'editor')>Editor</option></select></label>
        <label class="admin-filter-field">Fecha<input name="from" type="date" value="{{ is_string(request('from')) ? request('from') : '' }}"></label>
        <div class="admin-filter-actions"><button type="submit" class="button button-primary">Aplicar</button><a class="button button-tertiary" href="{{ route('admin.components.utilities') }}">Limpiar</a></div>
    </form>
    </section>
    <section class="utility-section" aria-labelledby="utility-pagination">
    <h3 id="utility-pagination">Paginación</h3>
    <p class="muted">137 registros ficticios en memoria. Los controles conservan la consulta.</p>
    {{ \App\Support\AdminPagination::demo()->links() }}
    </section>
    <section class="utility-section" aria-label="Ejemplos de Toggle">
    <h3>Toggle de estado</h3>
    <div class="field-grid">
        <div class="field"><strong>Activo</strong><x-admin.status-toggle :active="true" label="Estado del ejemplo activo" demo="success" /></div>
        <div class="field"><strong>Inactivo</strong><x-admin.status-toggle :active="false" label="Estado del ejemplo inactivo" demo="success" /></div>
        <div class="field"><strong>Guardando</strong><x-admin.status-toggle :active="true" label="Estado del ejemplo en proceso" demo="loading" /></div>
        <div class="field"><strong>Deshabilitado</strong><x-admin.status-toggle :active="false" :disabled="true" label="Estado del ejemplo deshabilitado" demo="success" /></div>
        <div class="field"><strong>Error simulado</strong><x-admin.status-toggle :active="true" label="Estado del ejemplo con error" demo="error" /></div>
    </div>
    </section>
    <section class="utility-section" aria-label="Ejemplos de Notify">
    <h3>Notificaciones</h3>
    <div class="page-actions">
        <button type="button" class="button button-secondary" data-notify-trigger="success" data-notify-text="Cambios guardados.">Éxito</button>
        <button type="button" class="button button-secondary" data-notify-trigger="info" data-notify-text="Operación completada.">Información</button>
        <button type="button" class="button button-secondary" data-notify-trigger="warning" data-notify-text="La operación terminó. Algunos elementos requieren revisión antes de continuar con el siguiente paso.">Advertencia</button>
        <button type="button" class="button button-secondary" data-notify-trigger="error" data-notify-text="No fue posible completar la operación.">Error</button>
    </div>
    </section>
@endsection

@section('integration')
&lt;x-admin.button variant="primary" type="submit"&gt;Guardar cambios&lt;/x-admin.button&gt;
AdminButton.setLoading(button, true); // Restaurar con false al finalizar.
$records = $authorizedQuery-&gt;adminPaginate();
@{{ $records-&gt;links() }}
&lt;form class="admin-filter-bar"&gt;...&lt;div class="admin-filter-actions"&gt;...&lt;/div&gt;&lt;/form&gt;
&lt;x-admin.status-toggle :active="$record-&gt;is_active" :action="$statusUrl" :label="'Estado de '.$record-&gt;name" /&gt;
AdminNotify.show({ type: 'success', message: 'Cambios guardados.' });
@endsection
