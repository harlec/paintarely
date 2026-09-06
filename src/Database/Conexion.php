<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;

class Conexion
{
    private static ?PDO $instancia = null;

    public static function obtener(): PDO
    {
        if (self::$instancia === null) {
            $host = env('DB_HOST', '127.0.0.1');
            $port = env('DB_PORT', '3306');
            $db   = env('DB_NAME', 'paintarely');
            $user = env('DB_USER', 'root');
            $pass = env('DB_PASS', '');

            $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

            try {
                self::$instancia = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                throw new PDOException('No se pudo conectar a la base de datos: ' . $e->getMessage(), (int) $e->getCode());
            }
        }

        return self::$instancia;
    }
}
