<?php

require_once __DIR__ . '/../../config/env.php';

load_env(__DIR__ . '/../../.env');

class FintocAPI
{
    private $url;
    private $api_key;

    public function __construct()
    {
        $this->url = env_value('FINTOC_API_URL', 'https://api.fintoc.com/v1/payment_intents');
        $this->api_key = env_value('FINTOC_API_KEY', '');

        if (empty($this->api_key)) {
            throw new Exception('FINTOC_API_KEY no está configurada.');
        }
    }

    public function generateWidgetToken($amount, $order_id)
    {
        $appBaseUrl = rtrim(env_value('APP_BASE_URL', ''), '/');
        $appName = trim(env_value('APP_NAME', ''), '/');

        if ($appBaseUrl === '') {
            throw new Exception('APP_BASE_URL no está configurada.');
        }

        $appUrl = $appName !== '' ? $appBaseUrl . '/' . $appName : $appBaseUrl;

        $data = array(
            "amount" => $amount,
            "currency" => "CLP",
            "webhook_url" => $appUrl . '/pagos/fintoc/webhook.php',
            "metadata" => array(
                "order_id" => $order_id,
                "origen" => $appName !== '' ? $appName : 'portal'
            )
        );

        $ch = curl_init($this->url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: ' . $this->api_key,
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);

        if (env_value('APP_DEBUG', 'false') === 'true') {
            error_log('FINTOC CREATE RESPONSE: ' . $response);
        }

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('Error CURL Fintoc: ' . $error);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new Exception('Error HTTP Fintoc ' . $httpCode . ': ' . $response);
        }

        $responseObj = json_decode($response);

        if (!$responseObj || !isset($responseObj->widget_token) || !isset($responseObj->id)) {
            throw new Exception('Respuesta inválida desde Fintoc: ' . $response);
        }

        return array(
            'widget_token' => $responseObj->widget_token,
            'id' => $responseObj->id
        );
    }
}