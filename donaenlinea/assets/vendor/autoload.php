<?php

// Verifica si el archivo de autoload se está cargando desde la ubicación correcta
if (!file_exists(__DIR__ . '/composer/autoload_real.php')) {
    throw new RuntimeException('Autoload file not found. Did you run "composer install"?');
}

// Incluye el archivo que realmente configura el autoloader para las clases del proyecto
require_once __DIR__ . '/composer/autoload_real.php';

return ComposerAutoloaderInit::getLoader();
