<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'conexion.php';

// ===== TRAER LAS TARIFAS DESDE LA BASE DE DATOS =====
$tarifas_por_tipo = array();

$resultado = $conexion->query("SELECT * FROM tarifas");

if ($resultado) {
    while ($fila = $resultado->fetch_assoc()) {
        $clave = strtolower(trim($fila['tipo']));
        $tarifas_por_tipo[$clave] = $fila;
    }
}

// ===== TARIFAS Y VALORES DE RESPALDO =====
$pasaje = isset($tarifas_por_tipo['pasaje regular'])
    ? $tarifas_por_tipo['pasaje regular']
    : array(
        'valor' => '$5.000',
        'descripcion' => 'Viaja entre Sampués y Sincelejo pagando al llegar a tu destino.'
    );

$tiquete = isset($tarifas_por_tipo['tiquete'])
    ? $tarifas_por_tipo['tiquete']
    : array(
        'valor' => '$4.500',
        'descripcion' => 'Comprado directamente en la base.'
    );

// ===== ENCOMIENDAS: se muestran u ocultan y se escribe la nota desde el panel (Tarifas) =====
$mostrar_enc = true;
$nota_enc = 'Servicio aún no disponible, próximo a funcionar.';

$res_cfg = $conexion->query("SELECT clave, valor FROM configuracion WHERE clave IN ('mostrar_encomiendas', 'encomiendas_nota')");
if ($res_cfg) {
    while ($c = $res_cfg->fetch_assoc()) {
        if ($c['clave'] === 'mostrar_encomiendas') {
            $mostrar_enc = ($c['valor'] !== '0');
        }
        if ($c['clave'] === 'encomiendas_nota' && trim($c['valor']) !== '') {
            $nota_enc = $c['valor'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tarifas - Cootrasam</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="css/variables.css">
<link rel="stylesheet" href="css/base.css">
<link rel="stylesheet" href="css/nav.css">
<link rel="stylesheet" href="css/paginas.css">
<link rel="stylesheet" href="css/reveal.css">

<style>
/* Tarjetas de tarifas */
.tarifas-principales {
    display: grid;
    grid-template-columns: minmax(0, 680px);
    justify-content: center;
    margin-top: 20px;
}

.tarifa-card {
    background: #fff;
    border: 1px solid #f0e5da;
    border-radius: 14px;
    padding: 26px;
    box-shadow: 0 5px 18px rgba(50, 30, 10, 0.05);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    min-width: 0;
}

.tarifa-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(50, 30, 10, 0.10);
}

.tarifa-card-cabecera {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 18px;
}

.tarifa-icono {
    width: 50px;
    height: 50px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: #fff0e5;
    color: var(--color-primary);
    font-size: 21px;
}

.tarifa-card h3 {
    font-family: var(--font-display);
    font-size: 19px;
    line-height: 1.35;
    font-weight: 600;
    color: var(--color-dark);
    margin: 0;
}

.tarifa-subtitulo {
    color: var(--color-gray);
    font-size: 12px;
    margin-top: 4px;
}

.tarifa-precio {
    font-family: var(--font-display);
    font-size: 32px;
    line-height: 1.2;
    font-weight: 700;
    color: var(--color-primary);
    margin: 14px 0 10px;
    overflow-wrap: anywhere;
}

.tarifa-descripcion {
    color: var(--color-gray);
    font-size: 14px;
    line-height: 1.75;
    margin: 0;
    overflow-wrap: anywhere;
}

.tiquete-base {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    margin-top: 22px;
    padding: 15px;
    background: #fff8f1;
    border: 1px solid #f3dfce;
    border-radius: 10px;
}

.tiquete-base-info {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    min-width: 0;
}

.tiquete-base-info i {
    color: var(--color-primary);
    margin-top: 4px;
}

.tiquete-base-info strong {
    display: block;
    color: var(--color-dark);
    font-size: 13px;
    line-height: 1.5;
}

.tiquete-base-info span {
    display: block;
    color: var(--color-gray);
    font-size: 12px;
    line-height: 1.5;
    margin-top: 3px;
}

.tiquete-base-precio {
    color: var(--color-primary);
    font-family: var(--font-display);
    font-size: 21px;
    font-weight: 700;
    white-space: nowrap;
}

/* Apartado de encomiendas */
.encomiendas-seccion {
    padding: 0 0 50px;
    background: linear-gradient(180deg, #fff8f2 0%, #fff 100%);
}

.encomiendas-card {
    max-width: 680px;
    margin: 0 auto;
    padding: 26px;
    background: #fff;
    border: 1px solid #f0e5da;
    border-radius: 14px;
    box-shadow: 0 5px 18px rgba(50, 30, 10, 0.05);
}

.encomiendas-card-cabecera {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 15px;
}

.encomiendas-mensaje {
    margin-top: 20px;
    padding: 15px;
    background: #fff8f1;
    border: 1px solid #f3dfce;
    border-radius: 10px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.encomiendas-mensaje i {
    color: var(--color-primary);
    margin-top: 4px;
}

.encomiendas-mensaje p {
    margin: 0;
    color: var(--color-gray);
    font-size: 13px;
    line-height: 1.7;
}

@media (max-width: 700px) {
    .tarifa-card,
    .encomiendas-card {
        padding: 21px;
    }

    .tarifa-card h3 {
        font-size: 18px;
    }

    .tarifa-precio {
        font-size: 29px;
    }
}

@media (max-width: 380px) {
    .tiquete-base {
        align-items: flex-start;
        flex-direction: column;
        gap: 8px;
    }
}
</style>
</head>

<body>

<?php include 'includes/header.php'; ?>

<main>

    <div class="page-banner">
        <div class="container">
            <h1>Tarifas</h1>

            <div class="route-strip" style="color:#fff;">
                <span class="parada">SAMPUÉS</span>
                <span class="via"></span>
                <i class="fa-solid fa-ticket"></i>
                <span class="via"></span>
                <span class="parada">SINCELEJO</span>
            </div>
        </div>
    </div>

    <section class="section-soft-orange reveal" style="padding:40px 0;">
        <div class="dot-pattern"></div>

        <div class="container" style="position:relative;">
            <a class="back-link" onclick="history.back()" style="color:#fff; font-weight:700;">← Volver</a>

            <h2 style="margin-top:10px; color:#fff;">Precios claros para tu viaje</h2>

            <p style="max-width:700px; color:#fff1e6;">
                Conoce nuestras tarifas de transporte de pasajeros<?php echo $mostrar_enc ? ' y nuestro servicio de encomiendas' : ''; ?>.
                Consulta el valor de tu viaje y desplázate con tranquilidad.
            </p>
        </div>
    </section>

    <div class="diagonal-divider"></div>

    <section style="padding:10px 0 35px; background:linear-gradient(180deg,#fff 0%,#fff8f2 100%);">
        <div class="container">

            <div class="tarifas-principales">

                <div class="tarifa-card reveal">
                    <div class="tarifa-card-cabecera">
                        <div class="tarifa-icono">
                            <i class="fa-solid fa-bus"></i>
                        </div>

                        <div>
                            <h3>Pasaje regular</h3>
                            <p class="tarifa-subtitulo">Sampués ↔ Sincelejo</p>
                        </div>
                    </div>

                    <p class="tarifa-descripcion">
                        Para quienes realizan el recorrido completo entre Sampués y Sincelejo,
                        en cualquiera de los dos sentidos.
                    </p>

                    <p class="tarifa-precio">
                        <?php echo htmlspecialchars($pasaje['valor']); ?>
                    </p>

                    <p class="tarifa-descripcion">
                        <?php echo htmlspecialchars($pasaje['descripcion']); ?>
                    </p>

                    <div class="tiquete-base">
                        <div class="tiquete-base-info">
                            <i class="fa-solid fa-ticket"></i>
                            <div>
                                <strong>Tiquete comprado en la base</strong>
                                <span><?php echo htmlspecialchars($tiquete['descripcion']); ?></span>
                            </div>
                        </div>

                        <div class="tiquete-base-precio">
                            <?php echo htmlspecialchars($tiquete['valor']); ?>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <?php if ($mostrar_enc): ?>
    <section class="encomiendas-seccion reveal">
        <div class="container">

            <div class="encomiendas-card">
                <div class="encomiendas-card-cabecera">
                    <div class="tarifa-icono">
                        <i class="fa-solid fa-box-open"></i>
                    </div>

                    <div>
                        <h3>Servicio de encomiendas</h3>
                        <p class="tarifa-subtitulo">Envíos entre nuestros destinos</p>
                    </div>
                </div>

                <p class="tarifa-descripcion">
                    En COOTRASAM contamos con el servicio de transporte de encomiendas
                    entre nuestros destinos.
                </p>

                <div class="encomiendas-mensaje">
                    <i class="fa-solid fa-circle-info"></i>
                    <p>
                        <strong>NOTA:</strong>
                        <?php echo htmlspecialchars($nota_enc); ?>
                    </p>
                </div>
            </div>

        </div>
    </section>
    <?php endif; ?>

    <section class="section-dark reveal" style="padding:50px 0;">
        <div class="container" style="text-align:center;">
            <h3 style="font-family:var(--font-display); font-size:20px; margin-bottom:8px;">
                ¿Tienes dudas sobre una tarifa?
            </h3>

            <p style="color:#ccc; font-size:14px; margin-bottom:18px;">
                Escríbenos y te ayudamos a confirmar el valor de tu viaje<?php echo $mostrar_enc ? ' o resolver dudas sobre encomiendas' : ''; ?>.
            </p>

            <a href="https://wa.me/573144047517" target="_blank" rel="noopener noreferrer" class="btn-whatsapp">
                <i class="fa-brands fa-whatsapp"></i>
                Consultar por WhatsApp
            </a>
        </div>
    </section>

</main>

<?php include 'includes/footer.php'; ?>

<script src="js/nav.js"></script>
<script src="js/reveal.js"></script>

</body>
</html>
