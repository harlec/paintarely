(function () {
  const capas = document.getElementById('lienzoCapas');
  const capaSvg = document.getElementById('capaSvg');
  const paleta = document.getElementById('paletaColores');
  const btnGuardar = document.getElementById('btnGuardarDibujo');
  if (!capas || !capaSvg || !paleta) return;

  let colorActivo = null;

  paleta.addEventListener('click', (evento) => {
    const swatch = evento.target.closest('.swatch');
    if (!swatch) return;
    document.querySelectorAll('.swatch').forEach((s) => s.classList.remove('activo'));
    swatch.classList.add('activo');
    colorActivo = swatch.dataset.color;
  });

  capaSvg.addEventListener('click', (evento) => {
    if (!colorActivo) return;
    const zona = evento.target.closest('.zona-color');
    if (!zona) return;
    zona.setAttribute('fill', colorActivo);
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
        btnGuardar.textContent = 'Error al guardar';
        console.error(resultado.error);
      }
    } catch (error) {
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
