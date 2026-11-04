@extends('layouts.admin')

@section('title', 'Permisos')

@section('content')
    <div class="page-intro"><div><p class="eyebrow">Administración</p><h2>Permisos del sistema</h2><p class="muted">Capacidades definidas por código. Se asignan a través de roles.</p></div></div>
    <div class="notice"><strong>Catálogo protegido</strong><span>Los permisos base no se crean desde el panel. Los proyectos derivados pueden añadir capacidades mediante sus migraciones o seeders.</span></div>
    @forelse($permissions as $group => $items)
        <section class="panel permission-list">
            <div class="panel-heading"><div><p class="eyebrow">{{ $group }}</p><h2>{{ ucfirst($group) }}</h2></div><span class="badge badge-neutral">{{ $items->count() }} capacidades</span></div>
            <div class="permission-table">
                @foreach($items as $permission)
                    <div class="permission-row"><div><code>{{ $permission->key }}</code><small>{{ $permission->description }}</small></div><span>{{ $permission->roles_count }} roles</span></div>
                @endforeach
            </div>
        </section>
    @empty
        <x-admin.empty-state title="Catálogo sin permisos" description="Ejecuta los seeders para instalar las capacidades base." />
    @endforelse
@endsection
