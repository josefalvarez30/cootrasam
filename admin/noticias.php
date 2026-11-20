<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
include '../conexion.php';

$mensaje = "";
$carpeta_imagenes = "../assets/img/noticias/"; // carpeta real en el servidor
$ruta_publica = "assets/img/noticias/";          // la misma ruta, como la ve el sitio público

// ===== GUARDAR (agregar nuevo o editar existente) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'guardar') {
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $tipo = $_POST['tipo'];
    $titulo = $_POST['titulo'];
    $contenido = $_POST['contenido'];
    $fecha = $_POST['fecha'];

    // Si estamos editando, partimos de la imagen que ya tenía (por si no suben una nueva)
    $imagen = isset($_POST['imagen_actual']) ? $_POST['imagen_actual'] : '';

    // ===== Si el usuario seleccionó un archivo nuevo, lo subimos =====
    if (isset($_FILES['imagen_nueva']) && $_FILES['imagen_nueva']['error'] === 0) {
        if (!is_dir($carpeta_imagenes)) {
            mkdir($carpeta_imagenes, 0777, true);
        }
        $nombre_original = basename($_FILES['imagen_nueva']['name']);
        // Le agregamos la fecha/hora para que nunca choque con otro archivo del mismo nombre
        $nombre_archivo = time() . '_' . preg_replace('/[^A-Za-z0-9.\-_]/', '', $nombre_original);
        $destino = $carpeta_imagenes . $nombre_archivo;

        if (move_uploaded_file($_FILES['imagen_nueva']['tmp_name'], $destino)) {
            $imagen = $ruta_publica . $nombre_archivo;
        }
    }

    if ($id === '') {
        $sql = "INSERT INTO noticias_avisos (tipo, titulo, contenido, imagen, fecha) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssss", $tipo, $titulo, $contenido, $imagen, $fecha);
        $stmt->execute();
        $stmt->close();
        $mensaje = "Publicación agregada correctamente.";
    } else {
        $sql = "UPDATE noticias_avisos SET tipo=?, titulo=?, contenido=?, imagen=?, fecha=? WHERE id=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sssssi", $tipo, $titulo, $contenido, $imagen, $fecha, $id);
        $stmt->execute();
        $stmt->close();
        $mensaje = "Publicación actualizada correctamente.";
    }
}

// ===== ELIMINAR =====
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $conexion->prepare("DELETE FROM noticias_avisos WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $mensaje = "Publicación eliminada.";
}

