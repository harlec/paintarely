(function () {
  const capas = document.getElementById('lienzoCapas');
  const capaSvg = document.getElementById('capaSvg');
  const canvas = document.getElementById('canvasDibujo');
  const btnFinalizar = document.getElementById('btnFinalizarTrazo');
  const btnLimpiar = document.getElementById('btnLimpiar');
  if (!capas || !canvas) return;

  const ctx = canvas.getContext('2d');
  let dibujando = false;
  let trazoFinalizado = false;

  function ajustarTamano() {
    const rect = capas.getBoundingClientRect();
    canvas.width = rect.width;
    canvas.height = rect.height;
  }

  function posicionDesdeEvento(evento) {
    const rect = canvas.getBoundingClientRect();
    const punto = evento.touches ? evento.touches[0] : evento;
    return {
      x: punto.clientX - rect.left,
      y: punto.clientY - rect.top,
    };
  }

  function iniciarTrazo(evento) {
    if (trazoFinalizado) return;
    dibujando = true;
    const { x, y } = posicionDesdeEvento(evento);
    ctx.beginPath();
    ctx.moveTo(x, y);
  }

  function continuarTrazo(evento) {
    if (!dibujando || trazoFinalizado) return;
    evento.preventDefault();
    const { x, y } = posicionDesdeEvento(evento);
    ctx.lineWidth = 4;
    ctx.lineCap = 'round';
    ctx.strokeStyle = '#264653';
    ctx.lineTo(x, y);
    ctx.stroke();
  }

  function terminarTrazo() {
    dibujando = false;
  }

  canvas.addEventListener('mousedown', iniciarTrazo);
  canvas.addEventListener('mousemove', continuarTrazo);
  window.addEventListener('mouseup', terminarTrazo);
  canvas.addEventListener('touchstart', iniciarTrazo);
  canvas.addEventListener('touchmove', continuarTrazo);
  canvas.addEventListener('touchend', terminarTrazo);

  btnLimpiar?.addEventListener('click', () => {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
  });

  btnFinalizar?.addEventListener('click', () => {
    trazoFinalizado = true;
    canvas.style.pointerEvents = 'none';
    btnFinalizar.disabled = true;
    document.getElementById('paletaColores')?.removeAttribute('hidden');
  });

  fetch(capas.dataset.svgUrl)
    .then((r) => r.text())
    .then((svgTexto) => {
      capaSvg.innerHTML = svgTexto;
      ajustarTamano();
    });

  window.addEventListener('resize', ajustarTamano);
})();
