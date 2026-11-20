
<?php
date_default_timezone_set('America/Bogota');
require_once __DIR__ . '/conexion.php';

$datos = array(
    'semana' => array(),
    'sabado' => array(),
    'domingo' => array()
);

$resultado = $conexion->query(
    "SELECT * FROM horarios ORDER BY origen"
);

if ($resultado) {
    while ($fila = $resultado->fetch_assoc()) {
        if (isset($datos[$fila['tipo_dia']])) {
            $datos[$fila['tipo_dia']][$fila['origen']] = $fila;
        }
    }
}

$n = (int)date('N');
$dia_hoy = ($n === 6) ? 'sabado' : (($n === 7) ? 'domingo' : 'semana');

function tarjeta($datos, $tipo_dia, $origen, $destino) {
    echo '<div class="hz-card">';
    echo '<div class="hz-ruta">' . htmlspecialchars($origen, ENT_QUOTES, 'UTF-8') .
         ' <span class="hz-flecha">→</span> ' .
         htmlspecialchars($destino, ENT_QUOTES, 'UTF-8') . '</div>';

    if (isset($datos[$tipo_dia][$origen])) {
        $d = $datos[$tipo_dia][$origen];

        echo '<div class="hz-horas">';
        echo '<div><div class="hz-lab">Primera salida</div><div class="hz-big">' .
             htmlspecialchars($d['primera_salida'], ENT_QUOTES, 'UTF-8') .
             '</div></div>';
        echo '<div><div class="hz-lab">Última salida</div><div class="hz-big">' .
             htmlspecialchars($d['ultima_salida'], ENT_QUOTES, 'UTF-8') .
             '</div></div>';
        echo '</div>';

        echo '<span class="hz-chip">Frecuencia: ' .
             htmlspecialchars($d['frecuencia'], ENT_QUOTES, 'UTF-8') .
             '</span>';
    } else {
        echo '<p class="hz-pend">Este horario todavía está por confirmar. Escríbenos y te decimos.</p>';
        echo '<span class="hz-chip hz-chip-pend">Por confirmar</span>';
    }

    echo '</div>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Horarios - Cootrasam</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<link rel="stylesheet" href="css/variables.css">
<link rel="stylesheet" href="css/base.css">
<link rel="stylesheet" href="css/nav.css">
<link rel="stylesheet" href="css/paginas.css">
<link rel="stylesheet" href="css/reveal.css">

<style>
.hz-tabs {
    display:flex;
    gap:8px;
    flex-wrap:wrap;
    margin:44px 0 20px;
}
.hz-tab {
    padding:11px 24px;
    border-radius:30px;
    border:0;
    cursor:pointer;
    background:var(--color-light-bg);
    color:var(--color-gray);
    font:600 14px var(--font-main);
    transition:var(--transition);
}
.hz-tab:hover { background:#e9dcc3; }
.hz-tab.on { background:var(--color-primary); color:#fff; }
.hz-hoy {
    display:inline-block;
    margin-left:8px;
    padding:2px 8px;
    border-radius:10px;
    background:rgba(255,255,255,.25);
    font-size:11px;
}
.hz-tab:not(.on) .hz-hoy { background:#fff; color:var(--color-primary); }
.hz-panel[hidden] { display:none; }
.hz-cards {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:24px;
}
.hz-card {
    background:#fff;
    border-radius:16px;
    padding:28px;
    box-shadow:0 4px 18px rgba(0,0,0,.07);
    border-top:4px solid var(--color-primary);
}
.hz-ruta {
    font-family:var(--font-display);
    font-size:24px;
    font-weight:600;
}
.hz-flecha { color:var(--color-primary); }
.hz-horas {
    display:flex;
    gap:40px;
    flex-wrap:wrap;
    margin:22px 0;
}
.hz-lab {
    font-size:12px;
    letter-spacing:1px;
    text-transform:uppercase;
    color:var(--color-gray);
}
.hz-big {
    font-family:var(--font-display);
    font-size:46px;
    color:var(--color-primary);
    line-height:1.1;
}
.hz-chip {
    display:inline-block;
    background:var(--color-light-bg);
    color:var(--color-gray);
    border-radius:20px;
    padding:6px 14px;
    font-size:13px;
    font-weight:600;
}
.hz-chip-pend { background:#fef3c7; color:#92400e; }
.hz-pend { color:var(--color-gray); margin:22px 0 16px; }

@media(max-width:760px) {
    .hz-cards { grid-template-columns:1fr; }
    .hz-horas { gap:24px; }
    .hz-big { font-size:38px; }
}
</style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<main>
    <div class="page-banner">
        <div class="container">
            <h1>Horarios y salidas</h1>

            <div class="route-strip" style="color:#fff;">
                <span class="parada">SAMPUÉS</span>
                <span class="via"></span>
                <i class="fa-solid fa-clock"></i>
                <span class="via"></span>
                <span class="parada">SINCELEJO</span>
            </div>

            <p style="margin-top:10px;">
                Consulta nuestros horarios de salida entre Sampués y Sincelejo
                para cada día de la semana. No necesitas reservar.
            </p>
        </div>
    </div>

    <section style="padding:10px 0 60px;background:linear-gradient(180deg,#fff 0%,#fff8f2 100%);">
        <div class="container">

            <!-- Horarios normales administrables -->
            <div class="hz-tabs" role="tablist">
                <button type="button"
                    class="hz-tab<?php echo $dia_hoy === 'semana' ? ' on' : ''; ?>"
                    data-dia="semana" role="tab">
                    Entre semana
                    <?php if ($dia_hoy === 'semana') echo '<span class="hz-hoy">Hoy</span>'; ?>
                </button>

                <button type="button"
                    class="hz-tab<?php echo $dia_hoy === 'sabado' ? ' on' : ''; ?>"
                    data-dia="sabado" role="tab">
                    Sábado
                    <?php if ($dia_hoy === 'sabado') echo '<span class="hz-hoy">Hoy</span>'; ?>
                </button>

                <button type="button"
                    class="hz-tab<?php echo $dia_hoy === 'domingo' ? ' on' : ''; ?>"
                    data-dia="domingo" role="tab">
                    Domingo
                    <?php if ($dia_hoy === 'domingo') echo '<span class="hz-hoy">Hoy</span>'; ?>
                </button>
            </div>

            <div class="hz-panel" id="hz-semana" role="tabpanel"
                <?php if ($dia_hoy !== 'semana') echo 'hidden'; ?>>
                <div class="hz-cards">
                    <?php
                    tarjeta($datos, 'semana', 'Sampués', 'Sincelejo');
                    tarjeta($datos, 'semana', 'Sincelejo', 'Sampués');
                    ?>
                </div>
            </div>

            <div class="hz-panel" id="hz-sabado" role="tabpanel"
                <?php if ($dia_hoy !== 'sabado') echo 'hidden'; ?>>
                <div class="hz-cards">
                    <?php
                    tarjeta($datos, 'sabado', 'Sampués', 'Sincelejo');
                    tarjeta($datos, 'sabado', 'Sincelejo', 'Sampués');
                    ?>
                </div>
            </div>

            <div class="hz-panel" id="hz-domingo" role="tabpanel"
                <?php if ($dia_hoy !== 'domingo') echo 'hidden'; ?>>
                <div class="hz-cards">
                    <?php
                    tarjeta($datos, 'domingo', 'Sampués', 'Sincelejo');
                    tarjeta($datos, 'domingo', 'Sincelejo', 'Sampués');
                    ?>
                </div>
            </div>

        </div>
    </section>

    <section class="section-dark reveal" style="padding:50px 0;">
        <div class="container" style="text-align:center;">
            <h3 style="font-family:var(--font-display);font-size:20px;margin-bottom:8px;">
                ¿Necesitas confirmar una salida?
            </h3>

            <p style="color:#ccc;font-size:14px;margin-bottom:18px;">
                Escríbenos y te confirmamos la hora más próxima disponible.
            </p>

            <a href="https://wa.me/573144047517"
               target="_blank"
               rel="noopener"
               class="btn-whatsapp">
                <i class="fa-brands fa-whatsapp"></i> Consultar por WhatsApp
            </a>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>

<script src="js/nav.js"></script>
<script src="js/reveal.js"></script>

<script>
(function() {
    var tabs = document.querySelectorAll('.hz-tab');
    var paneles = document.querySelectorAll('.hz-panel');

    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            var dia = this.getAttribute('data-dia');

            tabs.forEach(function(t) {
                t.classList.remove('on');
            });

            paneles.forEach(function(p) {
                p.hidden = true;
            });

            this.classList.add('on');
            document.getElementById('hz-' + dia).hidden = false;
        });
    });
})();
</script>

</body>
</html>
