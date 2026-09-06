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

  <div class="herramientas">
    <div class="pinceles" id="pinceles">
      <button type="button" class="pincel-btn activo" data-grosor="4" data-alfa="1" data-modo="lapiz" title="Lápiz fino">✏️</button>
      <button type="button" class="pincel-btn" data-grosor="10" data-alfa="1" data-modo="lapiz" title="Marcador grueso">🖊️</button>
      <button type="button" class="pincel-btn" data-grosor="18" data-alfa="0.45" data-modo="lapiz" title="Crayón suave">🖍️</button>
      <button type="button" class="pincel-btn" data-grosor="22" data-alfa="1" data-modo="borrador" title="Borrador">🧽</button>
    </div>

    <div class="fila-colores">
      <div class="paleta-swatches" id="paletaSwatches">
        <?php foreach (['#ffb3c6', '#ffd8a8', '#fff3a0', '#b9f5d0', '#a8e6ff', '#c9b8ff', '#ffffff', '#4a3f5c'] as $indice => $color): ?>
          <button type="button" class="swatch<?= $indice === 0 ? ' activo' : '' ?>" style="background: <?= $color ?>" data-color="<?= $color ?>"></button>
        <?php endforeach; ?>
        <button type="button" class="swatch swatch-personalizado" id="btnColorPersonalizado" title="Elige cualquier color">🎨</button>
      </div>

      <div class="selector-espectro" id="selectorEspectro" hidden>
        <canvas id="canvasEspectro" width="240" height="150"></canvas>
        <div class="espectro-acciones">
          <button type="button" id="btnGotero" class="boton-gotero" hidden>💧 Gotero</button>
          <span class="espectro-preview" id="espectroPreview"></span>
          <button type="button" id="btnCerrarEspectro" class="boton-cerrar">Listo</button>
        </div>
      </div>
    </div>
  </div>

  <div class="controles">
    <button id="btnFinalizarTrazo" type="button">Finalizar trazo</button>
    <button id="btnLimpiar" type="button">Limpiar</button>
    <button id="btnGuardarDibujo" type="button" disabled>Guardar dibujo</button>
  </div>
  <p class="pista" id="pistaColorear" hidden>Toca una zona del dibujo para colorearla ✨</p>
</main>

<script src="/assets/js/canvas-draw.js"></script>
<script src="/assets/js/color-palette.js"></script>
</body>
</html>
