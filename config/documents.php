<?php

return [
    'disk' => env('DOCUMENTS_DISK', 'local'),
    'directory' => 'documents',
    'max_size_kb' => (int) env('DOCUMENTS_MAX_SIZE_KB', 10240),
    'retention_days' => (int) env('DOCUMENTS_RETENTION_DAYS', 90),
    'orphan_retention_days' => (int) env('DOCUMENTS_ORPHAN_RETENTION_DAYS', 30),
    'audit_scan_limit' => (int) env('DOCUMENTS_AUDIT_SCAN_LIMIT', 500),
    'metadata_max_bytes' => 8192,
    'allowed_mime_types' => [
        'application/pdf' => ['pdf'],
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'text/plain' => ['txt', 'csv'],
        'text/csv' => ['csv'],
    ],
    'blocked_extensions' => [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar',
        'exe', 'dll', 'com', 'bat', 'cmd', 'ps1', 'sh', 'bash', 'zsh',
        'js', 'mjs', 'cjs', 'html', 'htm', 'svg', 'jar', 'msi', 'scr',
        'asp', 'aspx', 'jsp', 'cgi', 'pl', 'py', 'rb', 'conf', 'config',
        'htaccess', 'htpasswd', 'env', 'ini', 'dll', 'so', 'dylib',
    ],
];
