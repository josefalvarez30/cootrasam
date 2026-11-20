
<?php
date_default_timezone_set('America/Bogota');
require_once __DIR__ . '/conexion.php';

// Configuración de contacto
$config = array();
$resultado_config = $conexion->query("SELECT clave, valor FROM configuracion");

if ($resultado_config) {
    while ($c = $resultado_config->fetch_assoc()) {
        $config[$c['clave']] = $c['valor'];
    }
}

$idx_telefono = !empty($config['telefono']) ? $config['telefono'] : '+57 314 404 7517';
$idx_whatsapp = !empty($config['whatsapp']) ? $config['whatsapp'] : '573144047517';
$idx_correo = !empty($config['correo']) ? $config['correo'] : 'josefealgo@gmail.com';

$idx_wa_clean = preg_replace('/[^0-9]/', '', $idx_whatsapp);
$idx_tel_clean = preg_replace('/\s+/', '', $idx_telefono);

// Flota administrable
$flota = array();
$resultado_flota = $conexion->query("SELECT * FROM flota ORDER BY orden, id");

if ($resultado_flota) {
    while ($f = $resultado_flota->fetch_assoc()) {
        $flota[] = $f;
    }
}

// Fotografías del inicio
$fotos_hero = array();
$resultado_hero = $conexion->query("SELECT imagen FROM hero_fotos ORDER BY orden, id");

if ($resultado_hero) {
    while ($fh = $resultado_hero->fetch_assoc()) {
        $fotos_hero[] = $fh['imagen'];
    }
}

$video_existe = file_exists(__DIR__ . '/assets/video/conocenos.mp4');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Cootrasam - Cooperativa de Transportadores de Sampués</title>

<meta name="description" content="Transporte de pasajeros y encomiendas entre Sampués y Sincelejo. Cootrasam, más de 35 años conectando destinos.">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="stylesheet" href="css/variables.css">
<link rel="stylesheet" href="css/base.css">
<link rel="stylesheet" href="css/nav.css">
<link rel="stylesheet" href="css/home.css">
<link rel="stylesheet" href="css/reveal.css">
<link rel="stylesheet" href="css/carousel3d.css">
<link rel="stylesheet" href="css/hero-slideshow.css?v=3">
<link rel="stylesheet" href="css/video-conocenos.css">
<link rel="stylesheet" href="css/inicio-premium.css?v=2">
</head>

<body class="inicio-premium">

<?php include 'includes/header.php'; ?>

