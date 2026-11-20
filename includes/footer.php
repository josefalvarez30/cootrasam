<?php
// Cargar la configuración de contacto si la página no la trajo ya
if (!isset($config) || empty($config)) {
    $config = array();
    $resultado_config = false;

    // 1) Intentar con la conexión que ya existe (otra parte de la página pudo haberla cerrado)
    if (isset($conexion) && $conexion) {
        try {
            $resultado_config = @$conexion->query("SELECT clave, valor FROM configuracion");
        } catch (Throwable $e) {
            $resultado_config = false;
        }
    }

    // 2) Si no sirvió, abrir una conexión nueva
    if (!$resultado_config) {
        include __DIR__ . '/../conexion.php';
        if (isset($conexion) && !$conexion->connect_error) {
            $resultado_config = $conexion->query("SELECT clave, valor FROM configuracion");
        }
    }

    if ($resultado_config) {
        while ($fila_config = $resultado_config->fetch_assoc()) {
            $config[$fila_config['clave']] = $fila_config['valor'];
        }
    }
}

$f_telefono   = !empty($config['telefono'])   ? $config['telefono']   : '+57 314 404 7517';
$f_whatsapp   = !empty($config['whatsapp'])   ? $config['whatsapp']   : '573144047517';
$f_correo     = !empty($config['correo'])     ? $config['correo']     : 'josefealgo@gmail.com';
$f_facebook   = !empty($config['facebook'])   ? $config['facebook']   : 'https://facebook.com/cootrasam.ltda';
$f_instagram  = !empty($config['instagram'])  ? $config['instagram']  : 'https://instagram.com/cootrasamltda';
$f_twitter    = !empty($config['twitter'])    ? $config['twitter']    : 'https://twitter.com/Cootrasam_ltda';

// Limpiar WhatsApp de caracteres no numéricos
$f_wa_clean = preg_replace('/[^0-9]/', '', $f_whatsapp);
?>
<footer style="background:#1a1a1a; color:#fff; padding:40px 0 20px;">
  <div class="container" style="display:grid; grid-template-columns:1.3fr 1fr 1fr; gap:30px; padding-bottom:24px; border-bottom:1px solid #333;">
    <div>
      <div style="font-weight:800; color:var(--color-primary); font-size:20px; margin-bottom:10px;">COOTRASAM</div>
      <p style="color:#aaa; font-size:14px;">Cooperativa de Transportadores de Sampués. Transporte seguro entre Sampués y Sincelejo desde hace más de 35 años.</p>
    </div>
    <div>
      <div style="font-weight:700; margin-bottom:10px;">Contacto</div>
      <p style="color:#ccc; font-size:14px; margin-bottom:6px;"><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($f_telefono); ?></p>
      <p style="color:#ccc; font-size:14px;"><i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($f_correo); ?></p>
    </div>
    <div>
      <div style="font-weight:700; margin-bottom:10px;">Síguenos</div>
      <div style="display:flex; gap:10px; font-size:18px;">
        <a href="<?php echo htmlspecialchars($f_facebook); ?>" target="_blank" style="color:#fff;"><i class="fa-brands fa-facebook"></i></a>
        <a href="<?php echo htmlspecialchars($f_instagram); ?>" target="_blank" style="color:#fff;"><i class="fa-brands fa-instagram"></i></a>
        <a href="<?php echo htmlspecialchars($f_twitter); ?>" target="_blank" style="color:#fff;"><i class="fa-brands fa-x-twitter"></i></a>
      </div>
    </div>
  </div>
  <div class="container" style="text-align:center; padding-top:16px; color:#777; font-size:13px;">
    © 2026 Cootrasam. Todos los derechos reservados. &nbsp;·&nbsp; <a href="tratamiento-datos.php" style="color:#999;">Habeas Data</a>
  </div>
</footer>

<a href="https://wa.me/<?php echo htmlspecialchars($f_wa_clean); ?>" target="_blank" class="whatsapp-float" aria-label="Escríbenos por WhatsApp">
  <i class="fa-brands fa-whatsapp"></i>
</a>
