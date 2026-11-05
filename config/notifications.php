<?php

return [
    'branding' => [
        'name' => env('NOTIFICATION_BRAND_NAME', env('APP_NAME', 'Proyecto Base')),
        'logo_url' => env('NOTIFICATION_BRAND_LOGO_URL'),
        'greeting' => env('NOTIFICATION_GREETING', 'Hola'),
        'footer' => env('NOTIFICATION_FOOTER', 'Mensaje automático. Si tienes preguntas, contacta al equipo de soporte.'),
        'primary_color' => env('NOTIFICATION_PRIMARY_COLOR', '#126c72'),
    ],
];
