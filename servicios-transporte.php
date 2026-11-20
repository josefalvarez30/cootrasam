<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sampués ↔ Sincelejo - Cootrasam</title>
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
<main>
  <div class="page-banner">
    <div class="container">
      <h1>Sampués ↔ Sincelejo</h1>
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
      <a class="back-link" onclick="history.back()" style="color:#fff; font-weight:700;">← Volver</a>
      <h2 style="margin-top:10px; color:#fff;">Nuestra ruta principal</h2>
      <p style="max-width:700px; color:#fff1e6;">Conectamos Sampués y Sincelejo todos los días, con salidas frecuentes y más de 30 años de experiencia en la vía.</p>
    </div>
  </section>

  <div class="diagonal-divider"></div>

  <section style="padding:10px 0 50px; background:linear-gradient(180deg,#fff 0%,#fff8f2 100%);">
    <div class="container">
      <div class="valores-grid">
        <div class="valor-card reveal">
          <div class="icono"><i class="fa-solid fa-clock"></i></div>
          <h3>Salidas frecuentes</h3>
          <p>Buses cada aproximadamente 12 minutos en ambos sentidos, desde temprano hasta la noche. Consulta el detalle completo en <a href="horarios.php">Horarios</a>.</p>
        </div>
        <div class="valor-card reveal">
          <div class="icono"><i class="fa-solid fa-shield-halved"></i></div>
          <h3>Seguridad</h3>
          <p>Operamos con habilitación vigente del Ministerio de Transporte, con más de 30 años de trayectoria en la ruta.</p>
        </div>
        <div class="valor-card reveal">
          <div class="icono"><i class="fa-solid fa-ticket"></i></div>
          <h3>Tarifas</h3>
          <p>Precios accesibles para el viaje diario. Consulta los valores vigentes en <a href="tarifas.php">Tarifas</a>.</p>
        </div>
        <div class="valor-card reveal">
          <div class="icono"><i class="fa-solid fa-bus"></i></div>
          <h3>Servicio de Alimentador</h3>
          <p>Recogida gratuita de pasajeros en Sampués hasta la base, donde podrán abordar el próximo bus con destino a Sincelejo.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="section-dark reveal" style="padding:50px 0;">
    <div class="container" style="text-align:center;">
      <h3 style="font-family:var(--font-display); font-size:20px; margin-bottom:8px;">¿Listo para viajar con nosotros?</h3>
      <p style="color:#ccc; font-size:14px; margin-bottom:18px;">Consulta horarios o escríbenos directamente.</p>
      <a href="https://wa.me/573144047517" target="_blank" class="btn-whatsapp"><i class="fa-brands fa-whatsapp"></i> Escríbenos por WhatsApp</a>
    </div>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
<script src="js/nav.js"></script>
<script src="js/reveal.js"></script>
</body>
</html>
