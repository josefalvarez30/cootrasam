<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

include '../conexion.php';
date_default_timezone_set('America/Bogota');

$bases = array('Sampués', 'Sincelejo');
$mensaje = "";
$aviso = "";

function hora_12($ts) {
    return date('g:i', $ts) . ' ' .
        (date('a', $ts) === 'am' ? 'a. m.' : 'p. m.');
}

function recortar_texto($t, $max) {
    $t = trim($t);

    return function_exists('mb_substr')
        ? mb_substr($t, 0, $max, 'UTF-8')
        : substr($t, 0, $max);
}

// Comprobar si existe la tabla salidas.
$tabla_ok = false;

try {
    $resultado_tabla = $conexion->query(
        "SELECT id, bus, origen, fecha_hora FROM salidas LIMIT 1"
    );

    $tabla_ok = ($resultado_tabla !== false);
} catch (Throwable $e) {
    $tabla_ok = false;
}

$ahora = time();
$hoy_inicio = date('Y-m-d', $ahora) . ' 00:00:00';
$hoy = date('Y-m-d', $ahora);

$msgs = array(
    'creada'      => 'Salida anotada.',
    'actualizada' => 'Salida actualizada.',
    'eliminada'   => 'Salida eliminada.',
    'limpiada'    => 'Se borraron las salidas de días anteriores.'
);

if (isset($_GET['msg']) && isset($msgs[$_GET['msg']])) {
    $mensaje = $msgs[$_GET['msg']];
}

$editando = null;
$lista = null;
$viejas = 0;
$buses_flota = array();

