<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
include '../conexion.php';

$mensaje = "";
$error = "";

$dir_flota = '../assets/img/flota/';
if (!is_dir($dir_flota)) {
    mkdir($dir_flota, 0777, true);
}

// ===== REPARADOR AUTOMÁTICO DE POSICIONES (Normaliza orden a 1, 2, 3...) =====
$res_norm = $conexion->query("SELECT id FROM flota ORDER BY orden ASC, id ASC");
if ($res_norm) {
    $pos_i = 1;
    while ($r_norm = $res_norm->fetch_assoc()) {
        $stmt_fix = $conexion->prepare("UPDATE flota SET orden = ? WHERE id = ?");
        $stmt_fix->bind_param("ii", $pos_i, $r_norm['id']);
        $stmt_fix->execute();
        $stmt_fix->close();
        $pos_i++;
    }
}

// ===== MOVER POSICIÓN (FLECHAS ARRIBA / ABAJO) =====
if (isset($_GET['mover']) && isset($_GET['id'])) {
    $id_mover = intval($_GET['id']);
    $direccion = $_GET['mover']; // 'up' o 'down'

    $stmt_curr = $conexion->prepare("SELECT id, orden FROM flota WHERE id = ?");
    $stmt_curr->bind_param("i", $id_mover);
    $stmt_curr->execute();
    $curr = $stmt_curr->get_result()->fetch_assoc();
    $stmt_curr->close();

    if ($curr) {
        $curr_orden = intval($curr['orden']);

        if ($direccion === 'up') {
            $stmt_target = $conexion->prepare("SELECT id, orden FROM flota WHERE orden < ? ORDER BY orden DESC LIMIT 1");
            $stmt_target->bind_param("i", $curr_orden);
        } else {
            $stmt_target = $conexion->prepare("SELECT id, orden FROM flota WHERE orden > ? ORDER BY orden ASC LIMIT 1");
            $stmt_target->bind_param("i", $curr_orden);
        }

        $stmt_target->execute();
        $target = $stmt_target->get_result()->fetch_assoc();
        $stmt_target->close();

        if ($target) {
            $target_id = $target['id'];
            $target_orden = $target['orden'];

            $stmt_sw1 = $conexion->prepare("UPDATE flota SET orden = ? WHERE id = ?");
            $stmt_sw1->bind_param("ii", $target_orden, $id_mover);
            $stmt_sw1->execute();
            $stmt_sw1->close();

            $stmt_sw2 = $conexion->prepare("UPDATE flota SET orden = ? WHERE id = ?");
            $stmt_sw2->bind_param("ii", $curr_orden, $target_id);
            $stmt_sw2->execute();
            $stmt_sw2->close();
        }
    }
    // Redireccionar manteniendo el foco en la tabla
    header("Location: flota.php#tabla-buses");
    exit;
}

// ===== AGREGAR BUS =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'agregar') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $ruta_imagen = "";

    $res_m = $conexion->query("SELECT MAX(orden) as max_o FROM flota");
    $row_m = $res_m ? $res_m->fetch_assoc() : null;
    $orden = ($row_m && isset($row_m['max_o']) && $row_m['max_o'] !== null) ? intval($row_m['max_o']) + 1 : 1;

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        $permitidas = array('jpg', 'jpeg', 'png', 'webp');
        if (in_array($ext, $permitidas)) {
            $nombre_archivo = 'bus_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $destino = $dir_flota . $nombre_archivo;
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
                $ruta_imagen = 'assets/img/flota/' . $nombre_archivo;
            } else {
                $error = "Error al subir la imagen al servidor.";
            }
        } else {
            $error = "Formato de imagen no permitido (solo JPG, PNG, WEBP).";
        }
    }

    if (empty($error)) {
        $stmt = $conexion->prepare("INSERT INTO flota (nombre, descripcion, imagen, orden) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $nombre, $descripcion, $ruta_imagen, $orden);
        if ($stmt->execute()) {
            $mensaje = "Bus agregado correctamente a la flota.";
        } else {
            $error = "Error al guardar en la base de datos.";
        }
        $stmt->close();
    }
}

// ===== EDITAR BUS =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'editar') {
    $id = intval($_POST['id']);
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);

    $stmt_act = $conexion->prepare("SELECT imagen FROM flota WHERE id = ?");
    $stmt_act->bind_param("i", $id);
    $stmt_act->execute();
    $res_act = $stmt_act->get_result()->fetch_assoc();
    $stmt_act->close();

    $ruta_imagen = $res_act ? $res_act['imagen'] : "";

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        $permitidas = array('jpg', 'jpeg', 'png', 'webp');
        if (in_array($ext, $permitidas)) {
            $nombre_archivo = 'bus_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $destino = $dir_flota . $nombre_archivo;
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
                if (!empty($ruta_imagen) && file_exists('../' . $ruta_imagen)) {
                    @unlink('../' . $ruta_imagen);
                }
                $ruta_imagen = 'assets/img/flota/' . $nombre_archivo;
            }
        }
    }

    $stmt = $conexion->prepare("UPDATE flota SET nombre = ?, descripcion = ?, imagen = ? WHERE id = ?");
    $stmt->bind_param("sssi", $nombre, $descripcion, $ruta_imagen, $id);
    if ($stmt->execute()) {
        $mensaje = "Información del bus actualizada correctamente.";
    } else {
        $error = "Error al actualizar en la base de datos.";
    }
    $stmt->close();
}

