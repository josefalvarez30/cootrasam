
<?php
/*
 * COOTRASAM - Configuracion de correo SMTP
 * Compatible con PHP 5.6
 *
 * Este archivo debe permanecer fuera
 * de la carpeta publica www.
 */

return array(
    'host' => 'smtp.gmail.com',
    'puerto' => 587,
    'seguridad' => 'tls',

    'usuario' => '@',

    'clave' => '**********',

    'remitente' => '@gmail.com',
    'nombre_remitente' => 'COOTRASAM - Atencion PQR',

    'correo_notificaciones' => '@gmail.com'
);
/*esta parte es lo que se debe cambiar por el correo de la empresa y la clave de aplicacion que se hace en el mismo correo 
'usuario' => 'TU_CORREO',
'clave' => 'TU_CONTRASEÑA_DE_APLICACION',
'remitente' => 'TU_CORREO',
'correo_notificaciones' => 'TU_CORREO'
*/