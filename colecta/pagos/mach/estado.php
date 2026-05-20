<?php

require_once __DIR__ . '/../../conexion/configuracion.php';
include __DIR__ . '/key/authorization.php';

$machBaseUrl = getenv('MACH_API_BASE_URL');
if ($machBaseUrl === '') {
  $machBaseUrl = 'https://biz.soymach.com';
}

$query = "
    SELECT token, estado_pago_id
    FROM donaciones_mach
    WHERE estado_pago_id = 2
      AND token IS NOT NULL
      AND token <> ''
";

$result = $db->query($query);

if (!$result) {
  error_log("ERROR SELECT donaciones_mach: " . $db->error);
  exit("Error consultando donaciones MACH");
}

while ($row = $result->fetch_assoc()) {
  $token = $row['token'];

  $curl = curl_init();

  curl_setopt_array(
    $curl,
    array(
      CURLOPT_HTTPHEADER => $headers,
      CURLOPT_HEADER => false,
      CURLOPT_URL => rtrim($machBaseUrl, '/') . "/payments/" . urlencode($token),
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_NONE,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_RETURNTRANSFER => true,
    )
  );

  $response = curl_exec($curl);
  $curlError = curl_error($curl);
  $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  curl_close($curl);

  if ($response === false || $curlError !== '') {
    error_log("ERROR MACH CURL estado.php: " . $curlError);
    echo "Error consultando token: " . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . "<br>";
    continue;
  }

  $array = json_decode($response, true);

  if ($httpCode < 200 || $httpCode >= 300 || !is_array($array)) {
    error_log("ERROR MACH API estado.php HTTP " . $httpCode . " RESPONSE: " . $response);
    echo "Respuesta inválida para token: " . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . "<br>";
    continue;
  }

  $status = isset($array['status']) ? $array['status'] : '';
  $amount = isset($array['amount']) ? $array['amount'] : '';
  $customer_id = isset($array['metadata']['customer_id']) ? $array['metadata']['customer_id'] : '';

  if ($customer_id === '') {
    echo "Sin customer_id para token: " . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . "<br>";
    continue;
  }

  $customer_id_safe = $db->real_escape_string($customer_id);

  $query2 = "SELECT estado_pago_id FROM donaciones_mach WHERE orden_compra = '$customer_id_safe'";
  $result2 = $db->query($query2);

  if (!$result2) {
    error_log("ERROR SELECT estado_pago_id MACH: " . $db->error);
    continue;
  }

  $row2 = $result2->fetch_row();
  $estado_pago_id = isset($row2[0]) ? $row2[0] : '';

  echo 'orden_id: ' . htmlspecialchars($customer_id, ENT_QUOTES, 'UTF-8') .
    ' | estado web: ' . htmlspecialchars($estado_pago_id, ENT_QUOTES, 'UTF-8') .
    ' | estado mach: ' . htmlspecialchars($status, ENT_QUOTES, 'UTF-8') .
    ' | monto: ' . htmlspecialchars($amount, ENT_QUOTES, 'UTF-8');

  if ($status === 'CONFIRMED') {
    $sql = "UPDATE donaciones_mach SET estado_pago_id = 1 WHERE orden_compra = '$customer_id_safe'";
    $updateMach = $db->query($sql);

    if ($updateMach) {
      echo ' | actualizada tabla donaciones_mach';
    } else {
      echo ' | no se actualizó donaciones_mach';
      error_log("ERROR UPDATE donaciones_mach: " . $db->error);
    }

    $sql = "UPDATE donaciones SET estado_id = 1 WHERE id = '$customer_id_safe'";
    $updateMachDona = $db->query($sql);

    if ($updateMachDona) {
      echo ' | actualizada tabla donaciones';
    } else {
      echo ' | no se actualizó donaciones';
      error_log("ERROR UPDATE donaciones: " . $db->error);
    }
  }

  echo '<br>';
}
?>