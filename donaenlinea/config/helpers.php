<?php

function app_base_url()
{
    return rtrim(env_value('APP_BASE_URL'), '/');
}

function app_name()
{
    return trim(env_value('APP_NAME'), '/');
}

function base_url($path = '')
{
    $url = app_base_url();

    if (app_name() !== '') {
        $url .= '/' . app_name();
    }

    if ($path !== '') {
        $url .= '/' . ltrim($path, '/');
    }

    return $url;
}

function asset($path = '')
{
    return base_url($path);
}

function redirect($path = '')
{
    header('Location: ' . base_url($path));
    exit;
}

function is_local()
{
    return env_value('APP_ENV') === 'local';
}

function is_production()
{
    return env_value('APP_ENV') === 'production';
}