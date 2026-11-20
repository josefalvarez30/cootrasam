<?php
// Aquí se hace la conexión con la base de datos que usa la página, la cual estara vacia por los datos personales.

$host = "localhost";
$usuario_bd = "";
$password_bd = "";
$nombre_bd = "";

$conexion = new mysqli($host, $usuario_bd, $password_bd, $nombre_bd);

// Si la conexión falla se muestra el error para poder revisarlo.
if ($conexion->connect_error) {
    die("Error al conectar con la base de datos: " . $conexion->connect_error);
}


$conexion->set_charset("utf8mb4");
?>
