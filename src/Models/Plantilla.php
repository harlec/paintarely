<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Conexion;

class Plantilla
{
    public static function listarTodas(): array
    {
        $pdo = Conexion::obtener();
        return $pdo->query('SELECT id, nombre, nivel_dificultad, svg_path FROM plantillas ORDER BY creado_en DESC')
            ->fetchAll();
    }

    public static function buscarPorId(int $id): ?array
    {
        $pdo = Conexion::obtener();
        $stmt = $pdo->prepare('SELECT id, nombre, nivel_dificultad, svg_path, zonas_color FROM plantillas WHERE id = ?');
        $stmt->execute([$id]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }
}
