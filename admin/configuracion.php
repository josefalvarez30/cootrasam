<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
include '../conexion.php';

$mensaje = "";

// ===== ELIMINAR LOGO O VIDEO =====
if (isset($_GET['eliminar'])) {
    if ($_GET['eliminar'] === 'logo') {
        if (file_exists('../assets/img/logo/logo-cootrasam.png')) {
            @unlink('../assets/img/logo/logo-cootrasam.png');
            $mensaje = "Logo eliminado correctamente.";
        }
    } elseif ($_GET['eliminar'] === 'video') {
        if (file_exists('../assets/video/conocenos.mp4')) {
            @unlink('../assets/video/conocenos.mp4');
            $mensaje = "Video eliminado correctamente.";
        }
    }
}

// ===== Función para guardar un valor: actualiza si ya existe, inserta si no =====
function guardarConfig($conexion, $clave, $valor) {
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
    $stmt->execute();
    $stmt->close();
}

// ===== GUARDAR datos de texto =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'guardar_datos') {
    $campos = array('telefono', 'whatsapp', 'correo', 'direccion_sampues', 'direccion_sincelejo', 'coords_sampues', 'coords_sincelejo', 'facebook', 'instagram', 'twitter');
    foreach ($campos as $campo) {
        if (isset($_POST[$campo])) {
            guardarConfig($conexion, $campo, trim($_POST[$campo]));
        }
    }
    $mensaje = "Datos de contacto actualizados correctamente.";
}

// ===== SUBIR IMÁGENES Y VIDEO — rutas fijas, se sobreescriben =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'subir_imagenes') {

    $destinos = array(
        'logo'  => array('campo' => 'logo_archivo',  'ruta' => '../assets/img/logo/',  'nombre' => 'logo-cootrasam.png'),
        'video' => array('campo' => 'video_archivo', 'ruta' => '../assets/video/',     'nombre' => 'conocenos.mp4'),
    );

    $subidas = 0;
    foreach ($destinos as $clave => $info) {
        $campo = $info['campo'];
        if (isset($_FILES[$campo]) && $_FILES[$campo]['error'] === 0) {
            if (!is_dir($info['ruta'])) {
                mkdir($info['ruta'], 0777, true);
            }
            $destino_final = $info['ruta'] . $info['nombre'];
            if (move_uploaded_file($_FILES[$campo]['tmp_name'], $destino_final)) {
                $subidas++;
            }
        }
    }

    $mensaje = $subidas > 0 ? "Se actualizaron $subidas archivo(s) correctamente." : "No seleccionaste ningún archivo nuevo para subir.";
}

// ===== Traer los valores actuales de configuración =====
$config = array();
$resultado = $conexion->query("SELECT clave, valor FROM configuracion");
while ($fila = $resultado->fetch_assoc()) {
    $config[$fila['clave']] = $fila['valor'];
}
function val($config, $clave) {
    return isset($config[$clave]) ? htmlspecialchars($config[$clave]) : '';
}

// ===== Verificar qué archivos ya existen, para mostrar vista previa =====
$logo_existe  = file_exists('../assets/img/logo/logo-cootrasam.png');
$video_existe = file_exists('../assets/video/conocenos.mp4');
// Para evitar que el navegador muestre una imagen vieja en caché al reemplazarla
$cache_bust = time();

