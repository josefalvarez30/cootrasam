<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Reconectar a la base de datos de forma segura si la conexión se cerró previamente
if (!isset($conexion) || !($conexion instanceof mysqli) || !@$conexion->ping()) {
    @include_once __DIR__ . '/../conexion.php';
}

// Cargar configuración de contacto y redes sociales desde la base de datos
$config = array();
if (isset($conexion) && $conexion instanceof mysqli && @$conexion->ping()) {
    $res_cfg = @$conexion->query("SELECT clave, valor FROM configuracion");
    if ($res_cfg) {
        while ($f = $res_cfg->fetch_assoc()) {
            $config[$f['clave']] = $f['valor'];
        }
    }
}

// Valores dinámicos con respaldo a tus datos por defecto
$hdr_telefono = (!empty($config['telefono'])) ? $config['telefono'] : '+57 314 404 7517';
$hdr_correo   = (!empty($config['correo']))   ? $config['correo']   : 'josefealgo@gmail.com';
$hdr_fb       = (!empty($config['facebook'])) ? $config['facebook'] : 'https://facebook.com/cootrasam.ltda';
$hdr_ig       = (!empty($config['instagram']))? $config['instagram']: 'https://instagram.com/cootrasamltda';
$hdr_tw       = (!empty($config['twitter']))  ? $config['twitter']  : 'https://twitter.com/Cootrasam_ltda';

$hdr_tel_clean = preg_replace('/[^0-9\+]/', '', $hdr_telefono);

// Evitar imágenes guardadas en caché al actualizar el logo desde el admin
$logo_path = __DIR__ . '/../assets/img/logo/logo-cootrasam.png';
$logo_v    = file_exists($logo_path) ? '?v=' . filemtime($logo_path) : '';
?>

<div class="topbar">
  <div class="container">
    <div class="topbar-info">
      <a href="tel:<?php echo htmlspecialchars($hdr_tel_clean); ?>"><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($hdr_telefono); ?></a>
      <a href="mailto:<?php echo htmlspecialchars($hdr_correo); ?>"><i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($hdr_correo); ?></a>
    </div>
    <div class="topbar-social">
      <a href="<?php echo htmlspecialchars($hdr_fb); ?>" target="_blank"><i class="fa-brands fa-facebook"></i></a>
      <a href="<?php echo htmlspecialchars($hdr_ig); ?>" target="_blank"><i class="fa-brands fa-instagram"></i></a>
      <a href="<?php echo htmlspecialchars($hdr_tw); ?>" target="_blank"><i class="fa-brands fa-x-twitter"></i></a>
    </div>
  </div>
</div>

<header class="main-header">
  <nav class="main-nav">
    <div class="container">
      <a href="index.php" class="nav-logo">
        <img src="assets/img/logo/logo-cootrasam.png<?php echo $logo_v; ?>" alt="Cootrasam">
      </a>

      <ul class="nav-menu" id="navMenu">
        <li><a href="index.php">Inicio</a></li>

        <li>
          <a href="#">Empresa ▾</a>
          <ul class="dropdown">
            <li><a href="empresa-quienes-somos.php">Quiénes somos</a></li>
            <li><a href="empresa-historia.php">Historia</a></li>
            <li><a href="empresa-mision-vision.php">Misión y visión</a></li>
            <li><a href="certificaciones.php">Certificaciones</a></li>
          </ul>
        </li>

        <li>
          <a href="#">Servicios ▾</a>
          <ul class="dropdown">
            <li><a href="servicios-transporte.php">servicio de transporte</a></li>
            <li><a href="servicios-encomiendas.php">Encomiendas</a></li>
            <li><a href="servicios-especiales.php">Servicios especiales</a></li>
          </ul>
        </li>

        <li><a href="horarios.php">Horarios</a></li>
        <li><a href="tarifas.php">Tarifas</a></li>

        <li>
          <a href="#">Información ▾</a>
          <ul class="dropdown">
            <li><a href="info-noticias.php">Noticias</a></li>
            <li><a href="info-avisos.php">Avisos</a></li>
            <li><a href="info-faq.php">Preguntas frecuentes</a></li>
          </ul>
        </li>

        <li><a href="pqr.php">PQR</a></li>
        <li><a href="contacto.php" class="btn"><i class="fa-solid fa-phone"></i> Contáctanos</a></li>
      </ul>

      <button class="hamburger" id="hamburgerBtn" aria-label="Abrir menú">
        <span></span><span></span><span></span>
      </button>
    </div>
  </nav>
</header>
