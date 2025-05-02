<?php

function loadEnv($path) {
    if (!file_exists($path)) return;

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        putenv(trim($line)); // postavlja npr. DB_USER, APP_ENV
    }
}

loadEnv(__DIR__ . '/.env');

$config = [
    'local' => [
        'db_host' => getenv('DB_HOST_LOCAL'),
        'db_user' => getenv('DB_USER_LOCAL'),
        'db_pass' => getenv('DB_PASS_LOCAL'),
    ],
    'prod' => [
        'db_host' => getenv('DB_HOST_PROD'),
        'db_user' => getenv('DB_USER_PROD'),
        'db_pass' => getenv('DB_PASS_PROD'),
    ],
];

$env = getenv('APP_ENV') === 'local' ? 'local' : 'prod';

return array_merge($config[$env], [
    'db_name' => getenv('DB_NAME'),
]);
