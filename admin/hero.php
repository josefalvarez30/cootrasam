
<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
include '../conexion.php';

$mensaje = "";
$carpeta_imagenes = "../assets/img/hero/";
$ruta_publica = "assets/img/hero/";

// AGREGAR FOTO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['accion']) ? $_POST['accion'] : '') === 'agregar') {
    if (isset($_FILES['imagen_nueva']) && $_FILES['imagen_nueva']['error'] === 0) {
        if (!is_dir($carpeta_imagenes)) {
            mkdir($carpeta_imagenes, 0777, true);
        }

        $nombre_original = basename($_FILES['imagen_nueva']['name']);
        $nombre_archivo = time() . '_' .
            preg_replace('/[^A-Za-z0-9.\-_]/', '', $nombre_original);
        $destino = $carpeta_imagenes . $nombre_archivo;

        if (move_uploaded_file($_FILES['imagen_nueva']['tmp_name'], $destino)) {
            $imagen = $ruta_publica . $nombre_archivo;

            $res = $conexion->query("SELECT MAX(orden) AS maximo FROM hero_fotos");
            $fila = $res ? $res->fetch_assoc() : null;
            $orden = (int)(isset($fila['maximo']) ? $fila['maximo'] : 0) + 1;

            $stmt = $conexion->prepare(
                "INSERT INTO hero_fotos (imagen, orden) VALUES (?, ?)"
            );
            $stmt->bind_param("si", $imagen, $orden);
            $stmt->execute();
            $stmt->close();

            $mensaje = "Foto agregada correctamente.";
        } else {
            $mensaje = "No se pudo subir la foto.";
        }
    } else {
        $mensaje = "Debes seleccionar una foto.";
    }
}

// MOVER FOTO
if (isset($_GET['mover'], $_GET['direccion'])) {
    $id = (int)$_GET['mover'];
    $direccion = $_GET['direccion'];

    $stmt = $conexion->prepare("SELECT id, orden FROM hero_fotos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $actual = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($actual && in_array($direccion, ['arriba', 'abajo'], true)) {
        if ($direccion === 'arriba') {
            $stmt = $conexion->prepare(
                "SELECT id, orden FROM hero_fotos WHERE orden < ? ORDER BY orden DESC LIMIT 1"
            );
        } else {
            $stmt = $conexion->prepare(
                "SELECT id, orden FROM hero_fotos WHERE orden > ? ORDER BY orden ASC LIMIT 1"
            );
        }

        $stmt->bind_param("i", $actual['orden']);
        $stmt->execute();
        $vecina = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($vecina) {
            $stmt = $conexion->prepare("UPDATE hero_fotos SET orden = ? WHERE id = ?");
            $stmt->bind_param("ii", $vecina['orden'], $actual['id']);
            $stmt->execute();
            $stmt->bind_param("ii", $actual['orden'], $vecina['id']);
            $stmt->execute();
            $stmt->close();
            $mensaje = "Orden actualizado.";
        }
    }
}

// ELIMINAR FOTO
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];

    $stmt = $conexion->prepare("SELECT imagen FROM hero_fotos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $foto = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stmt = $conexion->prepare("DELETE FROM hero_fotos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Borra también el archivo para no dejar imágenes huérfanas.
    if ($foto && strpos($foto['imagen'], $ruta_publica) === 0) {
        $archivo = '../' . $foto['imagen'];
        if (is_file($archivo)) {
            unlink($archivo);
        }
    }

    $mensaje = "Foto eliminada.";
}

