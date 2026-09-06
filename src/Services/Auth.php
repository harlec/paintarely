<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Usuario;

class Auth
{
    public static function iniciar(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function iniciarSesion(int $usuarioId): void
    {
        self::iniciar();
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = $usuarioId;
    }

    public static function cerrarSesion(): void
    {
        self::iniciar();
        $_SESSION = [];
        session_destroy();
    }

    public static function id(): ?int
    {
        self::iniciar();
        return $_SESSION['usuario_id'] ?? null;
    }

    public static function usuario(): ?array
    {
        $id = self::id();
        return $id ? Usuario::buscarPorId($id) : null;
    }

    public static function autenticado(): bool
    {
        return self::id() !== null;
    }

    public static function requerir(): array
    {
        $usuario = self::usuario();
        if ($usuario === null) {
            header('Location: /login');
            exit;
        }
        return $usuario;
    }
}