// ===== ELIMINAR BUS =====
if (isset($_GET['eliminar'])) {
    $id_del = intval($_GET['eliminar']);
    $stmt_del = $conexion->prepare("SELECT imagen FROM flota WHERE id = ?");
    $stmt_del->bind_param("i", $id_del);
    $stmt_del->execute();
    $res_del = $stmt_del->get_result()->fetch_assoc();
    $stmt_del->close();

    if ($res_del) {
        if (!empty($res_del['imagen']) && file_exists('../' . $res_del['imagen'])) {
            @unlink('../' . $res_del['imagen']);
        }
        $stmt = $conexion->prepare("DELETE FROM flota WHERE id = ?");
        $stmt->bind_param("i", $id_del);
        $stmt->execute();
        $stmt->close();
        $mensaje = "Bus eliminado de la flota.";
    }
}

// Buscar bus a editar
$bus_editar = null;
if (isset($_GET['editar'])) {
    $id_edit = intval($_GET['editar']);
    $stmt_e = $conexion->prepare("SELECT * FROM flota WHERE id = ?");
    $stmt_e->bind_param("i", $id_edit);
    $stmt_e->execute();
    $bus_editar = $stmt_e->get_result()->fetch_assoc();
    $stmt_e->close();
}

// Listar buses ordenados
$flota = array();
$res_flota = $conexion->query("SELECT * FROM flota ORDER BY orden ASC, id ASC");
if ($res_flota) {
    while ($row = $res_flota->fetch_assoc()) {
        $flota[] = $row;
    }
}
$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestión de Flota - Panel Admin Cootrasam</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
  body { background: #f7f7f7; }
  header { background: #1a1a1a; color: #fff; padding: 16px 30px; display: flex; justify-content: space-between; align-items: center; }
  header h1 { font-family: 'Oswald', sans-serif; font-size: 18px; color: #fb923c; }
  header a { color: #fff; text-decoration: none; font-size: 13px; }
  .container { max-width: 950px; margin: 30px auto; padding: 0 20px; }
  .back-link { display:inline-block; margin-bottom: 18px; color:#c2410c; font-weight:700; text-decoration:none; font-size:14px; }
  h2 { font-family: 'Oswald', sans-serif; color:#1a1a1a; margin-bottom: 18px; }

  .alert-ok { background:#dcfce7; color:#15803d; padding:10px 16px; border-radius:8px; margin-bottom:18px; font-size:14px; }
  .alert-err { background:#fee2e2; color:#b91c1c; padding:10px 16px; border-radius:8px; margin-bottom:18px; font-size:14px; }

  .form-box { background:#fff; border-radius:10px; padding:24px; box-shadow:0 2px 10px rgba(0,0,0,0.06); border-top: 4px solid #c2410c; margin-bottom: 30px; }
  .form-box h3 { font-size:16px; margin-bottom:16px; color:#1a1a1a; }
  label { display:block; font-size:12px; font-weight:700; margin-bottom:4px; color:#555; }
  input[type=text], textarea { width: 100%; padding: 9px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px; margin-bottom: 14px; }
  textarea { height: 75px; resize: vertical; }
  input[type=file] { width: 100%; padding: 9px 12px; border: 1px dashed #ccc; border-radius: 6px; font-size: 13px; margin-bottom: 14px; background:#fafafa; }

  .btn-submit { background:#c2410c; color:#fff; border:none; padding:10px 22px; border-radius:6px; font-weight:700; font-size:13px; cursor:pointer; }
  .btn-submit:hover { background:#a3360a; }
  .btn-cancel { display:inline-block; margin-left:10px; color:#666; text-decoration:none; font-size:13px; }

  .tabla-box { background:#fff; border-radius:10px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.06); }
  table { width:100%; border-collapse:collapse; font-size:13px; }
  th { text-align:left; background:#f3f3f3; padding:10px 12px; color:#444; }
  td { padding:12px; border-bottom:1px solid #eee; vertical-align:middle; }
  .bus-thumb { width:70px; height:45px; object-fit:cover; border-radius:6px; background:#f0f0f0; border:1px solid #ddd; }
  .sin-foto { width:70px; height:45px; border-radius:6px; background:#eee; display:flex; align-items:center; justify-content:center; color:#aaa; font-size:11px; }

  .orden-controls { display:flex; align-items:center; gap:8px; }
  .orden-badge { background:#f3f4f6; color:#374151; font-weight:700; padding:4px 8px; border-radius:6px; font-size:12px; }
  .btn-arrow { display:inline-flex; align-items:center; justify-content:center; width:26px; height:26px; background:#e5e7eb; color:#1f2937; border-radius:4px; text-decoration:none; font-size:11px; font-weight:bold; }
  .btn-arrow:hover { background:#c2410c; color:#fff; }

  .btn-edit { color:#0284c7; text-decoration:none; font-weight:700; margin-right:12px; }
  .btn-del { color:#dc2626; text-decoration:none; font-weight:700; }
</style>
</head>
<body>
  <header>
    <h1><i class="fa-solid fa-bus"></i> Panel Cootrasam</h1>
    <a href="index.php">← Volver al panel</a>
  </header>

  <div class="container">
    <a href="index.php" class="back-link">← Volver al inicio del panel</a>
    <h2><i class="fa-solid fa-van-shuttle"></i> Gestión de Nuestra Flota</h2>

    <?php if ($mensaje): ?>
      <div class="alert-ok"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert-err"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- ===== FORMULARIO AGREGAR / EDITAR ===== -->
    <div class="form-box">
      <h3><?php echo $bus_editar ? 'Editar Bus' : 'Agregar Nuevo Bus'; ?></h3>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="accion" value="<?php echo $bus_editar ? 'editar' : 'agregar'; ?>">
        <?php if ($bus_editar): ?>
          <input type="hidden" name="id" value="<?php echo $bus_editar['id']; ?>">
        <?php endif; ?>

        <label>Nombre del Bus / Tipo</label>
        <input type="text" name="nombre" value="<?php echo $bus_editar ? htmlspecialchars($bus_editar['nombre']) : ''; ?>" placeholder="Ej: Microbús 18 Pasajeros" required>

        <label>Descripción corta</label>
        <textarea name="descripcion" placeholder="Ej: Vehículo equipado con aire acondicionado, silletería reclinable y GPS." required><?php echo $bus_editar ? htmlspecialchars($bus_editar['descripcion']) : ''; ?></textarea>

        <label>Foto del Bus</label>
        <?php if ($bus_editar && !empty($bus_editar['imagen'])): ?>
          <div style="margin-bottom:8px;">
            <img src="../<?php echo htmlspecialchars($bus_editar['imagen']); ?>" class="bus-thumb" alt="Foto actual">
            <span style="font-size:11px; color:#888;">Foto actual. Selecciona un archivo abajo solo si deseas cambiarla.</span>
          </div>
        <?php endif; ?>
        <input type="file" name="imagen" accept="image/*">

        <button type="submit" class="btn-submit"><?php echo $bus_editar ? 'Guardar Cambios' : 'Agregar Bus'; ?></button>
        <?php if ($bus_editar): ?>
          <a href="flota.php#tabla-buses" class="btn-cancel">Cancelar edición</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- ===== LISTADO DE BUSES CON ANCLA ===== -->
    <div class="tabla-box" id="tabla-buses">
      <h3 style="font-size:15px; margin-bottom:14px; color:#1a1a1a;">Buses registrados</h3>
      <?php if (count($flota) > 0): ?>
        <table>
          <thead>
            <tr>
              <th>Foto</th>
              <th>Nombre</th>
              <th>Descripción</th>
              <th>Posición / Orden</th>
              <th style="text-align:right;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $total_buses = count($flota);
            foreach ($flota as $index => $bus):
            ?>
              <tr>
                <td>
                  <?php if (!empty($bus['imagen']) && file_exists('../' . $bus['imagen'])): ?>
                    <img src="../<?php echo htmlspecialchars($bus['imagen']); ?>" class="bus-thumb" alt="Bus">
                  <?php else: ?>
                    <div class="sin-foto"><i class="fa-solid fa-bus"></i></div>
                  <?php endif; ?>
                </td>
                <td><strong><?php echo htmlspecialchars($bus['nombre']); ?></strong></td>
                <td><span style="color:#666; font-size:12px;"><?php echo htmlspecialchars($bus['descripcion']); ?></span></td>
                <td>
                  <div class="orden-controls">
                    <span class="orden-badge">#<?php echo ($index + 1); ?></span>
                    <?php if ($index > 0): ?>
                      <a href="flota.php?mover=up&id=<?php echo $bus['id']; ?>" class="btn-arrow" title="Subir posición"><i class="fa-solid fa-arrow-up"></i></a>
                    <?php endif; ?>
                    <?php if ($index < $total_buses - 1): ?>
                      <a href="flota.php?mover=down&id=<?php echo $bus['id']; ?>" class="btn-arrow" title="Bajar posición"><i class="fa-solid fa-arrow-down"></i></a>
                    <?php endif; ?>
                  </div>
                </td>
                <td style="text-align:right;">
                  <a href="flota.php?editar=<?php echo $bus['id']; ?>#tabla-buses" class="btn-edit"><i class="fa-solid fa-pen"></i> Editar</a>
                  <a href="flota.php?eliminar=<?php echo $bus['id']; ?>#tabla-buses" class="btn-del" onclick="return confirm('¿Seguro que deseas eliminar este bus?');"><i class="fa-solid fa-trash"></i> Eliminar</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p style="color:#888; font-size:13px; text-align:center; padding:20px 0;">No hay buses registrados aún. Agrega uno con el formulario de arriba.</p>
      <?php endif; ?>
    </div>

  </div>
</body>
</html>
