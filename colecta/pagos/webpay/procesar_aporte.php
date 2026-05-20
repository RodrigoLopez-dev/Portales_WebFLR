<?php
require("../../enigma.php");
require("../../conexion/configuracion.php");

$eni = new Enigma();

$appUrl = rtrim(getenv('APP_URL'), '/');
if ($appUrl === '') {
  $appUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/colecta';
}

$returnPath = getenv('WEBPAY_RETURN_PATH');
if ($returnPath === '') {
  $returnPath = '/pagos/webpay/xt_compra.php';
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

if (!$_GET) {
  die("Sin parámetros");
}

$eni->decode_get2($_SERVER["REQUEST_URI"]);

if (!isset($_GET['id']) || !isset($_GET['monto'])) {
  die("Faltan parámetros");
}

$orden = $_GET['id'];
$monto = intval($_GET['monto']);

if ($monto <= 0) {
  die("Monto inválido");
}

if ($TbkApiKeyId === '' || $TbkApiKeySecret === '') {
  error_log("WEBPAY config incompleta en procesar_aporte.php");
  header("Location: " . $appUrl . $responsePath);
  exit();
}

$id_encoded = $eni->encode_this('id=' . $_GET['id'] . '&monto=' . $_GET['monto']);

$return_url = $appUrl . $returnPath . "?" . $id_encoded;

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
    return (object) array(
      "ok" => false,
      "http" => $http,
      "error" => $err,
      "raw" => null
    );
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

$data = json_encode(array(
  "buy_order" => strval($orden),
  "session_id" => strval($orden),
  "amount" => $monto,
  "return_url" => $return_url
));

$endpoint = "/rswebpaytransaction/api/webpay/v1.0/transactions";

$res = webpay_request(
  $WEBPAY_BASE,
  $endpoint,
  "POST",
  $TbkApiKeyId,
  $TbkApiKeySecret,
  $data
);

if (!$res->ok || !isset($res->json->url) || !isset($res->json->token)) {
  error_log("ERROR WEBPAY INIT: HTTP " . $res->http . " RAW: " . $res->raw);
  header("Location: " . $appUrl . $responsePath . "?" . $id_encoded);
  exit();
}

$url_tbk = $res->json->url;
$token = $res->json->token;
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <title>Redirigiendo a Webpay…</title>
</head>

<body>
  <form id="tbk" action="<?php echo htmlspecialchars($url_tbk, ENT_QUOTES, 'UTF-8'); ?>" method="post">
    <input type="hidden" name="token_ws" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
  </form>

  <script>
    document.getElementById('tbk').submit();
  </script>
</body>

</html>