<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Paintarely - <?= htmlspecialchars($plantilla['nombre']) ?></title>
<link rel="stylesheet" href="/assets/css/estilos.css">
</head>
<body>
<header class="cabecera">
  <h1><?= htmlspecialchars($plantilla['nombre']) ?></h1>
  <a href="/" class="volver">&larr; Volver a la galería</a>
</header>

<main class="lienzo-wrap">
  <div class="lienzo-capas" id="lienzoCapas"
       data-plantilla-id="<?= (int) $plantilla['id'] ?>"
       data-svg-url="/assets/img/plantillas/<?= htmlspecialchars(basename($plantilla['svg_path'])) ?>">
    <div class="capa-svg" id="capaSvg"></div>
    <canvas class="capa-dibujo" id="canvasDibujo"></canvas>
  </div>

  <div class="controles">
    <button id="btnFinalizarTrazo">Finalizar trazo</button>
    <button id="btnLimpiar" type="button">Limpiar</button>
  </div>

  <div class="paleta" id="paletaColores" hidden>
    <p>Elige un color y toca una zona para colorear:</p>
    <div class="paleta-swatches">
      <?php foreach (['#e63946', '#f1a208', '#ffe066', '#2a9d8f', '#264653', '#8338ec', '#ffffff'] as $color): ?>
        <button class="swatch" style="background: <?= $color ?>" data-color="<?= $color ?>" type="button"></button>
      <?php endforeach; ?>
    </div>
    <button id="btnGuardarDibujo">Guardar dibujo</button>
  </div>
</main>

<script src="/assets/js/canvas-draw.js"></script>
<script src="/assets/js/color-palette.js"></script>
</body>
</html>
