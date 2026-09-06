<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Conexion;

class DibujoEnviado
{
    public static function crear(int $usuarioId, int $plantillaId, string $imagenPath): int
    {
        $pdo = Conexion::obtener();
        $stmt = $pdo->prepare(
            'INSERT INTO dibujos_enviados (usuario_id, plantilla_id, imagen_path) VALUES (?, ?, ?)'
        );
        $stmt->execute([$usuarioId, $plantillaId, $imagenPath]);
        return (int) $pdo->lastInsertId();
    }

    public static function listarPorUsuario(int $usuarioId): array
    {
        $pdo = Conexion::obtener();
        $stmt = $pdo->prepare(
            'SELECT d.id, d.imagen_path, d.fecha_envio, p.nombre AS plantilla_nombre
             FROM dibujos_enviados d
             JOIN plantillas p ON p.id = d.plantilla_id
             WHERE d.usuario_id = ?
             ORDER BY d.fecha_envio DESC'
        );
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll();
    }
}
