<?php
require_once 'FintocAPI.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['monto'];
    $order_id = $_POST['order_id'];
    $fintocAPI = new FintocAPI();
    $result = $fintocAPI->generateWidgetToken($amount, $order_id);
    require_once 'payment_widget.php';
}
?>