<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quiénes somos - Cootrasam</title>
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
      <h1>Quiénes somos</h1>
      <div class="route-strip" style="color:#fff;">
        <span class="parada">SAMPUÉS</span>
        <span class="via"></span>
        <i class="fa-solid fa-bus"></i>
        <span class="via"></span>
        <span class="parada">SINCELEJO</span>
      </div>
    </div>
  </div>
  <div class="mini-stats reveal">
    <div class="mini-stat"><strong><span class="count" data-target=""></span>1990</strong><span>Año de fundación</span></div>
    <div class="mini-stat"><strong><span class="count" data-target=""></span>+35</strong><span>Años de trayectoria</span></div>
    <div class="mini-stat"><strong><span class="count" data-target=""></span>100%</strong><span>Cooperativa</span></div>
  </div>
  <section class="section-soft-orange reveal" style="padding:40px 0;">
    <div class="dot-pattern"></div>
    <div class="container" style="position:relative;">
      <a class="back-link" onclick="history.back()" style="color:#fff; font-weight:700;">← Volver</a>
      <h2 style="margin-top:10px; color:#fff;">Una cooperativa de la comunidad, para la comunidad</h2>
      <p style="max-width:700px; color:#fff1e6;">Cootrasam la cooperativa de transportadora de Sampués Sucre con licencia de funcionamiento por resolución N° 006 del año 1997 con 22 buses cubriendo cada 12 minutos la ruta Sampués – Sincelejo y viceversa.
También con servicio especial que te llevara a cualquier destino a nivel nacional, según la resolución 037 del 2014 de fecha 19 de diciembre del 2014.
Somos una empresa modelo de transporte en el departamento de Sucre, orgullosamente Sampuesana.
.</p>
    </div>
  </section>
  <div class="diagonal-divider"></div>
  <section class="content-block section-light">
    <div class="container">
      <h2 class="reveal">Nuestros valores</h2>
      <div class="valores-grid">
        <div class="valor-card reveal"><div class="icono"><i class="fa-solid fa-shield-halved"></i></div><h3>Seguridad</h3><p>Prioridad en cada viaje.</p></div>
        <div class="valor-card reveal"><div class="icono"><i class="fa-solid fa-clock"></i></div><h3>Puntualidad</h3><p>Horarios que se respetan.</p></div>
        <div class="valor-card reveal"><div class="icono"><i class="fa-solid fa-people-group"></i></div><h3>Cooperativismo</h3><p>Trabajamos entre y para la comunidad.</p></div>
        <div class="valor-card reveal"><div class="icono"><i class="fa-solid fa-handshake"></i></div><h3>Compromiso</h3><p>Con Sampués y con Sincelejo.</p></div>
      </div>
    </div>
  </section>
  <section class="section-dark reveal" style="padding:50px 0;">
    <div class="container" style="text-align:center;">
      <h3 style="font-family:var(--font-display); font-size:20px; margin-bottom:8px;">¿Quieres saber más de Cootrasam?</h3>
      <p style="color:#ccc; font-size:14px; margin-bottom:18px;">Escríbenos y con gusto te contamos más sobre nuestra historia y nuestro servicio.</p>
      <a href="https://wa.me/573144047517" target="_blank" class="btn-whatsapp"><i class="fa-brands fa-whatsapp"></i> Escríbenos por WhatsApp</a>
    </div>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
<script src="js/nav.js"></script>
<script src="js/reveal.js"></script>
</body>
</html>
