<?php

function load_env($path)
{
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {

        $line = trim($line);

        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }

        $parts = explode('=', $line, 2);

        if (count($parts) !== 2) {
            continue;
        }

        $key = trim($parts[0]);
        $value = trim($parts[1]);

        $value = trim($value, "\"'");

        putenv($key . '=' . $value);

        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

function env_value($key, $default = null)
{
    $value = getenv($key);

    if ($value === false || $value === null || $value === '') {
        return $default;
    }

    return $value;
}

require_once __DIR__ . '/helpers.php';