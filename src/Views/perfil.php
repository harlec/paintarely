<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Paintarely - Mi perfil</title>
<link rel="stylesheet" href="/assets/css/estilos.css">
</head>
<body>
<header class="cabecera">
  <h1><?= htmlspecialchars($usuario['nombre']) ?></h1>
  <p><?= (int) $usuario['monedas'] ?> monedas</p>
  <a href="/" class="volver">&larr; Volver a la galería</a>
</header>

<main class="galeria">
  <?php if (empty($dibujos)): ?>
    <p class="vacio">Todavía no has guardado ningún dibujo.</p>
  <?php else: ?>
    <?php foreach ($dibujos as $dibujo): ?>
      <div class="tarjeta">
        <img src="/<?= htmlspecialchars($dibujo['imagen_path']) ?>" alt="<?= htmlspecialchars($dibujo['plantilla_nombre']) ?>">
        <span class="tarjeta-nombre"><?= htmlspecialchars($dibujo['plantilla_nombre']) ?></span>
        <span class="tarjeta-nivel"><?= htmlspecialchars($dibujo['fecha_envio']) ?></span>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</main>
</body>
</html>
