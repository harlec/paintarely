<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Conexion;

class Usuario
{
    public static function crear(string $nombre, string $email, string $password): int
    {
        $pdo = Conexion::obtener();
        $stmt = $pdo->prepare(
            'INSERT INTO usuarios (nombre, email, password_hash) VALUES (?, ?, ?)'
        );
        $stmt->execute([$nombre, $email, password_hash($password, PASSWORD_DEFAULT)]);
        return (int) $pdo->lastInsertId();
    }

    public static function buscarPorEmail(string $email): ?array
    {
        $pdo = Conexion::obtener();
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    public static function buscarPorId(int $id): ?array
    {
        $pdo = Conexion::obtener();
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
        $stmt->execute([$id]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    public static function existeEmail(string $email): bool
    {
        return self::buscarPorEmail($email) !== null;
    }
}
