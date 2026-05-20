<?php
function get_client_ip()
{
    if (getenv('HTTP_CLIENT_IP')) {
        return getenv('HTTP_CLIENT_IP');
    }

    if (getenv('HTTP_X_FORWARDED_FOR')) {
        $ips = explode(',', getenv('HTTP_X_FORWARDED_FOR'));
        return trim($ips[0]);
    }

    if (getenv('HTTP_X_FORWARDED')) {
        return getenv('HTTP_X_FORWARDED');
    }

    if (getenv('HTTP_FORWARDED_FOR')) {
        return getenv('HTTP_FORWARDED_FOR');
    }

    if (getenv('HTTP_FORWARDED')) {
        return getenv('HTTP_FORWARDED');
    }

    if (getenv('REMOTE_ADDR')) {
        return getenv('REMOTE_ADDR');
    }

    return 'UNKNOWN';
}

function ip_info($ip = null, $purpose = "location", $deep_detect = true)
{
    $output = null;

    if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
        $ip = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : '';

        if ($deep_detect) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $forwardedIps = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $forwardedIp = trim($forwardedIps[0]);

                if (filter_var($forwardedIp, FILTER_VALIDATE_IP)) {
                    $ip = $forwardedIp;
                }
            }

            if (isset($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            }
        }
    }

    $purpose = str_replace(array("name", "\n", "\t", " ", "-", "_"), null, strtolower(trim($purpose)));
    $support = array("country", "countrycode", "state", "region", "city", "location", "address", "latitude", "longitude");

    $continents = array(
        "AF" => "Africa",
        "AN" => "Antarctica",
        "AS" => "Asia",
        "EU" => "Europe",
        "OC" => "Australia (Oceania)",
        "NA" => "North America",
        "SA" => "South America"
    );

    if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
        $json = @file_get_contents("https://www.geoplugin.net/json.gp?ip=" . urlencode($ip));
        $ipdat = $json ? json_decode($json) : null;

        if ($ipdat && isset($ipdat->geoplugin_countryCode) && strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
            switch ($purpose) {
                case "location":
                    $continentCode = strtoupper($ipdat->geoplugin_continentCode);
                    $output = array(
                        "city" => isset($ipdat->geoplugin_city) ? $ipdat->geoplugin_city : '',
                        "state" => isset($ipdat->geoplugin_regionName) ? $ipdat->geoplugin_regionName : '',
                        "country" => isset($ipdat->geoplugin_countryName) ? $ipdat->geoplugin_countryName : '',
                        "country_code" => isset($ipdat->geoplugin_countryCode) ? $ipdat->geoplugin_countryCode : '',
                        "continent" => isset($continents[$continentCode]) ? $continents[$continentCode] : '',
                        "continent_code" => isset($ipdat->geoplugin_continentCode) ? $ipdat->geoplugin_continentCode : '',
                        "latitude" => isset($ipdat->geoplugin_latitude) ? $ipdat->geoplugin_latitude : '',
                        "longitude" => isset($ipdat->geoplugin_longitude) ? $ipdat->geoplugin_longitude : ''
                    );
                    break;

                case "address":
                    $address = array();

                    if (isset($ipdat->geoplugin_countryName) && strlen($ipdat->geoplugin_countryName) >= 1) {
                        $address[] = $ipdat->geoplugin_countryName;
                    }

                    if (isset($ipdat->geoplugin_regionName) && strlen($ipdat->geoplugin_regionName) >= 1) {
                        $address[] = $ipdat->geoplugin_regionName;
                    }

                    if (isset($ipdat->geoplugin_city) && strlen($ipdat->geoplugin_city) >= 1) {
                        $address[] = $ipdat->geoplugin_city;
                    }

                    $output = implode(", ", array_reverse($address));
                    break;

                case "city":
                    $output = isset($ipdat->geoplugin_city) ? $ipdat->geoplugin_city : '';
                    break;

                case "state":
                case "region":
                    $output = isset($ipdat->geoplugin_regionName) ? $ipdat->geoplugin_regionName : '';
                    break;

                case "country":
                    $output = isset($ipdat->geoplugin_countryName) ? $ipdat->geoplugin_countryName : '';
                    break;

                case "countrycode":
                    $output = isset($ipdat->geoplugin_countryCode) ? $ipdat->geoplugin_countryCode : '';
                    break;

                case "latitude":
                    $output = isset($ipdat->geoplugin_latitude) ? $ipdat->geoplugin_latitude : '';
                    break;

                case "longitude":
                    $output = isset($ipdat->geoplugin_longitude) ? $ipdat->geoplugin_longitude : '';
                    break;
            }
        }
    }

    return $output;
}
?>