if ($tabla_ok) {

    // Guardar una salida nueva o actualizar una existente.
    if (
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['accion']) &&
        $_POST['accion'] === 'guardar'
    ) {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

        $bus = recortar_texto(
            isset($_POST['bus']) ? $_POST['bus'] : '',
            50
        );

        $bus = function_exists('mb_strtoupper')
            ? mb_strtoupper($bus, 'UTF-8')
            : strtoupper($bus);

        $origen = isset($_POST['origen']) ? $_POST['origen'] : '';
        $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : '';
        $hora = isset($_POST['hora']) ? $_POST['hora'] : '';

        $valido = (
            $bus !== '' &&
            in_array($origen, $bases, true) &&
            preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) &&
            preg_match('/^\d{2}:\d{2}$/', $hora)
        );

        // Validar que la fecha y la hora sean reales.
        if ($valido) {
            $fecha_obj = DateTime::createFromFormat('!Y-m-d', $fecha);
            $hora_obj = DateTime::createFromFormat('!H:i', $hora);

            $valido = (
                $fecha_obj &&
                $fecha_obj->format('Y-m-d') === $fecha &&
                $hora_obj &&
                $hora_obj->format('H:i') === $hora
            );
        }

        if ($valido) {
            $fecha_hora = $fecha . ' ' . $hora . ':00';

            if ($id > 0) {
                $stmt = $conexion->prepare(
                    "UPDATE salidas
                     SET bus = ?, origen = ?, fecha_hora = ?
                     WHERE id = ?"
                );

                if ($stmt) {
                    $stmt->bind_param(
                        "sssi",
                        $bus,
                        $origen,
                        $fecha_hora,
                        $id
                    );

                    $codigo = 'actualizada';
                }
            } else {
                $stmt = $conexion->prepare(
                    "INSERT INTO salidas (bus, origen, fecha_hora)
                     VALUES (?, ?, ?)"
                );

                if ($stmt) {
                    $stmt->bind_param(
                        "sss",
                        $bus,
                        $origen,
                        $fecha_hora
                    );

                    $codigo = 'creada';
                }
            }

            if (isset($stmt) && $stmt) {
                if ($stmt->execute()) {
                    $stmt->close();

                    header(
                        "Location: salidas.php?msg=" . $codigo .
                        "&o=" . urlencode($origen) .
                        "&f=" . urlencode($fecha)
                    );
                    exit;
                } else {
                    $aviso = "No se pudo guardar la salida. Intenta nuevamente.";
                    $stmt->close();
                }
            } else {
                $aviso = "No se pudo preparar el registro en la base de datos.";
            }
        } else {
            $aviso = "Revisa los datos: falta el bus, la base, la fecha o la hora.";
        }
    }

    // Eliminar una salida.
    if (isset($_GET['eliminar'])) {
        $id = (int) $_GET['eliminar'];

        if ($id > 0) {
            $stmt = $conexion->prepare(
                "DELETE FROM salidas WHERE id = ?"
            );

            if ($stmt) {
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();
            }
        }

        header("Location: salidas.php?msg=eliminada");
        exit;
    }

    // Borrar las salidas anteriores al día de hoy.
    if (isset($_GET['limpiar'])) {
        $stmt = $conexion->prepare(
            "DELETE FROM salidas WHERE fecha_hora < ?"
        );

        if ($stmt) {
            $stmt->bind_param("s", $hoy_inicio);
            $stmt->execute();
            $stmt->close();
        }

        header("Location: salidas.php?msg=limpiada");
        exit;
    }

    // Cargar una salida para editar.
    if (isset($_GET['editar'])) {
        $id = (int) $_GET['editar'];

        if ($id > 0) {
            $stmt = $conexion->prepare(
                "SELECT * FROM salidas WHERE id = ?"
            );

            if ($stmt) {
                $stmt->bind_param("i", $id);
                $stmt->execute();

                $resultado_edicion = $stmt->get_result();
                $editando = $resultado_edicion->fetch_assoc();

                $stmt->close();
            }
        }
    }

    // Listar las salidas de hoy en adelante.
    $stmt = $conexion->prepare(
        "SELECT *
         FROM salidas
         WHERE fecha_hora >= ?
         ORDER BY fecha_hora ASC, id ASC"
    );

    if ($stmt) {
        $stmt->bind_param("s", $hoy_inicio);
        $stmt->execute();
        $lista = $stmt->get_result();
        $stmt->close();
    }

    // Contar las salidas de días anteriores.
    $stmt = $conexion->prepare(
        "SELECT COUNT(*) AS n
         FROM salidas
         WHERE fecha_hora < ?"
    );

    if ($stmt) {
        $stmt->bind_param("s", $hoy_inicio);
        $stmt->execute();

        $fila_n = $stmt->get_result()->fetch_assoc();
        $viejas = isset($fila_n['n']) ? (int) $fila_n['n'] : 0;

        $stmt->close();
    }

    // Sugerencias de placas usadas anteriormente.
    $res_flota = $conexion->query(
        "SELECT DISTINCT bus FROM salidas ORDER BY bus LIMIT 60"
    );

    if ($res_flota) {
        while ($b = $res_flota->fetch_assoc()) {
            $buses_flota[] = $b['bus'];
        }
    }

    // Valores iniciales del formulario.
    if ($editando) {
        $f_bus = $editando['bus'];
        $f_origen = $editando['origen'];
        $f_fecha = date(
            'Y-m-d',
            strtotime($editando['fecha_hora'])
        );
        $f_hora = date(
            'H:i',
            strtotime($editando['fecha_hora'])
        );
    } else {
        $f_bus = '';

        $f_origen = (
            isset($_GET['o']) &&
            in_array($_GET['o'], $bases, true)
        ) ? $_GET['o'] : 'Sampués';

        $f_fecha = (
            isset($_GET['f']) &&
            preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['f'])
        ) ? $_GET['f'] : $hoy;

        $f_hora = '';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Salidas - Panel Admin Cootrasam</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Poppins', sans-serif;
}

body {
    background: #f7f7f7;
}

