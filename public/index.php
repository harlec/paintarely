<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

use App\Controllers\AuthController;
use App\Controllers\DibujoController;
use App\Controllers\UsuarioController;

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = rtrim($uri ?: '/', '/') ?: '/';
$metodo = $_SERVER['REQUEST_METHOD'];

try {
    if ($uri === '/' && $metodo === 'GET') {
        (new DibujoController())->galeria();
    } elseif (preg_match('#^/plantillas/(\d+)$#', $uri, $m) && $metodo === 'GET') {
        (new DibujoController())->dibujar((int) $m[1]);
    } elseif ($uri === '/dibujos/guardar' && $metodo === 'POST') {
        (new DibujoController())->guardar();
    } elseif ($uri === '/registro' && $metodo === 'GET') {
        (new AuthController())->registroForm();
    } elseif ($uri === '/registro' && $metodo === 'POST') {
        (new AuthController())->registrar();
    } elseif ($uri === '/login' && $metodo === 'GET') {
        (new AuthController())->loginForm();
    } elseif ($uri === '/login' && $metodo === 'POST') {
        (new AuthController())->iniciarSesion();
    } elseif ($uri === '/logout' && $metodo === 'POST') {
        (new AuthController())->cerrarSesion();
    } elseif ($uri === '/perfil' && $metodo === 'GET') {
        (new UsuarioController())->perfil();
    } else {
        responder404();
    }
} catch (Throwable $e) {
    http_response_code(500);
    if (env('APP_DEBUG', 'false') === 'true') {
        echo '<pre>' . htmlspecialchars((string) $e) . '</pre>';
    } else {
        echo 'Ocurrió un error inesperado.';
    }
}

function responder404(): void
{
    http_response_code(404);
    echo 'Página no encontrada.';
}
