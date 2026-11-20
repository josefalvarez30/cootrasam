<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

include '../conexion.php';

$mensaje = "";
$error = "";

// Tarifas que se editan aquí. La clave (en minúscula) es la que busca la página pública,
// por eso el nombre NO se puede cambiar desde el panel.
$tarifas_editables = array(
    'pasaje regular' => array(
        'nombre' => 'Pasaje regular',
        'icono'  => 'fa-bus',
        'ayuda'  => 'Precio del recorrido completo entre Sampués y Sincelejo.'
    ),
    'tiquete' => array(
        'nombre' => 'Tiquete',
        'icono'  => 'fa-ticket',
        'ayuda'  => 'Precio del tiquete comprado en la base. En la descripción puedes decir dónde se compra.'
    )
);

$nota_encomiendas_inicial = 'Servicio aún no disponible, próximo a funcionar.';

function recortar($texto, $max) {
    $texto = trim($texto);
    return function_exists('mb_substr') ? mb_substr($texto, 0, $max, 'UTF-8') : substr($texto, 0, $max);
}

// Guarda un dato de la tabla configuracion: lo actualiza si ya existe, lo crea si no
function guardar_config($conexion, $clave, $valor) {
    $stmt = $conexion->prepare("SELECT clave FROM configuracion WHERE clave = ?");
    $stmt->bind_param("s", $clave);
    $stmt->execute();
    $existe = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($existe) {
        $stmt = $conexion->prepare("UPDATE configuracion SET valor = ? WHERE clave = ?");
        $stmt->bind_param("ss", $valor, $clave);
    } else {
        $stmt = $conexion->prepare("INSERT INTO configuracion (clave, valor) VALUES (?, ?)");
        $stmt->bind_param("ss", $clave, $valor);
    }
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

// Guarda el valor y la descripción de una tarifa: la actualiza si existe, la crea si no
function guardar_tarifa($conexion, $clave, $nombre, $valor, $descripcion) {
    $stmt = $conexion->prepare("SELECT id FROM tarifas WHERE LOWER(TRIM(tipo)) = ? LIMIT 1");
    $stmt->bind_param("s", $clave);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($fila) {
        $id = (int)$fila['id'];
        $stmt = $conexion->prepare("UPDATE tarifas SET valor = ?, descripcion = ? WHERE id = ?");
        $stmt->bind_param("ssi", $valor, $descripcion, $id);
    } else {
        $stmt = $conexion->prepare("INSERT INTO tarifas (tipo, valor, descripcion) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre, $valor, $descripcion);
    }
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

// ===== GUARDAR =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {

    if ($_POST['accion'] === 'guardar_tarifa') {
        $clave = isset($_POST['clave']) ? $_POST['clave'] : '';
        $valor = recortar(isset($_POST['valor']) ? $_POST['valor'] : '', 20);
        $descripcion = recortar(isset($_POST['descripcion']) ? $_POST['descripcion'] : '', 150);

        if (!isset($tarifas_editables[$clave])) {
            $error = "Esa tarifa no se puede editar.";
        } elseif ($valor === '') {
            $error = "Escribe el precio o valor.";
        } elseif (guardar_tarifa($conexion, $clave, $tarifas_editables[$clave]['nombre'], $valor, $descripcion)) {
            $mensaje = $tarifas_editables[$clave]['nombre'] . " actualizado correctamente.";
        } else {
            $error = "No se pudo guardar. Intenta de nuevo.";
        }
    }

    if ($_POST['accion'] === 'guardar_encomiendas') {
        $mostrar = (isset($_POST['mostrar']) && $_POST['mostrar'] === '1') ? '1' : '0';
        $nota = recortar(isset($_POST['nota']) ? $_POST['nota'] : '', 250);

        if (guardar_config($conexion, 'mostrar_encomiendas', $mostrar) && guardar_config($conexion, 'encomiendas_nota', $nota)) {
            $mensaje = "Apartado de encomiendas actualizado correctamente.";
        } else {
            $error = "No se pudo guardar. Intenta de nuevo.";
        }
    }
}

// ===== LEER LOS VALORES ACTUALES =====
$actual = array();
$res = $conexion->query("SELECT * FROM tarifas");
if ($res) {
    while ($f = $res->fetch_assoc()) {
        $actual[strtolower(trim($f['tipo']))] = $f;
    }
}

$mostrar_enc = '1';
$nota_enc = $nota_encomiendas_inicial;
$res = $conexion->query("SELECT clave, valor FROM configuracion WHERE clave IN ('mostrar_encomiendas', 'encomiendas_nota')");
if ($res) {
    while ($c = $res->fetch_assoc()) {
        if ($c['clave'] === 'mostrar_encomiendas') { $mostrar_enc = ($c['valor'] === '0') ? '0' : '1'; }
        if ($c['clave'] === 'encomiendas_nota' && trim($c['valor']) !== '') { $nota_enc = $c['valor']; }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Tarifas - Panel Admin Cootrasam</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
body { background: #f7f7f7; }

header { background: #1a1a1a; color: #fff; padding: 16px 30px; display: flex; justify-content: space-between; align-items: center; gap: 15px; }
header h1 { font-family: 'Oswald', sans-serif; font-size: 18px; color: #fb923c; }
header a { color: #fff; text-decoration: none; font-size: 13px; }

.container { max-width: 700px; margin: 30px auto; padding: 0 20px; }
.back-link { display: inline-block; margin-bottom: 18px; color: #c2410c; font-weight: 700; text-decoration: none; font-size: 14px; }
h2 { font-family: 'Oswald', sans-serif; color: #1a1a1a; margin-bottom: 6px; }
.subtitulo { color: #888; font-size: 13px; margin-bottom: 20px; line-height: 1.6; }

.mensaje-ok, .mensaje-error { padding: 10px 16px; border-radius: 8px; margin-bottom: 18px; font-size: 14px; }
.mensaje-ok { background: #dcfce7; color: #15803d; }
.mensaje-error { background: #fee2e2; color: #b91c1c; }

.tarifa-card { background: #fff; border-radius: 10px; padding: 22px 24px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); margin-bottom: 18px; border-top: 4px solid #c2410c; }
.tarifa-card .tipo-label { display: block; font-weight: 700; font-size: 13px; color: #c2410c; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 6px; }
.tarifa-card .ayuda { font-size: 12px; color: #888; margin-bottom: 16px; line-height: 1.5; }

label { display: block; font-size: 12px; font-weight: 700; margin-bottom: 4px; color: #555; }
input, textarea, select { width: 100%; padding: 9px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px; margin-bottom: 14px; background: #fff; }
textarea { min-height: 75px; resize: vertical; }
input:focus, textarea:focus, select:focus { outline: 2px solid #fed7aa; border-color: #c2410c; }

button { background: #c2410c; color: #fff; border: none; padding: 9px 20px; border-radius: 6px; font-weight: 700; font-size: 13px; cursor: pointer; }
button:hover { background: #a3360a; }

.separador { font-family: 'Oswald', sans-serif; font-size: 16px; color: #1a1a1a; margin: 28px 0 12px; }

@media (max-width: 500px) {
    header { padding: 15px; flex-wrap: wrap; }
    .container { margin: 22px auto; padding: 0 14px; }
    .tarifa-card { padding: 18px; }
}
</style>
</head>

<body>

<header>
    <h1><i class="fa-solid fa-bus"></i> Panel Cootrasam</h1>
    <a href="index.php">← Volver al panel</a>
</header>

<div class="container">

    <a href="index.php" class="back-link">← Volver</a>

    <h2><i class="fa-solid fa-ticket"></i> Tarifas</h2>

    <p class="subtitulo">
        Cambia el precio y la descripción de las tarifas que se ven en la página de Tarifas.
        Los nombres no se pueden cambiar para que la página siga funcionando bien.
    </p>

    <?php if ($mensaje): ?>
        <div class="mensaje-ok"><i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="mensaje-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php foreach ($tarifas_editables as $clave => $info):
        $fila = isset($actual[$clave]) ? $actual[$clave] : null;
        $v = $fila ? $fila['valor'] : '';
        $d = ($fila && $fila['descripcion'] !== null) ? $fila['descripcion'] : '';
    ?>
        <div class="tarifa-card">
            <span class="tipo-label"><i class="fa-solid <?php echo $info['icono']; ?>"></i> <?php echo htmlspecialchars($info['nombre']); ?></span>
            <p class="ayuda"><?php echo htmlspecialchars($info['ayuda']); ?></p>

            <form method="POST">
                <input type="hidden" name="accion" value="guardar_tarifa">
                <input type="hidden" name="clave" value="<?php echo htmlspecialchars($clave); ?>">

                <label for="valor_<?php echo md5($clave); ?>">Precio o valor</label>
                <input type="text" id="valor_<?php echo md5($clave); ?>" name="valor" value="<?php echo htmlspecialchars($v); ?>" maxlength="20" placeholder="Ej: $5.000" required>

                <label for="desc_<?php echo md5($clave); ?>">Descripción</label>
                <textarea id="desc_<?php echo md5($clave); ?>" name="descripcion" maxlength="150"><?php echo htmlspecialchars($d); ?></textarea>

                <button type="submit"><i class="fa-solid fa-floppy-disk"></i> Guardar cambios</button>
            </form>
        </div>
    <?php endforeach; ?>

    <div class="separador">Servicio de encomiendas</div>

    <div class="tarifa-card">
        <span class="tipo-label"><i class="fa-solid fa-box-open"></i> Apartado de encomiendas</span>
        <p class="ayuda">
            Aparece debajo del pasaje en la página de Tarifas. Si eliges "No", desaparece de la página,
            y también se quita la mención a encomiendas de los textos de esa página.
        </p>

        <form method="POST">
            <input type="hidden" name="accion" value="guardar_encomiendas">

            <label for="mostrar_enc">¿Mostrar en la página?</label>
            <select id="mostrar_enc" name="mostrar">
                <option value="1" <?php echo $mostrar_enc === '1' ? 'selected' : ''; ?>>Sí, mostrarlo</option>
                <option value="0" <?php echo $mostrar_enc === '0' ? 'selected' : ''; ?>>No, ocultarlo</option>
            </select>

            <label for="nota_enc">Mensaje que aparece en la nota</label>
            <textarea id="nota_enc" name="nota" maxlength="250"><?php echo htmlspecialchars($nota_enc); ?></textarea>

            <button type="submit"><i class="fa-solid fa-floppy-disk"></i> Guardar cambios</button>
        </form>
    </div>

</div>

</body>
</html>
