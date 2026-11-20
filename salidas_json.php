
<?php
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Bogota');

require_once __DIR__ . '/conexion.php';

$ahora = time();
$salidas = [];

$sql = "SELECT id, bus, origen, fecha_hora
        FROM salidas
        WHERE fecha_hora >= NOW()
        ORDER BY fecha_hora ASC";

$resultado = $conexion->query($sql);

if (!$resultado) {
    http_response_code(500);
    echo json_encode([
        'error' => 'No se pudieron consultar las salidas.',
        'ahora' => $ahora,
        'salidas' => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

while ($fila = $resultado->fetch_assoc()) {
    $origen = mb_strtolower(trim($fila['origen']), 'UTF-8');

    if (strpos($origen, 'sampu') !== false) {
        $clave = 'sampues';
    } elseif (strpos($origen, 'sincele') !== false) {
        $clave = 'sincelejo';
    } else {
        continue;
    }

    $ts = strtotime($fila['fecha_hora']);

    if ($ts === false || $ts < $ahora) {
        continue;
    }

    $salidas[] = [
        'id' => (int) $fila['id'],
        'bus' => $fila['bus'],
        'origen' => $fila['origen'],
        'clave' => $clave,
        'ts' => $ts
    ];
}

echo json_encode([
    'ahora' => $ahora,
    'salidas' => $salidas
], JSON_UNESCAPED_UNICODE);

$conexion->close();
