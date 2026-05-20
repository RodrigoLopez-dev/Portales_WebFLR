<?php

function get_client_ip()
{
    $keys = array(
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    );

    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ips = explode(',', $_SERVER[$key]);
            $ip = trim($ips[0]);

            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }

    return 'UNKNOWN';
}

function ip_info($ip = null, $purpose = 'location', $deep_detect = true)
{
    $output = null;

    if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

        if ($deep_detect) {
            $clientIp = get_client_ip();

            if ($clientIp !== 'UNKNOWN') {
                $ip = $clientIp;
            }
        }
    }

    $purpose = str_replace(
        array('name', "\n", "\t", ' ', '-', '_'),
        '',
        strtolower(trim($purpose))
    );

    $support = array(
        'country',
        'countrycode',
        'state',
        'region',
        'city',
        'location',
        'address',
        'latitude',
        'longitude'
    );

    $continents = array(
        'AF' => 'Africa',
        'AN' => 'Antarctica',
        'AS' => 'Asia',
        'EU' => 'Europe',
        'OC' => 'Australia (Oceania)',
        'NA' => 'North America',
        'SA' => 'South America'
    );

    if (!filter_var($ip, FILTER_VALIDATE_IP) || !in_array($purpose, $support)) {
        return $output;
    }

    $url = 'https://www.geoplugin.net/json.gp?ip=' . urlencode($ip);

    $response = @file_get_contents($url);

    if ($response === false) {
        error_log('No fue posible consultar geoplugin para IP: ' . $ip);
        return $output;
    }

    $ipdat = json_decode($response);

    if (!$ipdat || !isset($ipdat->geoplugin_countryCode)) {
        return $output;
    }

    if (strlen(trim($ipdat->geoplugin_countryCode)) != 2) {
        return $output;
    }

    switch ($purpose) {
        case 'location':
            $continentCode = isset($ipdat->geoplugin_continentCode) ? strtoupper($ipdat->geoplugin_continentCode) : '';

            $output = array(
                'city' => isset($ipdat->geoplugin_city) ? $ipdat->geoplugin_city : '',
                'state' => isset($ipdat->geoplugin_regionName) ? $ipdat->geoplugin_regionName : '',
                'country' => isset($ipdat->geoplugin_countryName) ? $ipdat->geoplugin_countryName : '',
                'country_code' => isset($ipdat->geoplugin_countryCode) ? $ipdat->geoplugin_countryCode : '',
                'continent' => isset($continents[$continentCode]) ? $continents[$continentCode] : '',
                'continent_code' => isset($ipdat->geoplugin_continentCode) ? $ipdat->geoplugin_continentCode : '',
                'latitude' => isset($ipdat->geoplugin_latitude) ? $ipdat->geoplugin_latitude : '',
                'longitude' => isset($ipdat->geoplugin_longitude) ? $ipdat->geoplugin_longitude : ''
            );
            break;

        case 'address':
            $address = array();

            if (!empty($ipdat->geoplugin_countryName)) {
                $address[] = $ipdat->geoplugin_countryName;
            }

            if (!empty($ipdat->geoplugin_regionName)) {
                $address[] = $ipdat->geoplugin_regionName;
            }

            if (!empty($ipdat->geoplugin_city)) {
                $address[] = $ipdat->geoplugin_city;
            }

            $output = implode(', ', array_reverse($address));
            break;

        case 'city':
            $output = isset($ipdat->geoplugin_city) ? $ipdat->geoplugin_city : '';
            break;

        case 'state':
        case 'region':
            $output = isset($ipdat->geoplugin_regionName) ? $ipdat->geoplugin_regionName : '';
            break;

        case 'country':
            $output = isset($ipdat->geoplugin_countryName) ? $ipdat->geoplugin_countryName : '';
            break;

        case 'countrycode':
            $output = isset($ipdat->geoplugin_countryCode) ? $ipdat->geoplugin_countryCode : '';
            break;

        case 'latitude':
            $output = isset($ipdat->geoplugin_latitude) ? $ipdat->geoplugin_latitude : '';
            break;

        case 'longitude':
            $output = isset($ipdat->geoplugin_longitude) ? $ipdat->geoplugin_longitude : '';
            break;
    }

    return $output;
}