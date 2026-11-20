
<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

date_default_timezone_set('America/Bogota');

/*
 * COOTRASAM - Registro de PQR
 *
 * 1. Valida el formulario.
 * 2. Guarda la solicitud en MySQL.
 * 3. Genera el radicado.
 * 4. Envía confirmación al usuario.
 * 5. Notifica al administrador.
 *
 * Compatible con PHP 5.6 y PHPMailer 5.
 */

function respuesta_pqr($ok, $mensaje, $codigo, $radicado) {

    http_response_code($codigo);

    echo json_encode(array(
        'ok' => $ok,
        'mensaje' => $mensaje,
        'radicado' => $radicado
    ));

    exit;
}

function dato_pqr($nombre) {

    if (
        !isset($_POST[$nombre]) ||
        !is_string($_POST[$nombre])
    ) {
        return '';
    }

    return trim($_POST[$nombre]);
}

function longitud_pqr($texto) {

    if (function_exists('mb_strlen')) {
        return mb_strlen($texto, 'UTF-8');
    }

    return strlen($texto);
}

/*
 * Enviar un correo utilizando PHPMailer.
 *
 * Devuelve true si el servidor SMTP
 * acepta el mensaje.
 */
function enviar_correo_pqr(
    $config,
    $destinatario,
    $nombre_destinatario,
    $asunto,
    $contenido
) {

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
            $destinatario,
            $nombre_destinatario
        );

        $mail->isHTML(false);

        $mail->Subject = $asunto;

        $mail->Body = $contenido;

        return $mail->send();

    } catch (Exception $e) {

        error_log(
            'PQR: error al enviar correo: ' .
            $e->getMessage()
        );

        return false;
    }
}

/*
 * Validar método de envío.
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    respuesta_pqr(
        false,
        'Método no permitido.',
        405,
        ''
    );
}

/*
 * Validar token de seguridad.
 */
$token = dato_pqr('pqr_token');

if (
    empty($_SESSION['pqr_token']) ||
    $token === '' ||
    !function_exists('hash_equals') ||
    !hash_equals($_SESSION['pqr_token'], $token)
) {

    respuesta_pqr(
        false,
        'La sesión del formulario venció. Actualiza la página e intenta nuevamente.',
        403,
        ''
    );
}

/*
 * Campo invisible contra envíos automáticos.
 */
if (dato_pqr('website') !== '') {

    respuesta_pqr(
        false,
        'No fue posible procesar la solicitud.',
        400,
        ''
    );
}

/*
 * Recibir los datos del formulario.
 */
$tipo = dato_pqr('tipo');

$nombre = dato_pqr('nombre');

$correo = dato_pqr('correo');

$telefono = dato_pqr('telefono');

$cedula = dato_pqr('cedula');

$descripcion = dato_pqr('descripcion');

$fecha_texto = dato_pqr('fecha');

$acepta = dato_pqr('acepta_datos');

/*
 * Validar tipo de solicitud.
 */
$tipos = array(
    'Petición',
    'Queja',
    'Reclamo',
    'Sugerencia',
    'Felicitación'
);

if (!in_array($tipo, $tipos, true)) {

    respuesta_pqr(
        false,
        'Selecciona un tipo de solicitud válido.',
        400,
        ''
    );
}

/*
 * Validar nombre.
 */
if (
    longitud_pqr($nombre) < 3 ||
    longitud_pqr($nombre) > 100
) {

    respuesta_pqr(
        false,
        'Escribe un nombre válido.',
        400,
        ''
    );
}

/*
 * Validar correo.
 */
if (
    longitud_pqr($correo) > 100 ||
    !filter_var($correo, FILTER_VALIDATE_EMAIL)
) {

    respuesta_pqr(
        false,
        'Escribe un correo electrónico válido.',
        400,
        ''
    );
}

/*
 * Validar teléfono.
 */
if (
    longitud_pqr($telefono) < 7 ||
    longitud_pqr($telefono) > 20 ||
    !preg_match('/^[0-9+() .-]+$/', $telefono)
) {

    respuesta_pqr(
        false,
        'Escribe un teléfono válido.',
        400,
        ''
    );
}

