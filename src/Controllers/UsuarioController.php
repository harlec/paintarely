<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\DibujoEnviado;
use App\Services\Auth;

class UsuarioController
{
    public function perfil(): void
    {
        $usuario = Auth::requerir();
        $dibujos = DibujoEnviado::listarPorUsuario((int) $usuario['id']);
        require BASE_PATH . '/src/Views/perfil.php';
    }
}
