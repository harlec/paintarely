<?php

use App\Services\Auth;

$usuarioSesion = Auth::usuario();
?>
<nav class="nav-usuario">
  <?php if ($usuarioSesion): ?>
    <a href="/perfil"><?= htmlspecialchars($usuarioSesion['nombre']) ?> · <?= (int) $usuarioSesion['monedas'] ?> monedas</a>
    <form method="POST" action="/logout" class="form-inline">
      <button type="submit">Salir</button>
    </form>
  <?php else: ?>
    <a href="/login">Iniciar sesión</a>
    <a href="/registro">Crear cuenta</a>
  <?php endif; ?>
</nav>
