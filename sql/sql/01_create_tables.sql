-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-11-2025 a las 13:19:24
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
(2, 'Liquido', 'Descripción de la categoría 2'),
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

--
-- Volcado de datos para la tabla `contaminaciones`
--

INSERT INTO `contaminaciones` (`id`, `planta_id`, `fase_tipo`, `fase_id`, `tipo`, `cantidad`, `motivo`, `descripcion`, `fecha_contaminacion`, `fecha_registro`) VALUES
(6, 3, 'establecimiento', 9, 'endogena', 3, 'gmhgm', NULL, '2025-11-10', '2025-11-10 16:33:12');

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

--
-- Volcado de datos para la tabla `fase_adaptacion`
--

INSERT INTO `fase_adaptacion` (`id`, `planta_id`, `fecha_inicio`, `fecha_finalizacion`, `condiciones_adaptacion`, `medio_cultivo_id`, `resultado_adaptacion`, `contaminacion_id`, `estado_id`, `usuario_registro_id`, `observaciones`, `fecha_creacion`) VALUES
(5, 3, '2025-11-10', NULL, 'ghtyjtrhhtrh', 14, 'exitosa', NULL, 9, 10, '', '2025-11-10 14:05:16'),
(6, 2, '2025-11-10', NULL, '', 14, '', NULL, 9, 10, '', '2025-11-10 22:11:05'),
(7, 1, '2025-11-11', NULL, 'fghjklñ{\r\n', 14, 'murqashimi', NULL, 9, 10, '', '2025-11-11 11:53:57');

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

--
-- Volcado de datos para la tabla `fase_adaptacion_elementos`
--

INSERT INTO `fase_adaptacion_elementos` (`id`, `fase_adaptacion_id`, `reactivo_id`, `cantidad`) VALUES
(3, 6, 1, '1000');

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

--
-- Volcado de datos para la tabla `fase_enraizamiento`
--

