
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
 * COOTRASAM - Formulario PQR
 * Compatible con PHP 5.6.
 */

if (empty($_SESSION['pqr_token'])) {

    if (function_exists('openssl_random_pseudo_bytes')) {
        $seguro = false;
        $bytes = openssl_random_pseudo_bytes(32, $seguro);

        if ($bytes !== false && $seguro === true) {
            $_SESSION['pqr_token'] = bin2hex($bytes);
        }
    }

    if (empty($_SESSION['pqr_token'])) {
        http_response_code(503);
        die(
            'No se pudo iniciar el formulario PQR. ' .
            'Verifica que OpenSSL esté habilitado en PHP.'
        );
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>PQR - COOTRASAM</title>

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Oswald:wght@500;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >

    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/nav.css">
    <link rel="stylesheet" href="css/paginas.css">
    <link rel="stylesheet" href="css/formularios.css?v=4">

    <style>
        /*
         * Únicamente estilos de los nuevos botones.
         * No se modifica el diseño del formulario.
         */

        .pqr-consultar-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 13px 20px;
            background: #c2410c;
            color: #ffffff !important;
            border-radius: 9px;
            text-decoration: none !important;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
            transition: background .2s ease;
        }

        .pqr-consultar-btn:hover {
            background: #9a3412;
        }

        .pqr-consultar-btn:focus-visible {
            outline: 3px solid #fdba74;
            outline-offset: 3px;
        }

        .pqr-consultar-lateral {
            margin-top: 18px;
        }

        .pqr-consultar-lateral .pqr-consultar-btn {
            width: 100%;
        }

        .pqr-result .pqr-consultar-btn {
            margin-top: 12px;
        }
    </style>

</head>

<body>

<?php include 'includes/header.php'; ?>

<main class="pqr-page">

    <section class="pqr-hero">
        <div class="container">

            <span class="pqr-eyebrow">
                <i class="fa-solid fa-headset"></i>
                ATENCIÓN AL USUARIO
            </span>

            <h1>
                Tu voz nos ayuda<br>
                a seguir mejorando.
            </h1>

            <p>
                Presenta tus peticiones, quejas,
                reclamos, sugerencias o felicitaciones.
                En COOTRASAM queremos escucharte.
            </p>

        </div>
    </section>

    <section class="pqr-main">
        <div class="container">

            <div class="pqr-intro">

                <span class="pqr-section-label">
                    ESTAMOS PARA ESCUCHARTE
                </span>

                <h2>¿Cómo podemos ayudarte?</h2>

                <p>
                    Completa el formulario con tus datos.
                    Al registrar tu solicitud recibirás
                    un número de radicado para hacer
                    seguimiento.
                </p>

            </div>

            <div class="pqr-layout">

                <div class="pqr-card">

                    <div class="pqr-card-heading">

                        <div class="pqr-heading-icon">
                            <i class="fa-solid fa-file-pen"></i>
                        </div>

                        <div>
                            <h3>Registra tu PQR</h3>

                            <p>
                                Los campos con * son obligatorios.
                            </p>
                        </div>

                    </div>

                    <form
                        id="formPQR"
                        method="post"
                        action="guardar_pqr.php"
                    >

                        <input
                            type="hidden"
                            name="pqr_token"
                            value="<?php
                                echo htmlspecialchars(
                                    $_SESSION['pqr_token'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                            ?>"
                        >

                        <!-- Campo antispam oculto -->
                        <div
                            class="pqr-honeypot"
                            aria-hidden="true"
                            style="
                                position:absolute;
                                left:-9999px;
                                width:1px;
                                height:1px;
                                overflow:hidden;
                            "
                        >
                            <label for="website">
                                Sitio web
                            </label>

                            <input
                                type="text"
                                id="website"
                                name="website"
                                tabindex="-1"
                                autocomplete="off"
                            >
                        </div>

                        <div class="pqr-fields">

                            <div class="pqr-field pqr-full">

                                <label for="tipo">
                                    Tipo de solicitud *
                                </label>

                                <select
                                    id="tipo"
                                    name="tipo"
                                    required
                                >
                                    <option value="">
                                        Selecciona una opción
                                    </option>

                                    <option value="Petición">
                                        Petición
                                    </option>

                                    <option value="Queja">
                                        Queja
                                    </option>

                                    <option value="Reclamo">
                                        Reclamo
                                    </option>

                                    <option value="Sugerencia">
                                        Sugerencia
                                    </option>

                                    <option value="Felicitación">
                                        Felicitación
                                    </option>
                                </select>

                            </div>

                            <div class="pqr-field pqr-full">

                                <label for="nombre">
                                    Nombre completo *
                                </label>

                                <input
                                    type="text"
                                    id="nombre"
                                    name="nombre"
                                    maxlength="100"
                                    autocomplete="name"
                                    required
                                    placeholder="Escribe tu nombre completo"
                                >

                            </div>

                            <div class="pqr-field">

                                <label for="correo">
                                    Correo electrónico *
                                </label>

                                <input
                                    type="email"
                                    id="correo"
                                    name="correo"
                                    maxlength="100"
                                    autocomplete="email"
                                    required
                                    placeholder="correo@ejemplo.com"
                                >

                            </div>

                            <div class="pqr-field">

                                <label for="telefono">
                                    Teléfono *
                                </label>

                                <input
                                    type="tel"
                                    id="telefono"
                                    name="telefono"
                                    maxlength="20"
                                    autocomplete="tel"
                                    required
                                    placeholder="Número de contacto"
                                >

                            </div>

                            <div class="pqr-field">

                                <label for="cedula">
                                    Documento (opcional)
                                </label>

                                <input
                                    type="text"
                                    id="cedula"
                                    name="cedula"
                                    maxlength="30"
                                    placeholder="Número de documento"
                                >

                            </div>

                            <div class="pqr-field">

                                <label for="fecha">
                                    Fecha relacionada (opcional)
                                </label>

                                <input
                                    type="date"
                                    id="fecha"
                                    name="fecha"
                                >

                            </div>

                            <div class="pqr-field pqr-full">

                                <label for="descripcion">
                                    Describe tu solicitud *
                                </label>

                                <textarea
                                    id="descripcion"
                                    name="descripcion"
                                    rows="6"
                                    maxlength="5000"
                                    minlength="10"
                                    required
                                    placeholder="Cuéntanos qué sucedió o cómo podemos ayudarte..."
                                ></textarea>

                                <small>
                                    Escribe al menos 10 caracteres.
                                </small>

                            </div>

                            <div class="pqr-field pqr-full">

                                <label class="pqr-check">

                                    <input
                                        type="checkbox"
                                        id="acepta_datos"
                                        name="acepta_datos"
                                        value="1"
                                        required
                                    >

                                    <span>
                                        Acepto el tratamiento de mis
                                        datos personales conforme a la

                                        <a
                                            href="tratamiento-datos.php"
                                            target="_blank"
                                            rel="noopener"
                                        >
                                            Política de Tratamiento
                                            de Datos
                                        </a>.
                                    </span>

                                </label>

                            </div>

                        </div>

                        <button
                            type="submit"
                            class="pqr-submit"
                            id="btnEnviarPQR"
                        >
                            <i class="fa-solid fa-paper-plane"></i>
                            Enviar solicitud
                        </button>

                        <div
                            id="mensajePQR"
                            class="pqr-result"
                            role="status"
                            aria-live="polite"
                            style="display:none;"
                        ></div>

                    </form>

                </div>

                <aside class="pqr-side">

                    <div class="pqr-info-card">

                        <div class="pqr-side-icon">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </div>

                        <h3>Consulta tu PQR</h3>

                        <p>
                            Si ya registraste una solicitud,
                            puedes consultar su estado
                            utilizando tu número de radicado
                            y el correo electrónico
                            con el que la enviaste.
                        </p>

                        <div class="pqr-consultar-lateral">

                            <a
                                href="consultar_pqr.php"
                                class="pqr-consultar-btn"
                            >
                                <i class="fa-solid fa-magnifying-glass"></i>
                                Consultar mi PQR
                            </a>

                        </div>

                    </div>

                    <div class="pqr-help-card">

                        <h3>
                            <i class="fa-solid fa-circle-info"></i>
                            Ten en cuenta
                        </h3>

                        <p>
                            Describe tu solicitud de forma
                            clara y proporciona un correo válido.
                        </p>

                        <p>
                            Conserva el radicado que aparece
                            después de enviar el formulario.
                        </p>

                        <p>
                            Nuestro equipo revisará la información
                            registrada para gestionar tu solicitud.
                        </p>

                    </div>

                </aside>

            </div>

        </div>
    </section>

</main>

<?php include 'includes/footer.php'; ?>

<script src="js/nav.js"></script>
<script src="js/pqr.js?v=2"></script>

</body>
</html>
