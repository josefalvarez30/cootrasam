
<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel de Administración - Cootrasam</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&family=Oswald:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Poppins', sans-serif;
}

body {
    background: #f7f7f7;
    color: #1a1a1a;
    min-height: 100vh;
}

header {
    background: #1a1a1a;
    color: #fff;
    padding: 16px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

header h1 {
    font-family: 'Oswald', sans-serif;
    font-size: 20px;
    color: #fb923c;
    display: flex;
    align-items: center;
    gap: 10px;
}

.btn-logout {
    background: #c2410c;
    color: #fff;
    text-decoration: none;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 700;
}

.btn-logout:hover {
    background: #a3360a;
}

.container {
    max-width: 1100px;
    margin: 40px auto;
    padding: 0 20px;
}

.welcome h2 {
    font-family: 'Oswald', sans-serif;
    font-size: 26px;
    margin-bottom: 4px;
}

.welcome p {
    color: #666;
    font-size: 14px;
    margin-bottom: 30px;
}

.grid-menu {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 20px;
}

.card-item {
    background: #fff;
    border-radius: 12px;
    padding: 22px;
    text-decoration: none;
    color: inherit;
    box-shadow: 0 2px 10px rgba(0,0,0,.05);
    border-top: 4px solid #c2410c;
    transition: transform .2s, box-shadow .2s;
    display: flex;
    flex-direction: column;
    min-height: 150px;
}

.card-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,.1);
}

.card-icon {
    width: 36px;
    height: 36px;
    background: #fff3eb;
    color: #c2410c;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    margin-bottom: 12px;
}

.card-item h3 {
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 6px;
    color: #1a1a1a;
}

.card-item p {
    font-size: 12px;
    color: #666;
    line-height: 1.5;
}

@media (max-width: 600px) {
    header {
        padding: 16px 20px;
    }

    .container {
        margin: 25px auto;
    }
}
</style>
</head>

<body>

<header>
    <h1><i class="fa-solid fa-bus"></i> Panel Cootrasam</h1>
    <a href="logout.php" class="btn-logout">Cerrar sesión</a>
</header>

<div class="container">
    <div class="welcome">
        <h2>Hola, Administrador Cootrasam</h2>
        <p>Elige qué quieres editar.</p>
    </div>

    <div class="grid-menu">

        <a href="horarios.php" class="card-item">
            <div class="card-icon"><i class="fa-solid fa-clock"></i></div>
            <h3>Horarios</h3>
            <p>Editar horarios de entre semana, sábado y domingo.</p>
        </a>

        <a href="tarifas.php" class="card-item">
            <div class="card-icon"><i class="fa-solid fa-ticket"></i></div>
            <h3>Tarifas</h3>
            <p>Editar el valor del pasaje.</p>
        </a>

        <a href="noticias.php" class="card-item">
            <div class="card-icon"><i class="fa-solid fa-newspaper"></i></div>
            <h3>Noticias y avisos</h3>
            <p>Agregar o quitar publicaciones.</p>
        </a>

        <a href="destinos.php" class="card-item">
            <div class="card-icon"><i class="fa-solid fa-map-location-dot"></i></div>
            <h3>Destinos</h3>
            <p>Administrar la galería de servicios especiales.</p>
        </a>

        <a href="flota.php" class="card-item">
            <div class="card-icon"><i class="fa-solid fa-van-shuttle"></i></div>
            <h3>Nuestra flota</h3>
            <p>Agregar, editar o quitar buses del sitio.</p>
        </a>

        <a href="hero.php" class="card-item">
            <div class="card-icon"><i class="fa-solid fa-images"></i></div>
            <h3>Fotos del fondo</h3>
            <p>Administrar las imágenes del carrusel principal.</p>
        </a>

        <a href="pqr.php" class="card-item">
            <div class="card-icon"><i class="fa-solid fa-inbox"></i></div>
            <h3>Mensajes PQR</h3>
            <p>Ver los mensajes enviados por los usuarios.</p>
        </a>

        <a href="configuracion.php" class="card-item">
            <div class="card-icon"><i class="fa-solid fa-gear"></i></div>
            <h3>Configuración</h3>
            <p>Administrar teléfono, correo, direcciones y redes.</p>
        </a>

    </div>
</div>

</body>
</html>
