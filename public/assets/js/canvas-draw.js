window.PaintarelyEstado = window.PaintarelyEstado || {
  color: '#4a3f5c',
  pincel: { grosor: 4, alfa: 1, modo: 'lapiz' },
};

(function () {
  const capas = document.getElementById('lienzoCapas');
  const capaSvg = document.getElementById('capaSvg');
  const canvas = document.getElementById('canvasDibujo');
  const botonesPincel = document.querySelectorAll('.pincel-btn');
  const panelPinceles = document.getElementById('pinceles');
  const btnFinalizar = document.getElementById('btnFinalizarTrazo');
  const btnLimpiar = document.getElementById('btnLimpiar');
  const btnGuardar = document.getElementById('btnGuardarDibujo');
  const pistaColorear = document.getElementById('pistaColorear');
  if (!capas || !canvas) return;

  const estado = window.PaintarelyEstado;
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
    ctx.lineWidth = estado.pincel.grosor;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.globalAlpha = estado.pincel.alfa;
    ctx.globalCompositeOperation = estado.pincel.modo === 'borrador' ? 'destination-out' : 'source-over';
    ctx.strokeStyle = estado.color;
    ctx.lineTo(x, y);
    ctx.stroke();
  }

  function terminarTrazo() {
    dibujando = false;
    ctx.globalAlpha = 1;
    ctx.globalCompositeOperation = 'source-over';
  }

  canvas.addEventListener('mousedown', iniciarTrazo);
  canvas.addEventListener('mousemove', continuarTrazo);
  window.addEventListener('mouseup', terminarTrazo);
  canvas.addEventListener('touchstart', iniciarTrazo);
  canvas.addEventListener('touchmove', continuarTrazo);
  canvas.addEventListener('touchend', terminarTrazo);

  botonesPincel.forEach((boton) => {
    boton.addEventListener('click', () => {
      estado.pincel = {
        grosor: Number(boton.dataset.grosor),
        alfa: Number(boton.dataset.alfa),
        modo: boton.dataset.modo,
      };
      botonesPincel.forEach((b) => b.classList.remove('activo'));
      boton.classList.add('activo');
    });
  });

  btnLimpiar?.addEventListener('click', () => {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
  });

  btnFinalizar?.addEventListener('click', () => {
    trazoFinalizado = true;
    canvas.style.pointerEvents = 'none';
    btnFinalizar.disabled = true;
    panelPinceles?.setAttribute('hidden', '');
    pistaColorear?.removeAttribute('hidden');
    if (btnGuardar) btnGuardar.disabled = false;
  });

  fetch(capas.dataset.svgUrl)
    .then((r) => r.text())
    .then((svgTexto) => {
      capaSvg.innerHTML = svgTexto;
      ajustarTamano();
    });

  window.addEventListener('resize', ajustarTamano);
})();
