-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-10-2025 a las 03:47:12
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `invitrosoft`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`) VALUES
(1, 'solido', 'Descripción de la categoría 1'),
(2, 'liquido', 'Descripción de la categoría 2'),
(3, 'gaseoso', 'Descripción de la categoría 3');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contaminaciones`
--

CREATE TABLE `contaminaciones` (
  `id` int(11) NOT NULL,
  `planta_id` int(11) NOT NULL,
  `fase_tipo` enum('establecimiento','multiplicacion','enraizamiento','adaptacion') NOT NULL,
  `fase_id` int(11) NOT NULL COMMENT 'ID de la fase espec?fica',
  `tipo` enum('endogena','exogena') NOT NULL,
  `cantidad` int(11) DEFAULT 0,
  `motivo` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_contaminacion` date NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fases_protocolo`
--

CREATE TABLE `fases_protocolo` (
  `id` int(11) NOT NULL,
  `protocolo_id` int(11) NOT NULL,
  `numero_fase` int(11) NOT NULL,
  `nombre_fase` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `fases_protocolo`
--

INSERT INTO `fases_protocolo` (`id`, `protocolo_id`, `numero_fase`, `nombre_fase`, `descripcion`, `imagen_url`) VALUES
(13, 1, 1, 'eleccionhn', 'ebbvelekvbhfv', ''),
(14, 1, 2, 'limpiesa', 'crevrevevv', 'fase_68de6e90efec8.png'),
(15, 2, 1, 'ii,oi,o', ',i,,,,,,i,io,o,nnnnnmm', 'fase_68e06bd582b02.jpeg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fase_adaptacion`
--

CREATE TABLE `fase_adaptacion` (
  `id` int(11) NOT NULL,
  `planta_id` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_finalizacion` date DEFAULT NULL,
  `condiciones_adaptacion` text DEFAULT NULL,
  `medio_cultivo_id` int(11) DEFAULT NULL COMMENT 'FK a parametros tipo origen',
  `resultado_adaptacion` varchar(255) DEFAULT NULL,
  `contaminacion_id` int(11) DEFAULT NULL,
  `estado_id` int(11) DEFAULT NULL,
  `usuario_registro_id` int(11) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fase_adaptacion_elementos`
--

CREATE TABLE `fase_adaptacion_elementos` (
  `id` int(11) NOT NULL,
  `fase_adaptacion_id` int(11) NOT NULL,
  `reactivo_id` int(11) NOT NULL,
  `cantidad` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fase_adaptacion_formulaciones`
--

CREATE TABLE `fase_adaptacion_formulaciones` (
  `id` int(11) NOT NULL,
  `fase_adaptacion_id` int(11) NOT NULL,
  `formulacion_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fase_enraizamiento`
--

CREATE TABLE `fase_enraizamiento` (
  `id` int(11) NOT NULL,
  `planta_id` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_finalizacion` date DEFAULT NULL,
  `medio_utilizado_id` int(11) DEFAULT NULL COMMENT 'FK a parametros tipo origen (mismo que medio cultivo)',
  `estado_raices_id` int(11) DEFAULT NULL COMMENT 'FK a parametros tipo 5 estadoRaices',
  `contaminacion_id` int(11) DEFAULT NULL,
  `estado_id` int(11) DEFAULT NULL,
  `usuario_registro_id` int(11) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fase_establecimiento`
--

CREATE TABLE `fase_establecimiento` (
  `id` int(11) NOT NULL,
  `planta_id` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_finalizacion` date DEFAULT NULL,
  `metodo_propagacion` varchar(255) DEFAULT NULL,
  `contaminacion_id` int(11) DEFAULT NULL,
  `usuario_registro_id` int(11) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fase_establecimiento_elementos`
--

CREATE TABLE `fase_establecimiento_elementos` (
  `id` int(11) NOT NULL,
  `fase_establecimiento_id` int(11) NOT NULL,
  `reactivo_id` int(11) NOT NULL,
  `cantidad` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fase_multiplicacion`
--

CREATE TABLE `fase_multiplicacion` (
  `id` int(11) NOT NULL,
  `planta_id` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_finalizacion` date DEFAULT NULL,
  `num_explantes_generados` int(11) DEFAULT 0,
  `tiempo_estimacion_madurez` int(11) DEFAULT NULL COMMENT 'en meses',
  `contaminacion_id` int(11) DEFAULT NULL,
  `usuario_registro_id` int(11) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fase_multiplicacion_elementos`
--

CREATE TABLE `fase_multiplicacion_elementos` (
  `id` int(11) NOT NULL,
  `fase_multiplicacion_id` int(11) NOT NULL,
  `reactivo_id` int(11) NOT NULL,
  `cantidad` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fase_reactivos`
--

CREATE TABLE `fase_reactivos` (
  `id` int(11) NOT NULL,
  `fase_id` int(11) NOT NULL,
  `reactivo_id` int(11) NOT NULL,
  `cantidad` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `fase_reactivos`
--

INSERT INTO `fase_reactivos` (`id`, `fase_id`, `reactivo_id`, `cantidad`) VALUES
(4, 15, 3, '85');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `formulaciones`
--

CREATE TABLE `formulaciones` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `tipo` enum('soluciones-madre','medios-cultivo','soluciones-desinfectantes') NOT NULL,
  `concentracion` varchar(100) DEFAULT NULL,
  `volumen` varchar(50) DEFAULT NULL,
  `desinfectante` varchar(100) DEFAULT NULL,
  `solucion_madre` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `formulaciones`
--

INSERT INTO `formulaciones` (`id`, `nombre`, `tipo`, `concentracion`, `volumen`, `desinfectante`, `solucion_madre`) VALUES
(2, 'Adanies de Jesús', 'soluciones-madre', '', NULL, '', 'tykm'),
(3, 'Adanies de Jesús', 'soluciones-madre', '', NULL, '', 'yuk,,,g,'),
(4, 'omel', 'soluciones-madre', '', NULL, '', 'bn n vn,'),
(5, 'Adanies de Jesús (Copia)', 'soluciones-madre', '', NULL, '', 'tykm'),
(6, 'Adanies de Jesús (Copia)', 'soluciones-madre', '', NULL, '', 'tykm'),
(7, 'Adanies de Jesús (Copia)', 'soluciones-madre', '', NULL, '', 'tykm'),
(8, 'Adanies de Jesús (Copia)', 'soluciones-madre', '', NULL, '', 'tykm');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `formulacion_reactivos`
--

CREATE TABLE `formulacion_reactivos` (
  `id` int(11) NOT NULL,
  `formulacion_id` int(11) NOT NULL,
  `reactivo_id` int(11) NOT NULL,
  `cantidad` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupos_formulacion`
--

CREATE TABLE `grupos_formulacion` (
  `id` int(11) NOT NULL,
  `formulacion_id` int(11) NOT NULL,
  `nombre_grupo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `grupos_formulacion`
--

INSERT INTO `grupos_formulacion` (`id`, `formulacion_id`, `nombre_grupo`) VALUES
(16, 2, 'dshfjgmm'),
(17, 3, 'kj,,g,,,'),
(18, 4, 'b vb'),
(19, 5, 'dshfjgmm'),
(20, 6, 'dshfjgmm'),
(21, 7, 'dshfjgmm'),
(22, 8, 'dshfjgmm');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupo_reactivos`
--

CREATE TABLE `grupo_reactivos` (
  `id` int(11) NOT NULL,
  `grupo_id` int(11) NOT NULL,
  `reactivo_id` int(11) NOT NULL,
  `cantidad` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `grupo_reactivos`
--

INSERT INTO `grupo_reactivos` (`id`, `grupo_id`, `reactivo_id`, `cantidad`) VALUES
(17, 16, 3, '21'),
(18, 16, 3, '12'),
(19, 17, 5, '25'),
(20, 18, 3, '14'),
(21, 18, 3, '12'),
(22, 19, 3, '21'),
(23, 19, 3, '12'),
(24, 20, 3, '21'),
(25, 20, 3, '12'),
(26, 21, 3, '21'),
(27, 21, 3, '12'),
(28, 22, 3, '21'),
(29, 22, 3, '12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `parametros`
--

CREATE TABLE `parametros` (
  `id_parametro` int(11) NOT NULL,
  `id_tipo` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `usuarios` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `parametros`
--

INSERT INTO `parametros` (`id_parametro`, `id_tipo`, `nombre`, `descripcion`, `usuarios`) VALUES
(4, 1, 'masculino', 'Eres hombre', NULL),
(5, 1, 'Femenino', 'Eres mujer', NULL),
(9, 2, 'Activo', 'esta en uso', NULL),
(10, 2, 'Inactivo', 'No esta en uso', NULL),
(11, 1, '37 tipos de gay', 'eres cacorro pro', NULL),
(13, 2, 'Medio medio', 'ne se pa\'', NULL),
(14, 3, 'casamalla', 'como ervideros', NULL),
(15, 4, 'ml', 'mililitro', NULL),
(16, 5, 'iniciada', 'ergergr', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plantas`
--

CREATE TABLE `plantas` (
  `id` int(11) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `nombre_comun` varchar(255) NOT NULL,
  `nombre_cientifico` varchar(255) DEFAULT NULL,
  `especie` varchar(255) DEFAULT NULL,
  `origen_id` int(11) DEFAULT NULL,
  `protocolo_id` int(11) DEFAULT NULL,
  `metodo_propagacion` varchar(255) DEFAULT NULL,
  `fase_actual` enum('seleccion','establecimiento','multiplicacion','enraizamiento','adaptacion') DEFAULT 'seleccion',
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado_id` int(11) DEFAULT NULL,
  `usuario_registro_id` int(11) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `plantas`
--

INSERT INTO `plantas` (`id`, `codigo`, `nombre_comun`, `nombre_cientifico`, `especie`, `origen_id`, `protocolo_id`, `metodo_propagacion`, `fase_actual`, `fecha_inicio`, `fecha_fin`, `estado_id`, `usuario_registro_id`, `observaciones`, `fecha_creacion`) VALUES
(6, '002', 'piña', 'Annanas', 'ertgh', 14, 1, 'Murashine', 'seleccion', '2025-10-10', NULL, 9, 8, '', '2025-10-10 11:38:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `protocolos`
--

CREATE TABLE `protocolos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `tecnica_utilizada` text DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `protocolos`
--

INSERT INTO `protocolos` (`id`, `nombre`, `tecnica_utilizada`, `fecha_creacion`) VALUES
(1, 'Adanies de Jesús', 'Murazhimi scock', '2025-10-01 20:23:32'),
(2, 'omel', 'uytfrgnun', '2025-10-03 19:35:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `protocolo_formulacion`
--

CREATE TABLE `protocolo_formulacion` (
  `id` int(11) NOT NULL,
  `protocolo_id` int(11) NOT NULL,
  `formulacion_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `protocolo_formulacion`
--

INSERT INTO `protocolo_formulacion` (`id`, `protocolo_id`, `formulacion_id`) VALUES
(1, 2, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reactivos`
--

CREATE TABLE `reactivos` (
  `id` int(11) NOT NULL,
  `nombre_comun` varchar(255) NOT NULL,
  `formula_quimica` varchar(255) DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `unidad_medida` varchar(50) DEFAULT NULL,
  `cantidad_total` int(11) DEFAULT 0,
  `fecha_vencimiento` date DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reactivos`
--

INSERT INTO `reactivos` (`id`, `nombre_comun`, `formula_quimica`, `categoria_id`, `unidad_medida`, `cantidad_total`, `fecha_vencimiento`, `imagen`) VALUES
(3, 'Hipoclorito de sodio', 'C12H22O11', 2, 'g', 5000, '2025-12-11', 'reactivo_68d3d7ac54ead.png'),
(4, 'Sacarosa', 'C12H22O11', 2, 'gl', 2000, '2025-12-19', 'reactivo_68e09216cedd5.png'),
(5, 'Acido sulfurico', 'H2O', 1, 'g', 600, '2025-12-26', 'reactivo_68e0926bf39d0.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_parametro`
--

CREATE TABLE `tipo_parametro` (
  `id_tipo` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_parametro`
--

INSERT INTO `tipo_parametro` (`id_tipo`, `nombre`, `descripcion`) VALUES
(1, 'genero', 'Par?metros de g?nero'),
(2, 'estado', 'Par?metros de estado'),
(3, 'origen', 'Par?metros de origen'),
(4, 'umedida', 'Par?metros de unidad de medida'),
(5, 'estadoRaices', 'Par?metros de estado de ra?ces');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `identidad` varchar(50) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `genero` int(11) NOT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `tipo` enum('admin','aprendiz','pasante') DEFAULT 'admin',
  `tiempo_uso` varchar(255) DEFAULT NULL,
  `ficha_formacion` varchar(255) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp(),
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `identidad`, `nombre`, `genero`, `telefono`, `email`, `password`, `tipo`, `tiempo_uso`, `ficha_formacion`, `fecha_creacion`, `created_at`, `foto`) VALUES
(8, '9230482309483209', 'omel', 5, '2215455633', 'nombre@soy.sena.edu.co', '$2y$10$XXddPwaLG14rEG9E5Gmu6OWE01VmebZP6030.4QtVcQcmAir8poVW', 'admin', NULL, NULL, '2025-10-05 17:30:18', '2025-10-05 12:30:18', '/invitrosoft/img/user/user_8_1760206817.png'),
(9, '123456', 'Adanies de Jesús', 4, '2215455633', 'nombre-dos@soy.sena.edu.co', '$2y$10$V9xCv04JeZY5kh38qcSPt.fyIhema9tpYKmfqqRnyEf7qs2Qj.JJy', 'aprendiz', '2', '2999678', '2025-10-09 20:49:04', '2025-10-09 15:49:04', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `contaminaciones`
--
ALTER TABLE `contaminaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `planta_id` (`planta_id`);

--
-- Indices de la tabla `fases_protocolo`
--
ALTER TABLE `fases_protocolo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `protocolo_id` (`protocolo_id`);

--
-- Indices de la tabla `fase_adaptacion`
--
ALTER TABLE `fase_adaptacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `planta_id` (`planta_id`),
  ADD KEY `medio_cultivo_id` (`medio_cultivo_id`),
  ADD KEY `contaminacion_id` (`contaminacion_id`),
  ADD KEY `estado_id` (`estado_id`),
  ADD KEY `usuario_registro_id` (`usuario_registro_id`);

--
-- Indices de la tabla `fase_adaptacion_elementos`
--
ALTER TABLE `fase_adaptacion_elementos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fase_adaptacion_id` (`fase_adaptacion_id`),
  ADD KEY `reactivo_id` (`reactivo_id`);

--
-- Indices de la tabla `fase_adaptacion_formulaciones`
--
ALTER TABLE `fase_adaptacion_formulaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fase_adaptacion_id` (`fase_adaptacion_id`),
  ADD KEY `formulacion_id` (`formulacion_id`);

--
-- Indices de la tabla `fase_enraizamiento`
--
ALTER TABLE `fase_enraizamiento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `planta_id` (`planta_id`),
  ADD KEY `medio_utilizado_id` (`medio_utilizado_id`),
  ADD KEY `estado_raices_id` (`estado_raices_id`),
  ADD KEY `contaminacion_id` (`contaminacion_id`),
  ADD KEY `estado_id` (`estado_id`),
  ADD KEY `usuario_registro_id` (`usuario_registro_id`);

--
-- Indices de la tabla `fase_establecimiento`
--
ALTER TABLE `fase_establecimiento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `planta_id` (`planta_id`),
  ADD KEY `contaminacion_id` (`contaminacion_id`),
  ADD KEY `usuario_registro_id` (`usuario_registro_id`);

--
-- Indices de la tabla `fase_establecimiento_elementos`
--
ALTER TABLE `fase_establecimiento_elementos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fase_establecimiento_id` (`fase_establecimiento_id`),
  ADD KEY `reactivo_id` (`reactivo_id`);

--
-- Indices de la tabla `fase_multiplicacion`
--
ALTER TABLE `fase_multiplicacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `planta_id` (`planta_id`),
  ADD KEY `contaminacion_id` (`contaminacion_id`),
  ADD KEY `usuario_registro_id` (`usuario_registro_id`);

--
-- Indices de la tabla `fase_multiplicacion_elementos`
--
ALTER TABLE `fase_multiplicacion_elementos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fase_multiplicacion_id` (`fase_multiplicacion_id`),
  ADD KEY `reactivo_id` (`reactivo_id`);

--
-- Indices de la tabla `fase_reactivos`
--
ALTER TABLE `fase_reactivos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fase_id` (`fase_id`),
  ADD KEY `reactivo_id` (`reactivo_id`);

--
-- Indices de la tabla `formulaciones`
--
ALTER TABLE `formulaciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `formulacion_reactivos`
--
ALTER TABLE `formulacion_reactivos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `formulacion_id` (`formulacion_id`),
  ADD KEY `reactivo_id` (`reactivo_id`);

--
-- Indices de la tabla `grupos_formulacion`
--
ALTER TABLE `grupos_formulacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `formulacion_id` (`formulacion_id`);

--
-- Indices de la tabla `grupo_reactivos`
--
ALTER TABLE `grupo_reactivos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `grupo_id` (`grupo_id`),
  ADD KEY `reactivo_id` (`reactivo_id`);

--
-- Indices de la tabla `parametros`
--
ALTER TABLE `parametros`
  ADD PRIMARY KEY (`id_parametro`),
  ADD KEY `idx_parametros_tipo` (`id_tipo`);

--
-- Indices de la tabla `plantas`
--
ALTER TABLE `plantas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `origen_id` (`origen_id`),
  ADD KEY `protocolo_id` (`protocolo_id`),
  ADD KEY `estado_id` (`estado_id`),
  ADD KEY `usuario_registro_id` (`usuario_registro_id`);

--
-- Indices de la tabla `protocolos`
--
ALTER TABLE `protocolos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `protocolo_formulacion`
--
ALTER TABLE `protocolo_formulacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `protocolo_id` (`protocolo_id`),
  ADD KEY `formulacion_id` (`formulacion_id`);

--
-- Indices de la tabla `reactivos`
--
ALTER TABLE `reactivos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `tipo_parametro`
--
ALTER TABLE `tipo_parametro`
  ADD PRIMARY KEY (`id_tipo`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `identidad` (`identidad`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `genero` (`genero`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `contaminaciones`
--
ALTER TABLE `contaminaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `fases_protocolo`
--
ALTER TABLE `fases_protocolo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `fase_adaptacion`
--
ALTER TABLE `fase_adaptacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `fase_adaptacion_elementos`
--
ALTER TABLE `fase_adaptacion_elementos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `fase_adaptacion_formulaciones`
--
ALTER TABLE `fase_adaptacion_formulaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `fase_enraizamiento`
--
ALTER TABLE `fase_enraizamiento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `fase_establecimiento`
--
ALTER TABLE `fase_establecimiento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `fase_establecimiento_elementos`
--
ALTER TABLE `fase_establecimiento_elementos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `fase_multiplicacion`
--
ALTER TABLE `fase_multiplicacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `fase_multiplicacion_elementos`
--
ALTER TABLE `fase_multiplicacion_elementos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `fase_reactivos`
--
ALTER TABLE `fase_reactivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `formulaciones`
--
ALTER TABLE `formulaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `formulacion_reactivos`
--
ALTER TABLE `formulacion_reactivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `grupos_formulacion`
--
ALTER TABLE `grupos_formulacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `grupo_reactivos`
--
ALTER TABLE `grupo_reactivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `parametros`
--
ALTER TABLE `parametros`
  MODIFY `id_parametro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `plantas`
--
ALTER TABLE `plantas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `protocolos`
--
ALTER TABLE `protocolos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `protocolo_formulacion`
--
ALTER TABLE `protocolo_formulacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `reactivos`
--
ALTER TABLE `reactivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `tipo_parametro`
--
ALTER TABLE `tipo_parametro`
  MODIFY `id_tipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `contaminaciones`
--
ALTER TABLE `contaminaciones`
  ADD CONSTRAINT `contaminaciones_ibfk_1` FOREIGN KEY (`planta_id`) REFERENCES `plantas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `fases_protocolo`
--
ALTER TABLE `fases_protocolo`
  ADD CONSTRAINT `fases_protocolo_ibfk_1` FOREIGN KEY (`protocolo_id`) REFERENCES `protocolos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `fase_adaptacion`
--
ALTER TABLE `fase_adaptacion`
  ADD CONSTRAINT `fase_adaptacion_ibfk_1` FOREIGN KEY (`planta_id`) REFERENCES `plantas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fase_adaptacion_ibfk_2` FOREIGN KEY (`medio_cultivo_id`) REFERENCES `parametros` (`id_parametro`),
  ADD CONSTRAINT `fase_adaptacion_ibfk_3` FOREIGN KEY (`contaminacion_id`) REFERENCES `contaminaciones` (`id`),
  ADD CONSTRAINT `fase_adaptacion_ibfk_4` FOREIGN KEY (`estado_id`) REFERENCES `parametros` (`id_parametro`),
  ADD CONSTRAINT `fase_adaptacion_ibfk_5` FOREIGN KEY (`usuario_registro_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `fase_adaptacion_elementos`
--
ALTER TABLE `fase_adaptacion_elementos`
  ADD CONSTRAINT `fase_adaptacion_elementos_ibfk_1` FOREIGN KEY (`fase_adaptacion_id`) REFERENCES `fase_adaptacion` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fase_adaptacion_elementos_ibfk_2` FOREIGN KEY (`reactivo_id`) REFERENCES `reactivos` (`id`);

--
-- Filtros para la tabla `fase_adaptacion_formulaciones`
--
ALTER TABLE `fase_adaptacion_formulaciones`
  ADD CONSTRAINT `fase_adaptacion_formulaciones_ibfk_1` FOREIGN KEY (`fase_adaptacion_id`) REFERENCES `fase_adaptacion` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fase_adaptacion_formulaciones_ibfk_2` FOREIGN KEY (`formulacion_id`) REFERENCES `formulaciones` (`id`);

--
-- Filtros para la tabla `fase_enraizamiento`
--
ALTER TABLE `fase_enraizamiento`
  ADD CONSTRAINT `fase_enraizamiento_ibfk_1` FOREIGN KEY (`planta_id`) REFERENCES `plantas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fase_enraizamiento_ibfk_2` FOREIGN KEY (`medio_utilizado_id`) REFERENCES `parametros` (`id_parametro`),
  ADD CONSTRAINT `fase_enraizamiento_ibfk_3` FOREIGN KEY (`estado_raices_id`) REFERENCES `parametros` (`id_parametro`),
  ADD CONSTRAINT `fase_enraizamiento_ibfk_4` FOREIGN KEY (`contaminacion_id`) REFERENCES `contaminaciones` (`id`),
  ADD CONSTRAINT `fase_enraizamiento_ibfk_5` FOREIGN KEY (`estado_id`) REFERENCES `parametros` (`id_parametro`),
  ADD CONSTRAINT `fase_enraizamiento_ibfk_6` FOREIGN KEY (`usuario_registro_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `fase_establecimiento`
--
ALTER TABLE `fase_establecimiento`
  ADD CONSTRAINT `fase_establecimiento_ibfk_1` FOREIGN KEY (`planta_id`) REFERENCES `plantas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fase_establecimiento_ibfk_2` FOREIGN KEY (`contaminacion_id`) REFERENCES `contaminaciones` (`id`),
  ADD CONSTRAINT `fase_establecimiento_ibfk_3` FOREIGN KEY (`usuario_registro_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `fase_establecimiento_elementos`
--
ALTER TABLE `fase_establecimiento_elementos`
  ADD CONSTRAINT `fase_establecimiento_elementos_ibfk_1` FOREIGN KEY (`fase_establecimiento_id`) REFERENCES `fase_establecimiento` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fase_establecimiento_elementos_ibfk_2` FOREIGN KEY (`reactivo_id`) REFERENCES `reactivos` (`id`);

--
-- Filtros para la tabla `fase_multiplicacion`
--
ALTER TABLE `fase_multiplicacion`
  ADD CONSTRAINT `fase_multiplicacion_ibfk_1` FOREIGN KEY (`planta_id`) REFERENCES `plantas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fase_multiplicacion_ibfk_2` FOREIGN KEY (`contaminacion_id`) REFERENCES `contaminaciones` (`id`),
  ADD CONSTRAINT `fase_multiplicacion_ibfk_3` FOREIGN KEY (`usuario_registro_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `fase_multiplicacion_elementos`
--
ALTER TABLE `fase_multiplicacion_elementos`
  ADD CONSTRAINT `fase_multiplicacion_elementos_ibfk_1` FOREIGN KEY (`fase_multiplicacion_id`) REFERENCES `fase_multiplicacion` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fase_multiplicacion_elementos_ibfk_2` FOREIGN KEY (`reactivo_id`) REFERENCES `reactivos` (`id`);

--
-- Filtros para la tabla `fase_reactivos`
--
ALTER TABLE `fase_reactivos`
  ADD CONSTRAINT `fase_reactivos_ibfk_1` FOREIGN KEY (`fase_id`) REFERENCES `fases_protocolo` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fase_reactivos_ibfk_2` FOREIGN KEY (`reactivo_id`) REFERENCES `reactivos` (`id`);

--
-- Filtros para la tabla `formulacion_reactivos`
--
ALTER TABLE `formulacion_reactivos`
  ADD CONSTRAINT `formulacion_reactivos_ibfk_1` FOREIGN KEY (`formulacion_id`) REFERENCES `formulaciones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `formulacion_reactivos_ibfk_2` FOREIGN KEY (`reactivo_id`) REFERENCES `reactivos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `grupos_formulacion`
--
ALTER TABLE `grupos_formulacion`
  ADD CONSTRAINT `grupos_formulacion_ibfk_1` FOREIGN KEY (`formulacion_id`) REFERENCES `formulaciones` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `grupo_reactivos`
--
ALTER TABLE `grupo_reactivos`
  ADD CONSTRAINT `grupo_reactivos_ibfk_1` FOREIGN KEY (`grupo_id`) REFERENCES `grupos_formulacion` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grupo_reactivos_ibfk_2` FOREIGN KEY (`reactivo_id`) REFERENCES `reactivos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `parametros`
--
ALTER TABLE `parametros`
  ADD CONSTRAINT `parametros_ibfk_1` FOREIGN KEY (`id_tipo`) REFERENCES `tipo_parametro` (`id_tipo`);

--
-- Filtros para la tabla `plantas`
--
ALTER TABLE `plantas`
  ADD CONSTRAINT `plantas_ibfk_1` FOREIGN KEY (`origen_id`) REFERENCES `parametros` (`id_parametro`),
  ADD CONSTRAINT `plantas_ibfk_2` FOREIGN KEY (`protocolo_id`) REFERENCES `protocolos` (`id`),
  ADD CONSTRAINT `plantas_ibfk_3` FOREIGN KEY (`estado_id`) REFERENCES `parametros` (`id_parametro`),
  ADD CONSTRAINT `plantas_ibfk_4` FOREIGN KEY (`usuario_registro_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `protocolo_formulacion`
--
ALTER TABLE `protocolo_formulacion`
  ADD CONSTRAINT `protocolo_formulacion_ibfk_1` FOREIGN KEY (`protocolo_id`) REFERENCES `protocolos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `protocolo_formulacion_ibfk_2` FOREIGN KEY (`formulacion_id`) REFERENCES `formulaciones` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reactivos`
--
ALTER TABLE `reactivos`
  ADD CONSTRAINT `reactivos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`genero`) REFERENCES `parametros` (`id_parametro`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
