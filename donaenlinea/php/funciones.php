<?php

date_default_timezone_set('America/Santiago');

require_once __DIR__ . '/../config/database.php';

function guardarDonacion($datos)
{
    $conn = db_connect();

    $sql = "
        INSERT INTO donaciones_online 
        (
            rut,
            nombre,
            apellidos,
            email,
            telefono,
            monto,
            medio_pago_id,
            estado_pago_id,
            ip,
            latitud,
            longitud,
            ciudad,
            region,
            pais,
            utm_source,
            utm_medium,
            utm_campaign
        ) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log('Error preparando guardarDonacion: ' . $conn->error);
        return false;
    }

    $rut = isset($datos['rut']) ? str_replace('.', '', $datos['rut']) : '';
    $nombre = isset($datos['nombre']) ? $datos['nombre'] : '';
    $apellido = isset($datos['apellido']) ? $datos['apellido'] : '';
    $email = isset($datos['email']) ? $datos['email'] : '';
    $telefono = isset($datos['telefono']) ? $datos['telefono'] : '';
    $monto = isset($datos['monto']) ? (int)$datos['monto'] : 0;
    $medio_pago_id = isset($datos['medio_pago_id']) ? (int)$datos['medio_pago_id'] : 0;
    $estado_pago_id = isset($datos['estado_pago_id']) ? (int)$datos['estado_pago_id'] : 2;
    $ip_transaccion = isset($datos['ip_transaccion']) ? $datos['ip_transaccion'] : '';
    $ip_latitud = isset($datos['ip_latitud']) ? $datos['ip_latitud'] : '';
    $ip_longitud = isset($datos['ip_longitud']) ? $datos['ip_longitud'] : '';
    $ip_ciudad = isset($datos['ip_ciudad']) ? $datos['ip_ciudad'] : '';
    $ip_region = isset($datos['ip_region']) ? $datos['ip_region'] : '';
    $ip_pais = isset($datos['ip_pais']) ? $datos['ip_pais'] : '';
    $utm_source = isset($datos['utm_source']) ? $datos['utm_source'] : '';
    $utm_medium = isset($datos['utm_medium']) ? $datos['utm_medium'] : '';
    $utm_campaign = isset($datos['utm_campaign']) ? $datos['utm_campaign'] : '';

    $stmt->bind_param(
        'sssssiiisssssssss',
        $rut,
        $nombre,
        $apellido,
        $email,
        $telefono,
        $monto,
        $medio_pago_id,
        $estado_pago_id,
        $ip_transaccion,
        $ip_latitud,
        $ip_longitud,
        $ip_ciudad,
        $ip_region,
        $ip_pais,
        $utm_source,
        $utm_medium,
        $utm_campaign
    );

    if (!$stmt->execute()) {
        error_log('Error ejecutando guardarDonacion: ' . $stmt->error);
        $stmt->close();
        return false;
    }

    $insert_id = $stmt->insert_id;

    $stmt->close();

    return $insert_id;
}