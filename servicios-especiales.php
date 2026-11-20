<?php include 'conexion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Servicios Especiales - Cootrasam</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="css/variables.css">
<link rel="stylesheet" href="css/base.css">
<link rel="stylesheet" href="css/nav.css">
<link rel="stylesheet" href="css/paginas.css">
<link rel="stylesheet" href="css/reveal.css">
<style>
  .destinos-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 22px;
    margin-top: 10px;
  }
  @media (max-width: 900px) { .destinos-grid { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 560px) { .destinos-grid { grid-template-columns: 1fr; } }

  .destino-card {
    border-radius: 14px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    transition: var(--transition, transform 0.2s ease);
  }
  .destino-card:hover { transform: translateY(-4px); }

  .destino-foto {
    height: 170px;
    background: linear-gradient(160deg, #e8874a, #b6551f 60%, #111 100%);
    background-size: cover;
    background-position: center;
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255,255,255,0.85);
    font-size: 30px;
  }
  .destino-nombre {
    padding: 14px 16px;
    text-align: center;
    font-family: var(--font-display);
    font-weight: 600;
    color: var(--color-dark);
    font-size: 15px;
  }
  .destinos-nota {
    text-align: center;
    color: var(--color-gray, #888);
    font-size: 13px;
    margin-top: 22px;
    font-style: italic;
  }
</style>
</head>
<body>
<?php include 'includes/header.php'; ?>
<main>
  <div class="page-banner">
    <div class="container">
      <h1>Servicios Especiales</h1>
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
      <h2 style="margin-top:10px; color:#fff;">Transporte a la medida de tu evento</h2>
      <p style="max-width:700px; color:#fff1e6;">Además de nuestra ruta habitual, ofrecemos servicios especiales para grupos, instituciones y ocasiones particulares.</p>
    </div>
  </section>

  <div class="diagonal-divider"></div>

  <section style="padding:10px 0 50px; background:linear-gradient(180deg,#fff 0%,#fff8f2 100%);">
    <div class="container">
      <div class="valores-grid">
        <div class="valor-card reveal">
          <div class="icono"><i class="fa-solid fa-graduation-cap"></i></div>
          <h3>Transporte Estudiantil</h3>
          <p>Servicio para instituciones educativas en Sampués y alrededores.</p>
        </div>
        <div class="valor-card reveal">
          <div class="icono"><i class="fa-solid fa-users-line"></i></div>
          <h3>Viajes Especiales</h3>
          <p>Transporte grupal para eventos, excursiones o actividades particulares.</p>
        </div>
        <div class="valor-card reveal">
          <div class="icono"><i class="fa-solid fa-circle-info"></i></div>
          <h3>Disponibilidad</h3>
          <!-- ⚠️ Este texto quedó vacío en el archivo original (no lo inventé, solo lo señalo).
               Escribe aquí la info real sobre disponibilidad/condiciones. -->
          <p style="color:#7c5a3c; font-style:italic;"></p>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== Galería de destinos — AHORA CONECTADA a la tabla `destinos` ===== -->
  <section class="reveal" style="padding:10px 0 60px; background:#fff8f2;">
    <div class="container">
      <div class="section-label" style="text-align:center;">Nuestro alcance</div>
      <div class="section-title" style="text-align:center;">Destinos a los que hemos llegado</div>
      <p style="max-width:650px; margin:0 auto 10px; text-align:center; color:var(--color-gray,#888); font-size:14px;">
        Con nuestros Viajes Especiales hemos acompañado a grupos y excursiones a distintos lugares de Colombia. Estos son algunos de los destinos.
      </p>

      <?php
        $destinos = $conexion->query("SELECT * FROM destinos ORDER BY orden ASC, id ASC");
      ?>

      <?php if ($destinos->num_rows === 0): ?>
        <p class="destinos-nota" style="margin-top:30px;">Próximamente agregaremos fotos de los destinos que hemos visitado.</p>
      <?php else: ?>

      <div class="destinos-grid">
        <?php while ($d = $destinos->fetch_assoc()): ?>
        <div class="destino-card">
          <div class="destino-foto"<?php if (!empty($d['imagen'])): ?> style="background-image:url('<?php echo htmlspecialchars($d['imagen']); ?>');"<?php endif; ?>>
            <?php if (empty($d['imagen'])): ?><i class="fa-solid fa-image"></i><?php endif; ?>
          </div>
          <div class="destino-nombre"><?php echo htmlspecialchars($d['nombre']); ?></div>
        </div>
        <?php endwhile; ?>
      </div>

      <p class="destinos-nota">Todos estos destinos y muchos más puedes visitar con la ayuda de Cootrasam y sus servicios de viajes especiales.</p>

      <?php endif; ?>
    </div>
  </section>

  <section class="section-dark reveal" style="padding:50px 0;">
    <div class="container" style="text-align:center;">
      <h3 style="font-family:var(--font-display); font-size:20px; margin-bottom:8px;">¿Necesitas un servicio especial?</h3>
      <p style="color:#ccc; font-size:14px; margin-bottom:18px;">Cuéntanos qué necesitas y te confirmamos disponibilidad.</p>
      <a href="https://wa.me/573144047517" target="_blank" class="btn-whatsapp"><i class="fa-brands fa-whatsapp"></i> Consultar disponibilidad</a>
    </div>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
<script src="js/nav.js"></script>
<script src="js/reveal.js"></script>
</body>
</html>
