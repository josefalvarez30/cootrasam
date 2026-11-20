
<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../conexion.php';
date_default_timezone_set('America/Bogota');

function escapar_pqr($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function radicado_pqr($id, $fecha) {
    return 'PQR-' . date('Y', strtotime($fecha)) . '-' .
        str_pad($id, 4, '0', STR_PAD_LEFT);
}

function fecha_hora_pqr($fecha) {
    return empty($fecha) ? '—' :
        date('d/m/Y h:i a', strtotime($fecha));
}

function obtener_pqr($conexion, $id) {
    $stmt = $conexion->prepare(
        "SELECT * FROM pqr_mensajes WHERE id = ? LIMIT 1"
    );
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $fila = $resultado ? $resultado->fetch_assoc() : null;
    $stmt->close();
    return $fila;
}

if (empty($_SESSION['csrf_pqr'])) {
    if (!function_exists('openssl_random_pseudo_bytes')) {
        die('Se necesita OpenSSL para proteger el formulario.');
    }
    $fuerte = false;
    $bytes = openssl_random_pseudo_bytes(32, $fuerte);
    if ($bytes === false || !$fuerte) {
        die('No se pudo proteger el formulario.');
    }
    $_SESSION['csrf_pqr'] = bin2hex($bytes);
}

$csrf = $_SESSION['csrf_pqr'];

$estados = array(
    'nuevo' => 'Nuevo',
    'leido' => 'Leído',
    'respondido' => 'Respondido'
);

$mensaje_ok = '';
$mensaje_error = '';
$detalle = null;
$respuestas = array();
$lista = null;

$tabla_pqr_ok = (bool)$conexion->query(
    "SELECT id FROM pqr_mensajes LIMIT 1"
);

$tabla_respuestas_ok = (bool)$conexion->query(
    "SELECT id, pqr_id, respuesta, fecha,
            enviado, fecha_envio
     FROM pqr_respuestas LIMIT 1"
);

/*
 * ACCIONES DEL ADMINISTRADOR
 */
if ($tabla_pqr_ok && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = isset($_POST['csrf_token'])
        ? (string)$_POST['csrf_token'] : '';

    if (!hash_equals($csrf, $token)) {
        http_response_code(403);
        die('Sesión de seguridad vencida. Recarga la página.');
    }

    $accion = isset($_POST['accion'])
        ? (string)$_POST['accion'] : '';

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    $solicitud = $id > 0
        ? obtener_pqr($conexion, $id) : null;

    if (!$solicitud) {

        $mensaje_error = 'La solicitud indicada no existe.';

    } elseif ($accion === 'guardar_respuesta') {

        $texto = isset($_POST['respuesta'])
            ? trim($_POST['respuesta']) : '';

        if (!$tabla_respuestas_ok) {
            $mensaje_error = 'No se encontró la tabla de respuestas.';
        } elseif ($texto === '') {
            $mensaje_error = 'Escribe una respuesta.';
        } elseif (strlen($texto) > 20000) {
            $mensaje_error = 'La respuesta es demasiado extensa.';
        } else {

            $stmt = $conexion->prepare(
                "INSERT INTO pqr_respuestas
                 (pqr_id, respuesta, enviado)
                 VALUES (?, ?, 0)"
            );

            if ($stmt) {
                $stmt->bind_param('is', $id, $texto);

                if ($stmt->execute()) {
                    $mensaje_ok =
                        'Respuesta guardada como borrador. ' .
                        'Todavía no se ha enviado.';
                } else {
                    $mensaje_error =
                        'No se pudo guardar la respuesta.';
                }

                $stmt->close();
            } else {
                $mensaje_error =
                    'No se pudo preparar el guardado.';
            }
        }

    } elseif ($accion === 'editar_borrador') {

        $respuesta_id = isset($_POST['respuesta_id'])
            ? (int)$_POST['respuesta_id'] : 0;
        $texto = isset($_POST['respuesta'])
            ? trim($_POST['respuesta']) : '';

        if (!$tabla_respuestas_ok || $respuesta_id <= 0) {
            $mensaje_error = 'No se encontró el borrador.';
        } elseif ($texto === '') {
            $mensaje_error = 'La respuesta no puede estar vacía.';
        } elseif (strlen($texto) > 20000) {
            $mensaje_error = 'La respuesta es demasiado extensa.';
        } else {
            $stmt = $conexion->prepare(
                "UPDATE pqr_respuestas
                 SET respuesta = ?
                 WHERE id = ? AND pqr_id = ? AND enviado = 0"
            );
            if ($stmt) {
                $stmt->bind_param('sii', $texto, $respuesta_id, $id);
                if ($stmt->execute() && $stmt->affected_rows === 1) {
                    $mensaje_ok = 'Borrador actualizado correctamente.';
                } else {
                    $mensaje_error = 'No se pudo actualizar el borrador. Puede que ya haya sido enviado o no tenga cambios.';
                }
                $stmt->close();
            } else {
                $mensaje_error = 'No se pudo preparar la edición.';
            }
        }

    } elseif ($accion === 'eliminar_borrador') {

        $respuesta_id = isset($_POST['respuesta_id'])
            ? (int)$_POST['respuesta_id'] : 0;

        if (!$tabla_respuestas_ok || $respuesta_id <= 0) {
            $mensaje_error = 'No se encontró el borrador.';
        } else {
            $stmt = $conexion->prepare(
                "DELETE FROM pqr_respuestas
                 WHERE id = ? AND pqr_id = ? AND enviado = 0"
            );
            if ($stmt) {
                $stmt->bind_param('ii', $respuesta_id, $id);
                if ($stmt->execute() && $stmt->affected_rows === 1) {
                    $mensaje_ok = 'Borrador eliminado correctamente.';
                } else {
                    $mensaje_error = 'No se pudo eliminar. Puede que el borrador ya haya sido enviado.';
                }
                $stmt->close();
            } else {
                $mensaje_error = 'No se pudo preparar la eliminación.';
            }
        }

    } elseif ($accion === 'enviar_respuesta') {

        $respuesta_id = isset($_POST['respuesta_id'])
            ? (int)$_POST['respuesta_id'] : 0;

        $ruta_config = dirname(__FILE__) . '/../config/pqr_smtp_config.php';
        $ruta_mailer = dirname(__DIR__) .
            '/PHPMailer/PHPMailerAutoload.php';

        if (!$tabla_respuestas_ok || $respuesta_id <= 0) {

            $mensaje_error =
                'No se encontró la respuesta que deseas enviar.';

        } elseif (!filter_var(
            $solicitud['correo'],
            FILTER_VALIDATE_EMAIL
        )) {

            $mensaje_error =
                'El correo del destinatario no es válido.';

        } elseif (!is_file($ruta_config) ||
                  !is_file($ruta_mailer)) {

            $mensaje_error =
                'Falta la configuración SMTP o PHPMailer.';

        } else {

            /*
             * Evitar envíos simultáneos del mismo borrador.
             */
            $conexion->begin_transaction();

            $stmt = $conexion->prepare(
                "SELECT id, respuesta, enviado
                 FROM pqr_respuestas
                 WHERE id = ? AND pqr_id = ?
                 FOR UPDATE"
            );

            $borrador = null;

            if ($stmt) {
                $stmt->bind_param('ii', $respuesta_id, $id);

                if ($stmt->execute()) {
                    $resultado = $stmt->get_result();
                    if ($resultado) {
                        $borrador = $resultado->fetch_assoc();
                    }
                }

                $stmt->close();
            }

            if (!$borrador) {

                $conexion->rollback();
                $mensaje_error =
                    'El borrador no existe o no corresponde a esta PQR.';

            } elseif ((int)$borrador['enviado'] === 1) {

                $conexion->rollback();
                $mensaje_error =
                    'Esta respuesta ya fue enviada.';

            } else {

                $config = require $ruta_config;

                $campos_config = array(
                    'host', 'puerto', 'seguridad',
                    'usuario', 'clave', 'remitente',
                    'nombre_remitente'
                );

                $config_valida = is_array($config);

                foreach ($campos_config as $campo_config) {
                    if (!$config_valida ||
                        !isset($config[$campo_config]) ||
                        trim((string)$config[$campo_config]) === '') {
                        $config_valida = false;
                        break;
                    }
                }

                if (!$config_valida ||
                    $config['clave'] ===
                    'COLOCA_AQUI_TU_CLAVE_DE_APLICACION') {

                    $conexion->rollback();
                    $mensaje_error =
                        'La configuración SMTP está incompleta.';

                } else {

                    require_once $ruta_mailer;

                    $enviado = false;

                    try {

                        $mail = new PHPMailer(true);
                        $mail->isSMTP();

                        $mail->Host = $config['host'];
                        $mail->Port = (int)$config['puerto'];
                        $mail->SMTPAuth = true;
                        $mail->SMTPSecure = $config['seguridad'];
                        $mail->Username = $config['usuario'];
                        $mail->Password = $config['clave'];
                        $mail->CharSet = 'UTF-8';
                        $mail->Timeout = 20;

                        $mail->setFrom(
                            $config['remitente'],
                            $config['nombre_remitente']
                        );

                        $mail->addAddress(
                            $solicitud['correo'],
                            $solicitud['nombre']
                        );

                        $radicado_envio = radicado_pqr(
                            $id,
                            $solicitud['fecha']
                        );

                        $mail->Subject =
                            'Respuesta a su solicitud ' .
                            $radicado_envio . ' - COOTRASAM';

                        $mail->isHTML(false);

                        $mail->Body =
                            "COOTRASAM LTDA\n\n" .
                            "Hola " . $solicitud['nombre'] .
                            ",\n\n" .
                            "En respuesta a su solicitud " .
                            $radicado_envio . ":\n\n" .
                            $borrador['respuesta'] .
                            "\n\nAtentamente,\nCOOTRASAM LTDA";

                        $enviado = $mail->send();

                    } catch (Exception $e) {
                        error_log(
                            'Error SMTP PQR: ' .
                            $e->getMessage()
                        );
                    }

                    if (!$enviado) {

                        $conexion->rollback();

                        $mensaje_error =
                            'No se pudo enviar el correo. ' .
                            'El borrador sigue guardado.';

                    } else {

                        $stmt = $conexion->prepare(
                            "UPDATE pqr_respuestas
                             SET enviado = 1,
                                 fecha_envio = NOW()
                             WHERE id = ?
                               AND pqr_id = ?
                               AND enviado = 0"
                        );

                        $registro_ok = false;

                        if ($stmt) {
                            $stmt->bind_param(
                                'ii',
                                $respuesta_id,
                                $id
                            );

                            $registro_ok =
                                $stmt->execute() &&
                                $stmt->affected_rows === 1;

                            $stmt->close();
                        }

                        if ($registro_ok) {

                            $stmt = $conexion->prepare(
                                "UPDATE pqr_mensajes
                                 SET estado = 'respondido',
                                     leido = 1
                                 WHERE id = ?"
                            );

                            if ($stmt) {
                                $stmt->bind_param('i', $id);
                                $registro_ok = $stmt->execute();
                                $stmt->close();
                            } else {
                                $registro_ok = false;
                            }
                        }

                        if ($registro_ok &&
                            $conexion->commit()) {

                            $mensaje_ok =
                                'Correo enviado correctamente. ' .
                                'La solicitud quedó como Respondido.';

                        } else {

                            $conexion->rollback();

                            $mensaje_error =
                                'El correo fue aceptado, pero no ' .
                                'se pudo actualizar la base de datos. ' .
                                'No lo reenvíes sin revisar.';

                            error_log(
                                'PQR: correo enviado sin ' .
                                'confirmación en MySQL. ID: ' .
                                $respuesta_id
                            );
                        }
                    }
                }
            }
        }

    } elseif ($accion === 'cambiar_estado') {

        $nuevo_estado = isset($_POST['estado'])
            ? (string)$_POST['estado'] : '';

        if ($nuevo_estado !== 'nuevo' &&
            $nuevo_estado !== 'leido') {

            $mensaje_error =
                'Respondido se asigna al enviar el correo.';

        } elseif ($solicitud['estado'] === 'respondido') {

            $mensaje_error =
                'Esta solicitud ya figura como respondida.';

        } else {

            $stmt = $conexion->prepare(
                "UPDATE pqr_mensajes
                 SET estado = ?
                 WHERE id = ?"
            );

            if ($stmt) {
                $stmt->bind_param('si', $nuevo_estado, $id);

                if ($stmt->execute()) {
                    $mensaje_ok =
                        'Estado actualizado correctamente.';
                } else {
                    $mensaje_error =
                        'No se pudo actualizar el estado.';
                }

                $stmt->close();
            }
        }

    } else {

        $mensaje_error = 'Acción no reconocida.';
    }

    $_GET['ver'] = $id;
}

/*
 * DETALLE E HISTORIAL
 */
if ($tabla_pqr_ok && isset($_GET['ver'])) {

    $id_detalle = (int)$_GET['ver'];

    if ($id_detalle > 0) {
        $detalle = obtener_pqr($conexion, $id_detalle);
    }

    if ($detalle && $detalle['estado'] === 'nuevo') {

        $stmt = $conexion->prepare(
            "UPDATE pqr_mensajes
             SET estado = 'leido',
                 leido = 1
             WHERE id = ?
               AND estado = 'nuevo'"
        );

        if ($stmt) {
            $stmt->bind_param('i', $id_detalle);

            if ($stmt->execute()) {
                $detalle['estado'] = 'leido';
            }

            $stmt->close();
        }
    }

    if ($detalle && $tabla_respuestas_ok) {

        $stmt = $conexion->prepare(
            "SELECT id, respuesta, fecha,
                    enviado, fecha_envio
             FROM pqr_respuestas
             WHERE pqr_id = ?
             ORDER BY fecha DESC, id DESC"
        );

        if ($stmt) {
            $stmt->bind_param('i', $id_detalle);
            $stmt->execute();

            $resultado = $stmt->get_result();

            if ($resultado) {
                while ($fila = $resultado->fetch_assoc()) {
                    $respuestas[] = $fila;
                }
            }

            $stmt->close();
        }
    }
}

/*
 * LISTADO Y FILTROS
 */
$filtro = '';

if (isset($_GET['estado']) &&
    isset($estados[$_GET['estado']])) {
    $filtro = $_GET['estado'];
}

$conteos = array(
    'nuevo' => 0,
    'leido' => 0,
    'respondido' => 0
);

$total_todos = 0;
$total_paginas = 1;
$pagina = 1;

if ($tabla_pqr_ok) {

    $resultado = $conexion->query(
        "SELECT estado, COUNT(*) AS cantidad
         FROM pqr_mensajes
         GROUP BY estado"
    );

    if ($resultado) {
        while ($fila = $resultado->fetch_assoc()) {
            if (isset($conteos[$fila['estado']])) {
                $conteos[$fila['estado']] =
                    (int)$fila['cantidad'];
            }
        }
    }

    $total_todos = array_sum($conteos);

    $total_filtrado = $filtro !== ''
        ? $conteos[$filtro] : $total_todos;

    $por_pagina = 20;

    $pagina = isset($_GET['p'])
        ? max(1, (int)$_GET['p']) : 1;

    $total_paginas = max(
        1,
        (int)ceil($total_filtrado / $por_pagina)
    );

    if ($pagina > $total_paginas) {
        $pagina = $total_paginas;
    }

    $offset = ($pagina - 1) * $por_pagina;

    if ($filtro !== '') {

        $stmt_lista = $conexion->prepare(
            "SELECT id, nombre, correo, telefono,
                    tipo, medio, estado, fecha
             FROM pqr_mensajes
             WHERE estado = ?
             ORDER BY fecha DESC, id DESC
             LIMIT ? OFFSET ?"
        );

        if ($stmt_lista) {
            $stmt_lista->bind_param(
                'sii',
                $filtro,
                $por_pagina,
                $offset
            );
        }

    } else {

        $stmt_lista = $conexion->prepare(
            "SELECT id, nombre, correo, telefono,
                    tipo, medio, estado, fecha
             FROM pqr_mensajes
             ORDER BY fecha DESC, id DESC
             LIMIT ? OFFSET ?"
        );

        if ($stmt_lista) {
            $stmt_lista->bind_param(
                'ii',
                $por_pagina,
                $offset
            );
        }
    }

    if (isset($stmt_lista) && $stmt_lista) {
        $stmt_lista->execute();
        $lista = $stmt_lista->get_result();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Mensajes PQR - Panel Cootrasam</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Oswald:wght@500;600;700&display=swap"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f7f7f7;
            color: #222;
            font-family: 'Poppins', sans-serif;
        }

        header {
            background: #1a1a1a;
            color: white;
            padding: 16px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        header h1 {
            font-family: 'Oswald', sans-serif;
            font-size: 20px;
            color: #fb923c;
            margin: 0;
        }

        header a {
            color: white;
            text-decoration: none;
            font-size: 13px;
        }

        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }

        h2, h3 {
            font-family: 'Oswald', sans-serif;
        }

        h2 {
            margin: 0 0 20px;
        }

        .back-link {
            display: inline-block;
            color: #c2410c;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            margin-bottom: 15px;
        }

        .mensaje-ok,
        .mensaje-error {
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            line-height: 1.6;
        }

        .mensaje-ok {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .mensaje-error {
            background: #fff7ed;
            color: #9a3412;
            border: 1px solid #fdba74;
        }

        .tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 20px;
        }

        .tab {
            background: white;
            color: #555;
            padding: 9px 15px;
            border-radius: 30px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 1px 5px rgba(0,0,0,.07);
        }

        .tab.activa {
            background: #c2410c;
            color: white;
        }

        .tabla-contenedor {
            overflow-x: auto;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            text-align: left;
            padding: 13px 14px;
            border-bottom: 1px solid #eee;
            font-size: 12px;
            vertical-align: middle;
        }

        th {
            background: #fff8f2;
            color: #c2410c;
            font-size: 11px;
            text-transform: uppercase;
        }

        tr.nuevo td {
            background: #fffaf5;
            font-weight: 700;
        }

        td a {
            color: #c2410c;
            font-weight: 700;
            text-decoration: none;
        }

        .badge {
            display: inline-block;
            border-radius: 20px;
            padding: 5px 11px;
            font-size: 11px;
            font-weight: 700;
        }

        .badge-nuevo {
            background: #fee2e2;
            color: #b91c1c;
        }

        .badge-leido {
            background: #e0e7ff;
            color: #3730a3;
        }

        .badge-respondido {
            background: #dcfce7;
            color: #15803d;
        }

        .paginacion {
            display: flex;
            justify-content: center;
            gap: 7px;
            flex-wrap: wrap;
        }

        .paginacion a,
        .paginacion span {
            background: white;
            padding: 7px 12px;
            border-radius: 7px;
            font-size: 13px;
            color: #555;
            text-decoration: none;
        }

        .paginacion .actual {
            background: #c2410c;
            color: white;
        }

        .detalle,
        .panel-respuestas {
            background: white;
            border-radius: 12px;
            padding: 26px;
            margin-bottom: 22px;
            box-shadow: 0 2px 12px rgba(0,0,0,.06);
        }

        .detalle {
            border-top: 4px solid #c2410c;
        }

        .detalle-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 22px;
        }

        .detalle-top h3 {
            margin: 0;
            font-size: 24px;
        }

        .campos {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px 24px;
            margin-bottom: 25px;
        }

        .campo label {
            display: block;
            color: #888;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .campo span {
            font-size: 14px;
            overflow-wrap: anywhere;
        }

        .mensaje-texto {
            background: #fafafa;
            padding: 18px;
            border-radius: 9px;
            line-height: 1.7;
            font-size: 14px;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            margin: 10px 0 24px;
        }

        .acciones {
            border-top: 1px solid #eee;
            padding-top: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 17px;
            border: 0;
            border-radius: 7px;
            color: white;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
        }

        .btn-guardar {
            background: #1a1a1a;
        }

        .btn-naranja {
            background: #c2410c;
        }

        .btn-enviar {
            background: #15803d;
        }

        .btn-editar {
            background: #2563eb;
        }

        .btn-eliminar {
            background: #b91c1c;
        }

        .botones-borrador {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 9px;
            margin-top: 14px;
        }

        .botones-borrador form {
            margin: 0;
        }

        .form-editar-borrador {
            margin-top: 14px;
            padding: 16px;
            background: #fff8f2;
            border-radius: 9px;
        }

        .form-editar-borrador textarea {
            min-height: 140px;
        }

        .enlace-cancelar {
            display: inline-block;
            margin-left: 12px;
            color: #555;
            font-size: 13px;
            text-decoration: none;
            font-weight: 600;
        }

        .estado-form {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            margin-left: auto;
        }

        select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 7px;
            font-family: inherit;
        }

        .panel-respuestas h3 {
            margin: 0 0 10px;
            font-size: 23px;
        }

        .texto-ayuda {
            color: #666;
            font-size: 13px;
            line-height: 1.7;
            margin-bottom: 18px;
        }

        textarea {
            display: block;
            width: 100%;
            min-height: 180px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 9px;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
            margin-bottom: 15px;
        }

        textarea:focus {
            outline: 2px solid #fb923c;
            border-color: #c2410c;
        }

        .respuesta-item {
            border: 1px solid #eee;
            border-left: 4px solid #c2410c;
            border-radius: 8px;
            padding: 17px;
            margin-top: 14px;
        }

        .respuesta-cabecera {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 12px;
            color: #666;
        }

        .respuesta-texto {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            line-height: 1.7;
            font-size: 14px;
        }

        .etiqueta-borrador {
            background: #fef3c7;
            color: #92400e;
            padding: 4px 9px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 700;
        }

        .etiqueta-enviado {
            background: #dcfce7;
            color: #166534;
            padding: 4px 9px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 700;
        }

        @media (max-width: 640px) {
            header {
                padding: 15px;
            }

            .container {
                padding: 0 12px;
            }

            .detalle,
            .panel-respuestas {
                padding: 18px;
            }

            .campos {
                grid-template-columns: 1fr;
            }

            .estado-form {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>

<body>

<header>
    <h1>
        <i class="fa-solid fa-bus"></i>
        Panel Cootrasam
    </h1>

    <a href="index.php">
        Volver al panel
    </a>
</header>

<div class="container">

    <a class="back-link"
       href="<?php echo $detalle ? 'pqr.php' : 'index.php'; ?>">
        &larr; Volver<?php
            echo $detalle ? ' a la lista' : '';
        ?>
    </a>

    <h2>
        <i class="fa-solid fa-inbox"></i>
        Mensajes PQR
    </h2>

    <?php if ($mensaje_ok !== ''): ?>
        <div class="mensaje-ok">
            <?php echo escapar_pqr($mensaje_ok); ?>
        </div>
    <?php endif; ?>

    <?php if ($mensaje_error !== ''): ?>
        <div class="mensaje-error">
            <?php echo escapar_pqr($mensaje_error); ?>
        </div>
    <?php endif; ?>

    <?php if (!$tabla_pqr_ok): ?>

        <div class="mensaje-error">
            No se pudo consultar la tabla pqr_mensajes.
        </div>

    <?php elseif (isset($_GET['ver']) && !$detalle): ?>

        <div class="mensaje-error">
            No se encontró la solicitud indicada.
        </div>

    <?php elseif ($detalle): ?>

        <?php
            $radicado = radicado_pqr(
                $detalle['id'],
                $detalle['fecha']
            );

            $estado_actual =
                isset($estados[$detalle['estado']])
                ? $estados[$detalle['estado']]
                : $detalle['estado'];
        ?>

        <div class="detalle">

            <div class="detalle-top">

                <h3>
                    <?php echo escapar_pqr($radicado); ?>
                </h3>

                <span class="badge badge-<?php
                    echo escapar_pqr($detalle['estado']);
                ?>">
                    <?php echo escapar_pqr($estado_actual); ?>
                </span>

            </div>

            <div class="campos">

                <div class="campo">
                    <label>Recibido</label>
                    <span>
                        <?php echo escapar_pqr(
                            fecha_hora_pqr($detalle['fecha'])
                        ); ?>
                    </span>
                </div>

                <div class="campo">
                    <label>Tipo</label>
                    <span>
                        <?php echo escapar_pqr($detalle['tipo']); ?>
                    </span>
                </div>

                <div class="campo">
                    <label>Nombre</label>
                    <span>
                        <?php echo escapar_pqr($detalle['nombre']); ?>
                    </span>
                </div>

                <div class="campo">
                    <label>Documento</label>
                    <span>
                        <?php echo !empty($detalle['cedula'])
                            ? escapar_pqr($detalle['cedula'])
                            : '—'; ?>
                    </span>
                </div>

                <div class="campo">
                    <label>Teléfono</label>
                    <span>
                        <?php echo !empty($detalle['telefono'])
                            ? escapar_pqr($detalle['telefono'])
                            : '—'; ?>
                    </span>
                </div>

                <div class="campo">
                    <label>Correo electrónico</label>
                    <span>
                        <?php echo escapar_pqr($detalle['correo']); ?>
                    </span>
                </div>

                <div class="campo">
                    <label>Fecha relacionada</label>
                    <span>
                        <?php
                            echo !empty($detalle['fecha_relacionada'])
                            ? escapar_pqr(
                                date(
                                    'd/m/Y',
                                    strtotime(
                                        $detalle['fecha_relacionada']
                                    )
                                )
                            )
                            : '—';
                        ?>
                    </span>
                </div>

                <div class="campo">
                    <label>Medio registrado</label>
                    <span>
                        <?php echo
                            $detalle['medio'] === 'whatsapp'
                            ? 'WhatsApp'
                            : 'Correo'; ?>
                    </span>
                </div>

            </div>

            <div class="campo">
                <label>Descripción de la solicitud</label>
            </div>

            <div class="mensaje-texto"><?php
                echo escapar_pqr($detalle['mensaje']);
            ?></div>

            <!--
                Se eliminaron los botones:
                Abrir correo y Abrir WhatsApp.
            -->

            <?php if (
                $detalle['estado'] !== 'respondido'
            ): ?>

                <div class="acciones">

                    <form method="POST" class="estado-form">

                        <input type="hidden"
                               name="csrf_token"
                               value="<?php
                                   echo escapar_pqr($csrf);
                               ?>">

                        <input type="hidden"
                               name="accion"
                               value="cambiar_estado">

                        <input type="hidden"
                               name="id"
                               value="<?php
                                   echo (int)$detalle['id'];
                               ?>">

                        <select name="estado">

                            <option value="nuevo"
                                <?php echo
                                    $detalle['estado'] === 'nuevo'
                                    ? 'selected'
                                    : ''; ?>>
                                Nuevo
                            </option>

                            <option value="leido"
                                <?php echo
                                    $detalle['estado'] === 'leido'
                                    ? 'selected'
                                    : ''; ?>>
                                Leído
                            </option>

                        </select>

                        <button class="btn btn-guardar"
                                type="submit">
                            Guardar estado
                        </button>

                    </form>

                </div>

            <?php endif; ?>

        </div>

        <div class="panel-respuestas">

            <h3>
                <i class="fa-solid fa-pen-to-square"></i>
                Redactar respuesta
            </h3>

            <p class="texto-ayuda">
                Escribe una respuesta y guárdala como borrador.
                Después podrás enviarla al correo del usuario
                desde el historial.
            </p>

            <?php if (!$tabla_respuestas_ok): ?>

                <div class="mensaje-error">
                    No se pudo consultar la tabla pqr_respuestas.
                </div>

            <?php else: ?>

                <form method="POST">

                    <input type="hidden"
                           name="csrf_token"
                           value="<?php
                               echo escapar_pqr($csrf);
                           ?>">

                    <input type="hidden"
                           name="accion"
                           value="guardar_respuesta">

                    <input type="hidden"
                           name="id"
                           value="<?php
                               echo (int)$detalle['id'];
                           ?>">

                    <textarea name="respuesta"
                              maxlength="20000"
                              required
                              placeholder="Escribe aquí la respuesta para el usuario..."></textarea>

                    <button type="submit"
                            class="btn btn-naranja">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Guardar respuesta
                    </button>

                </form>

            <?php endif; ?>

        </div>

        <div class="panel-respuestas">

            <h3>
                <i class="fa-solid fa-clock-rotate-left"></i>
                Historial de respuestas
            </h3>

            <?php if (!$tabla_respuestas_ok): ?>

                <p class="texto-ayuda">
                    El historial no está disponible.
                </p>

            <?php elseif (count($respuestas) === 0): ?>

                <p class="texto-ayuda">
                    Todavía no hay respuestas guardadas
                    para esta solicitud.
                </p>

            <?php else: ?>

                <?php foreach ($respuestas as $respuesta): ?>

                    <div class="respuesta-item">

                        <div class="respuesta-cabecera">

                            <span>
                                Guardada:
                                <?php echo escapar_pqr(
                                    fecha_hora_pqr(
                                        $respuesta['fecha']
                                    )
                                ); ?>
                            </span>

                            <?php if (
                                (int)$respuesta['enviado'] === 1
                            ): ?>

                                <span class="etiqueta-enviado">
                                    Enviada por correo
                                </span>

                            <?php else: ?>

                                <span class="etiqueta-borrador">
                                    Borrador - No enviado
                                </span>

                            <?php endif; ?>

                        </div>

                        <div class="respuesta-texto"><?php
                            echo escapar_pqr(
                                $respuesta['respuesta']
                            );
                        ?></div>

                        <?php if ((int)$respuesta['enviado'] === 0): ?>

                            <?php
                                $editando = isset($_GET['editar']) &&
                                    (int)$_GET['editar'] === (int)$respuesta['id'];
                            ?>

                            <?php if ($editando): ?>
                                <form method="POST" class="form-editar-borrador">
                                    <input type="hidden" name="csrf_token"
                                           value="<?php echo escapar_pqr($csrf); ?>">
                                    <input type="hidden" name="accion" value="editar_borrador">
                                    <input type="hidden" name="id"
                                           value="<?php echo (int)$detalle['id']; ?>">
                                    <input type="hidden" name="respuesta_id"
                                           value="<?php echo (int)$respuesta['id']; ?>">

                                    <label for="editar-<?php echo (int)$respuesta['id']; ?>">
                                        <strong>Editar borrador antes de enviarlo</strong>
                                    </label>
                                    <textarea id="editar-<?php echo (int)$respuesta['id']; ?>"
                                              name="respuesta" maxlength="20000"
                                              required><?php echo escapar_pqr($respuesta['respuesta']); ?></textarea>

                                    <button type="submit" class="btn btn-naranja">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        Guardar cambios
                                    </button>
                                    <a class="enlace-cancelar"
                                       href="pqr.php?ver=<?php echo (int)$detalle['id']; ?>">
                                        Cancelar
                                    </a>
                                </form>
                            <?php endif; ?>

                            <div class="botones-borrador">
                                <form method="POST"
                                      onsubmit="return confirm('¿Enviar esta respuesta por correo al usuario?');">
                                    <input type="hidden" name="csrf_token"
                                           value="<?php echo escapar_pqr($csrf); ?>">
                                    <input type="hidden" name="accion" value="enviar_respuesta">
                                    <input type="hidden" name="id"
                                           value="<?php echo (int)$detalle['id']; ?>">
                                    <input type="hidden" name="respuesta_id"
                                           value="<?php echo (int)$respuesta['id']; ?>">
                                    <button type="submit" class="btn btn-enviar">
                                        <i class="fa-solid fa-paper-plane"></i>
                                        Enviar por correo
                                    </button>
                                </form>

                                <?php if (!$editando): ?>
                                    <a class="btn btn-editar"
                                       href="pqr.php?ver=<?php echo (int)$detalle['id']; ?>&amp;editar=<?php echo (int)$respuesta['id']; ?>">
                                        <i class="fa-solid fa-pen"></i>
                                        Editar
                                    </a>
                                <?php endif; ?>

                                <form method="POST"
                                      onsubmit="return confirm('¿Eliminar definitivamente este borrador? Esta acción no se puede deshacer.');">
                                    <input type="hidden" name="csrf_token"
                                           value="<?php echo escapar_pqr($csrf); ?>">
                                    <input type="hidden" name="accion" value="eliminar_borrador">
                                    <input type="hidden" name="id"
                                           value="<?php echo (int)$detalle['id']; ?>">
                                    <input type="hidden" name="respuesta_id"
                                           value="<?php echo (int)$respuesta['id']; ?>">
                                    <button type="submit" class="btn btn-eliminar">
                                        <i class="fa-solid fa-trash"></i>
                                        Eliminar
                                    </button>
                                </form>
                            </div>

                        <?php endif; ?>

                        <?php if (
                            (int)$respuesta['enviado'] === 1 &&
                            !empty($respuesta['fecha_envio'])
                        ): ?>

                            <p class="texto-ayuda">
                                Fecha de envío registrada:
                                <?php echo escapar_pqr(
                                    fecha_hora_pqr(
                                        $respuesta['fecha_envio']
                                    )
                                ); ?>
                            </p>

                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>

    <?php else: ?>

        <div class="tabs">

            <a class="tab <?php
                echo $filtro === '' ? 'activa' : '';
            ?>" href="pqr.php">
                Todos (<?php echo $total_todos; ?>)
            </a>

            <?php foreach ($estados as $clave => $etiqueta): ?>

                <a class="tab <?php
                    echo $filtro === $clave
                        ? 'activa'
                        : '';
                ?>" href="pqr.php?estado=<?php
                    echo urlencode($clave);
                ?>">

                    <?php echo escapar_pqr($etiqueta); ?>
                    (<?php echo $conteos[$clave]; ?>)

                </a>

            <?php endforeach; ?>

        </div>

        <div class="tabla-contenedor">

            <table>

                <thead>
                    <tr>
                        <th>Radicado</th>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Medio</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (
                        !$lista ||
                        $lista->num_rows === 0
                    ): ?>

                        <tr>
                            <td colspan="9">
                                No hay solicitudes con este filtro.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php while (
                            $fila = $lista->fetch_assoc()
                        ): ?>

                            <tr class="<?php
                                echo $fila['estado'] === 'nuevo'
                                    ? 'nuevo'
                                    : '';
                            ?>">

                                <td>
                                    <a href="pqr.php?ver=<?php
                                        echo (int)$fila['id'];
                                    ?>">
                                        <?php echo escapar_pqr(
                                            radicado_pqr(
                                                $fila['id'],
                                                $fila['fecha']
                                            )
                                        ); ?>
                                    </a>
                                </td>

                                <td>
                                    <?php echo escapar_pqr(
                                        fecha_hora_pqr(
                                            $fila['fecha']
                                        )
                                    ); ?>
                                </td>

                                <td>
                                    <?php echo escapar_pqr(
                                        $fila['tipo']
                                    ); ?>
                                </td>

                                <td>
                                    <?php echo escapar_pqr(
                                        $fila['nombre']
                                    ); ?>
                                </td>

                                <td>
                                    <?php echo escapar_pqr(
                                        $fila['correo']
                                    ); ?>
                                </td>

                                <td>
                                    <?php echo escapar_pqr(
                                        $fila['telefono']
                                    ); ?>
                                </td>

                                <td>
                                    <?php echo
                                        $fila['medio'] === 'whatsapp'
                                        ? 'WhatsApp'
                                        : 'Correo'; ?>
                                </td>

                                <td>
                                    <span class="badge badge-<?php
                                        echo escapar_pqr(
                                            $fila['estado']
                                        );
                                    ?>">

                                        <?php echo escapar_pqr(
                                            isset(
                                                $estados[$fila['estado']]
                                            )
                                            ? $estados[$fila['estado']]
                                            : $fila['estado']
                                        ); ?>

                                    </span>
                                </td>

                                <td>
                                    <a href="pqr.php?ver=<?php
                                        echo (int)$fila['id'];
                                    ?>">
                                        Ver
                                    </a>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

        <?php if ($total_paginas > 1): ?>

            <div class="paginacion">

                <?php for (
                    $i = 1;
                    $i <= $total_paginas;
                    $i++
                ): ?>

                    <?php if ($i === $pagina): ?>

                        <span class="actual">
                            <?php echo $i; ?>
                        </span>

                    <?php else: ?>

                        <a href="pqr.php?p=<?php
                            echo $i;
                        ?><?php
                            echo $filtro !== ''
                                ? '&amp;estado=' .
                                  urlencode($filtro)
                                : '';
                        ?>">
                            <?php echo $i; ?>
                        </a>

                    <?php endif; ?>

                <?php endfor; ?>

            </div>

        <?php endif; ?>

    <?php endif; ?>

</div>

</body>
</html>
