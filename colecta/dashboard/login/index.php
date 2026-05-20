<?php
// Include GP config file && User class
include_once 'gpConfig.php';
include_once 'User.php';

$appBaseUrl = getenv('APP_BASE_URL');
$appName = getenv('APP_NAME');

if (!$appBaseUrl) {
    die('Falta APP_BASE_URL en .env');
}

if (!$appName) {
    die('Falta APP_NAME en .env');
}

$appUrl = rtrim($appBaseUrl, '/') . '/' . trim($appName, '/');

if (isset($_GET['code'])) {
    $gClient->authenticate($_GET['code']);
    $_SESSION['token'] = $gClient->getAccessToken();
    header('Location: ' . filter_var($redirectURL, FILTER_SANITIZE_URL));
    exit;
}

if (isset($_SESSION['token'])) {
    try {
        $gClient->setAccessToken($_SESSION['token']);
    } catch (Exception $e) {
        unset($_SESSION['token']);
        unset($_SESSION['userData']);
        header('Location: ' . filter_var($redirectURL, FILTER_SANITIZE_URL));
        exit;
    }
}

if ($gClient->getAccessToken()) {
    // Get user profile data from Google
    $gpUserProfile = $google_oauthV2->userinfo->get();

    // Initialize User class
    $user = new User();

    // Insert or update user data to the database
    $gpUserData = array(
        'oauth_provider' => 'google',
        'oauth_uid' => $gpUserProfile['id'],
        'name' => $gpUserProfile['given_name'],
        'lastname' => $gpUserProfile['family_name'],
        'mail' => $gpUserProfile['email'],
        'locale' => $gpUserProfile['locale'],
        'picture' => $gpUserProfile['picture']
    );

    $userData = $user->checkUser($gpUserData);

    // Storing user data into session
    $_SESSION['userData'] = $userData;

    if (!empty($userData)) {
        header('Location: ' . $appUrl . '/dashboard/dashboard/');
        exit;
    } else {
        $output = '<h3 style="color:red">Se ha producido un error, inténtelo más tarde</h3>';
    }
} else {
    $authUrl = $gClient->createAuthUrl();
    $output = '<a href="' . filter_var($authUrl, FILTER_SANITIZE_URL) . '" class="btn">Clic aquí para inicio con @flrosas.cl</a>';
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Dashboard Colecta FLR</title>
    <link href="plugins/bootstrap/css/bootstrap.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <style>
        .btn {
            -webkit-border-radius: 0;
            -moz-border-radius: 0;
            border-radius: 0px;
            -webkit-box-shadow: 0px 1px 3px #666666;
            -moz-box-shadow: 0px 1px 3px #666666;
            box-shadow: 0px 1px 3px #666666;
            font-family: Arial;
            color: #ffffff;
            font-size: 20px;
            background: #af0a3d;
            padding: 10px 30px 10px 30px;
            text-decoration: none;
        }

        .btn:hover {
            background: #7a101e;
            text-decoration: none;
            color: #ffffff;
        }

        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }

        body {
            margin: 50px auto;
            text-align: center;
            width: 800px;
        }

        h1 {
            font-family: 'Passion One';
            font-size: 2rem;
            text-transform: uppercase;
        }

        label {
            width: 150px;
            display: inline-block;
            text-align: left;
            font-size: 1.5rem;
            font-family: 'Lato';
        }

        input {
            border: 2px solid #ccc;
            font-size: 1.5rem;
            font-weight: 100;
            font-family: 'Lato';
            padding: 10px;
        }

        form {
            margin: 25px auto;
            padding: 20px;
            border: 5px solid #ccc;
            width: 500px;
            background: #eee;
        }

        div.form-element {
            margin: 20px 0;
        }

        button {
            padding: 10px;
            font-size: 1.5rem;
            font-family: 'Lato';
            font-weight: 100;
            background: yellowgreen;
            color: white;
            border: none;
        }

        p.success,
        p.error {
            color: white;
            font-family: lato;
            background: yellowgreen;
            display: inline-block;
            padding: 2px 10px;
        }

        p.error {
            background: orangered;
        }
    </style>
</head>

<body>
    <br><br><br><br><br>
    <div class="col-sm-12">
        <?php echo $output; ?>
        <br><br>
    </div>
</body>

</html>