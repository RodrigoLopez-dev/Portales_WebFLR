<?php

return [
    'host' => env_value('DB_HOST', 'localhost'),
    'port' => env_value('DB_PORT', '3306'),
    'dbname' => env_value('DB_DATABASE', ''),
    'username' => env_value('DB_USERNAME', ''),
    'password' => env_value('DB_PASSWORD', ''),
    'charset' => env_value('DB_CHARSET', 'utf8mb4'),
];