$fotos = $conexion->query("SELECT * FROM hero_fotos ORDER BY orden, id");
$total_fotos = $fotos ? $fotos->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Fotos del Hero - Panel Admin Cootrasam</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0;font-family:'Poppins',sans-serif}
body{background:#f7f7f7;color:#222}
header{background:#1a1a1a;color:white;padding:16px 30px;display:flex;justify-content:space-between;align-items:center}
header h1{font-family:'Oswald',sans-serif;font-size:18px;color:#fb923c}
header a{color:white;text-decoration:none;font-size:13px}
.container{max-width:800px;margin:30px auto;padding:0 20px}
.back-link{display:inline-block;margin-bottom:18px;color:#c2410c;font-weight:700;text-decoration:none;font-size:14px}
h2{font-family:'Oswald',sans-serif;margin-bottom:6px}
.subtitulo{color:#888;font-size:13px;margin-bottom:20px}
.mensaje{background:#dcfce7;color:#166534;padding:10px 16px;border-radius:8px;margin-bottom:18px;font-size:14px}
.fotos-lista{display:flex;flex-direction:column;gap:12px;margin-bottom:28px}
.foto-fila{background:white;border-radius:10px;box-shadow:0 2px 10px #0001;display:flex;align-items:center;gap:14px;padding:10px 14px;flex-wrap:wrap}
.numero{width:28px;height:28px;border-radius:50%;background:#c2410c;color:white;font-weight:700;font-size:13px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.foto-fila img{width:165px;height:55px;object-fit:cover;border-radius:6px;flex-shrink:0}
.acciones{margin-left:auto;display:flex;align-items:center;gap:6px}
.btn-mover{background:#f3f3f3;border:none;width:30px;height:30px;border-radius:6px;color:#555;text-decoration:none;display:flex;align-items:center;justify-content:center}
.deshabilitado{opacity:.3;pointer-events:none}
.eliminar{color:#b91c1c;font-weight:700;text-decoration:none;font-size:12px;margin-left:8px}
.sin-fotos{color:#999;font-size:13px;margin-bottom:28px}
.form-box{background:white;border-radius:10px;padding:24px;box-shadow:0 2px 10px #0001;border-top:4px solid #15803d}
.form-box h3{font-size:15px;margin-bottom:14px}
label.campo{display:block;font-size:12px;font-weight:700;margin-bottom:4px;color:#555}
input[type=file]{width:100%;padding:9px 12px;border:1px dashed #ccc;border-radius:6px;font-size:13px;margin-bottom:14px;background:#fafafa}
button{background:#15803d;color:white;border:0;padding:10px 22px;border-radius:6px;font-weight:700;font-size:13px;cursor:pointer}
button:hover{background:#116530}
</style>
</head>
<body>
<header>
  <h1><i class="fa-solid fa-bus"></i> Panel Cootrasam</h1>
  <a href="index.php">← Volver al panel</a>
</header>

<div class="container">
  <a href="index.php" class="back-link">← Volver</a>
  <h2><i class="fa-solid fa-images"></i> Fotos del fondo (Hero)</h2>
  <p class="subtitulo">Administra las fotos que rotan en la página de inicio. Puedes agregarlas, cambiar su orden o eliminarlas.</p>

  <?php if ($mensaje): ?>
    <div class="mensaje"><?php echo htmlspecialchars($mensaje); ?></div>
  <?php endif; ?>

  <?php if (!$fotos || $total_fotos === 0): ?>
    <p class="sin-fotos">Todavía no hay ninguna foto agregada.</p>
  <?php else: ?>
    <div class="fotos-lista">
    <?php $i = 0; while ($f = $fotos->fetch_assoc()): $i++; ?>
      <div class="foto-fila">
        <div class="numero"><?php echo $i; ?></div>
        <img src="../<?php echo htmlspecialchars($f['imagen']); ?>" alt="Foto del hero">
        <div class="acciones">
          <a href="?mover=<?php echo (int)$f['id']; ?>&direccion=arriba"
             class="btn-mover <?php echo $i === 1 ? 'deshabilitado' : ''; ?>" title="Subir">
            <i class="fa-solid fa-chevron-up"></i>
          </a>
          <a href="?mover=<?php echo (int)$f['id']; ?>&direccion=abajo"
             class="btn-mover <?php echo $i === $total_fotos ? 'deshabilitado' : ''; ?>" title="Bajar">
            <i class="fa-solid fa-chevron-down"></i>
          </a>
          <a href="?eliminar=<?php echo (int)$f['id']; ?>" class="eliminar"
             onclick="return confirm('¿Eliminar esta foto?');">Eliminar</a>
        </div>
      </div>
    <?php endwhile; ?>
    </div>
  <?php endif; ?>

  <div class="form-box">
    <h3>Agregar una foto nueva</h3>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="accion" value="agregar">
      <label class="campo">Foto</label>
      <input type="file" name="imagen_nueva" accept="image/*" required>
      <button type="submit">Agregar foto</button>
    </form>
  </div>
</div>
</body>
</html>
