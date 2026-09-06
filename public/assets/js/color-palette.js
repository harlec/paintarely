window.PaintarelyEstado = window.PaintarelyEstado || {
  color: '#4a3f5c',
  pincel: { grosor: 4, alfa: 1, modo: 'lapiz' },
};

(function () {
  const capas = document.getElementById('lienzoCapas');
  const capaSvg = document.getElementById('capaSvg');
  const swatches = document.getElementById('paletaSwatches');
  const colorPersonalizado = document.getElementById('colorPersonalizado');
  const btnGuardar = document.getElementById('btnGuardarDibujo');
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

  colorPersonalizado?.addEventListener('input', (evento) => {
    estado.color = evento.target.value;
    marcarActivo(colorPersonalizado.closest('.swatch'));
  });

  capaSvg.addEventListener('click', (evento) => {
    const zona = evento.target.closest('.zona-color');
    if (!zona) return;
    zona.setAttribute('fill', estado.color);
  });

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
