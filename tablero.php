<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Salidas - Cootrasam</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="css/salidas.css">
<style>
  body { margin: 0; background: #0a0a0b; min-height: 100vh; display: flex; flex-direction: column; font-family: 'Poppins', sans-serif; }
  .tv-barra { display: flex; justify-content: space-between; align-items: center; padding: 10px 22px; }
  .tv-barra a, .tv-barra button { color: #a1a1aa; text-decoration: none; background: none; border: 1px solid #333; border-radius: 6px; padding: 6px 12px; cursor: pointer; font-family: inherit; font-size: 13px; }
  .tv-barra a:hover, .tv-barra button:hover { color: #fff; border-color: #555; }
  .tv-contenido { flex: 1; padding: 0 22px 22px; }
</style>
</head>
<body>
  <div class="tv-barra">
    <a href="index.php">← Volver al sitio</a>
    <button type="button" id="btnPantalla">Pantalla completa</button>
  </div>

  <div class="tv-contenido">
    <div class="sal-tablero sal-grande" data-salidas="tablero" data-limite="6">

      <div class="sal-cabecera">
        <div class="sal-titulo">
          <img src="assets/img/logo/logo-cootrasam.png" alt="" onerror="this.style.display='none'">
          <div>
            Salidas
            <div class="sal-subtitulo">Cootrasam · Sampués y Sincelejo</div>
          </div>
        </div>
        <div class="sal-reloj">
          <div><span class="sal-reloj-hms">--:--:--</span><span class="sal-reloj-ampm"></span></div>
          <div class="sal-reloj-fecha"></div>
        </div>
      </div>

      <div class="sal-columnas">
        <div class="sal-col" data-base="sampues">
          <div class="sal-ruta"><span>Sampués</span><i class="fa-solid fa-arrow-right-long"></i><span>Sincelejo</span></div>
          <div class="sal-encabezados"><span>Hora</span><span>Placa</span><span>Faltan</span></div>
          <ul class="sal-lista"></ul>
          <p class="sal-vacio">Cargando salidas...</p>
        </div>
        <div class="sal-col" data-base="sincelejo">
          <div class="sal-ruta"><span>Sincelejo</span><i class="fa-solid fa-arrow-right-long"></i><span>Sampués</span></div>
          <div class="sal-encabezados"><span>Hora</span><span>Placa</span><span>Faltan</span></div>
          <ul class="sal-lista"></ul>
          <p class="sal-vacio">Cargando salidas...</p>
        </div>
      </div>

    </div>
  </div>

<script src="js/salidas.js"></script>
<script>
  // Botón para poner el tablero en pantalla completa (ideal para el televisor de la base)
  document.getElementById('btnPantalla').addEventListener('click', function () {
    var el = document.documentElement;
    if (!document.fullscreenElement) {
      if (el.requestFullscreen) { el.requestFullscreen(); }
      else if (el.webkitRequestFullscreen) { el.webkitRequestFullscreen(); }
    } else if (document.exitFullscreen) {
      document.exitFullscreen();
    }
  });
</script>
</body>
</html>
