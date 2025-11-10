<?php

declare(strict_types=1);

return [
    'app' => [
        'name' => 'Fundación Crece Contigo - Portal de Datos',
        'base_url' => getenv('APP_URL') ?: '',
        'timezone' => getenv('APP_TIMEZONE') ?: 'America/Guayaquil',
    ],
    'database' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => (int) (getenv('DB_PORT') ?: 3306),
        'database' => getenv('DB_DATABASE') ?: 'crece_portal',
        'username' => getenv('DB_USERNAME') ?: 'creceportaluser',
        'password' => getenv('DB_PASSWORD') ?: 'Crece2k!!!',
        'charset' => 'utf8mb4',
    ],
    'mail' => [
        'from_address' => getenv('MAIL_FROM_ADDRESS') ?: 'no-reply@fundacioncrececontigo.org',
        'from_name' => getenv('MAIL_FROM_NAME') ?: 'Fundación Crece Contigo',
    ],
];
