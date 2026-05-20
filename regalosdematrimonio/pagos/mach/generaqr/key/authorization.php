<?php

require_once __DIR__ . '/../../../../config/env.php';

load_env(__DIR__ . '/../../../../.env');

function mach_headers()
{
    $machBearerToken = env_value('MACH_BEARER_TOKEN', '');

    if (empty($machBearerToken)) {
        throw new Exception('MACH_BEARER_TOKEN no configurado.');
    }

    return array(
        "Content-Type: application/json",
        "Authorization: Bearer " . $machBearerToken
    );
}

$headers = mach_headers();