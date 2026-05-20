<?php

session_start();

unset($_SESSION['token']);
unset($_SESSION['access_token']);
unset($_SESSION['userData']);

session_destroy();

header('Location: ./');
exit;