<?php

namespace App\Support;

class BasePermissions
{
    /** @var array<string, string> */
    public const DEFINITIONS = [
        'dashboard.view' => 'Ver el resumen administrativo',
        'users.view' => 'Consultar usuarios',
        'users.create' => 'Crear usuarios',
        'users.update' => 'Editar usuarios',
        'users.disable' => 'Activar o desactivar usuarios',
        'roles.view' => 'Consultar roles',
        'roles.create' => 'Crear roles',
        'roles.update' => 'Editar roles',
        'roles.assign' => 'Asignar roles a usuarios',
        'permissions.view' => 'Consultar permisos',
        'permissions.assign' => 'Asignar permisos a roles',
        'audit.view' => 'Consultar auditoría administrativa',
        'system.audit.view' => 'Consultar herramientas técnicas y estado del sistema',
        'notifications.view' => 'Consultar mensajes transaccionales y sus intentos',
        'notifications.retry' => 'Reintentar mensajes transaccionales fallidos',
        'notifications.view' => 'Consultar mensajes transaccionales y sus intentos',
        'notifications.retry' => 'Reintentar mensajes transaccionales fallidos',
    ];

    /** @var list<string> */
    public const ADMINISTRATIVE_CORE = [
        'users.view', 'users.create', 'users.update', 'users.disable',
        'roles.view', 'roles.create', 'roles.update', 'roles.assign',
        'permissions.view', 'permissions.assign',
        'notifications.view', 'notifications.retry',
    ];
}
