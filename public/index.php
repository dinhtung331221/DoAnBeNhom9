<?php

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Controllers\ApiController;
use App\Controllers\AuthController;
use App\Controllers\PageController;
use App\Core\Request;

$request = new Request();
$uriPath = parse_url(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/', PHP_URL_PATH);
$scriptDirectory = str_replace('\\', '/', dirname(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '/'));
if ($scriptDirectory !== '/' && $scriptDirectory !== '.' && strpos($uriPath, $scriptDirectory) === 0) {
    $uriPath = substr($uriPath, strlen($scriptDirectory));
}
$path = trim($uriPath, '/');
$method = $request->method();

$pages = new PageController();
$auth = new AuthController();

if (strpos($path, 'api/') === 0) {
    (new ApiController())->dispatch(substr($path, 4), $request);
    exit;
}

$routes = array(
    'GET' => array(
        '' => array($pages, 'home'),
        'login' => array($auth, 'loginForm'),
        'register' => array($auth, 'registerForm'),
        'dashboard' => array($pages, 'dashboard'),
        'admin/categories' => array($pages, 'categories'),
        'admin/products' => array($pages, 'products'),
        'admin/tables' => array($pages, 'tables'),
        'admin/qr' => array($pages, 'qrCodes'),
        'menu' => array($pages, 'menu'),
        'cart' => array($pages, 'cart'),
        'kitchen' => array($pages, 'kitchen'),
    ),
    'POST' => array(
        'login' => array($auth, 'login'),
        'register' => array($auth, 'register'),
        'logout' => array($auth, 'logout'),
    ),
);

if (isset($routes[$method][$path])) {
    call_user_func($routes[$method][$path], $request);
    exit;
}

http_response_code(404);
require BASE_PATH . '/app/Views/errors/404.php';

