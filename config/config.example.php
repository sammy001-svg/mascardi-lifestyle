<?php
/**
 * Copy this file to config.php and fill in real values.
 * config.php is gitignored — never commit real credentials.
 */
return [
    'app_env' => 'development', // 'development' | 'production'
    'app_url' => 'http://localhost:8000',
    'app_name' => 'Mascardi Lifestyle',

    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'mascardi_lifestyle',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],

    // Random 32+ char string, unique per environment. Used to key CSRF/session salts.
    'app_key' => 'CHANGE-THIS-TO-A-RANDOM-SECRET-STRING',
];