<main>

    <!-- Inicio: fotografías administrables -->
    <section class="hero reveal">

        <?php if (count($fotos_hero) > 0): ?>
        <div class="hero-bg-slideshow">
            <?php foreach ($fotos_hero as $index => $foto): ?>
            <div class="hero-bg-slide<?php echo $index === 0 ? ' active' : ''; ?>"
                 style="background-image:url('<?php echo htmlspecialchars($foto, ENT_QUOTES, 'UTF-8'); ?>');">
            </div>
            <?php endforeach; ?>
        </div>
        <div class="hero-bg-overlay"></div>
        <?php endif; ?>

        <div class="container">

            <div class="route-strip" style="color:#fff;">
                <span class="parada">SAMPUÉS</span>
                <span class="via"></span>
                <i class="fa-solid fa-bus"></i>
                <span class="via"></span>
                <span class="parada">SINCELEJO</span>
            </div>

            <h1>
                Conectamos a Sampués con
                <span>seguridad y confianza</span>
            </h1>

            <p>
                Transporte de pasajeros y encomiendas entre Sampués y Sincelejo, todos los días.<br>
                Operando desde 1990, con habilitación vigente del Ministerio de Transporte.
            </p>

            <div class="hero-actions">
                <a href="horarios.php" class="btn">Consultar Horarios</a>
                <a href="tarifas.php" class="btn-outline">Ver Tarifas</a>
            </div>

        </div>
    </section>

    <!-- Estadísticas -->
    <div class="stats-wrap">
        <div class="stats-bar reveal">

            <div class="stat">
                <strong>
                    <span class="count" data-target=""></span>
                    <span class="stat-suffix">35+</span>
                </strong>
                <span>Años de servicio</span>
            </div>

            <div class="stat">
                <strong>
                    <span class="count" data-target=""></span>
                    <span class="stat-suffix">2</span>
                </strong>
                <span>Bases</span>
            </div>

            <div class="stat">
                <strong>
                    <span class="count" data-target=""></span>
                    <span class="stat-suffix">100%</span>
                </strong>
                <span>Cooperativa</span>
            </div>

        </div>
    </div>

    <!-- Servicios -->
    <section class="premium-section premium-section--warm reveal">
        <div class="container">

            <div class="premium-heading">
                <div class="section-label">Nuestros servicios</div>
                <div class="section-title">
                    Soluciones de transporte para ti
                </div>
            </div>

            <div class="services-grid">

                <div class="service-card">
                    <div class="icon-circle">
                        <i class="fa-solid fa-bus"></i>
                    </div>
                    <div class="service-card-content">
                        <h3>Transporte</h3>
                        <p>Ruta Sampués - Sincelejo, con salidas frecuentes.</p>
                    </div>
                </div>

                <div class="service-card">
                    <div class="icon-circle">
                        <i class="fa-solid fa-box"></i>
                    </div>
                    <div class="service-card-content">
                        <h3>Encomiendas</h3>
                        <p>Servicio aun no disponible, proximo a funcionar.</p>
                    </div>
                </div>

                <div class="service-card">
                    <div class="icon-circle">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <div class="service-card-content">
                        <h3>Servicios Especiales</h3>
                        <p>Transporte estudiantil y viajes especiales.</p>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- Por qué elegirnos -->
    <section class="premium-section premium-section--white reveal">
        <div class="container">

            <div class="section-label" style="text-align:center;">
                Por qué elegirnos
            </div>

            <div class="why-grid">

                <div class="why-item">
                    <div class="icon-dark">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <span>Seguridad</span>
                </div>

                <div class="why-item">
                    <div class="icon-dark">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <span>Experiencia</span>
                </div>

                <div class="why-item">
                    <div class="icon-dark">
                        <i class="fa-solid fa-couch"></i>
                    </div>
                    <span>Comodidad</span>
                </div>

                <div class="why-item">
                    <div class="icon-dark">
                        <i class="fa-solid fa-leaf"></i>
                    </div>
                    <span>Responsabilidad</span>
                </div>

            </div>

        </div>
    </section>

    <!-- Flota administrable -->
    <section id="nuestra-flota" class="premium-section premium-section--warm reveal">
        <div class="container">

            <div class="section-label">Nuestra flota</div>
            <div class="section-title">Buses modernos y seguros</div>

            <?php if (count($flota) > 0): ?>

            <div class="carousel3d">

                <div class="carousel3d-stage">

                    <?php foreach ($flota as $index => $bus):
                        $pos = ($index === 0) ? 'active' : (($index === 1) ? 'next' : 'hidden');
                    ?>

                    <div class="card3d" data-pos="<?php echo $pos; ?>">

                        <div class="card3d-image"
                            <?php if (!empty($bus['imagen'])): ?>
                            style="background-image:url('<?php echo htmlspecialchars($bus['imagen'], ENT_QUOTES, 'UTF-8'); ?>');background-size:cover;background-position:center;"
                            <?php endif; ?>>

                            <?php if (empty($bus['imagen'])): ?>
                            <i class="fa-solid fa-bus"></i>
                            <?php endif; ?>

                        </div>

                        <div class="card3d-body">
                            <h3>
                                <?php echo htmlspecialchars($bus['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                            </h3>
                            <p>
                                <?php echo htmlspecialchars($bus['descripcion'], ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                        </div>

                    </div>

                    <?php endforeach; ?>

                </div>

                <button class="carousel3d-btn carousel3d-prev"
                        type="button"
                        aria-label="Anterior">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>

                <button class="carousel3d-btn carousel3d-next"
                        type="button"
                        aria-label="Siguiente">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>

                <div class="carousel3d-dots"></div>

            </div>

            <?php else: ?>

            <p style="color:#888;text-align:center;padding:30px 0;">
                Nuestra información de flota no está disponible en este momento.
            </p>

            <?php endif; ?>

        </div>
    </section>

    <!-- Video institucional -->
    <section id="conocenos" class="video-section reveal">
        <div class="container">

            <div class="section-label">Conócenos</div>

            <?php if ($video_existe): ?>

            <div class="video-conocenos-container">

                <video class="video-conocenos-bg" muted playsinline>
                    <source src="assets/video/conocenos.mp4" type="video/mp4">
                </video>

                <div class="video-conocenos-overlay"></div>

                <video class="video-conocenos-main"
                       id="videoConocenosMain"
                       controls
                       playsinline>
                    <source src="assets/video/conocenos.mp4" type="video/mp4">
                </video>

            </div>

            <?php else: ?>

            <div class="video-no-disponible">
                <i class="fa-solid fa-circle-play"></i>
                <p>Video institucional no disponible actualmente.</p>
            </div>

            <?php endif; ?>

        </div>
    </section>

    <!-- Canales de atención -->
    <section class="premium-section premium-section--white reveal">
        <div class="container" style="text-align:center;">

            <div class="section-label">Canales de atención</div>
            <div class="section-title">Habla con nosotros</div>

            <div class="channels-grid">

                <a href="https://wa.me/<?php echo htmlspecialchars($idx_wa_clean, ENT_QUOTES, 'UTF-8'); ?>"
                   target="_blank"
                   rel="noopener"
                   class="channel-item">

                    <div class="icon-round" style="background:#25D366;">
                        <i class="fa-brands fa-whatsapp"></i>
                    </div>

                    <div>WhatsApp</div>

                    <span class="channel-dato">
                        <?php echo htmlspecialchars($idx_whatsapp, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </a>

                <a href="tel:<?php echo htmlspecialchars($idx_tel_clean, ENT_QUOTES, 'UTF-8'); ?>"
                   class="channel-item">

                    <div class="icon-round" style="background:#111;color:#fb923c;">
                        <i class="fa-solid fa-phone"></i>
                    </div>

                    <div>Llamada</div>

                    <span class="channel-dato">
                        <?php echo htmlspecialchars($idx_telefono, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </a>

                <a href="mailto:<?php echo htmlspecialchars($idx_correo, ENT_QUOTES, 'UTF-8'); ?>"
                   class="channel-item">

                    <div class="icon-round" style="background:#111;color:#fb923c;">
                        <i class="fa-solid fa-envelope"></i>
                    </div>

                    <div>Correo</div>

                    <span class="channel-dato">
                        <?php echo htmlspecialchars($idx_correo, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </a>

                <a href="contacto.php" class="channel-item">

                    <div class="icon-round" style="background:#111;color:#fb923c;">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>

                    <div>Bases</div>

                    <span class="channel-dato">Sampués y Sincelejo</span>
                </a>

            </div>

        </div>
    </section>

</main>

<?php include 'includes/footer.php'; ?>

<script src="js/nav.js"></script>
<script src="js/reveal.js"></script>
<script src="js/carousel3d.js"></script>
<script src="js/hero-slideshow.js"></script>
<script src="js/video-conocenos.js"></script>

</body>
</html>