/*
 * Validar documento.
 */
if (longitud_pqr($cedula) > 30) {

    respuesta_pqr(
        false,
        'El documento es demasiado largo.',
        400,
        ''
    );
}

/*
 * Validar descripción.
 */
if (
    longitud_pqr($descripcion) < 10 ||
    longitud_pqr($descripcion) > 5000
) {

    respuesta_pqr(
        false,
        'La descripción debe tener entre 10 y 5000 caracteres.',
        400,
        ''
    );
}

/*
 * Validar aceptación de tratamiento de datos.
 */
if ($acepta !== '1') {

    respuesta_pqr(
        false,
        'Debes aceptar la política de tratamiento de datos.',
        400,
        ''
    );
}

/*
 * Validar fecha relacionada.
 */
$fecha_relacionada = null;

if ($fecha_texto !== '') {

    $fecha_objeto = DateTime::createFromFormat(
        '!Y-m-d',
        $fecha_texto
    );

    if (
        !$fecha_objeto ||
        $fecha_objeto->format('Y-m-d') !== $fecha_texto
    ) {

        respuesta_pqr(
            false,
            'La fecha relacionada no es válida.',
            400,
            ''
        );
    }

    $fecha_relacionada = $fecha_texto;
}

/*
 * Convertir documento vacío a NULL.
 */
if ($cedula === '') {
    $cedula = null;
}

/*
 * Conexión con MySQL.
 */
require_once __DIR__ . '/conexion.php';

if (
    !isset($conexion) ||
    !($conexion instanceof mysqli)
) {

    respuesta_pqr(
        false,
        'No se pudo conectar con el sistema.',
        500,
        ''
    );
}

/*
 * Guardar solicitud.
 *
 * Esta parte mantiene el funcionamiento
 * que ya tenías.
 */
$ahora = date('Y-m-d H:i:s');

$medio = 'correo';

$estado = 'nuevo';

