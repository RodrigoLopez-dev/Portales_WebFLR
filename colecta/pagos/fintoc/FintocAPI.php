<?php

require_once __DIR__ . '/../../config/env.php';

load_env(__DIR__ . '/../../.env');

class FintocAPI
{
    private $url;
    private $apiKey;

    public function __construct()
    {
        $this->url = env_value('FINTOC_API_URL', 'https://api.fintoc.com/v1/payment_intents');
        $this->apiKey = env_value('FINTOC_API_KEY', '');

        if (trim($this->apiKey) === '') {
            error_log('FintocAPI: FINTOC_API_KEY no configurada.');
        }
    }

    public function generateWidgetToken($amount, $orderId)
    {
        $amount = (int) $amount;
        $orderId = trim((string) $orderId);

        if ($amount <= 0 || $orderId === '') {
            error_log('FintocAPI: monto/order_id inválido.');
            return array(
                'widget_token' => '',
                'id' => ''
            );
        }

        if (trim($this->apiKey) === '') {
            error_log('FintocAPI: no se puede generar widget_token sin FINTOC_API_KEY.');
            return array(
                'widget_token' => '',
                'id' => ''
            );
        }

        $portal = trim(env_value('APP_NAME', ''), '/');

        $data = [
            "amount" => $amount,
            "currency" => "CLP",
            "metadata" => [
                "order_id" => (string) $orderId,
                "portal" => $portal
            ]
        ];

        $payload = json_encode($data);

        if ($payload === false) {
            error_log('FintocAPI: error al codificar payload JSON.');
            return array(
                'widget_token' => '',
                'id' => ''
            );
        }

        $ch = curl_init($this->url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: ' . $this->apiKey,
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($response === false || $curlError !== '') {
            error_log('FintocAPI CURL ERROR: ' . $curlError);
            return array(
                'widget_token' => '',
                'id' => ''
            );
        }

        $responseObj = json_decode($response);

        if ($httpCode < 200 || $httpCode >= 300 || !$responseObj) {
            error_log('FintocAPI HTTP ERROR ' . $httpCode . ' RESPONSE: ' . $response);
            return array(
                'widget_token' => '',
                'id' => ''
            );
        }

        return array(
            'widget_token' => isset($responseObj->widget_token) ? $responseObj->widget_token : '',
            'id' => isset($responseObj->id) ? $responseObj->id : ''
        );
    }
}