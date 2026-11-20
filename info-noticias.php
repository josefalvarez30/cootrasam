<?php include 'conexion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Noticias - Cootrasam</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="css/variables.css">
<link rel="stylesheet" href="css/base.css">
<link rel="stylesheet" href="css/nav.css">
<link rel="stylesheet" href="css/paginas.css">
<link rel="stylesheet" href="css/carousel3d.css">
<link rel="stylesheet" href="css/reveal.css">
</head>
<body>
<?php include 'includes/header.php'; ?>
<main>
  <div class="page-banner">
    <div class="container">
      <h1>Noticias</h1>
      <div class="route-strip" style="color:#fff;">
        <span class="parada">SAMPUÉS</span><span class="via"></span><i class="fa-solid fa-bus"></i><span class="via"></span><span class="parada">SINCELEJO</span>
      </div>
    </div>
  </div>

  <section style="padding:40px 0 60px; background:linear-gradient(180deg,#fff 0%,#fff8f2 100%);">
    <div class="container">
      <a class="back-link" onclick="history.back()">← Volver</a>

      <?php
        $meses = ['01'=>'ENE','02'=>'FEB','03'=>'MAR','04'=>'ABR','05'=>'MAY','06'=>'JUN','07'=>'JUL','08'=>'AGO','09'=>'SEP','10'=>'OCT','11'=>'NOV','12'=>'DIC'];

        function resumir($texto, $limite = 130) {
            $texto = trim($texto);
            if (mb_strlen($texto) <= $limite) return $texto;
            return mb_substr($texto, 0, $limite) . '…';
        }

        $resultado = $conexion->query("SELECT * FROM noticias_avisos WHERE tipo='noticia' ORDER BY fecha DESC");
        $noticias = [];
        while ($fila = $resultado->fetch_assoc()) { $noticias[] = $fila; }
        $total = count($noticias);
      ?>

      <?php if ($total === 0): ?>
        <div class="reveal" style="text-align:center; padding:60px 20px; color:var(--color-gray);">
          <i class="fa-solid fa-newspaper" style="font-size:40px; color:#ddd; margin-bottom:14px; display:block;"></i>
          Aún no hay noticias publicadas. Vuelve pronto.
        </div>
      <?php else: ?>

      <div class="carousel3d reveal">
        <div class="carousel3d-stage">

          <?php foreach ($noticias as $i => $n):
            if ($i === 0) { $pos = 'active'; }
            elseif ($i === 1) { $pos = 'next'; }
            elseif ($i === $total - 1 && $total > 2) { $pos = 'prev'; }
            else { $pos = 'hidden'; }

            $fecha_fmt = isset($meses[substr($n['fecha'],5,2)]) ? $meses[substr($n['fecha'],5,2)] . ' ' . substr($n['fecha'],0,4) : $n['fecha'];
          ?>
          <div class="card3d" data-pos="<?php echo $pos; ?>">
            <div class="card3d-image"<?php if (!empty($n['imagen'])): ?> style="background-image:url('<?php echo htmlspecialchars($n['imagen']); ?>'); background-size:cover; background-position:center;"<?php endif; ?>>
              <?php if (empty($n['imagen'])): ?><i class="fa-solid fa-newspaper"></i><?php endif; ?>
            </div>
            <div class="card3d-body">
              <span class="card3d-date"><?php echo htmlspecialchars($fecha_fmt); ?></span>
              <h3><?php echo htmlspecialchars($n['titulo']); ?></h3>
              <p><?php echo htmlspecialchars(resumir($n['contenido'])); ?></p>
              <a href="publicacion.php?id=<?php echo $n['id']; ?>">Leer más →</a>
            </div>
          </div>
          <?php endforeach; ?>

        </div>
        <button class="carousel3d-btn carousel3d-prev"><i class="fa-solid fa-chevron-left"></i></button>
        <button class="carousel3d-btn carousel3d-next"><i class="fa-solid fa-chevron-right"></i></button>
        <div class="carousel3d-dots"></div>
      </div>

      <?php endif; ?>
    </div>
  </section>

  <section class="section-dark reveal" style="padding:50px 0;">
    <div class="container" style="text-align:center;">
      <h3 style="font-family:var(--font-display); font-size:20px; margin-bottom:8px;">Síguenos para no perderte nada</h3>
      <p style="color:#ccc; font-size:14px; margin-bottom:18px;">Entérate de nuestras novedades por WhatsApp o redes sociales.</p>
      <a href="https://wa.me/573144047517" target="_blank" class="btn-whatsapp"><i class="fa-brands fa-whatsapp"></i> Escríbenos por WhatsApp</a>
    </div>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
<script src="js/nav.js"></script>
<script src="js/reveal.js"></script>
<script src="js/carousel3d.js"></script>
</body>
</html>
