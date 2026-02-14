<?php


// 🔥 CORS HEADERS — REQUIRED FOR BROWSER
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");

// 🔥 HANDLE PREFLIGHT REQUEST
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/jwt.php';

require_once __DIR__ . '/../core/Request.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Router.php';

require_once __DIR__ . '/../helpers/PasswordHelper.php';

$router = new Router();

require __DIR__ . '/../modules/auth/routes.php';
require __DIR__ . '/../modules/customers/routes.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (strpos($uri, '/api/') !== false) {
    $uri = substr($uri, strpos($uri, '/api/'));
}

$uri = urldecode($uri);   // decode %0A
$uri = trim($uri);        // remove whitespace/newlines

$router->resolve($method, $uri);




echo password_hash("123456", PASSWORD_BCRYPT);