$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Configuración - Panel Admin Cootrasam</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
  body { background: #f7f7f7; }
  header { background: #1a1a1a; color: #fff; padding: 16px 30px; display: flex; justify-content: space-between; align-items: center; }
  header h1 { font-family: 'Oswald', sans-serif; font-size: 18px; color: #fb923c; }
  header a { color: #fff; text-decoration: none; font-size: 13px; }
  .container { max-width: 800px; margin: 30px auto; padding: 0 20px; }
  .back-link { display:inline-block; margin-bottom: 18px; color:#c2410c; font-weight:700; text-decoration:none; font-size:14px; }
  h2 { font-family: 'Oswald', sans-serif; color:#1a1a1a; margin-bottom: 18px; }
  .mensaje-ok { background:#dcfce7; color:#15803d; padding:10px 16px; border-radius:8px; margin-bottom:18px; font-size:14px; }

  .form-box { background:#fff; border-radius:10px; padding:24px; box-shadow:0 2px 10px rgba(0,0,0,0.06); border-top: 4px solid #c2410c; margin-bottom: 24px; }
  .form-box h3 { font-size:15px; margin-bottom:16px; color:#1a1a1a; }
  .form-row { display:grid; grid-template-columns: 1fr 1fr; gap: 14px; }
  label { display:block; font-size:12px; font-weight:700; margin-bottom:4px; color:#555; }
  input[type=text] { width: 100%; padding: 9px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px; margin-bottom: 14px; }
  input[type=file] { width: 100%; padding: 9px 12px; border: 1px dashed #ccc; border-radius: 6px; font-size: 13px; margin-bottom: 6px; background:#fafafa; }
  .ayuda { font-size:11px; color:#999; margin-bottom:16px; }
  button { background:#c2410c; color:#fff; border:none; padding:10px 22px; border-radius:6px; font-weight:700; font-size:13px; cursor:pointer; }
  button:hover { background:#a3360a; }

  .imagen-item { margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #f0f0f0; }
  .imagen-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
  .imagen-preview { display:flex; align-items:center; gap:14px; margin-bottom: 10px; }
  .imagen-preview img { width:90px; height:60px; object-fit:cover; border-radius:6px; border:1px solid #eee; background:#f3f3f3; }
  .imagen-preview .sin-foto { width:90px; height:60px; border-radius:6px; background:#f3f3f3; display:flex; align-items:center; justify-content:center; color:#bbb; font-size:11px; text-align:center; }
  .video-badge { display:inline-block; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700; }
  .video-si { background:#dcfce7; color:#15803d; }
  .video-no { background:#f3f3f3; color:#999; }
  .btn-eliminar { color:#dc2626; text-decoration:none; font-size:12px; font-weight:700; padding:6px 12px; background:#fee2e2; border-radius:6px; display:inline-flex; align-items:center; gap:6px; }
  .btn-eliminar:hover { background:#fca5a5; }
</style>
</head>
<body>
  <header>
    <h1><i class="fa-solid fa-bus"></i> Panel Cootrasam</h1>
    <a href="index.php">← Volver al panel</a>
  </header>

  <div class="container">
    <a href="index.php" class="back-link">← Volver</a>
    <h2><i class="fa-solid fa-gear"></i> Configuración</h2>

    <?php if ($mensaje): ?>
      <div class="mensaje-ok"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>

    <!-- ===== Datos de contacto y redes ===== -->
    <div class="form-box">
      <h3>Datos de contacto y redes sociales</h3>
      <form method="POST">
        <input type="hidden" name="accion" value="guardar_datos">

        <div class="form-row">
          <div>
            <label>Teléfono</label>
            <input type="text" name="telefono" value="<?php echo val($config, 'telefono'); ?>">
          </div>
          <div>
            <label>WhatsApp (solo números, con indicativo)</label>
            <input type="text" name="whatsapp" value="<?php echo val($config, 'whatsapp'); ?>" placeholder="573144047517">
          </div>
        </div>

        <label>Correo</label>
        <input type="text" name="correo" value="<?php echo val($config, 'correo'); ?>">

        <div class="form-row">
          <div>
            <label>Dirección base Sampués</label>
            <input type="text" name="direccion_sampues" value="<?php echo val($config, 'direccion_sampues'); ?>">
          </div>
          <div>
            <label>Coordenadas Mapa Sampués (Latitud,Longitud)</label>
            <input type="text" name="coords_sampues" value="<?php echo val($config, 'coords_sampues'); ?>" placeholder="Ej: 9.183421,-75.381123">
          </div>
        </div>

        <div class="form-row">
          <div>
            <label>Dirección base Sincelejo</label>
            <input type="text" name="direccion_sincelejo" value="<?php echo val($config, 'direccion_sincelejo'); ?>">
          </div>
          <div>
            <label>Coordenadas Mapa Sincelejo (Latitud,Longitud)</label>
            <input type="text" name="coords_sincelejo" value="<?php echo val($config, 'coords_sincelejo'); ?>" placeholder="Ej: 9.298510,-75.397120">
          </div>
        </div>

        <label>Facebook (enlace completo)</label>
        <input type="text" name="facebook" value="<?php echo val($config, 'facebook'); ?>">

        <label>Instagram (enlace completo)</label>
        <input type="text" name="instagram" value="<?php echo val($config, 'instagram'); ?>">

        <label>Twitter / X (enlace completo)</label>
        <input type="text" name="twitter" value="<?php echo val($config, 'twitter'); ?>">

        <button type="submit">Guardar datos</button>
      </form>
    </div>

    <!-- ===== Logo y video ===== -->
    <div class="form-box">
      <h3>Logo y video institucional</h3>
      <p class="ayuda" style="margin-bottom:16px;">Las fotos del fondo (hero) ahora se administran en su propio módulo: <a href="hero.php" style="color:#c2410c; font-weight:700;">Fotos del fondo (Hero) →</a></p>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="accion" value="subir_imagenes">

        <div class="imagen-item">
          <label>Logo</label>
          <div class="imagen-preview">
            <?php if ($logo_existe): ?>
              <img src="../assets/img/logo/logo-cootrasam.png?v=<?php echo $cache_bust; ?>" alt="Logo actual">
              <a href="configuracion.php?eliminar=logo" class="btn-eliminar" onclick="return confirm('¿Seguro que deseas eliminar el logo actual?');">
                <i class="fa-solid fa-trash"></i> Eliminar logo
              </a>
            <?php else: ?>
              <div class="sin-foto">Sin logo todavía</div>
            <?php endif; ?>
            <input type="file" name="logo_archivo" accept="image/*" style="flex:1;">
          </div>
          <p class="ayuda">Se guarda como el logo oficial del sitio. Reemplaza automáticamente el anterior.</p>
        </div>

        <div class="imagen-item">
          <label>Video "Conócenos" (MP4)</label>
          <p style="margin-bottom:10px;">
            <span class="video-badge <?php echo $video_existe ? 'video-si' : 'video-no'; ?>">
              <?php echo $video_existe ? 'Ya hay un video subido' : 'Todavía no se ha subido video'; ?>
            </span>
            <?php if ($video_existe): ?>
              <a href="configuracion.php?eliminar=video" class="btn-eliminar" onclick="return confirm('¿Seguro que deseas eliminar el video actual?');">
                <i class="fa-solid fa-trash"></i> Eliminar video
              </a>
            <?php endif; ?>
          </p>
          <input type="file" name="video_archivo" accept="video/mp4">
          <p class="ayuda">Sube el video en formato MP4. Reemplaza automáticamente el anterior si subes uno nuevo.</p>
        </div>

        <button type="submit">Subir archivos</button>
      </form>
    </div>
  </div>
</body>
</html>
