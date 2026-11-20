<?php
include 'conexion.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conexion->prepare("SELECT * FROM noticias_avisos WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$pub = $res->fetch_assoc();
$stmt->close();

// Si el ID no existe, mandamos de vuelta al listado
if (!$pub) {
    header("Location: info-noticias.php");
    exit;
}

$meses = [
    '01'=>'enero',
    '02'=>'febrero',
    '03'=>'marzo',
    '04'=>'abril',
    '05'=>'mayo',
    '06'=>'junio',
    '07'=>'julio',
    '08'=>'agosto',
    '09'=>'septiembre',
    '10'=>'octubre',
    '11'=>'noviembre',
    '12'=>'diciembre'
];

$dia = intval(substr($pub['fecha'], 8, 2));
$mes = isset($meses[substr($pub['fecha'],5,2)]) ? $meses[substr($pub['fecha'],5,2)] : '';
$anio = substr($pub['fecha'], 0, 4);
$fecha_larga = "$dia de $mes de $anio";

$es_aviso = ($pub['tipo'] === 'aviso');
$volver_a = $es_aviso ? 'info-avisos.php' : 'info-noticias.php';
$volver_texto = $es_aviso ? 'Volver a Avisos' : 'Volver a Noticias';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?php echo htmlspecialchars($pub['titulo']); ?> - Cootrasam</title>

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

      <h1><?php echo htmlspecialchars($pub['titulo']); ?></h1>

      <div class="route-strip" style="color:#fff;">
        <span class="parada">SAMPUÉS</span>
        <span class="via"></span>
        <i class="fa-solid fa-bus"></i>
        <span class="via"></span>
        <span class="parada">SINCELEJO</span>
      </div>

    </div>
  </div>

  <section class="content-block">

    <div class="container">

      <a href="<?php echo $volver_a; ?>" class="back-link">
        <i class="fa-solid fa-arrow-left"></i>
        <?php echo $volver_texto; ?>
      </a>

      <?php if (!empty($pub['imagen'])): ?>

        <!-- Imagen completa: mantiene su proporción original y no se recorta -->
        <div class="publicacion-imagen-contenedor reveal">

          <img
            src="<?php echo htmlspecialchars($pub['imagen']); ?>"
            alt="<?php echo htmlspecialchars($pub['titulo']); ?>"
            class="publicacion-imagen"
          >

        </div>

      <?php else: ?>

        <div
          class="reveal"
          style="
            margin:20px 0;
            background:linear-gradient(135deg,#d97a3f,#c2410c);
            border-radius:12px;
            height:260px;
            display:flex;
            align-items:center;
            justify-content:center;
            color:rgba(255,255,255,0.6);
            font-size:40px;
          "
        >
          <i class="fa-solid <?php echo $es_aviso ? 'fa-triangle-exclamation' : 'fa-newspaper'; ?>"></i>
        </div>

      <?php endif; ?>

      <p style="color:var(--color-gray); font-size:13px; margin-bottom:16px;">

        <i class="fa-solid fa-calendar"></i>

        <?php echo htmlspecialchars($fecha_larga); ?>

        &nbsp;·&nbsp;

        <span style="text-transform:uppercase; font-weight:700; color:var(--color-primary);">
          <?php echo $es_aviso ? 'Aviso' : 'Noticia'; ?>
        </span>

      </p>

      <!-- La descripción puede crecer libremente hacia abajo -->
      <div class="reveal publicacion-contenido">

        <p>
          <?php echo nl2br(htmlspecialchars($pub['contenido'])); ?>
        </p>

      </div>

    </div>

  </section>

  <section class="section-dark cta-banner" style="margin:0;">

    <div class="container">

      <div class="reveal">

        <h2>¿Tienes alguna pregunta?</h2>

        <p>Escríbenos y con gusto te ayudamos.</p>

        <a
          href="https://wa.me/573144047517"
          target="_blank"
          class="btn-whatsapp"
        >
          <i class="fa-brands fa-whatsapp"></i>
          Escríbenos por WhatsApp
        </a>

      </div>

    </div>

  </section>

</main>

<?php include 'includes/footer.php'; ?>

<script src="js/nav.js"></script>
<script src="js/reveal.js"></script>

</body>
</html>
