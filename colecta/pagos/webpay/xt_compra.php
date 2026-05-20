<?php
require("../../conexion/configuracion.php");
require("../../enigma.php");

$eni = new Enigma();

$appUrl = rtrim(getenv('APP_URL'), '/');
if ($appUrl === '') {
  $appUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/colecta';
}

$responsePath = getenv('WEBPAY_RESPONSE_PATH');
if ($responsePath === '') {
  $responsePath = '/pagos/webpay/respuestaWebPay.php';
}

$TbkApiKeyId = getenv('WEBPAY_API_KEY_ID');
$TbkApiKeySecret = getenv('WEBPAY_API_KEY_SECRET');
$WEBPAY_BASE = getenv('WEBPAY_BASE_URL');

if ($WEBPAY_BASE === '') {
  $WEBPAY_BASE = 'https://webpay3g.transbank.cl';
}

if ($_GET) {
  $eni->decode_get2($_SERVER["REQUEST_URI"]);
}

if (!isset($_GET['id']) || !isset($_GET['monto'])) {
  header("Location: " . $appUrl . "/");
  exit();
}

$id_encoded = $eni->encode_this('id=' . $_GET['id'] . '&monto=' . $_GET['monto']);
$orden = $_GET['id'];
$monto = intval($_GET['monto']);

if (!isset($_POST['token_ws'])) {
  header("Location: " . $appUrl . $responsePath . "?" . $id_encoded);
  exit();
}

if ($TbkApiKeyId === '' || $TbkApiKeySecret === '') {
  error_log("WEBPAY config incompleta en xt_compra.php");
  header("Location: " . $appUrl . $responsePath . "?" . $id_encoded);
  exit();
}

$token = $_POST['token_ws'];

function webpay_request($base, $endpoint, $method, $apiKeyId, $apiKeySecret, $payload = null)
{
  $ch = curl_init();
  $url = $base . $endpoint;

  $headers = array(
    'Tbk-Api-Key-Id: ' . $apiKeyId,
    'Tbk-Api-Key-Secret: ' . $apiKeySecret,
    'Content-Type: application/json',
  );

  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  if ($payload !== null) {
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
  }

  $resp = curl_exec($ch);
  $err = curl_error($ch);
  $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($resp === false) {
    return (object) array("ok" => false, "http" => $http, "error" => $err, "raw" => null);
  }

  $json = json_decode($resp);

  return (object) array(
    "ok" => ($http >= 200 && $http < 300),
    "http" => $http,
    "error" => null,
    "raw" => $resp,
    "json" => $json
  );
}

$endpoint = "/rswebpaytransaction/api/webpay/v1.0/transactions/" . urlencode($token);
$res = webpay_request($WEBPAY_BASE, $endpoint, "PUT", $TbkApiKeyId, $TbkApiKeySecret, null);

$tbk_respuesta = 1;
$tbk_codigo_autorizacion = '';
$tbk_monto = $monto;
$tbk_tipo_pago = '';
$tbk_numero_final_tarjeta = '';
$tbk_fecha_transaccion = '';
$tbk_fecha_contable = '';
$tbk_numero_cuotas = '';

if ($res->ok && isset($res->json->status) && $res->json->status === 'AUTHORIZED') {
  $tbk_respuesta = 0;
  $tbk_codigo_autorizacion = isset($res->json->authorization_code) ? $res->json->authorization_code : '';
  $tbk_monto = isset($res->json->amount) ? $res->json->amount : $monto;
  $tbk_tipo_pago = isset($res->json->payment_type_code) ? $res->json->payment_type_code : '';

  $orden_update = $db->real_escape_string($orden);
  $sql_update = "UPDATE donaciones SET estado_id = 1 WHERE id = '{$orden_update}'";

  if (!$db->query($sql_update)) {
    error_log("ERROR UPDATE donaciones: " . $db->error);
  }

  $tbk_numero_final_tarjeta = (isset($res->json->card_detail) && isset($res->json->card_detail->card_number))
    ? $res->json->card_detail->card_number
    : '';

  $tbk_fecha_transaccion = isset($res->json->transaction_date) ? $res->json->transaction_date : '';
  $tbk_fecha_contable = isset($res->json->accounting_date) ? $res->json->accounting_date : '';
  $tbk_numero_cuotas = isset($res->json->installments_number) ? $res->json->installments_number : '';
}

$ts = time();

if (!empty($tbk_fecha_transaccion)) {
  $tmp = strtotime($tbk_fecha_transaccion);
  if ($tmp !== false) {
    $ts = $tmp;
  }
}

$fecha_tr = date('Y-m-d', $ts);
$hora_tr = date('His', $ts);
$tbk_ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

$token_db = $db->real_escape_string($token);
$orden_db = $db->real_escape_string($orden);
$resp_db = $db->real_escape_string(strval($tbk_respuesta));
$aut_db = $db->real_escape_string($tbk_codigo_autorizacion);
$monto_db = $db->real_escape_string(strval($tbk_monto));
$tipo_db = $db->real_escape_string($tbk_tipo_pago);
$card_db = $db->real_escape_string($tbk_numero_final_tarjeta);
$fecha_contable_db = $db->real_escape_string($tbk_fecha_contable);
$cuotas_db = $db->real_escape_string($tbk_numero_cuotas);
$fecha_tr_db = $db->real_escape_string($fecha_tr);
$hora_tr_db = $db->real_escape_string($hora_tr);
$ip_db = $db->real_escape_string($tbk_ip);

$sql = "
INSERT IGNORE INTO donaciones_webpay
(
  tbk_tipo_transaccion,
  tbk_respuesta,
  tbk_orden_compra,
  tbk_id_sesion,
  tbk_codigo_autorizacion,
  tbk_monto,
  tbk_numero_tarjeta,
  tbk_numero_final_tarjeta,
  tbk_fecha_expiracion,
  tbk_fecha_contable,
  tbk_fecha_transaccion,
  tbk_hora_transaccion,
  tbk_id_transaccion,
  tbk_tipo_pago,
  tbk_numero_cuotas,
  tbk_mac,
  tbk_monto_cuota,
  tbk_ip,
  token
)
VALUES
(
  'REST_WEBPAY_PLUS',
  '{$resp_db}',
  '{$orden_db}',
  '{$orden_db}',
  '{$aut_db}',
  '{$monto_db}',
  NULL,
  '{$card_db}',
  NULL,
  '{$fecha_contable_db}',
  '{$fecha_tr_db}',
  '{$hora_tr_db}',
  '{$token_db}',
  '{$tipo_db}',
  '{$cuotas_db}',
  NULL,
  NULL,
  '{$ip_db}',
  '{$token_db}'
)
";

if (!$db->query($sql)) {
  error_log("ERROR INSERT donaciones_webpay: " . $db->error);
}

$dest = $appUrl . $responsePath . "?" . $id_encoded;
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <title>Procesando…</title>
</head>

<body>
  <form id="f" action="<?php echo htmlspecialchars($dest, ENT_QUOTES, 'UTF-8'); ?>" method="post">
    <input type="hidden" name="token_ws" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
  </form>
  <script>document.getElementById('f').submit();</script>
</body>

</html>