<?php

// Manual load untuk file yang tidak bisa di-autoload
require_once __DIR__ . '/config/config.php';

// Manual load core class
require_once __DIR__ . '/core/App.php';
require_once __DIR__ . '/core/Controller.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/AuthMiddleware.php';
require_once __DIR__ . '/core/Csrf.php';
require_once __DIR__ . '/core/LoginThrottle.php';
require_once __DIR__ . '/core/Validator.php';
require_once __DIR__ . '/core/FileUploader.php';

// Autoload untuk controllers dan models
spl_autoload_register(function ($class) {
    $controllerPath = 'controllers/' . $class . '.php';
    $modelPath = 'models/' . $class . '.php';
    $corePath = 'core/' . $class . '.php';

    if (file_exists(__DIR__ . '/' . $controllerPath)) {
        require_once __DIR__ . '/' . $controllerPath;
    } elseif (file_exists(__DIR__ . '/' . $modelPath)) {
        require_once __DIR__ . '/' . $modelPath;
    } elseif (file_exists(__DIR__ . '/' . $corePath)) {
        require_once __DIR__ . '/' . $corePath;
    }
});
