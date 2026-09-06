window.PaintarelyEstado = window.PaintarelyEstado || {
  color: '#4a3f5c',
  pincel: { grosor: 4, alfa: 1, modo: 'lapiz' },
};

(function () {
  const UMBRAL_COMPLETADO = 0.6;

  const capas = document.getElementById('lienzoCapas');
  const capaSvg = document.getElementById('capaSvg');
  const canvas = document.getElementById('canvasDibujo');
  const canvasGuia = document.getElementById('canvasGuia');
  const botonesPincel = document.querySelectorAll('.pincel-btn');
  const btnPincelesToggle = document.getElementById('btnPincelesToggle');
  const panelPinceles = document.getElementById('panelPinceles');
  const sliderGrosor = document.getElementById('sliderGrosor');
  const grosorPreview = document.getElementById('grosorPreview');
  const btnFinalizar = document.getElementById('btnFinalizarTrazo');
  const btnLimpiar = document.getElementById('btnLimpiar');
  const btnGuardar = document.getElementById('btnGuardarDibujo');
  const pistaColorear = document.getElementById('pistaColorear');
  const pistaProgreso = document.getElementById('pistaProgreso');
  const porcentajeTrazo = document.getElementById('porcentajeTrazo');
  if (!capas || !canvas) return;

  const estado = window.PaintarelyEstado;
  const ctx = canvas.getContext('2d');
  const ctxGuia = canvasGuia ? canvasGuia.getContext('2d') : null;
  let dibujando = false;
  let trazoFinalizado = false;
  let puntosGuia = [];

  function ajustarTamano() {
    const rect = capas.getBoundingClientRect();
    canvas.width = rect.width;
    canvas.height = rect.height;
    if (canvasGuia) {
      canvasGuia.width = rect.width;
      canvasGuia.height = rect.height;
    }
    dibujarPuntosGuia();
  }

  function posicionDesdeEvento(evento) {
    const rect = canvas.getBoundingClientRect();
    const punto = evento.touches ? evento.touches[0] : evento;
    return {
      x: punto.clientX - rect.left,
      y: punto.clientY - rect.top,
    };
  }

  // --- Guía de trazo: puntos a lo largo de los <path class="guia-trazo"> ---
  function prepararPuntosGuia() {
    puntosGuia = [];
    const paths = capaSvg.querySelectorAll('.guia-trazo');
    paths.forEach((path) => {
      const svg = path.ownerSVGElement;
      const viewBox = svg && svg.viewBox && svg.viewBox.baseVal && svg.viewBox.baseVal.width
        ? svg.viewBox.baseVal
        : { x: 0, y: 0, width: 200, height: 200 };
      const longitud = path.getTotalLength();
      const numPuntos = Math.max(24, Math.round(longitud / 6));
      for (let i = 0; i <= numPuntos; i++) {
        const punto = path.getPointAtLength((i / numPuntos) * longitud);
        puntosGuia.push({
          u: (punto.x - viewBox.x) / viewBox.width,
          v: (punto.y - viewBox.y) / viewBox.height,
          cubierto: false,
        });
      }
    });
    actualizarProgreso();
  }

  function dibujarPuntosGuia() {
    if (!ctxGuia) return;
    ctxGuia.clearRect(0, 0, canvasGuia.width, canvasGuia.height);
    puntosGuia.forEach((p) => {
      if (p.cubierto) return; // ya trazado: el puntito desaparece y solo queda tu dibujo
      const x = p.u * canvasGuia.width;
      const y = p.v * canvasGuia.height;
      ctxGuia.beginPath();
      ctxGuia.arc(x, y, 3.5, 0, Math.PI * 2);
      ctxGuia.fillStyle = 'rgba(255,92,138,0.55)';
      ctxGuia.fill();
    });
  }

  function actualizarProgreso() {
    if (puntosGuia.length === 0) {
      // Plantilla sin guía definida: no bloquear al usuario.
      if (btnFinalizar) btnFinalizar.disabled = false;
      pistaProgreso?.setAttribute('hidden', '');
      return;
    }
    const cubiertos = puntosGuia.filter((p) => p.cubierto).length;
    const progreso = cubiertos / puntosGuia.length;
    if (porcentajeTrazo) porcentajeTrazo.textContent = Math.round(progreso * 100) + '%';
    if (btnFinalizar && !trazoFinalizado) {
      btnFinalizar.disabled = progreso < UMBRAL_COMPLETADO;
    }
  }

  function marcarPuntosCercanos(xPx, yPx) {
    if (puntosGuia.length === 0 || !canvasGuia) return;
    const tolerancia = canvasGuia.width * 0.045;
    let huboCambios = false;
    puntosGuia.forEach((p) => {
      if (p.cubierto) return;
      const dx = p.u * canvasGuia.width - xPx;
      const dy = p.v * canvasGuia.height - yPx;
      if (Math.sqrt(dx * dx + dy * dy) <= tolerancia) {
        p.cubierto = true;
        huboCambios = true;
      }
    });
    if (huboCambios) {
      dibujarPuntosGuia();
      actualizarProgreso();
    }
  }

  function reiniciarProgreso() {
    puntosGuia.forEach((p) => { p.cubierto = false; });
    dibujarPuntosGuia();
    actualizarProgreso();
  }

  function iniciarTrazo(evento) {
    if (trazoFinalizado) return;
    dibujando = true;
    const { x, y } = posicionDesdeEvento(evento);
    ctx.beginPath();
    ctx.moveTo(x, y);
    marcarPuntosCercanos(x, y);
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
    marcarPuntosCercanos(x, y);
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

  function actualizarPreviewGrosor() {
    if (!grosorPreview) return;
    const tamano = Math.max(6, Math.min(estado.pincel.grosor, 34));
    grosorPreview.style.width = tamano + 'px';
    grosorPreview.style.height = tamano + 'px';
  }

  botonesPincel.forEach((boton) => {
    boton.addEventListener('click', () => {
      estado.pincel = {
        grosor: Number(boton.dataset.grosor),
        alfa: Number(boton.dataset.alfa),
        modo: boton.dataset.modo,
      };
      botonesPincel.forEach((b) => b.classList.remove('activo'));
      boton.classList.add('activo');
      if (sliderGrosor) sliderGrosor.value = String(estado.pincel.grosor);
      actualizarPreviewGrosor();
    });
  });

  sliderGrosor?.addEventListener('input', () => {
    estado.pincel.grosor = Number(sliderGrosor.value);
    actualizarPreviewGrosor();
  });
  actualizarPreviewGrosor();

  btnPincelesToggle?.addEventListener('click', () => {
    panelPinceles.hidden = !panelPinceles.hidden;
  });

  document.addEventListener('click', (evento) => {
    if (!panelPinceles || panelPinceles.hidden) return;
    if (panelPinceles.contains(evento.target) || evento.target === btnPincelesToggle || btnPincelesToggle?.contains(evento.target)) {
      return;
    }
    panelPinceles.hidden = true;
  });

  btnLimpiar?.addEventListener('click', () => {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    reiniciarProgreso();
  });

  btnFinalizar?.addEventListener('click', () => {
    if (btnFinalizar.disabled) return;
    trazoFinalizado = true;
    canvas.style.pointerEvents = 'none';
    btnFinalizar.disabled = true;
    panelPinceles?.setAttribute('hidden', '');
    btnPincelesToggle?.setAttribute('hidden', '');
    pistaProgreso?.setAttribute('hidden', '');
    pistaColorear?.removeAttribute('hidden');
    if (btnGuardar) btnGuardar.disabled = false;
  });

  fetch(capas.dataset.svgUrl)
    .then((r) => r.text())
    .then((svgTexto) => {
      capaSvg.innerHTML = svgTexto;
      ajustarTamano();
      prepararPuntosGuia();
      dibujarPuntosGuia();
    });

  window.addEventListener('resize', ajustarTamano);
})();
