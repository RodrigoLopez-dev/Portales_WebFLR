<?php

$machBearerToken = getenv('MACH_BEARER_TOKEN');

if ($machBearerToken === false || trim($machBearerToken) === '') {
    error_log('MACH_BEARER_TOKEN no está configurado.');
    $machBearerToken = '';
}

$machBearerToken = trim($machBearerToken);

if (stripos($machBearerToken, 'Bearer ') === 0) {
    $machBearerToken = trim(substr($machBearerToken, 7));
}

$headers = array(
    'Content-Type: application/json',
    'Authorization: Bearer ' . $machBearerToken
);