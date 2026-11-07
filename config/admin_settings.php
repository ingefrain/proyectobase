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
            'title' => 'Notificaciones',
            'description' => 'Consulta entregas, reintentos y mensajes del sistema.',
            'action' => 'Abrir notificaciones',
            'route' => 'admin.notifications.index',
            'permission' => 'notifications.view',
        ],
        [
            'title' => 'Documentos',
            'description' => 'Consulta y administra archivos almacenados en el sistema.',
            'action' => 'Abrir documentos',
            'route' => 'admin.documents.index',
            'permission' => 'documents.view',
        ],
        [
            'title' => 'Identidad visual',
            'description' => 'Configura el nombre y logotipo mostrados por la aplicación.',
            'action' => 'Personalizar identidad visual',
            'route' => 'admin.branding.edit',
            'permission' => 'branding.manage',
        ],
        [
            'title' => 'Auditoría del sistema',
            'description' => 'Herramientas técnicas para revisar seguridad de acceso, actividad, permisos, entregas y estado de la aplicación.',
            'action' => 'Abrir auditoría del sistema',
            'route' => 'admin.system-audit.index',
            'permission' => 'system.audit.view',
        ],
    ],
];
