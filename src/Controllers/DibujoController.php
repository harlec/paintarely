<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\DibujoEnviado;
use App\Models\Plantilla;
use App\Services\Auth;

class DibujoController
{
    public function galeria(): void
    {
        $plantillas = Plantilla::listarTodas();
        require BASE_PATH . '/src/Views/galeria.php';
    }

    public function dibujar(int $id): void
    {
        $usuario = Auth::requerir();
        $plantilla = Plantilla::buscarPorId($id);
        if ($plantilla === null) {
            http_response_code(404);
            echo 'Plantilla no encontrada.';
            return;
        }
        require BASE_PATH . '/src/Views/dibujar.php';
    }

    public function guardar(): void
    {
        header('Content-Type: application/json');

        $usuarioId = Auth::id();
        if ($usuarioId === null) {
            http_response_code(401);
            echo json_encode(['error' => 'Debes iniciar sesión para guardar un dibujo.']);
            return;
        }

        $datos = json_decode(file_get_contents('php://input') ?: '', true);
        $imagenBase64 = $datos['imagen_base64'] ?? null;
        $plantillaId = (int) ($datos['plantilla_id'] ?? 0);

        if (!$imagenBase64 || !str_starts_with($imagenBase64, 'data:image/png;base64,')) {
            http_response_code(422);
            echo json_encode(['error' => 'Imagen inválida.']);
            return;
        }

        $binario = base64_decode(substr($imagenBase64, strlen('data:image/png;base64,')), true);
        if ($binario === false) {
            http_response_code(422);
            echo json_encode(['error' => 'No se pudo decodificar la imagen.']);
            return;
        }

        // Límite 2MB, según sección 6 del plan.
        if (strlen($binario) > 2 * 1024 * 1024) {
            http_response_code(413);
            echo json_encode(['error' => 'La imagen supera el tamaño máximo permitido (2MB).']);
            return;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($binario);
        if ($mime !== 'image/png') {
            http_response_code(422);
            echo json_encode(['error' => 'El archivo no es un PNG válido.']);
            return;
        }

        $directorio = BASE_PATH . "/public/uploads/dibujos_usuarios/{$usuarioId}";
        if (!is_dir($directorio) && !mkdir($directorio, 0755, true) && !is_dir($directorio)) {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo crear el directorio de subida.']);
            return;
        }

        $nombreArchivo = 'plantilla' . $plantillaId . '_' . time() . '.png';
        $rutaRelativa = "uploads/dibujos_usuarios/{$usuarioId}/{$nombreArchivo}";
        file_put_contents("{$directorio}/{$nombreArchivo}", $binario);

        $dibujoId = DibujoEnviado::crear($usuarioId, $plantillaId, $rutaRelativa);

        echo json_encode([
            'ok' => true,
            'dibujo_id' => $dibujoId,
            'ruta' => $rutaRelativa,
        ]);
    }
}
