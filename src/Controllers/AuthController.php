<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Usuario;
use App\Services\Auth;

class AuthController
{
    public function registroForm(): void
    {
        if (Auth::autenticado()) {
            header('Location: /perfil');
            return;
        }
        $errores = [];
        require BASE_PATH . '/src/Views/registro.php';
    }

    public function registrar(): void
    {
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim(strtolower($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $errores = [];
        if ($nombre === '') {
            $errores[] = 'El nombre es obligatorio.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El email no es válido.';
        }
        if (strlen($password) < 8) {
            $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
        }
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && Usuario::existeEmail($email)) {
            $errores[] = 'Ya existe una cuenta con ese email.';
        }

        if (!empty($errores)) {
            require BASE_PATH . '/src/Views/registro.php';
            return;
        }

        $usuarioId = Usuario::crear($nombre, $email, $password);
        Auth::iniciarSesion($usuarioId);
        header('Location: /perfil');
    }

    public function loginForm(): void
    {
        if (Auth::autenticado()) {
            header('Location: /perfil');
            return;
        }
        $errores = [];
        require BASE_PATH . '/src/Views/login.php';
    }

    public function iniciarSesion(): void
    {
        $email = trim(strtolower($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $usuario = Usuario::buscarPorEmail($email);
        if (!$usuario || !password_verify($password, $usuario['password_hash'])) {
            $errores = ['Email o contraseña incorrectos.'];
            require BASE_PATH . '/src/Views/login.php';
            return;
        }

        Auth::iniciarSesion((int) $usuario['id']);
        header('Location: /perfil');
    }

    public function cerrarSesion(): void
    {
        Auth::cerrarSesion();
        header('Location: /');
    }
}
