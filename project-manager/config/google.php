<?php

$app = require __DIR__ . '/app.php';

return [
    'client_id' => env_value('GOOGLE_CLIENT_ID', ''),
    'client_secret' => env_value('GOOGLE_CLIENT_SECRET', ''),
    'redirect_uri' => rtrim($app['app_url'], '/') . '/auth/google/callback',
    'allowed_domain' => env_value('GOOGLE_ALLOWED_DOMAIN', ''),

    'authorization_endpoint' => 'https://accounts.google.com/o/oauth2/v2/auth',
    'token_endpoint' => 'https://oauth2.googleapis.com/token',
    'userinfo_endpoint' => 'https://openidconnect.googleapis.com/v1/userinfo',

    'scopes' => 'openid email profile',
];