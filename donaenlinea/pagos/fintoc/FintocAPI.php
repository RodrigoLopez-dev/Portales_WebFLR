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
        $data = array(
            "amount" => $amount,
            "currency" => "CLP",
            "metadata" => array(
                "order_id" => $order_id,
                "origen" => "portal"
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

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('Error CURL Fintoc: ' . $error);
        }

        curl_close($ch);

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