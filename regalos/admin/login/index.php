<?php
session_start();

require_once __DIR__ . '/../../config/env.php';

load_env(__DIR__ . '/../../.env');

$google_client_id = env_value('GOOGLE_CLIENT_ID', '');
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Fundación Las Rosas</title>

  <link href="../../assets/plugins/bootstrap/css/bootstrap.css" rel="stylesheet">
  <link href="../../assets/css/style.css" rel="stylesheet">
  <link rel="icon" href="../../assets/img/favicon.ico" type="image/x-icon">

  <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>

<body>
  <div class="container">
    <div class="theme-red">
      <br><br><br>

      <div class="row">
        <div class="col-md-6 col-md-offset-3">
          <div class="panel panel-login">
            <div class="panel-body">
              <div class="row">
                <div class="col-lg-12"><br>

                  <div class="form-group text-center">
                    <img src="../../assets/img/logofinal01.png" width="135" height="135" style="box-shadow: 0px 1px 3px #666666;">
                  </div>

                  <div class="form-group text-center">
                    <div class="row">
                      <div class="col-sm-6 col-sm-offset-3">
                        <div 
                          id="g_id_onload"
                          data-client_id="<?php echo htmlspecialchars($google_client_id, ENT_QUOTES, 'UTF-8'); ?>"
                          data-callback="handleCredentialResponse">
                        </div>

                        <div class="g_id_signin" data-type="standard" data-theme="filled_blue" data-shape="circle"></div>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>

    <script>
      function handleCredentialResponse(response) {
        fetch('validate.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            token: response.credential
          })
        })
        .then(function(response) {
          if (!response.ok) {
            throw new Error('Error de red al comunicarse con el servidor');
          }

          return response.json();
        })
        .then(function(data) {
          if (data.authenticated) {
            window.location.href = '../dashboard/';
          } else {
            alert(data.error ? 'Error en la autenticación: ' + data.error : 'Error en la autenticación');
          }
        })
        .catch(function(error) {
          alert('Error en la autenticación: ' + error.message);
        });
      }
    </script>
  </div>
</body>

</html>