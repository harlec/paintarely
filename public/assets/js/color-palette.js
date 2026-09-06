window.PaintarelyEstado = window.PaintarelyEstado || {
  color: '#4a3f5c',
  pincel: { grosor: 4, alfa: 1, modo: 'lapiz' },
};

(function () {
  const capas = document.getElementById('lienzoCapas');
  const capaSvg = document.getElementById('capaSvg');
  const swatches = document.getElementById('paletaSwatches');
  const btnGuardar = document.getElementById('btnGuardarDibujo');
  const btnColorPersonalizado = document.getElementById('btnColorPersonalizado');
  const selectorEspectro = document.getElementById('selectorEspectro');
  const canvasEspectro = document.getElementById('canvasEspectro');
  const btnGotero = document.getElementById('btnGotero');
  const btnCerrarEspectro = document.getElementById('btnCerrarEspectro');
  const espectroPreview = document.getElementById('espectroPreview');
  if (!capas || !capaSvg || !swatches) return;

  const estado = window.PaintarelyEstado;

  function marcarActivo(elemento) {
    swatches.querySelectorAll('.swatch').forEach((s) => s.classList.remove('activo'));
    elemento.classList.add('activo');
  }

  swatches.addEventListener('click', (evento) => {
    const swatch = evento.target.closest('.swatch:not(.swatch-personalizado)');
    if (!swatch) return;
    estado.color = swatch.dataset.color;
    marcarActivo(swatch);
  });

  capaSvg.addEventListener('click', (evento) => {
    const zona = evento.target.closest('.zona-color');
    if (!zona) return;
    zona.setAttribute('fill', estado.color);
  });

  // --- Selector de espectro (paleta "toca cualquier tono") ---
  if (canvasEspectro) {
    const ctxEspectro = canvasEspectro.getContext('2d');

    function dibujarEspectro() {
      const { width, height } = canvasEspectro;
      const gradienteHue = ctxEspectro.createLinearGradient(0, 0, width, 0);
      const tonos = ['#ff3b30', '#ff9500', '#ffe600', '#34c759', '#00c7be', '#30b0ff', '#5e5ce6', '#af52de', '#ff2d78', '#ff3b30'];
      tonos.forEach((tono, i) => gradienteHue.addColorStop(i / (tonos.length - 1), tono));
      ctxEspectro.fillStyle = gradienteHue;
      ctxEspectro.fillRect(0, 0, width, height);

      const gradienteBrillo = ctxEspectro.createLinearGradient(0, 0, 0, height);
      gradienteBrillo.addColorStop(0, 'rgba(255,255,255,0.95)');
      gradienteBrillo.addColorStop(0.5, 'rgba(255,255,255,0)');
      gradienteBrillo.addColorStop(0.5, 'rgba(0,0,0,0)');
      gradienteBrillo.addColorStop(1, 'rgba(0,0,0,0.85)');
      ctxEspectro.fillStyle = gradienteBrillo;
      ctxEspectro.fillRect(0, 0, width, height);
    }

    dibujarEspectro();

    function rgbAHex(r, g, b) {
      return '#' + [r, g, b].map((v) => v.toString(16).padStart(2, '0')).join('');
    }

    function elegirColorDesde(evento) {
      const rect = canvasEspectro.getBoundingClientRect();
      const punto = evento.touches ? evento.touches[0] : evento;
      const x = Math.min(Math.max(punto.clientX - rect.left, 0), rect.width - 1);
      const y = Math.min(Math.max(punto.clientY - rect.top, 0), rect.height - 1);
      const escalaX = canvasEspectro.width / rect.width;
      const escalaY = canvasEspectro.height / rect.height;
      const pixel = ctxEspectro.getImageData(x * escalaX, y * escalaY, 1, 1).data;
      const hex = rgbAHex(pixel[0], pixel[1], pixel[2]);
      estado.color = hex;
      if (espectroPreview) espectroPreview.style.background = hex;
      if (btnColorPersonalizado) marcarActivo(btnColorPersonalizado);
    }

    let arrastrando = false;
    canvasEspectro.addEventListener('pointerdown', (e) => {
      arrastrando = true;
      canvasEspectro.setPointerCapture(e.pointerId);
      elegirColorDesde(e);
    });
    canvasEspectro.addEventListener('pointermove', (e) => {
      if (arrastrando) elegirColorDesde(e);
    });
    canvasEspectro.addEventListener('pointerup', () => { arrastrando = false; });

    btnColorPersonalizado?.addEventListener('click', () => {
      selectorEspectro.hidden = !selectorEspectro.hidden;
    });

    btnCerrarEspectro?.addEventListener('click', () => {
      selectorEspectro.hidden = true;
    });

    if (window.EyeDropper && btnGotero) {
      btnGotero.hidden = false;
      btnGotero.addEventListener('click', async () => {
        try {
          const resultado = await new EyeDropper().open();
          estado.color = resultado.sRGBHex;
          if (espectroPreview) espectroPreview.style.background = resultado.sRGBHex;
          if (btnColorPersonalizado) marcarActivo(btnColorPersonalizado);
        } catch (e) {
          // el usuario canceló el gotero
        }
      });
    }
  }

  btnGuardar?.addEventListener('click', async () => {
    const rect = capas.getBoundingClientRect();
    const svgElemento = capaSvg.querySelector('svg');
    const canvasFinal = document.createElement('canvas');
    canvasFinal.width = rect.width;
    canvasFinal.height = rect.height;
    const ctxFinal = canvasFinal.getContext('2d');

    if (svgElemento) {
      const svgTexto = new XMLSerializer().serializeToString(svgElemento);
      const svgUrl = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgTexto)));
      await dibujarImagenEn(ctxFinal, svgUrl, rect.width, rect.height);
    }

    const canvasTrazo = document.getElementById('canvasDibujo');
    ctxFinal.drawImage(canvasTrazo, 0, 0, rect.width, rect.height);

    const imagenBase64 = canvasFinal.toDataURL('image/png');

    btnGuardar.disabled = true;
    btnGuardar.textContent = 'Guardando...';

    try {
      const respuesta = await fetch('/dibujos/guardar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          plantilla_id: Number(capas.dataset.plantillaId),
          imagen_base64: imagenBase64,
        }),
      });
      const resultado = await respuesta.json();
      if (resultado.ok) {
        btnGuardar.textContent = '¡Guardado!';
      } else {
        btnGuardar.disabled = false;
        btnGuardar.textContent = 'Error al guardar';
        console.error(resultado.error);
      }
    } catch (error) {
      btnGuardar.disabled = false;
      btnGuardar.textContent = 'Error al guardar';
      console.error(error);
    }
  });

  function dibujarImagenEn(ctxDestino, url, ancho, alto) {
    return new Promise((resolve) => {
      const img = new Image();
      img.onload = () => {
        ctxDestino.drawImage(img, 0, 0, ancho, alto);
        resolve();
      };
      img.src = url;
    });
  }
})();