header {
    background: #1a1a1a;
    color: #fff;
    padding: 16px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

header h1 {
    font-family: 'Oswald', sans-serif;
    font-size: 18px;
    color: #fb923c;
}

header a {
    color: #fff;
    text-decoration: none;
    font-size: 13px;
}

.container {
    max-width: 900px;
    margin: 30px auto;
    padding: 0 20px;
}

.back-link {
    display: inline-block;
    margin-bottom: 18px;
    color: #c2410c;
    font-weight: 700;
    text-decoration: none;
    font-size: 14px;
}

h2 {
    font-family: 'Oswald', sans-serif;
    color: #1a1a1a;
    margin-bottom: 6px;
}

.subtitulo {
    color: #888;
    font-size: 13px;
    margin-bottom: 20px;
    line-height: 1.7;
}

.mensaje-ok {
    background: #dcfce7;
    color: #15803d;
    padding: 10px 16px;
    border-radius: 8px;
    margin-bottom: 18px;
    font-size: 14px;
}

.aviso {
    background: #fef9c3;
    color: #854d0e;
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 18px;
    font-size: 13px;
    line-height: 1.6;
}

.aviso code {
    display: block;
    background: #fff;
    padding: 10px;
    border-radius: 6px;
    margin-top: 8px;
    font-size: 12px;
    white-space: pre-wrap;
}

.form-box {
    background: #fff;
    border-radius: 10px;
    padding: 22px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    border-top: 4px solid #15803d;
    margin-bottom: 26px;
}

.form-box.modo-editar {
    border-top-color: #c2410c;
}

.form-box h3 {
    font-size: 15px;
    margin-bottom: 14px;
    color: #1a1a1a;
}

.fila {
    display: grid;
    grid-template-columns: 1.3fr 1fr 1fr 1fr;
    gap: 12px;
}

@media (max-width: 720px) {
    .fila {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 480px) {
    header {
        padding: 14px 16px;
        gap: 10px;
    }

    .container {
        padding: 0 14px;
    }

    .fila {
        grid-template-columns: 1fr;
    }

    .form-box {
        padding: 16px;
    }

    table {
        display: block;
        overflow-x: auto;
    }
}

label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    margin-bottom: 4px;
    color: #555;
}

input,
select {
    width: 100%;
    padding: 9px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
}

.acciones-form {
    margin-top: 14px;
    display: flex;
    gap: 10px;
    align-items: center;
}

button.guardar {
    background: #15803d;
    color: #fff;
    border: none;
    padding: 10px 22px;
    border-radius: 6px;
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
}

.modo-editar button.guardar {
    background: #c2410c;
}

.cancelar {
    color: #888;
    font-size: 13px;
    text-decoration: none;
}

.barra-lista {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    gap: 10px;
    flex-wrap: wrap;
}

.barra-lista h3 {
    font-size: 15px;
}

.btn-limpiar {
    font-size: 12px;
    color: #b91c1c;
    text-decoration: none;
    font-weight: 700;
}

table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
}

th,
td {
    padding: 12px 14px;
    text-align: left;
    font-size: 13px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}

th {
    background: #fff8f2;
    color: #c2410c;
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: 0.5px;
}

tr.paso td {
    color: #aaa;
}

.badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
}

.badge-pendiente {
    background: #dcfce7;
    color: #15803d;
}

.badge-paso {
    background: #f1f1f1;
    color: #888;
}

td a {
    margin-right: 10px;
    color: #c2410c;
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
}

