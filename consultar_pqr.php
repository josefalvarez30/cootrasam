
<?php
session_start();

require_once __DIR__ . '/conexion.php';

date_default_timezone_set('America/Bogota');

function limpiar_pqr($valor) {
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        'UTF-8'
    );
}

$mensaje = '';
$encontrada = false;
$solicitud = null;
$radicado = '';
$correo = '';

$estados = array(
    'nuevo' => 'Recibida',
    'leido' => 'En revisión',
    'respondido' => 'Respondida'
);

/*
 * Limitar intentos para proteger
 * los datos de los solicitantes.
 */
if (!isset($_SESSION['consultas_pqr'])) {
    $_SESSION['consultas_pqr'] = array();
}

$ahora = time();
$intentos = array();

foreach ($_SESSION['consultas_pqr'] as $momento) {
    if (($ahora - $momento) < 900) {
        $intentos[] = $momento;
    }
}

$_SESSION['consultas_pqr'] = $intentos;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $radicado = isset($_POST['radicado'])
        ? strtoupper(trim($_POST['radicado']))
        : '';

    $correo = isset($_POST['correo'])
        ? strtolower(trim($_POST['correo']))
        : '';

    if (count($_SESSION['consultas_pqr']) >= 5) {

        $mensaje =
            'Has realizado demasiadas consultas. ' .
            'Espera 15 minutos e inténtalo nuevamente.';

    } elseif (
        !preg_match(
            '/^PQR-([0-9]{4})-([0-9]{4,10})$/',
            $radicado,
            $coincidencias
        ) ||
        !filter_var($correo, FILTER_VALIDATE_EMAIL)
    ) {

        $mensaje =
            'Verifica el radicado y el correo electrónico.';

    } else {

        $_SESSION['consultas_pqr'][] = $ahora;

        $anio = (int)$coincidencias[1];
        $id = (int)$coincidencias[2];

        $stmt = $conexion->prepare(
            "SELECT id, tipo, estado, fecha
             FROM pqr_mensajes
             WHERE id = ?
               AND YEAR(fecha) = ?
               AND LOWER(correo) = ?
             LIMIT 1"
        );

        if ($stmt) {

            $stmt->bind_param(
                'iis',
                $id,
                $anio,
                $correo
            );

            if ($stmt->execute()) {

                $resultado = $stmt->get_result();

                if ($resultado) {
                    $solicitud = $resultado->fetch_assoc();
                }
            }

            $stmt->close();
        }

        if ($solicitud) {

            $encontrada = true;

        } else {

            $mensaje =
                'No encontramos una solicitud con ' .
                'esos datos. Verifica el radicado ' .
                'y el correo utilizado al registrarla.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Consultar PQR - COOTRASAM</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Oswald:wght@500;600;700&display=swap"
          rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #fff8f1;
            font-family: 'Poppins', sans-serif;
            color: #252525;
        }

        .contenedor {
            max-width: 620px;
            margin: 65px auto;
            padding: 0 18px;
        }

        .tarjeta {
            background: #fff;
            padding: 35px;
            border-radius: 15px;
            border-top: 5px solid #c2410c;
            box-shadow: 0 8px 30px rgba(0,0,0,.07);
        }

        h1 {
            font-family: 'Oswald', sans-serif;
            font-size: 30px;
            margin: 0 0 12px;
        }

        .descripcion {
            font-size: 14px;
            line-height: 1.7;
            color: #666;
            margin-bottom: 25px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 7px;
        }

        input {
            display: block;
            width: 100%;
            padding: 13px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
        }

        input:focus {
            outline: 2px solid #fdba74;
            border-color: #c2410c;
        }

        button {
            width: 100%;
            padding: 14px;
            background: #c2410c;
            color: white;
            border: 0;
            border-radius: 8px;
            font-family: inherit;
            font-weight: 700;
            cursor: pointer;
        }

        button:hover {
            background: #9a3412;
        }

        .error {
            background: #fff0e6;
            color: #9a3412;
            padding: 14px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 13px;
            line-height: 1.6;
        }

        .resultado {
            margin-top: 25px;
            padding: 22px;
            background: #f8faf8;
            border: 1px solid #dcebdc;
            border-radius: 10px;
        }

        .resultado h2 {
            font-family: 'Oswald', sans-serif;
            margin-top: 0;
            font-size: 23px;
        }

        .dato {
            margin: 13px 0;
            font-size: 14px;
        }

        .dato strong {
            display: block;
            color: #666;
            font-size: 12px;
            margin-bottom: 4px;
        }

        .estado {
            display: inline-block;
            padding: 7px 13px;
            border-radius: 20px;
            background: #dcfce7;
            color: #166534;
            font-weight: 700;
        }

        .volver {
            display: inline-block;
            margin-top: 20px;
            color: #c2410c;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        @media (max-width: 600px) {
            .contenedor {
                margin: 25px auto;
            }

            .tarjeta {
                padding: 23px;
            }
        }
    </style>
</head>

<body>

<div class="contenedor">

    <div class="tarjeta">

        <h1>Consulta tu PQR</h1>

        <p class="descripcion">
            Consulta el estado de tu petición,
            queja, reclamo, sugerencia o felicitación.
            Ingresa el número de radicado y el correo
            electrónico con el que registraste
            tu solicitud.
        </p>

        <form method="POST">

            <label for="radicado">
                Número de radicado
            </label>

            <input type="text"
                   id="radicado"
                   name="radicado"
                   placeholder="PQR-2026-0015"
                   maxlength="20"
                   value="<?php
                       echo limpiar_pqr($radicado);
                   ?>"
                   required>

            <label for="correo">
                Correo electrónico
            </label>

            <input type="email"
                   id="correo"
                   name="correo"
                   placeholder="ejemplo@correo.com"
                   maxlength="100"
                   value="<?php
                       echo limpiar_pqr($correo);
                   ?>"
                   required>

            <button type="submit">
                Consultar estado
            </button>

        </form>

        <?php if ($mensaje !== ''): ?>

            <div class="error">
                <?php echo limpiar_pqr($mensaje); ?>
            </div>

        <?php endif; ?>

        <?php if ($encontrada): ?>

            <div class="resultado">

                <h2>Estado de tu solicitud</h2>

                <div class="dato">
                    <strong>Radicado</strong>
                    <?php echo limpiar_pqr($radicado); ?>
                </div>

                <div class="dato">
                    <strong>Tipo de solicitud</strong>
                    <?php echo limpiar_pqr(
                        $solicitud['tipo']
                    ); ?>
                </div>

                <div class="dato">
                    <strong>Fecha de registro</strong>
                    <?php echo limpiar_pqr(
                        date(
                            'd/m/Y',
                            strtotime($solicitud['fecha'])
                        )
                    ); ?>
                </div>

                <div class="dato">
                    <strong>Estado actual</strong>

                    <span class="estado">
                        <?php
                            $estado = $solicitud['estado'];

                            echo limpiar_pqr(
                                isset($estados[$estado])
                                ? $estados[$estado]
                                : 'En revisión'
                            );
                        ?>
                    </span>
                </div>

                <p class="descripcion">
                    Si tu solicitud fue respondida,
                    revisa el correo electrónico
                    registrado, incluida la carpeta
                    de correo no deseado.
                </p>

            </div>

        <?php endif; ?>

        <a href="pqr.php" class="volver">
            &larr; Volver al formulario PQR
        </a>

    </div>

</div>

</body>
</html>
