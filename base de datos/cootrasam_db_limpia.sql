-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 19-11-2026 a las 04:35:02
-- Versión del servidor: 5.7.15-log
-- Versión de PHP: 5.6.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `cootrasam_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admin_usuarios`
--

CREATE TABLE `admin_usuarios` (
  `id` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `admin_usuarios`
--

INSERT INTO `admin_usuarios` (`id`, `usuario`, `contrasena`, `nombre`) VALUES
(1, 'admincootrasam', '$2y$10$gPN.yo3XVcuhLJOWYKMkouVqlU0zYBDM9ZiS8mMgPomFqCGQEUnvi', 'Administrador Cootrasam');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `clave` varchar(50) NOT NULL,
  `valor` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`clave`, `valor`) VALUES
('coords_sampues', ''),
('coords_sincelejo', ''),
('correo', 'josefealgo@gmail.com'),
('direccion_sampues', 'Calle 24 N° 13C - 40 La Balsa'),
('direccion_sincelejo', 'Cra. 25 #25-74, Sincelejo, Sucre'),
('encomiendas_nota', 'Servicio aún no disponible, próximo a funcionar.'),
('facebook', 'https://facebook.com/cootrasam.ltda'),
('instagram', 'https://instagram.com/cootrasamltda'),
('mostrar_encomiendas', '0'),
('telefono', '3144047517'),
('twitter', 'https://twitter.com/Cootrasam_ltda'),
('whatsapp', '3144047517');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `destinos`
--

CREATE TABLE `destinos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `orden` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `destinos`
--

INSERT INTO `destinos` (`id`, `nombre`, `imagen`, `orden`) VALUES
(1, 'monteria city', 'assets/img/destinos/destino_1793377025_5292.png', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `flota`
--

CREATE TABLE `flota` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(150) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `orden` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hero_fotos`
--

CREATE TABLE `hero_fotos` (
  `id` int(11) NOT NULL,
  `imagen` varchar(255) NOT NULL,
  `orden` int(11) DEFAULT '0',
  `enfoque` varchar(10) NOT NULL DEFAULT 'center'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `hero_fotos`
--

INSERT INTO `hero_fotos` (`id`, `imagen`, `orden`, `enfoque`) VALUES
(10, 'assets/img/hero/hero_1791924038_6325.jpeg', 7, 'top'),
(18, 'assets/img/hero/1791993452_logocootrasam.png', 2, 'center');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios`
--

CREATE TABLE `horarios` (
  `id` int(11) NOT NULL,
  `origen` varchar(20) NOT NULL,
  `tipo_dia` varchar(20) NOT NULL,
  `primera_salida` varchar(20) NOT NULL,
  `ultima_salida` varchar(20) NOT NULL,
  `frecuencia` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `horarios`
--

INSERT INTO `horarios` (`id`, `origen`, `tipo_dia`, `primera_salida`, `ultima_salida`, `frecuencia`) VALUES
(1, 'Sampués', 'semana', '5:40 a.m.', '8:30 p.m.', 'cada 12 min. aprox.'),
(2, 'Sincelejo', 'semana', '6:00 a.m.', '9:00 p.m.', 'cada 12 min. aprox.'),
(3, 'Sampués', 'sabado', '6:00 a.m.', '8:00 p.m.', 'cada 12 min. aprox.'),
(6, 'Sampués', 'domingo', '6:00 a.m.', '9:00 p.m.', '2'),
(7, 'Sincelejo', 'domingo', '5:40 a.m.', '8:00 p.m.', '12 min ');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `noticias_avisos`
--

CREATE TABLE `noticias_avisos` (
  `id` int(11) NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `contenido` text NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `fecha` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pqr_mensajes`
--

CREATE TABLE `pqr_mensajes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `cedula` varchar(30) DEFAULT NULL,
  `tipo` varchar(20) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha_relacionada` date DEFAULT NULL,
  `medio` varchar(20) NOT NULL DEFAULT 'correo',
  `estado` varchar(20) NOT NULL DEFAULT 'nuevo',
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  `leido` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `pqr_mensajes`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pqr_respuestas`
--

CREATE TABLE `pqr_respuestas` (
  `id` int(11) NOT NULL,
  `pqr_id` int(11) NOT NULL,
  `respuesta` text NOT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `enviado` tinyint(1) NOT NULL DEFAULT '0',
  `fecha_envio` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `pqr_respuestas`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarifas`
--

CREATE TABLE `tarifas` (
  `id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `valor` varchar(20) NOT NULL,
  `descripcion` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `tarifas`
--

INSERT INTO `tarifas` (`id`, `tipo`, `valor`, `descripcion`) VALUES
(1, 'Pasaje regular', '$5.000', 'Este se paga en el bus al llegar a su destino '),
(2, 'tiquete', '$4.500', 'puntos de ventas en las bases de cootrasam'),
(3, 'Encomiendas', 'Según tamaño', 'El valor se define en la base al momento de entregar la encomienda.'),
(4, 'Estudiantes', '$4.000', 'Tarifa especial para estudiantes.'),
(5, 'Trayectos parciales', '$4.000', 'Para recorridos entre puntos intermedios de la ruta.');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `admin_usuarios`
--
ALTER TABLE `admin_usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`clave`);

--
-- Indices de la tabla `destinos`
--
ALTER TABLE `destinos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `flota`
--
ALTER TABLE `flota`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `hero_fotos`
--
ALTER TABLE `hero_fotos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `horarios`
--
ALTER TABLE `horarios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `noticias_avisos`
--
ALTER TABLE `noticias_avisos`
  ADD PRIMARY KEY (`id`);

--

--
-- Indices de la tabla `pqr_mensajes`
--
ALTER TABLE `pqr_mensajes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pqr_respuestas`
--
ALTER TABLE `pqr_respuestas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pqr_id` (`pqr_id`);

--
-- Indices de la tabla `tarifas`
--
ALTER TABLE `tarifas`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `admin_usuarios`
--
ALTER TABLE `admin_usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT de la tabla `destinos`
--
ALTER TABLE `destinos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT de la tabla `flota`
--
ALTER TABLE `flota`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `hero_fotos`
--
ALTER TABLE `hero_fotos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
--
-- AUTO_INCREMENT de la tabla `horarios`
--
ALTER TABLE `horarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT de la tabla `noticias_avisos`
--
ALTER TABLE `noticias_avisos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
--
-- AUTO_INCREMENT de la tabla `pqr_mensajes`
--
ALTER TABLE `pqr_mensajes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `pqr_respuestas`
--
ALTER TABLE `pqr_respuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `tarifas`
--
ALTER TABLE `tarifas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `pqr_respuestas`
--
ALTER TABLE `pqr_respuestas`
  ADD CONSTRAINT `fk_pqr_respuestas_mensajes` FOREIGN KEY (`pqr_id`) REFERENCES `pqr_mensajes` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
