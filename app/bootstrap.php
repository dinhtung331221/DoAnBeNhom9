<?php

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/app/Core/Env.php';
Env::load(BASE_PATH . '/.env');

function env($key, $default = null)
{
    return Env::get($key, $default);
}

$appConfig = require BASE_PATH . '/config/app.php';
date_default_timezone_set($appConfig['timezone']);

if (session_status() === PHP_SESSION_NONE) {
    session_name('tabletap_session');
    session_set_cookie_params(0, '/', '', !empty($_SERVER['HTTPS']), true);
    session_start();
}

spl_autoload_register(function ($class) {
    $prefixes = array('App\\' => BASE_PATH . '/app/');
    foreach ($prefixes as $prefix => $baseDirectory) {
        if (strpos($class, $prefix) !== 0) {
            continue;
        }
        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDirectory . str_replace('\\', '/', $relativeClass) . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
});

set_exception_handler(function ($exception) use ($appConfig) {
    error_log($exception->__toString());
    $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    if (strpos($uri, '/api/') !== false) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array(
            'success' => false,
            'message' => !empty($appConfig['debug']) ? $exception->getMessage() : 'He thong dang ban. Vui long thu lai.',
        ));
        return;
    }
    http_response_code(500);
    echo !empty($appConfig['debug'])
        ? '<pre>' . htmlspecialchars($exception->__toString(), ENT_QUOTES, 'UTF-8') . '</pre>'
        : 'He thong dang ban. Vui long thu lai.';
});

function app_config($key = null)
{
    static $config;
    if ($config === null) {
        $config = require BASE_PATH . '/config/app.php';
    }
    return $key === null ? $config : (isset($config[$key]) ? $config[$key] : null);
}

function url($path = '')
{
    return app_config('url') . ($path === '' ? '' : '/' . ltrim($path, '/'));
}

function asset($path)
{
    return url($path);
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrf_token()
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function flash($key, $value = null)
{
    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }
    $message = isset($_SESSION['_flash'][$key]) ? $_SESSION['_flash'][$key] : null;
    unset($_SESSION['_flash'][$key]);
    return $message;
}

