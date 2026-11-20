<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Historia - Cootrasam</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="css/variables.css">
<link rel="stylesheet" href="css/base.css">
<link rel="stylesheet" href="css/nav.css">
<link rel="stylesheet" href="css/paginas.css">
<link rel="stylesheet" href="css/certificaciones.css">
<link rel="stylesheet" href="css/reveal.css">
</head>
<body>
<?php include 'includes/header.php'; ?>
<main>
  <div class="page-banner">
    <div class="container">
      <h1>Nuestra historia</h1>
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
    <div class="mini-stat"><strong><span class="count" data-target=""></span>1910</strong><span>Primeros vehículos</span></div>
    <div class="mini-stat"><strong><span class="count" data-target=""></span>1990</strong><span>Constitución oficial</span></div>
    <div class="mini-stat"><strong><span class="count" data-target=""></span>2026</strong><span>Al servicio hoy</span></div>
  </div>
  <section style="padding:10px 0 50px; background:linear-gradient(180deg,#fff 0%,#fff8f2 100%);">
    <div class="container">
      <a class="back-link" onclick="history.back()">← Volver</a>
      <div class="cert-timeline" style="margin-top:20px;">
        <div class="cert-item reveal">
          <div class="cert-icon"><i class="fa-solid fa-road"></i></div>
          <div class="cert-content">
            <span class="cert-badge">Años 1910 - 1930</span>
            <h3>Antes de la cooperativa</h3>
            <p>Antes de existir un servicio organizado, viajar a los pueblos vecinos se hacía a pie o en bestias. Los primeros vehículos que circularon en Sampués eran de particulares: Luis García Hernández, Abraham Chadid, Calazán Cassas y Rúgero García fueron algunos de los primeros en prestar transporte en la región.</p>
          </div>
        </div>
        <div class="cert-item reveal">
          <div class="cert-icon"><i class="fa-solid fa-file-signature"></i></div>
          <div class="cert-content">
            <span class="cert-badge">15 de diciembre de 1990</span>
            <h3>Nace Cootrasam</h3>
            <p>Los socios fundadores se reúnen en asamblea y constituyen oficialmente la Cooperativa de Transportadores de Sampués, Cootrasam Ltda., organizando bajo una misma empresa el servicio que antes prestaban particulares.</p>
          </div>
        </div>
        <div class="cert-item reveal">
          <div class="cert-icon"><i class="fa-solid fa-bus"></i></div>
          <div class="cert-content">
            <span class="cert-badge">Hoy</span>
            <h3>Más de 35 años conectando a Sampués</h3>
            <p>Cootrasam sigue prestando el servicio de transporte de pasajeros entre Sampués y Sincelejo, con habilitación vigente del Ministerio de Transporte y el respaldo de la Alcaldía Municipal de Sampués como única cooperativa autorizada para esta ruta.</p>
          </div>
        </div>
      </div>
    </div>
  </section>
  <section class="section-dark reveal" style="padding:50px 0;">
    <div class="container" style="text-align:center;">
      <h3 style="font-size:20px; margin-bottom:8px;">Una historia que sigue escribiéndose</h3>
      <p style="color:#ccc; font-size:14px; margin-bottom:18px;">Síguenos para conocer las novedades de Cootrasam.</p>
      <a href="https://wa.me/573144047517" target="_blank" class="btn-whatsapp"><i class="fa-brands fa-whatsapp"></i> Escríbenos por WhatsApp</a>
    </div>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
<script src="js/nav.js"></script>
<script src="js/reveal.js"></script>
</body>
</html>