INSERT INTO `fase_enraizamiento` (`id`, `planta_id`, `fecha_inicio`, `fecha_finalizacion`, `medio_utilizado_id`, `estado_raices_id`, `contaminacion_id`, `estado_id`, `usuario_registro_id`, `observaciones`, `fecha_creacion`) VALUES
(5, 3, '2025-11-10', NULL, 14, 18, NULL, 9, 10, '', '2025-11-10 14:01:15'),
(6, 2, '2025-11-10', NULL, 14, 16, NULL, 9, 10, '', '2025-11-10 16:31:04'),
(7, 1, '2025-11-10', NULL, 14, 18, NULL, 9, 10, '', '2025-11-10 22:17:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fase_enraizamiento_elementos`
--

CREATE TABLE `fase_enraizamiento_elementos` (
  `id` int(11) NOT NULL,
  `fase_enraizamiento_id` int(11) NOT NULL,
  `reactivo_id` int(11) NOT NULL,
  `cantidad` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `fase_enraizamiento_elementos`
--

INSERT INTO `fase_enraizamiento_elementos` (`id`, `fase_enraizamiento_id`, `reactivo_id`, `cantidad`) VALUES
(6, 7, 11, '1000');

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

--
-- Volcado de datos para la tabla `fase_establecimiento`
--

INSERT INTO `fase_establecimiento` (`id`, `planta_id`, `fecha_inicio`, `fecha_finalizacion`, `metodo_propagacion`, `contaminacion_id`, `usuario_registro_id`, `observaciones`, `fecha_creacion`) VALUES
(9, 3, '2025-11-10', NULL, 'Murashine', NULL, 10, '', '2025-11-10 13:19:23'),
(10, 2, '2025-11-10', NULL, 'Murashine', NULL, 10, 'htrhrth', '2025-11-10 13:57:25'),
(11, 1, '2025-11-10', NULL, 'muracioni stock', NULL, 10, '', '2025-11-10 16:31:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fase_establecimiento_elementos`
--
-- Error leyendo la estructura de la tabla invitrosoft.fase_establecimiento_elementos: #1932 - Table &#039;invitrosoft.fase_establecimiento_elementos&#039; doesn&#039;t exist in engine
-- Error leyendo datos de la tabla invitrosoft.fase_establecimiento_elementos: #1064 - Algo está equivocado en su sintax cerca &#039;FROM `invitrosoft`.`fase_establecimiento_elementos`&#039; en la linea 1

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

--
-- Volcado de datos para la tabla `fase_multiplicacion`
--

INSERT INTO `fase_multiplicacion` (`id`, `planta_id`, `fecha_inicio`, `fecha_finalizacion`, `num_explantes_generados`, `tiempo_estimacion_madurez`, `contaminacion_id`, `usuario_registro_id`, `observaciones`, `fecha_creacion`) VALUES
(7, 3, '2025-11-10', NULL, 12, 12, NULL, 10, '', '2025-11-10 13:55:21'),
(8, 2, '2025-11-10', NULL, 12, 12, NULL, 10, 'fgfgn', '2025-11-10 13:58:22'),
(9, 2, '2025-11-10', NULL, 12, 12, NULL, 10, 'fgfgn', '2025-11-10 13:58:27'),
(10, 1, '2025-11-10', NULL, 12, 23, NULL, 10, '', '2025-11-10 16:32:44');

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

--
-- Volcado de datos para la tabla `fase_multiplicacion_elementos`
--

INSERT INTO `fase_multiplicacion_elementos` (`id`, `fase_multiplicacion_id`, `reactivo_id`, `cantidad`) VALUES
(5, 7, 14, '35');

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
  `nombre_grupo` varchar(255) NOT NULL,
  `volumen` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupo_reactivos`
--

CREATE TABLE `grupo_reactivos` (
  `id` int(11) NOT NULL,
  `grupo_id` int(11) NOT NULL,
  `reactivo_id` int(11) NOT NULL,
  `cantidad` varchar(100) DEFAULT NULL,
  `solucion_madre` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `planta_id` int(11) DEFAULT NULL,
  `modulo` varchar(50) DEFAULT 'sistema',
  `accion` varchar(50) DEFAULT 'notificacion',
  `entidad` varchar(50) DEFAULT NULL,
  `entidad_id` int(11) DEFAULT NULL,
  `tipo` enum('success','warning','error','info') DEFAULT 'info',
  `titulo` varchar(255) NOT NULL,
  `mensaje` text DEFAULT NULL,
  `leida` tinyint(1) DEFAULT 0,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_lectura` datetime DEFAULT NULL,
  `datos_adicionales` text DEFAULT NULL,
  `fase_tipo` varchar(50) DEFAULT NULL COMMENT 'Tipo de fase relacionada',
  `fase_id` int(11) DEFAULT NULL COMMENT 'ID de la fase relacionada',
  `contaminacion_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id`, `usuario_id`, `planta_id`, `modulo`, `accion`, `entidad`, `entidad_id`, `tipo`, `titulo`, `mensaje`, `leida`, `fecha_creacion`, `fecha_lectura`, `datos_adicionales`, `fase_tipo`, `fase_id`, `contaminacion_id`) VALUES
(71, 10, 3, 'sistema', 'notificacion', NULL, NULL, '', 'Cambio de fase', 'La planta #3 cambió a la fase adaptacion', 0, '2025-11-10 09:05:16', NULL, NULL, 'adaptacion', 5, NULL),
(72, 8, NULL, 'reactivos', 'crear', 'reactivo', NULL, 'success', 'Nuevo Reactivo', 'Se ha creado el reactivo: Nitrato de amonio-prueba', 0, '2025-11-10 09:46:41', NULL, NULL, NULL, NULL, NULL),
(73, 10, 2, 'sistema', 'notificacion', NULL, NULL, '', 'Cambio de fase', 'La planta #2 cambió a la fase enraizamiento', 0, '2025-11-10 11:31:04', NULL, NULL, 'enraizamiento', 6, NULL),
(74, 10, 1, 'sistema', 'notificacion', NULL, NULL, '', 'Cambio de fase', 'La planta #1 cambió a la fase establecimiento', 0, '2025-11-10 11:31:41', NULL, NULL, 'establecimiento', 11, NULL),
(75, 10, 1, 'sistema', 'notificacion', NULL, NULL, '', 'Cambio de fase', 'La planta #1 cambió a la fase multiplicacion', 0, '2025-11-10 11:32:44', NULL, NULL, 'multiplicacion', 10, NULL),
(76, 10, 3, 'sistema', 'notificacion', NULL, NULL, '', 'Contaminación registrada', 'Se registró una contaminación (endogena) en la fase establecimiento', 0, '2025-11-10 11:33:12', NULL, NULL, NULL, NULL, 6),
(77, 10, 3, 'sistema', 'notificacion', NULL, NULL, '', 'Contaminación registrada', 'Se registró una contaminación (endogena) en la fase establecimiento', 0, '2025-11-10 11:33:12', NULL, NULL, NULL, NULL, 6),
(78, 10, 4, 'sistema', 'notificacion', NULL, NULL, '', 'Nueva planta creada', 'Se creó la planta piñelita (código 002)', 0, '2025-11-10 13:43:18', NULL, NULL, NULL, NULL, NULL),
(79, 8, NULL, 'reactivos', 'crear', 'reactivo', NULL, 'success', 'Nuevo Reactivo', 'Se ha creado el reactivo: Nitrato de amonio-prueba', 0, '2025-11-10 13:51:45', NULL, NULL, NULL, NULL, NULL),
(80, 8, NULL, 'reactivos', 'crear', 'reactivo', NULL, 'success', 'Nuevo Reactivo', 'Se ha creado el reactivo: Nitrato de amonio-prueba', 0, '2025-11-10 13:52:19', NULL, NULL, NULL, NULL, NULL),
(81, 10, 2, 'sistema', 'notificacion', NULL, NULL, '', 'Cambio de fase', 'La planta #2 cambió a la fase adaptacion', 0, '2025-11-10 17:11:05', NULL, NULL, 'adaptacion', 6, NULL),
(82, 10, 1, 'sistema', 'notificacion', NULL, NULL, '', 'Cambio de fase', 'La planta #1 cambió a la fase enraizamiento', 0, '2025-11-10 17:17:17', NULL, NULL, 'enraizamiento', 7, NULL),
(83, 10, 1, 'sistema', 'notificacion', NULL, NULL, '', 'Cambio de fase', 'La planta #1 cambió a la fase adaptacion', 0, '2025-11-11 06:53:57', NULL, NULL, 'adaptacion', 7, NULL);

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
(9, 2, 'Activo', 'Se está usando', NULL),
(10, 2, 'Inactivo', 'No esta en uso', NULL),
(13, 2, 'Medio medio', 'ne se pa\'', NULL),
(14, 3, 'casamalla', 'como ervideros', NULL),
(15, 4, 'ml', 'mililitro', NULL),
(16, 5, 'iniciada', 'ergergr', NULL),
(17, 5, 'Terminada', 'termino', NULL),
(18, 5, 'en proceso', 'esta en la mitad', NULL);

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
(1, '001', 'piña', 'Annanas', 'Ananas', 14, 2, 'Murashine', 'adaptacion', '2025-11-10', NULL, 9, 10, 'esta bonita', '2025-11-10 13:17:48'),
(2, '011', 'piña', 'Annanas', 'Ananas', 14, 2, 'Murashine', 'adaptacion', '2025-11-10', NULL, 9, 10, 'esta bonita', '2025-11-10 13:18:03'),
(3, '025', 'piña', 'Annanas', 'Ananas', 14, 2, 'Murashine', 'adaptacion', '2025-11-10', NULL, 9, 10, 'esta bonita', '2025-11-10 13:18:35');

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
(1, 'Nitrato de amonio', 'NH₄NO₃', 1, 'g/l', 1000, '2026-12-15', 'reactivo_68f00ff63868f.jpg'),
(2, 'Nitrato de potasio', 'KNO₃', 1, 'g/l', 2000, '2026-12-23', 'reactivo_6910b9c3ea2e2.png'),
(3, 'Fosfato monopotásico', 'KH₂PO₄', 1, 'g/l', 2000, '2026-12-14', NULL),
(4, 'Sulfato de magnesio heptahidratado', 'MgSO₄·7H₂O', 1, 'g/l', 2000, '2026-12-14', NULL),
(5, 'Sulfato de zinc heptahidratado', 'ZnSO₄·7H₂O', 1, 'g/l', 2000, '2026-12-14', NULL),
(6, 'Sulfato de cobre tetra-hidratado', 'CuSO₄·5H₂O', 1, 'g/l', 2000, '2026-12-14', NULL),
(7, 'Sulfato de manganeso tetra-hidratado', 'MnSO₄H₂O', 1, 'g/l', 2000, '2026-12-14', NULL),
(8, 'Ácido bórico', 'H₃BO₃', 1, 'g/l', 2000, '2026-12-14', NULL),
(9, 'Molibdato de sodio dihidratado', 'Na₂MoO₄·2H₂O', 1, 'g/l', 2000, '2026-12-14', NULL),
(10, 'Cloruro de cobalto hexahidratado', 'CoCl₂·6H₂O', 1, 'g/l', 2000, '2026-12-14', NULL),
(11, 'Yoduro de potasio', 'Kl', 1, 'mg/l', 1000, '2026-12-14', NULL),
(12, 'Cloruro de calcio dihidratado', 'CaCl₂·2H₂O', 1, 'g/l', 2000, '2026-12-14', NULL),
(13, 'Etilendiaminotetraacetato de sodio', 'Na₂EDTA', 1, 'mg/l', 2000, '2026-12-14', NULL),
(14, 'Sulfato ferroso heptahidratado', 'FeSO₄·7H₂O', 1, 'mg/l', 2000, '2026-12-14', NULL),
(15, 'Mio-inositol', 'C₆H₁₂O₆', 1, 'mg/l', 2000, '2026-12-14', NULL),
(26, 'Nitrato de amonio-prueba', 'NH₄NO₃', 2, 'g/l', 12000, '2027-10-10', NULL);

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
  `bibliografia` text DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `tipo` enum('admin','aprendiz','pasante') DEFAULT 'admin',
  `tiempo_uso` varchar(255) DEFAULT NULL,
  `ficha_formacion` varchar(255) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp(),
  `foto` varchar(255) DEFAULT NULL,
  `foto_url` varchar(512) DEFAULT NULL,
  `fecha_actualizacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `identidad`, `nombre`, `genero`, `telefono`, `bibliografia`, `email`, `password`, `tipo`, `tiempo_uso`, `ficha_formacion`, `fecha_creacion`, `created_at`, `foto`, `foto_url`, `fecha_actualizacion`) VALUES
(8, '9230482309483209', 'Manuel', 5, '3226908514', 'soy aprendiz sena, chico JAVA', 'nombre@soy.sena.edu.co', '$2y$10$qsMfdZ..HG3sZLm9rgyVTuOjVzt3BSzmWuxM7/tGkyYERA59YUthu', 'admin', NULL, NULL, '2025-10-05 17:30:18', '2025-10-05 12:30:18', 'img/user/user_8_691233de0b1e2.png', 'http://localhost/invitrosoft/img/user/user_8_691233de0b1e2.png', '2025-11-10 18:50:31'),
(10, '1128147059', 'Adanies de Jesús Basilio Ospino', 4, '3226905814', NULL, 'adaniesbasilio@gmail.com', '$2y$10$2D4IvDtd5Sido2Icpdrsa.Jzb5asyizyumXuLd7RKdicCoZ3RvNIu', 'aprendiz', '15', '2999380', '2025-10-21 13:54:18', '2025-10-21 08:54:18', '/invitrosoft/img/user/user_10_1761878138.jpg', NULL, '2025-11-08 14:08:18'),
(11, '123456', 'Emanuel martines', 4, '2215455633', NULL, 'emanuel@gmail.com', '$2y$10$CxfDOtloNEbgjhicUozcm.dSad2Ho6IiQ1aenDeXisRbpe1M9nnCa', 'aprendiz', '15', '2999380', '2025-10-21 14:47:48', '2025-10-21 09:47:48', NULL, NULL, '2025-11-08 14:08:18'),
(12, '12233645', 'Kanner Tapia', 4, '2215455633', NULL, 'kaner@gmail.com', '$2y$10$dDlytflkYRNTHolDKnT.JeHTCl7rpieVRL3IzsNWUDgp.fisDMv5O', 'admin', NULL, NULL, '2025-11-08 18:42:58', '2025-11-08 13:42:58', 'img/user/user_12_6910aaf12fe7c.gif', 'http://localhost/invitrosoft/img/user/user_12_6910aaf12fe7c.gif', '2025-11-09 14:53:37'),
(13, '1223625', 'omel valcazar', 4, '32263599', NULL, 'omel@gmail.com', '$2y$10$K2iguA31I4PLmzvoqJurIORZZzZcB97l9OnWu28ki8UOy8HUBv1gm', 'pasante', '9', NULL, '2025-11-10 22:27:42', '2025-11-10 17:27:42', NULL, NULL, '2025-11-10 22:27:42');

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
  ADD KEY `usuario_registro_id` (`usuario_registro_id`),
  ADD KEY `idx_fase_adaptacion_planta` (`planta_id`);

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
  ADD KEY `usuario_registro_id` (`usuario_registro_id`),
  ADD KEY `idx_fase_enraizamiento_planta` (`planta_id`);

--
-- Indices de la tabla `fase_enraizamiento_elementos`
--
ALTER TABLE `fase_enraizamiento_elementos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fase_enraizamiento_id` (`fase_enraizamiento_id`),
  ADD KEY `reactivo_id` (`reactivo_id`);

--
-- Indices de la tabla `fase_establecimiento`
--
ALTER TABLE `fase_establecimiento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `planta_id` (`planta_id`),
  ADD KEY `contaminacion_id` (`contaminacion_id`),
  ADD KEY `usuario_registro_id` (`usuario_registro_id`),
  ADD KEY `idx_fase_establecimiento_planta` (`planta_id`);

--
-- Indices de la tabla `fase_multiplicacion`
--
ALTER TABLE `fase_multiplicacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `planta_id` (`planta_id`),
  ADD KEY `contaminacion_id` (`contaminacion_id`),
  ADD KEY `usuario_registro_id` (`usuario_registro_id`),
  ADD KEY `idx_fase_multiplicacion_planta` (`planta_id`);

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
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `entidad` (`entidad`,`entidad_id`),
  ADD KEY `leida` (`leida`),
  ADD KEY `fecha_creacion` (`fecha_creacion`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `fases_protocolo`
--
ALTER TABLE `fases_protocolo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `fase_adaptacion`
--
ALTER TABLE `fase_adaptacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `fase_adaptacion_elementos`
--
ALTER TABLE `fase_adaptacion_elementos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `fase_adaptacion_formulaciones`
--
ALTER TABLE `fase_adaptacion_formulaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `fase_enraizamiento`
--
ALTER TABLE `fase_enraizamiento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `fase_enraizamiento_elementos`
--
ALTER TABLE `fase_enraizamiento_elementos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `fase_establecimiento`
--
ALTER TABLE `fase_establecimiento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `fase_multiplicacion`
--
ALTER TABLE `fase_multiplicacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `fase_multiplicacion_elementos`
--
ALTER TABLE `fase_multiplicacion_elementos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `fase_reactivos`
--
ALTER TABLE `fase_reactivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `formulaciones`
--
ALTER TABLE `formulaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `formulacion_reactivos`
--
ALTER TABLE `formulacion_reactivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `grupos_formulacion`
--
ALTER TABLE `grupos_formulacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT de la tabla `grupo_reactivos`
--
ALTER TABLE `grupo_reactivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT de la tabla `parametros`
--
ALTER TABLE `parametros`
  MODIFY `id_parametro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `plantas`
--
ALTER TABLE `plantas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `tipo_parametro`
--
ALTER TABLE `tipo_parametro`
  MODIFY `id_tipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

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
-- Filtros para la tabla `fase_enraizamiento_elementos`
--
ALTER TABLE `fase_enraizamiento_elementos`
  ADD CONSTRAINT `fase_enraizamiento_elementos_ibfk_1` FOREIGN KEY (`fase_enraizamiento_id`) REFERENCES `fase_enraizamiento` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fase_enraizamiento_elementos_ibfk_2` FOREIGN KEY (`reactivo_id`) REFERENCES `reactivos` (`id`);

--
-- Filtros para la tabla `fase_establecimiento`
--
ALTER TABLE `fase_establecimiento`
  ADD CONSTRAINT `fase_establecimiento_ibfk_1` FOREIGN KEY (`planta_id`) REFERENCES `plantas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fase_establecimiento_ibfk_2` FOREIGN KEY (`contaminacion_id`) REFERENCES `contaminaciones` (`id`),
  ADD CONSTRAINT `fase_establecimiento_ibfk_3` FOREIGN KEY (`usuario_registro_id`) REFERENCES `usuarios` (`id`);

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
