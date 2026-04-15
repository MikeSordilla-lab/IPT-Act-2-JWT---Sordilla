<?php

declare(strict_types=1);

use App\Config\Database;
use App\Controllers\AuthController;
use App\Helpers\Response;
use App\Middleware\AuthMiddleware;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenvClass = 'Dotenv\\Dotenv';
if (file_exists(__DIR__ . '/../.env') && class_exists($dotenvClass)) {
    $dotenvClass::createImmutable(__DIR__ . '/..')->safeLoad();
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    $db = Database::connect();

    if ($method === 'POST' && $path === '/register') {
        AuthController::register($db);
        exit;
    }

    if ($method === 'POST' && $path === '/login') {
        AuthController::login($db);
        exit;
    }

    if ($method === 'GET' && $path === '/protected') {
        $payload = AuthMiddleware::validateToken($db);
        AuthController::protected($payload);
        exit;
    }

    if ($method === 'POST' && $path === '/logout') {
        $payload = AuthMiddleware::validateToken($db);
        AuthController::logout($db, $payload);
        exit;
    }

    Response::error(404, 'Endpoint not found.');
} catch (RuntimeException $e) {
    Response::error(401, $e->getMessage());
} catch (Throwable $e) {
    Response::error(500, 'Server error: ' . $e->getMessage());
}
