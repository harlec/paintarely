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
    <canvas class="capa-guia-progreso" id="canvasGuia"></canvas>
    <canvas class="capa-dibujo" id="canvasDibujo"></canvas>

    <button type="button" id="btnPincelesToggle" class="fab-pinceles" title="Pinceles">
      <svg viewBox="0 0 24 24" width="22" height="22" fill="none">
        <path d="M4 20c0-3 1.5-5 4-6l9-9 2 2-9 9c-1 2.5-3 4-6 4Z" fill="#ffb3c6" stroke="#4a3f5c" stroke-width="1.3" stroke-linejoin="round"/>
        <path d="M15 5l2-2 2 2-2 2-2-2Z" fill="#a8e6ff" stroke="#4a3f5c" stroke-width="1.3" stroke-linejoin="round"/>
      </svg>
    </button>

    <div class="panel-pinceles" id="panelPinceles" hidden>
      <div class="tipos-pincel" id="pinceles">
        <button type="button" class="pincel-btn activo" data-grosor="4" data-alfa="1" data-modo="lapiz" title="Redondo">
          <svg viewBox="0 0 40 20" width="40" height="20"><rect x="18" y="7" width="20" height="6" rx="2" fill="#a8e6ff"/><path d="M2 10c0-3 3-5 8-5h8v10H10c-5 0-8-2-8-5Z" fill="#ffb3c6"/></svg>
        </button>
        <button type="button" class="pincel-btn" data-grosor="14" data-alfa="1" data-modo="lapiz" title="Plano">
          <svg viewBox="0 0 40 20" width="40" height="20"><rect x="18" y="7" width="20" height="6" rx="2" fill="#a8e6ff"/><rect x="2" y="4" width="14" height="12" rx="1.5" fill="#ffb3c6"/></svg>
        </button>
        <button type="button" class="pincel-btn" data-grosor="10" data-alfa="0.5" data-modo="lapiz" title="Angular">
          <svg viewBox="0 0 40 20" width="40" height="20"><rect x="18" y="7" width="20" height="6" rx="2" fill="#a8e6ff"/><path d="M2 15 L16 3 L16 9 L6 15 Z" fill="#ffb3c6"/></svg>
        </button>
        <button type="button" class="pincel-btn" data-grosor="22" data-alfa="1" data-modo="borrador" title="Borrador">
          <svg viewBox="0 0 40 20" width="40" height="20"><rect x="14" y="3" width="22" height="14" rx="3" fill="#c9b8ff"/><rect x="2" y="3" width="14" height="14" rx="3" fill="#ffd8a8"/></svg>
        </button>
      </div>

      <div class="control-grosor">
        <span class="grosor-preview" id="grosorPreview"></span>
        <input type="range" id="sliderGrosor" min="2" max="34" value="4" aria-label="Grosor del pincel">
      </div>
    </div>
  </div>

  <div class="herramientas">
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

  <p class="pista" id="pistaProgreso">Repasa el camino punteado ✏️ · <span id="porcentajeTrazo">0%</span></p>

  <div class="controles">
    <button id="btnFinalizarTrazo" type="button" disabled>Finalizar trazo</button>
    <button id="btnLimpiar" type="button">Limpiar</button>
    <button id="btnGuardarDibujo" type="button" disabled>Guardar dibujo</button>
  </div>
  <p class="pista" id="pistaColorear" hidden>Toca una zona del dibujo para colorearla ✨</p>
</main>

<script src="/assets/js/canvas-draw.js"></script>
<script src="/assets/js/color-palette.js"></script>
</body>
</html>
