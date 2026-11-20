
/*
 * COOTRASAM - Formulario PQR
 * Registro mediante PHP y MySQL.
 * Muestra el radicado sin salir del formulario.
 * Permite consultar la PQR después de registrarla.
 */

(function () {
    'use strict';

    function iniciarPQR() {

        var formulario = document.getElementById('formPQR');
        var boton = document.getElementById('btnEnviarPQR');
        var mensaje = document.getElementById('mensajePQR');

        if (!formulario || !boton || !mensaje) {
            return;
        }

        var enviando = false;

        function mostrarMensaje(texto, correcto, radicado) {

            mensaje.style.display = 'block';

            mensaje.className = correcto
                ? 'pqr-result pqr-result-ok'
                : 'pqr-result pqr-result-error';

            mensaje.innerHTML = '';

            var titulo = document.createElement('strong');

            titulo.textContent = correcto
                ? 'Solicitud registrada correctamente'
                : 'No se pudo registrar la solicitud';

            mensaje.appendChild(titulo);

            var parrafo = document.createElement('p');

            parrafo.textContent = texto;

            mensaje.appendChild(parrafo);

            if (correcto && radicado) {

                var numero = document.createElement('div');

                numero.className = 'pqr-radicado';
                numero.textContent = radicado;

                mensaje.appendChild(numero);

                var aviso = document.createElement('p');

                aviso.textContent =
                    'Guarda este número de radicado. ' +
                    'Lo necesitarás para consultar tu solicitud.';

                mensaje.appendChild(aviso);

                /*
                 * Nuevo botón para consultar la solicitud.
                 */
                var enlace = document.createElement('a');

                enlace.href = 'consultar_pqr.php';

                enlace.className = 'pqr-consultar-btn';

                var icono = document.createElement('i');

                icono.className =
                    'fa-solid fa-magnifying-glass';

                icono.setAttribute('aria-hidden', 'true');

                enlace.appendChild(icono);

                enlace.appendChild(
                    document.createTextNode(' Consultar mi PQR')
                );

                mensaje.appendChild(enlace);
            }

            mensaje.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }

        function bloquearFormulario(bloquear) {

            enviando = bloquear;

            boton.disabled = bloquear;

            boton.textContent = bloquear
                ? 'Registrando solicitud...'
                : 'Enviar solicitud';
        }

        formulario.addEventListener('submit', function (evento) {

            evento.preventDefault();

            if (enviando) {
                return;
            }

            mensaje.style.display = 'none';

            if (!formulario.checkValidity()) {

                formulario.reportValidity();

                return;
            }

            bloquearFormulario(true);

            var datos = new FormData(formulario);

            fetch('guardar_pqr.php', {
                method: 'POST',
                body: datos,
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(function (respuesta) {

                return respuesta.text().then(function (texto) {

                    var resultado;

                    try {

                        resultado = JSON.parse(texto);

                    } catch (error) {

                        throw new Error(
                            'El servidor devolvió una respuesta inesperada.'
                        );
                    }

                    if (!respuesta.ok || !resultado.ok) {

                        throw new Error(
                            resultado.mensaje ||
                            'No fue posible registrar la solicitud.'
                        );
                    }

                    return resultado;
                });
            })
            .then(function (resultado) {

                mostrarMensaje(
                    resultado.mensaje,
                    true,
                    resultado.radicado
                );

                formulario.reset();
            })
            .catch(function (error) {

                mostrarMensaje(
                    error.message ||
                    'Ocurrió un error al enviar la solicitud.',
                    false,
                    ''
                );
            })
            .then(function () {

                bloquearFormulario(false);
            });

        });
    }

    if (document.readyState === 'loading') {

        document.addEventListener(
            'DOMContentLoaded',
            iniciarPQR
        );

    } else {

        iniciarPQR();
    }

})();
