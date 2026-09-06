<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Paintarely - Galería de plantillas</title>
<link rel="stylesheet" href="/assets/css/estilos.css">
</head>
<body>
<header class="cabecera">
  <h1>Paintarely</h1>
  <p>Escoge una plantilla para empezar a dibujar</p>
  <?php require BASE_PATH . '/src/Views/parciales/nav.php'; ?>
</header>

<main class="galeria">
  <?php if (empty($plantillas)): ?>
    <p class="vacio">
      Todavía no hay plantillas cargadas. Inserta filas en la tabla <code>plantillas</code>
      (ver <code>database/schema.sql</code>) apuntando a un SVG en
      <code>public/assets/img/plantillas/</code>.
    </p>
  <?php else: ?>
    <?php foreach ($plantillas as $plantilla): ?>
      <a class="tarjeta" href="/plantillas/<?= (int) $plantilla['id'] ?>">
        <img src="/assets/img/plantillas/<?= htmlspecialchars(basename($plantilla['svg_path'])) ?>" alt="<?= htmlspecialchars($plantilla['nombre']) ?>">
        <span class="tarjeta-nombre"><?= htmlspecialchars($plantilla['nombre']) ?></span>
        <span class="tarjeta-nivel"><?= htmlspecialchars($plantilla['nivel_dificultad']) ?></span>
      </a>
    <?php endforeach; ?>
  <?php endif; ?>
</main>
</body>
</html>
