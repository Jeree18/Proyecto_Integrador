-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 17-11-2026 a las 21:30:56
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
-- Base de datos: `cursolandia`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos`
--

CREATE TABLE `cursos` (
  `ID` int(10) UNSIGNED NOT NULL,
  `Titulo` varchar(100) NOT NULL,
  `Descripcion` text NOT NULL,
  `Costo` decimal(10,2) DEFAULT 0.00,
  `Cupo` int(10) UNSIGNED DEFAULT 0,
  `Fecha_Inicio` date NOT NULL,
  `Fecha_Fin` date NOT NULL,
  `ID_Creador` int(10) UNSIGNED NOT NULL,
  `Publico` enum('si','no') NOT NULL DEFAULT 'si',
  `Imagen` varchar(255) DEFAULT 'Imagenes/imagen1.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `curso_etiqueta`
--

CREATE TABLE `curso_etiqueta` (
  `ID_Curso` int(10) UNSIGNED NOT NULL,
  `ID_Etiqueta` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `etiquetas`
--

CREATE TABLE `etiquetas` (
  `Nombre` varchar(50) NOT NULL,
  `ID` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inscripcion`
--

CREATE TABLE `inscripcion` (
  `ID_Usuario` int(10) UNSIGNED NOT NULL,
  `ID_Curso` int(10) UNSIGNED NOT NULL,
  `Estado` enum('activa','cancelada','finalizada') NOT NULL DEFAULT 'activa',
  `Fecha_Inscripcion` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materiales`
--

CREATE TABLE `materiales` (
  `ID` int(10) UNSIGNED NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Tipo` varchar(30) NOT NULL,
  `Ruta` varchar(255) NOT NULL,
  `ID_Curso` int(10) UNSIGNED NOT NULL,
  `Tamaño` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `ID` int(10) UNSIGNED NOT NULL,
  `Texto` text NOT NULL,
  `ID_usuario` int(10) UNSIGNED NOT NULL,
  `ID_pregunta` int(10) UNSIGNED DEFAULT NULL,
  `ID_respuesta` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas`
--

CREATE TABLE `preguntas` (
  `ID_pregunta` int(11) UNSIGNED NOT NULL,
  `Titulo` varchar(150) NOT NULL,
  `Texto` text NOT NULL,
  `Estado` enum('abierta','respondida','cerrada') NOT NULL DEFAULT 'abierta',
  `ID_curso` int(11) UNSIGNED NOT NULL,
  `ID_usuario` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuestas`
--

CREATE TABLE `respuestas` (
  `ID` int(10) UNSIGNED NOT NULL,
  `Texto` text NOT NULL,
  `Estado` enum('visible','oculta') NOT NULL DEFAULT 'visible',
  `ID_pregunta` int(10) UNSIGNED NOT NULL,
  `ID_usuario` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudpro`
--

CREATE TABLE `solicitudpro` (
  `ID_solicitud` int(10) UNSIGNED NOT NULL,
  `comprobante` varchar(255) NOT NULL,
  `Fecha_emision` date NOT NULL,
  `Fecha_vencimiento` date NOT NULL,
  `Estado` enum('Pendiente','Aprobada','Rechazada') NOT NULL DEFAULT 'Pendiente',
  `ID_usuario` int(10) UNSIGNED NOT NULL,
  `ID_admin` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `ID` int(11) UNSIGNED NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Apellido` varchar(50) NOT NULL,
  `Contraseña` varchar(255) NOT NULL,
  `Nacimiento` date NOT NULL,
  `Mail` varchar(100) NOT NULL,
  `Telefono` varchar(20) DEFAULT NULL,
  `Foto` varchar(255) DEFAULT NULL,
  `DNI` varchar(10) NOT NULL,
  `Rol` enum('usuario','administrador') NOT NULL DEFAULT 'usuario',
  `Antecedentes` text DEFAULT NULL,
  `Intereses` text DEFAULT NULL,
  `Plan` enum('gratuito','pro') NOT NULL DEFAULT 'gratuito'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `curso_etiqueta`
--
ALTER TABLE `curso_etiqueta`
  ADD PRIMARY KEY (`ID_Curso`,`ID_Etiqueta`),
  ADD KEY `fk_etiquetas_cursos` (`ID_Etiqueta`);

--
-- Indices de la tabla `etiquetas`
--
ALTER TABLE `etiquetas`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Nombre` (`Nombre`);

--
-- Indices de la tabla `inscripcion`
--
ALTER TABLE `inscripcion`
  ADD PRIMARY KEY (`ID_Usuario`,`ID_Curso`),
  ADD KEY `fk_curso_inscripcion` (`ID_Curso`);

--
-- Indices de la tabla `materiales`
--
ALTER TABLE `materiales`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `idx_materiales_id_curso` (`ID_Curso`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `fk_usuario_notificacion` (`ID_usuario`),
  ADD KEY `fk_pregunta_notificacion` (`ID_pregunta`),
  ADD KEY `fk_respuesta_notificacion` (`ID_respuesta`);

--
-- Indices de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  ADD PRIMARY KEY (`ID_pregunta`),
  ADD KEY `fk_cursos_preguntas` (`ID_curso`),
  ADD KEY `fk_usuarios_pregunta` (`ID_usuario`);

--
-- Indices de la tabla `respuestas`
--
ALTER TABLE `respuestas`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `fk_pregunta_respuesta` (`ID_pregunta`),
  ADD KEY `fk_usuarios_respuestas` (`ID_usuario`);

--
-- Indices de la tabla `solicitudpro`
--
ALTER TABLE `solicitudpro`
  ADD PRIMARY KEY (`ID_solicitud`),
  ADD KEY `fk_usuarios_solicitud` (`ID_usuario`),
  ADD KEY `fk_administrador_solicitud` (`ID_admin`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `DNI` (`DNI`),
  ADD UNIQUE KEY `Mail` (`Mail`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cursos`
--
ALTER TABLE `cursos`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `etiquetas`
--
ALTER TABLE `etiquetas`
  MODIFY `ID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `materiales`
--
ALTER TABLE `materiales`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  MODIFY `ID_pregunta` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `respuestas`
--
ALTER TABLE `respuestas`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `solicitudpro`
--
ALTER TABLE `solicitudpro`
  MODIFY `ID_solicitud` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `ID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `curso_etiqueta`
--
ALTER TABLE `curso_etiqueta`
  ADD CONSTRAINT `fk_cursos_etiquetas` FOREIGN KEY (`ID_Curso`) REFERENCES `cursos` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_etiquetas_cursos` FOREIGN KEY (`ID_Etiqueta`) REFERENCES `etiquetas` (`ID`);

--
-- Filtros para la tabla `inscripcion`
--
ALTER TABLE `inscripcion`
  ADD CONSTRAINT `fk_curso_inscripcion` FOREIGN KEY (`ID_Curso`) REFERENCES `cursos` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_usuario_inscripcion` FOREIGN KEY (`ID_Usuario`) REFERENCES `usuarios` (`ID`) ON DELETE CASCADE;

--
-- Filtros para la tabla `materiales`
--
ALTER TABLE `materiales`
  ADD CONSTRAINT `fk_curso_materiales` FOREIGN KEY (`ID_Curso`) REFERENCES `cursos` (`ID`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `fk_pregunta_notificacion` FOREIGN KEY (`ID_pregunta`) REFERENCES `preguntas` (`ID_pregunta`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_respuesta_notificacion` FOREIGN KEY (`ID_respuesta`) REFERENCES `respuestas` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_usuario_notificacion` FOREIGN KEY (`ID_usuario`) REFERENCES `usuarios` (`ID`) ON DELETE CASCADE;

--
-- Filtros para la tabla `preguntas`
--
ALTER TABLE `preguntas`
  ADD CONSTRAINT `fk_cursos_preguntas` FOREIGN KEY (`ID_curso`) REFERENCES `cursos` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_usuarios_pregunta` FOREIGN KEY (`ID_usuario`) REFERENCES `usuarios` (`ID`);

--
-- Filtros para la tabla `respuestas`
--
ALTER TABLE `respuestas`
  ADD CONSTRAINT `fk_pregunta_respuesta` FOREIGN KEY (`ID_pregunta`) REFERENCES `preguntas` (`ID_pregunta`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_usuarios_respuestas` FOREIGN KEY (`ID_usuario`) REFERENCES `usuarios` (`ID`);

--
-- Filtros para la tabla `solicitudpro`
--
ALTER TABLE `solicitudpro`
  ADD CONSTRAINT `fk_administrador_solicitud` FOREIGN KEY (`ID_admin`) REFERENCES `usuarios` (`ID`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_usuarios_solicitud` FOREIGN KEY (`ID_usuario`) REFERENCES `usuarios` (`ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
