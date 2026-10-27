<?php

return [
    'db' => [
        'driver' => $_ENV['DB_DRIVER'] ?? 'sqlite', // 'mysql' or 'sqlite'
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port' => $_ENV['DB_PORT'] ?? '3306',
        'dbname' => $_ENV['DB_NAME'] ?? 'chess_tournament',
        'user' => $_ENV['DB_USER'] ?? 'root',
        'pass' => $_ENV['DB_PASS'] ?? '',
        'sqlite_path' => $_ENV['SQLITE_PATH'] ?? __DIR__ . '/../storage/db/chess.sqlite',
    ],
    'app' => [
        'name' => 'Paete Chess Club Tournament Manager',
        'timezone' => 'Asia/Manila',
        'base_url' => $_ENV['APP_URL'] ?? '',
    ]
];
