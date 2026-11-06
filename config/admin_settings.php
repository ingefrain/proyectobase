<?php

return [
    'cards' => [
        [
            'title' => 'Usuarios',
            'description' => 'Gestiona las personas con acceso al sistema.',
            'action' => 'Entrar a administración de usuarios',
            'route' => 'admin.users.index',
            'permission' => 'users.view',
        ],
        [
            'title' => 'Roles',
            'description' => 'Define qué puede hacer cada tipo de usuario.',
            'action' => 'Administrar roles',
            'route' => 'admin.roles.index',
            'permission' => 'roles.view',
        ],
        [
            'title' => 'Permisos avanzados',
            'description' => 'Consulta las capacidades del sistema y cómo se asignan mediante roles.',
            'action' => 'Consultar permisos',
            'route' => 'admin.permissions.index',
            'permission' => 'permissions.view',
        ],
        [
            'title' => 'Notificaciones',
            'description' => 'Consulta entregas, reintentos y mensajes del sistema.',
            'action' => 'Abrir notificaciones',
            'route' => 'admin.notifications.index',
            'permission' => 'notifications.view',
        ],
        [
            'title' => 'Actividad',
            'description' => 'Consulta los cambios administrativos registrados en el sistema.',
            'action' => 'Abrir actividad',
            'route' => 'admin.audit.index',
            'permission' => 'audit.view',
        ],
    ],
];
