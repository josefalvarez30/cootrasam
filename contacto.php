<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contáctanos - Cootrasam</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="css/variables.css">
<link rel="stylesheet" href="css/base.css">
<link rel="stylesheet" href="css/nav.css">
<link rel="stylesheet" href="css/paginas.css">
<link rel="stylesheet" href="css/reveal.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<?php
// Cargar configuración de base de datos o valores por defecto
$c_telefono      = !empty($config['telefono'])           ? $config['telefono']           : '+57 314 404 7517';
$c_whatsapp      = !empty($config['whatsapp'])           ? $config['whatsapp']           : '573144047517';
$c_correo        = !empty($config['correo'])             ? $config['correo']             : 'josefealgo@gmail.com';
$c_dir_sampues   = !empty($config['direccion_sampues'])  ? $config['direccion_sampues']  : 'Calle 24 N° 13C - 40, La Balsa, Sampués';
$c_dir_sincelejo = !empty($config['direccion_sincelejo']) ? $config['direccion_sincelejo'] : 'Terminal / Base Sincelejo';

// Coordenadas dinámicas desde el panel Admin
$sampues_coords   = !empty($config['coords_sampues'])   ? $config['coords_sampues']   : '9.183421,-75.381123';
$sincelejo_coords = !empty($config['coords_sincelejo']) ? $config['coords_sincelejo'] : '9.298510,-75.397120';

$c_wa_clean = preg_replace('/[^0-9]/', '', $c_whatsapp);
?>

<main>

  <div class="page-banner">
    <div class="container">
      <h1>Contáctanos</h1>
      <p>Estamos para ayudarte. Escríbenos o visítanos.</p>
      <div class="route-strip" style="color:#fff;">
        <span class="parada">SAMPUÉS</span>
        <span class="via"></span>
        <i class="fa-solid fa-bus"></i>
        <span class="via"></span>
        <span class="parada">SINCELEJO</span>
      </div>
    </div>
  </div>

  <section class="section-soft-orange reveal" style="padding:40px 0;">
    <div class="dot-pattern"></div>
    <div class="container" style="position:relative;">
      <a class="back-link" onclick="history.back()" style="color:#fff; font-weight:700; cursor:pointer;">← Volver</a>
      <h2 style="margin-top:10px; color:#fff;">Información de contacto</h2>
      <p style="max-width:700px; color:#fff1e6;">Cualquier duda sobre rutas, horarios, tarifas o encomiendas, contáctanos por el medio que prefieras.</p>
    </div>
  </section>

  <div class="diagonal-divider"></div>

  <section style="padding:10px 0 50px; background:linear-gradient(180deg,#fff 0%,#fff8f2 100%);">
    <div class="container two-columns">

      <div class="reveal">
        <ul class="contact-list">
          <li>
            <i class="fa-solid fa-location-dot"></i>
            <div>
              <strong>Base Sampués</strong>
              <p><?php echo htmlspecialchars($c_dir_sampues); ?></p>
            </div>
          </li>
          <li>
            <i class="fa-solid fa-building-flag"></i>
            <div>
              <strong>Base Sincelejo</strong>
              <p><?php echo htmlspecialchars($c_dir_sincelejo); ?></p>
            </div>
          </li>
          <li>
            <i class="fa-brands fa-whatsapp"></i>
            <div>
              <strong>WhatsApp / Teléfono</strong>
              <p>
                <a href="https://wa.me/<?php echo htmlspecialchars($c_wa_clean); ?>" target="_blank">
                  <?php echo htmlspecialchars($c_whatsapp); ?>
                </a>
                <?php if ($c_telefono !== $c_whatsapp): ?>
                  &nbsp;·&nbsp; <a href="tel:<?php echo preg_replace('/\s+/', '', $c_telefono); ?>"><?php echo htmlspecialchars($c_telefono); ?></a>
                <?php endif; ?>
              </p>
            </div>
          </li>
          <li>
            <i class="fa-solid fa-envelope"></i>
            <div>
              <strong>Correo electrónico</strong>
              <p><a href="mailto:<?php echo htmlspecialchars($c_correo); ?>"><?php echo htmlspecialchars($c_correo); ?></a></p>
            </div>
          </li>
          <li>
            <i class="fa-solid fa-clock"></i>
            <div>
              <strong>Horario de atención</strong>
              <p>Todos los días, según horario de rutas</p>
            </div>
          </li>
        </ul>
      </div>

      <!-- Mapas cargados con Coordenadas desde la BD -->
      <div class="reveal" style="display:flex; flex-direction:column; gap:20px;">

        <!-- Mapa Sampués -->
        <div class="map-card" style="background:#fff; border-radius:12px; padding:12px; box-shadow:0 4px 15px rgba(0,0,0,0.06); border:1px solid #f0f0f0;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <strong style="color:#c2410c; font-size:14px;"><i class="fa-solid fa-map-pin"></i> Sampués: <?php echo htmlspecialchars($c_dir_sampues); ?></strong>
            <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($sampues_coords); ?>" target="_blank" style="font-size:12px; color:#c2410c; font-weight:600; text-decoration:none;"><i class="fa-solid fa-arrow-up-right-from-square"></i> Abrir en App</a>
          </div>
          <iframe
            src="https://maps.google.com/maps?q=<?php echo urlencode($sampues_coords); ?>&z=17&output=embed"
            width="100%" height="200" style="border:0; border-radius:8px;"
            allowfullscreen="" loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
          </iframe>
        </div>

        <!-- Mapa Sincelejo -->
        <div class="map-card" style="background:#fff; border-radius:12px; padding:12px; box-shadow:0 4px 15px rgba(0,0,0,0.06); border:1px solid #f0f0f0;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <strong style="color:#c2410c; font-size:14px;"><i class="fa-solid fa-map-pin"></i> Sincelejo: <?php echo htmlspecialchars($c_dir_sincelejo); ?></strong>
            <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($sincelejo_coords); ?>" target="_blank" style="font-size:12px; color:#c2410c; font-weight:600; text-decoration:none;"><i class="fa-solid fa-arrow-up-right-from-square"></i> Abrir en App</a>
          </div>
          <iframe
            src="https://maps.google.com/maps?q=<?php echo urlencode($sincelejo_coords); ?>&z=17&output=embed"
            width="100%" height="200" style="border:0; border-radius:8px;"
            allowfullscreen="" loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
          </iframe>
        </div>

      </div>

    </div>
  </section>

  <section class="section-dark" style="padding:50px 0;">
    <div class="container" style="text-align:center;">
      <h3 style="font-family:var(--font-display); font-size:20px; margin-bottom:8px;">¿Prefieres hablar directamente con nosotros?</h3>
      <p style="color:#ccc; font-size:14px; margin-bottom:18px;">Un clic y te respondemos por WhatsApp.</p>
      <a href="https://wa.me/<?php echo htmlspecialchars($c_wa_clean); ?>" target="_blank" class="btn-whatsapp">
        <i class="fa-brands fa-whatsapp"></i> Escríbenos por WhatsApp
      </a>
    </div>
  </section>

</main>
<?php include 'includes/footer.php'; ?>
<script src="js/nav.js"></script>
<script src="js/reveal.js"></script>
</body>
</html>