td a.eliminar {
    color: #b91c1c;
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

    <h2>
        <i class="fa-solid fa-clock"></i>
        Salidas del despacho
    </h2>

    <p class="subtitulo">
        Anota la placa de cada bus que va a salir y la hora.
        En el sitio se ve como un tablero con reloj, y las que ya pasaron
        se ocultan solas.
        <a href="../tablero.php" target="_blank"
           style="color:#c2410c;font-weight:700;">
            Ver el tablero
        </a>
    </p>

    <?php if ($mensaje): ?>
        <div class="mensaje-ok">
            <?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <?php if ($aviso): ?>
        <div class="aviso">
            <?php echo htmlspecialchars($aviso, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <?php if (!$tabla_ok): ?>

        <div class="aviso">
            <strong>Falta crear la tabla de salidas.</strong>
            En phpMyAdmin, selecciona la base <em>cootrasam_db</em>,
            abre la pestaña SQL y ejecuta:

            <code>CREATE TABLE salidas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bus VARCHAR(50) NOT NULL,
    origen VARCHAR(20) NOT NULL,
    fecha_hora DATETIME NOT NULL,
    KEY idx_origen_fecha (origen, fecha_hora)
);</code>
        </div>

    <?php else: ?>

        <div class="form-box <?php echo $editando ? 'modo-editar' : ''; ?>">

            <h3>
                <?php echo $editando ? 'Editando una salida' : 'Anotar una salida'; ?>
            </h3>

            <form method="POST" action="salidas.php">

                <input type="hidden" name="accion" value="guardar">

                <input type="hidden" name="id"
                       value="<?php echo $editando ? (int) $editando['id'] : 0; ?>">

                <div class="fila">

                    <div>
                        <label for="bus">Placa del bus</label>

                        <input
                            type="text"
                            id="bus"
                            name="bus"
                            list="lista-buses"
                            placeholder="Ej: SXK 123"
                            value="<?php echo htmlspecialchars($f_bus, ENT_QUOTES, 'UTF-8'); ?>"
                            required
                            autocomplete="off"
                        >

                        <datalist id="lista-buses">
                            <?php foreach ($buses_flota as $nb): ?>
                                <option value="<?php echo htmlspecialchars($nb, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>

                    <div>
                        <label for="origen">Sale de</label>

                        <select id="origen" name="origen" required>
                            <?php foreach ($bases as $b): ?>
                                <option
                                    value="<?php echo htmlspecialchars($b, ENT_QUOTES, 'UTF-8'); ?>"
                                    <?php echo $f_origen === $b ? 'selected' : ''; ?>
                                >
                                    <?php echo htmlspecialchars($b, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="fecha">Fecha</label>

                        <input
                            type="date"
                            id="fecha"
                            name="fecha"
                            value="<?php echo htmlspecialchars($f_fecha, ENT_QUOTES, 'UTF-8'); ?>"
                            required
                        >
                    </div>

                    <div>
                        <label for="hora">Hora</label>

                        <input
                            type="time"
                            id="hora"
                            name="hora"
                            value="<?php echo htmlspecialchars($f_hora, ENT_QUOTES, 'UTF-8'); ?>"
                            required
                        >
                    </div>

                </div>

                <div class="acciones-form">

                    <button type="submit" class="guardar">
                        <?php echo $editando ? 'Guardar cambios' : 'Anotar salida'; ?>
                    </button>

                    <?php if ($editando): ?>
                        <a href="salidas.php" class="cancelar">Cancelar</a>
                    <?php endif; ?>

                </div>

            </form>
        </div>

        <div class="barra-lista">
            <h3>Salidas de hoy en adelante</h3>

            <?php if ($viejas > 0): ?>
                <a
                    href="?limpiar=1"
                    class="btn-limpiar"
                    onclick="return confirm('Se borrarán las <?php echo $viejas; ?> salidas de días anteriores. ¿Continuar?');"
                >
                    Borrar las de días anteriores (<?php echo $viejas; ?>)
                </a>
            <?php endif; ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Hora</th>
                    <th>Sale de</th>
                    <th>Placa</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>

            <?php if (!$lista || $lista->num_rows === 0): ?>
                <tr>
                    <td colspan="5" style="color:#999;">
                        No hay salidas anotadas.
                    </td>
                </tr>
            <?php else: ?>

                <?php while ($s = $lista->fetch_assoc()): ?>

                    <?php
                    $ts = strtotime($s['fecha_hora']);
                    $ya_salio = $ts < $ahora;

                    $dia = date('Y-m-d', $ts) === $hoy
                        ? ''
                        : date('d/m', $ts) . ' ';
                    ?>

                    <tr class="<?php echo $ya_salio ? 'paso' : ''; ?>">

                        <td>
                            <strong>
                                <?php echo $dia . hora_12($ts); ?>
                            </strong>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($s['origen'], ENT_QUOTES, 'UTF-8'); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($s['bus'], ENT_QUOTES, 'UTF-8'); ?>
                        </td>

                        <td>
                            <span class="badge <?php echo $ya_salio ? 'badge-paso' : 'badge-pendiente'; ?>">
                                <?php echo $ya_salio ? 'Ya salió' : 'Por salir'; ?>
                            </span>
                        </td>

                        <td>
                            <a href="?editar=<?php echo (int) $s['id']; ?>">
                                Editar
                            </a>

                            <a
                                href="?eliminar=<?php echo (int) $s['id']; ?>"
                                class="eliminar"
                                onclick="return confirm('¿Eliminar esta salida?');"
                            >
                                Eliminar
                            </a>
                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php endif; ?>

            </tbody>
        </table>

    <?php endif; ?>

</div>

</body>
</html>
