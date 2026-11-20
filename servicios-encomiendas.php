<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Encomiendas - Cootrasam</title>
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
      <h1>Servicio de Encomiendas</h1>
      <div class="route-strip" style="color:#fff;">
        <span class="parada">SAMPUÉS</span>
        <span class="via"></span>
        <i class="fa-solid fa-box"></i>
        <span class="via"></span>
        <span class="parada">SINCELEJO</span>
      </div>
    </div>
  </div>

  <div class="mini-stats reveal">
    <div class="mini-stat"><strong><span class="count" data-target=></span>2</strong><span>Bases de entrega</span></div>
    <div class="mini-stat"><strong><span class="count" data-target=></span>4</strong><span>Pasos simples</span></div>
    <div class="mini-stat"><strong><span class="count" data-target=></span>12min</strong><span>Frecuencia de viajes</span></div>
  </div>

  <section class="section-soft-orange reveal" style="padding:40px 0;">
    <div class="dot-pattern"></div>
    <div class="container" style="position:relative;">
      <a class="back-link" onclick="history.back()" style="color:#fff; font-weight:700;">← Volver</a>
      <h2 style="margin-top:10px; color:#fff;">Envía tu encomienda con confianza (NOTA: servicio proximo a funcionar)</h2>
      <p style="max-width:700px; color:#fff1e6;">Enviar algo entre Sampués y Sincelejo con Cootrasam es rápido y sencillo, aprovechamos las mismas salidas frecuentes de nuestros buses.</p>
    </div>
  </section>

  <div class="diagonal-divider"></div>

  <section style="padding:10px 0 50px; background:linear-gradient(180deg,#fff 0%,#fff8f2 100%);">
    <div class="container">
      <div class="cert-timeline">
        <div class="cert-item reveal">
          <div class="cert-icon"><i class="fa-solid fa-1"></i></div>
          <div class="cert-content">
            <span class="cert-badge">Paso 1</span>
            <h3>Lleva tu encomienda</h3>
            <p>Acércate a la base de origen (Sampués o Sincelejo) con lo que deseas enviar.</p>
          </div>
        </div>
        <div class="cert-item reveal">
          <div class="cert-icon"><i class="fa-solid fa-2"></i></div>
          <div class="cert-content">
            <span class="cert-badge">Paso 2</span>
            <h3>Indica el destino</h3>
            <p>Informa hacia dónde va y quién la recogerá en la base de destino.</p>
          </div>
        </div>
        <div class="cert-item reveal">
          <div class="cert-icon"><i class="fa-solid fa-3"></i></div>
          <div class="cert-content">
            <span class="cert-badge">Paso 3</span>
            <h3>Paga el envío</h3>
            <p>En la base te indican el valor a pagar según el tamaño de tu encomienda.</p>
          </div>
        </div>
        <div class="cert-item reveal">
          <div class="cert-icon"><i class="fa-solid fa-4"></i></div>
          <div class="cert-content">
            <span class="cert-badge">Paso 4</span>
            <h3>Recógela en destino</h3>
            <p>La persona que indicaste la recoge directamente en la base de llegada.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section-dark reveal" style="padding:50px 0;">
    <div class="container" style="text-align:center;">
      <h3 style="font-family:var(--font-display); font-size:20px; margin-bottom:8px;">¿Tienes una encomienda por enviar?</h3>
      <p style="color:#ccc; font-size:14px; margin-bottom:18px;">Escríbenos y te confirmamos el valor y la próxima salida disponible.</p>
      <a href="https://wa.me/573144047517" target="_blank" class="btn-whatsapp"><i class="fa-brands fa-whatsapp"></i> Escríbenos por WhatsApp</a>
    </div>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
<script src="js/nav.js"></script>
<script src="js/reveal.js"></script>
</body>
</html>