// ===== Si venimos a editar, cargar ese registro =====
$editando = null;
if (isset($_GET['editar']) && !isset($_GET['nuevo'])) {
    $id = $_GET['editar'];
    $stmt = $conexion->prepare("SELECT * FROM noticias_avisos WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $editando = $res->fetch_assoc();
    $stmt->close();
}

// ===== Traer todas las publicaciones, más recientes primero =====
$publicaciones = $conexion->query("SELECT * FROM noticias_avisos ORDER BY fecha DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Noticias y avisos - Panel Admin Cootrasam</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
  body { background: #f7f7f7; }
  header {
    background: #1a1a1a; color: #fff; padding: 16px 30px;
    display: flex; justify-content: space-between; align-items: center;
  }
  header h1 { font-family: 'Oswald', sans-serif; font-size: 18px; color: #fb923c; }
  header a { color: #fff; text-decoration: none; font-size: 13px; }
  .container { max-width: 900px; margin: 30px auto; padding: 0 20px; }
  .back-link { display:inline-block; margin-bottom: 18px; color:#c2410c; font-weight:700; text-decoration:none; font-size:14px; }
  .titulo-row { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom: 18px; }
  h2 { font-family: 'Oswald', sans-serif; color:#1a1a1a; }
  .mensaje-ok { background:#dcfce7; color:#15803d; padding:10px 16px; border-radius:8px; margin-bottom:18px; font-size:14px; }

  .btn-nuevo {
    background:#15803d; color:#fff; border:none; padding:10px 18px;
    border-radius:6px; font-weight:700; font-size:13px; cursor:pointer;
    text-decoration:none; display:inline-flex; align-items:center; gap:6px;
  }
  .btn-nuevo:hover { background:#116530; }

  table { width: 100%; border-collapse: collapse; background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom: 36px; }
  th, td { padding: 12px 14px; text-align:left; font-size: 13px; border-bottom: 1px solid #eee; vertical-align: middle; }
  th { background:#fff8f2; color:#c2410c; text-transform:uppercase; font-size:11px; letter-spacing:0.5px; }
  td a { margin-right: 10px; color:#c2410c; text-decoration:none; font-size:12px; font-weight:700; }
  td a.eliminar { color:#b91c1c; }
  .badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
  .badge-noticia { background:#dbeafe; color:#1d4ed8; }
  .badge-aviso { background:#fef3c7; color:#92400e; }
  .mini-thumb { width:40px; height:40px; border-radius:6px; object-fit:cover; background:#eee; display:block; }

  .form-box { background:#fff; border-radius:10px; padding:24px; box-shadow:0 2px 10px rgba(0,0,0,0.06); border-top: 4px solid #888; }
  .form-box.modo-editar { border-top-color: #c2410c; }
  .form-box.modo-nuevo { border-top-color: #15803d; }
  .form-box h3 { font-size:15px; margin-bottom:4px; color:#1a1a1a; }
  .form-box .modo-aviso { font-size:12px; color:#888; margin-bottom:16px; }
  label { display:block; font-size:12px; font-weight:700; margin-bottom:4px; color:#555; }
  select, input[type=text], input[type=date], textarea {
    width: 100%; padding: 9px 12px; border: 1px solid #ddd; border-radius: 6px;
    font-size: 13px; margin-bottom: 14px; font-family: 'Poppins', sans-serif;
  }
  input[type=file] {
    width: 100%; padding: 9px 12px; border: 1px dashed #ccc; border-radius: 6px;
    font-size: 13px; margin-bottom: 6px; background:#fafafa;
  }
  textarea { resize: vertical; min-height: 90px; }
  .form-row { display:grid; grid-template-columns: 1fr 1fr; gap: 14px; }
  .ayuda { font-size:11px; color:#999; margin-bottom:14px; }
  .imagen-actual { display:flex; align-items:center; gap:10px; margin-bottom:10px; }
  .imagen-actual img { width:70px; height:70px; object-fit:cover; border-radius:8px; border:1px solid #eee; }
  button {
    background:#c2410c; color:#fff; border:none; padding:10px 22px;
    border-radius:6px; font-weight:700; font-size:13px; cursor:pointer;
  }
  button:hover { background:#a3360a; }
  .cancelar { display:inline-block; margin-left:10px; color:#888; font-size:13px; text-decoration:none; }
</style>
</head>
<body>
  <header>
    <h1><i class="fa-solid fa-bus"></i> Panel Cootrasam</h1>
    <a href="index.php">← Volver al panel</a>
  </header>

  <div class="container">
    <a href="index.php" class="back-link">← Volver</a>

    <div class="titulo-row">
      <h2><i class="fa-solid fa-newspaper"></i> Noticias y avisos</h2>
      <a href="noticias.php?nuevo=1" class="btn-nuevo"><i class="fa-solid fa-plus"></i> Agregar nueva publicación</a>
    </div>

    <?php if ($mensaje): ?>
      <div class="mensaje-ok"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>

    <table>
      <tr>
        <th>Foto</th>
        <th>Tipo</th>
        <th>Título</th>
        <th>Fecha</th>
        <th>Acciones</th>
      </tr>
      <?php if ($publicaciones->num_rows === 0): ?>
        <tr><td colspan="5" style="color:#999;">Todavía no hay ninguna noticia ni aviso agregado.</td></tr>
      <?php endif; ?>
      <?php while ($fila = $publicaciones->fetch_assoc()): ?>
      <tr>
        <td>
          <?php if (!empty($fila['imagen'])): ?>
            <img src="../<?php echo htmlspecialchars($fila['imagen']); ?>" class="mini-thumb" alt="">
          <?php else: ?>
            <div class="mini-thumb"></div>
          <?php endif; ?>
        </td>
        <td><span class="badge <?php echo $fila['tipo']=='noticia' ? 'badge-noticia' : 'badge-aviso'; ?>"><?php echo htmlspecialchars(ucfirst($fila['tipo'])); ?></span></td>
        <td><?php echo htmlspecialchars($fila['titulo']); ?></td>
        <td><?php echo htmlspecialchars($fila['fecha']); ?></td>
        <td>
          <a href="?editar=<?php echo $fila['id']; ?>">Editar</a>
          <a href="?eliminar=<?php echo $fila['id']; ?>" class="eliminar" onclick="return confirm('¿Seguro que quieres eliminar esta publicación?');">Eliminar</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>

    <div class="form-box <?php echo $editando ? 'modo-editar' : 'modo-nuevo'; ?>">
      <h3><?php echo $editando ? ' Editando una publicación existente' : ' Agregando una publicación NUEVA'; ?></h3>
      <p class="modo-aviso">
        <?php echo $editando
          ? 'Estás modificando "' . htmlspecialchars($editando['titulo']) . '".'
          : 'Este formulario está vacío y va a crear una publicación NUEVA.'; ?>
      </p>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="accion" value="guardar">
        <input type="hidden" name="id" value="<?php echo $editando ? $editando['id'] : ''; ?>">
        <input type="hidden" name="imagen_actual" value="<?php echo $editando ? htmlspecialchars($editando['imagen']) : ''; ?>">

        <div class="form-row">
          <div>
            <label>Tipo</label>
            <select name="tipo" required>
              <option value="noticia" <?php if ($editando && $editando['tipo']=='noticia') echo 'selected'; ?>>Noticia</option>
              <option value="aviso" <?php if ($editando && $editando['tipo']=='aviso') echo 'selected'; ?>>Aviso</option>
            </select>
          </div>
          <div>
            <label>Fecha</label>
            <input type="date" name="fecha" value="<?php echo $editando ? htmlspecialchars($editando['fecha']) : date('Y-m-d'); ?>" required>
          </div>
        </div>

        <label>Título</label>
        <input type="text" name="titulo" placeholder="Ej: Nueva ruta especial para diciembre" value="<?php echo $editando ? htmlspecialchars($editando['titulo']) : ''; ?>" required>

        <label>Contenido</label>
        <textarea name="contenido" placeholder="Escribe aquí el texto completo de la noticia o aviso..." required><?php echo $editando ? htmlspecialchars($editando['contenido']) : ''; ?></textarea>

        <label>Foto</label>
        <?php if ($editando && !empty($editando['imagen'])): ?>
          <div class="imagen-actual">
            <img src="../<?php echo htmlspecialchars($editando['imagen']); ?>" alt="">
            <span style="font-size:12px; color:#888;">Foto actual. Sube una nueva abajo solo si quieres reemplazarla.</span>
          </div>
        <?php endif; ?>
        <input type="file" name="imagen_nueva" accept="image/*">
        <p class="ayuda">Selecciona una foto desde tu computador (JPG o PNG). Si no seleccionas ninguna, se guarda sin foto (o se conserva la que ya tenía, si estás editando).</p>

        <button type="submit"><?php echo $editando ? 'Guardar cambios' : 'Publicar'; ?></button>
        <?php if ($editando): ?>
          <a href="noticias.php?nuevo=1" class="cancelar">Cancelar</a>
        <?php endif; ?>
      </form>
    </div>
  </div>
</body>
</html>
