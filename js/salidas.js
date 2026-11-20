
(function () {
  'use strict';

  const tablero = document.querySelector('[data-salidas]');
  if (!tablero) return;

  const api = tablero.dataset.url || 'salidas_json.php';
  const limite = parseInt(tablero.dataset.limite || '6', 10);

  const relojHora = document.querySelector('.sal-reloj-hms');
  const relojAmpm = document.querySelector('.sal-reloj-ampm');
  const relojFecha = document.querySelector('.sal-reloj-fecha');

  let salidas = [];
  let diferenciaServidor = 0;

  function ahoraServidor() {
    return Math.floor(Date.now() / 1000) + diferenciaServidor;
  }

  function actualizarReloj() {
    const ahora = new Date(ahoraServidor() * 1000);

    if (relojHora) {
      relojHora.textContent = ahora.toLocaleTimeString('es-CO', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
      }).replace(/\s?[ap]\.?m\.?/i, '').trim();
    }

    if (relojAmpm) {
      relojAmpm.textContent = ahora.toLocaleTimeString('es-CO', {
        hour: '2-digit',
        hour12: true
      }).replace(/[0-9]/g, '').replace(/[.:]/g, '').trim().toUpperCase();
    }

    if (relojFecha) {
      relojFecha.textContent = ahora.toLocaleDateString('es-CO', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
      });
    }
  }

  function tiempoRestante(segundos) {
    if (segundos <= 0) return 'Saliendo';

    const horas = Math.floor(segundos / 3600);
    const minutos = Math.floor((segundos % 3600) / 60);
    const segundosRestantes = segundos % 60;

    if (horas > 0) {
      return horas + ' h ' + minutos + ' min';
    }

    return String(minutos).padStart(2, '0') + ':' +
           String(segundosRestantes).padStart(2, '0');
  }

  function escapar(texto) {
    return String(texto ?? '').replace(/[&<>"']/g, function (caracter) {
      return {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      }[caracter];
    });
  }

  function dibujar() {
    document.querySelectorAll('.sal-col[data-base]').forEach(function (columna) {
      const clave = columna.dataset.base;
      const lista = columna.querySelector('.sal-lista');
      const mensaje = columna.querySelector('.sal-vacio');

      if (!lista) return;

      const ahora = ahoraServidor();

      const futuras = salidas
        .filter(function (salida) {
          return salida.clave === clave && Number(salida.ts) > ahora;
        })
        .sort(function (a, b) {
          return Number(a.ts) - Number(b.ts);
        })
        .slice(0, limite);

      if (!futuras.length) {
        lista.innerHTML = '';
        if (mensaje) {
          mensaje.textContent = 'No hay próximas salidas registradas.';
          mensaje.style.display = 'block';
        }
        return;
      }

      if (mensaje) mensaje.style.display = 'none';

      lista.innerHTML = futuras.map(function (salida, indice) {
        const fecha = new Date(Number(salida.ts) * 1000);
        const hora = fecha.toLocaleTimeString('es-CO', {
          hour: '2-digit',
          minute: '2-digit',
          hour12: true
        });

        const clase = indice === 0 ? ' sal-proxima' : '';

        return '<li class="' + clase.trim() + '">' +
          '<span>' + escapar(hora) + '</span>' +
          '<span>' + escapar(salida.bus || 'Sin placa') + '</span>' +
          '<span class="sal-cuenta" data-ts="' + Number(salida.ts) + '">' +
          escapar(tiempoRestante(Number(salida.ts) - ahora)) +
          '</span>' +
          '</li>';
      }).join('');
    });
  }

  function actualizarCuentaRegresiva() {
    const ahora = ahoraServidor();

    document.querySelectorAll('.sal-cuenta[data-ts]').forEach(function (elemento) {
      const ts = Number(elemento.dataset.ts);
      elemento.textContent = tiempoRestante(ts - ahora);
    });

    const habiaPasadoUnaSalida = salidas.some(function (salida) {
      return Number(salida.ts) <= ahora;
    });

    if (habiaPasadoUnaSalida) {
      salidas = salidas.filter(function (salida) {
        return Number(salida.ts) > ahora;
      });
      dibujar();
    }
  }

  async function cargarSalidas() {
    try {
      const respuesta = await fetch(api, { cache: 'no-store' });
      if (!respuesta.ok) throw new Error('Error al consultar las salidas');

      const datos = await respuesta.json();

      diferenciaServidor = Number(datos.ahora || Math.floor(Date.now() / 1000))
        - Math.floor(Date.now() / 1000);

      salidas = Array.isArray(datos.salidas) ? datos.salidas : [];
      dibujar();
      actualizarReloj();
    } catch (error) {
      document.querySelectorAll('.sal-vacio').forEach(function (mensaje) {
        mensaje.textContent = 'No fue posible cargar las salidas. Verifica la conexión.';
        mensaje.style.display = 'block';
      });
      console.error(error);
    }
  }

  actualizarReloj();
  cargarSalidas();

  setInterval(actualizarReloj, 1000);
  setInterval(actualizarCuentaRegresiva, 1000);
  setInterval(cargarSalidas, 30000);
})();