$sql = "INSERT INTO pqr_mensajes
    (
        nombre,
        correo,
        telefono,
        cedula,
        tipo,
        mensaje,
        fecha_relacionada,
        medio,
        estado,
        fecha
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conexion->prepare($sql);

if (!$stmt) {

    error_log(
        'PQR: error al preparar INSERT: ' .
        $conexion->error
    );

    respuesta_pqr(
        false,
        'No fue posible preparar el registro. Contacta a COOTRASAM.',
        500,
        ''
    );
}

$stmt->bind_param(
    'ssssssssss',
    $nombre,
    $correo,
    $telefono,
    $cedula,
    $tipo,
    $descripcion,
    $fecha_relacionada,
    $medio,
    $estado,
    $ahora
);

if (!$stmt->execute()) {

    error_log(
        'PQR: error al guardar: ' .
        $stmt->error
    );

    $stmt->close();

    respuesta_pqr(
        false,
        'No fue posible guardar tu solicitud. Intenta nuevamente.',
        500,
        ''
    );
}

$id = (int)$conexion->insert_id;

$stmt->close();

/*
 * Generar número de radicado.
 */
$radicado = 'PQR-' .
    date('Y', strtotime($ahora)) .
    '-' .
    str_pad($id, 4, '0', STR_PAD_LEFT);

/*
 * A partir de aquí, la PQR ya está
 * guardada en la base de datos.
 *
 * Los correos son notificaciones
 * adicionales y no deben impedir
 * mostrar el radicado.
 */

/*
 * Configuración SMTP existente.
 */
$ruta_config = dirname(__FILE__) . '/config/pqr_smtp_config.php';

$ruta_mailer = __DIR__ .
    '/PHPMailer/PHPMailerAutoload.php';

if (
    is_file($ruta_config) &&
    is_file($ruta_mailer)
) {

    $config = require $ruta_config;

    $campos_config = array(
        'host',
        'puerto',
        'seguridad',
        'usuario',
        'clave',
        'remitente',
        'nombre_remitente',
        'correo_notificaciones'
    );

    $config_valida = is_array($config);

    foreach ($campos_config as $campo_config) {

        if (
            !$config_valida ||
            !isset($config[$campo_config]) ||
            trim((string)$config[$campo_config]) === ''
        ) {

            $config_valida = false;

            break;
        }
    }

    if (
        $config_valida &&
        filter_var(
            $config['correo_notificaciones'],
            FILTER_VALIDATE_EMAIL
        ) &&
        filter_var(
            $config['remitente'],
            FILTER_VALIDATE_EMAIL
        )
    ) {

        require_once $ruta_mailer;

        /*
         * CORREO 1:
         * Confirmación para el usuario.
         */
        $asunto_usuario =
            'Confirmación de solicitud ' .
            $radicado . ' - COOTRASAM';

        $mensaje_usuario =
            "COOTRASAM LTDA\n\n" .
            "Hola " . $nombre . ",\n\n" .
            "Hemos recibido correctamente tu solicitud.\n\n" .
            "Número de radicado: " . $radicado . "\n" .
            "Tipo de solicitud: " . $tipo . "\n" .
            "Fecha de registro: " .
            date('d/m/Y h:i a', strtotime($ahora)) .
            "\n\n" .
            "Puedes consultar el estado de tu solicitud " .
            "en la sección 'Consultar mi PQR' " .
            "de nuestra página web.\n\n" .
            "Para realizar la consulta necesitarás " .
            "tu número de radicado y el correo " .
            "electrónico registrado.\n\n" .
            "Conserva este mensaje como comprobante " .
            "de recepción.\n\n" .
            "Atentamente,\n" .
            "COOTRASAM LTDA";

        $correo_usuario_enviado = enviar_correo_pqr(
            $config,
            $correo,
            $nombre,
            $asunto_usuario,
            $mensaje_usuario
        );

        if (!$correo_usuario_enviado) {

            error_log(
                'PQR: no se pudo enviar confirmación ' .
                'al usuario. Radicado: ' .
                $radicado
            );
        }

        /*
         * CORREO 2:
         * Aviso al administrador.
         */
        $asunto_admin =
            'Nueva solicitud PQR ' .
            $radicado . ' - COOTRASAM';

        $mensaje_admin =
            "COOTRASAM - NUEVA SOLICITUD PQR\n\n" .
            "Se ha registrado una nueva solicitud.\n\n" .
            "Radicado: " . $radicado . "\n" .
            "Fecha: " .
            date('d/m/Y h:i a', strtotime($ahora)) .
            "\n" .
            "Tipo: " . $tipo . "\n" .
            "Nombre: " . $nombre . "\n" .
            "Correo: " . $correo . "\n" .
            "Teléfono: " . $telefono . "\n\n" .
            "Descripción:\n" .
            $descripcion . "\n\n" .
            "Ingresa al panel administrativo " .
            "de COOTRASAM para revisar y " .
            "responder la solicitud.\n\n" .
            "Este es un mensaje automático.";

        $correo_admin_enviado = enviar_correo_pqr(
            $config,
            $config['correo_notificaciones'],
            'Administración COOTRASAM',
            $asunto_admin,
            $mensaje_admin
        );

        if (!$correo_admin_enviado) {

            error_log(
                'PQR: no se pudo enviar notificación ' .
                'al administrador. Radicado: ' .
                $radicado
            );
        }

    } else {

        error_log(
            'PQR: configuración SMTP incompleta ' .
            'o correo de notificaciones inválido. ' .
            'Radicado: ' . $radicado
        );
    }

} else {

    error_log(
        'PQR: no se encontró la configuración SMTP ' .
        'o PHPMailer. Radicado: ' . $radicado
    );
}

/*
 * Responder al formulario.
 *
 * Aunque algún correo falle, la PQR
 * ya quedó registrada en MySQL.
 */
respuesta_pqr(
    true,
    'Tu solicitud fue registrada correctamente.',
    200,
    $radicado
);
