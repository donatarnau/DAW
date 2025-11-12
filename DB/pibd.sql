-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 12, 2025 at 11:12 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pibd`
--

-- --------------------------------------------------------

--
-- Table structure for table `anuncios`
--

CREATE TABLE `anuncios` (
  `IdAnuncio` int(11) NOT NULL,
  `TAnuncio` smallint(6) NOT NULL,
  `TVivienda` smallint(6) NOT NULL,
  `FPrincipal` varchar(255) DEFAULT NULL,
  `Alternativo` varchar(255) NOT NULL,
  `Titulo` varchar(255) NOT NULL,
  `Precio` decimal(10,2) NOT NULL,
  `Texto` text NOT NULL,
  `Ciudad` varchar(255) NOT NULL,
  `Pais` int(11) NOT NULL,
  `Superficie` decimal(10,2) NOT NULL,
  `NHabitaciones` int(11) NOT NULL,
  `NBanyos` int(11) NOT NULL,
  `Planta` int(11) NOT NULL,
  `Anyo` int(11) NOT NULL,
  `FRegistro` timestamp NOT NULL DEFAULT current_timestamp(),
  `Usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `anuncios`
--

INSERT INTO `anuncios` (`IdAnuncio`, `TAnuncio`, `TVivienda`, `FPrincipal`, `Alternativo`, `Titulo`, `Precio`, `Texto`, `Ciudad`, `Pais`, `Superficie`, `NHabitaciones`, `NBanyos`, `Planta`, `Anyo`, `FRegistro`, `Usuario`) VALUES
(1, 1, 1, 'anuncio1.jpg', 'Foto principal del piso en el centro de Alicante', 'Piso céntrico en Alicante', 150000.00, 'Estupendo piso reformado en el centro de Alicante. Cerca de todos los servicios.', 'Alicante', 1, 95.50, 3, 2, 4, 1985, '2025-11-11 10:42:10', 2),
(2, 2, 4, 'anuncio2.jpg', 'Fachada del local comercial en calle principal de Madrid', 'Local comercial zona Goya', 1200.00, 'Local a pie de calle en zona muy comercial. Ideal para cualquier negocio.', 'Madrid', 1, 120.00, 1, 1, 0, 2000, '2025-11-11 10:42:10', 3),
(3, 1, 2, 'anuncio3.jpg', 'Vista exterior del chalet con jardín y piscina', 'Chalet de lujo con piscina', 450000.00, 'Espectacular chalet independiente en zona residencial tranquila. Jardín privado y piscina.', 'Valencia', 1, 250.00, 5, 3, 2, 2015, '2025-11-11 10:42:10', 2),
(4, 2, 1, 'anuncio4.jpg', 'Salón luminoso del piso en alquiler en Barcelona', 'Ático luminoso en Gràcia', 950.00, 'Acogedor ático con terraza en el barrio de Gràcia. Ideal parejas.', 'Barcelona', 1, 65.00, 2, 1, 6, 1960, '2025-11-11 10:42:10', 3),
(5, 1, 5, 'anuncio5.jpg', 'Plaza de garaje amplia y de fácil acceso', 'Plaza de garaje centro', 25000.00, 'Plaza de garaje amplia para coche grande y moto. Fácil maniobra.', 'Alicante', 1, 15.00, 0, 0, -2, 1995, '2025-11-11 10:42:10', 2),
(6, 2, 6, 'anuncio6.jpg', 'Oficina diáfana con grandes ventanales', 'Oficina moderna en Castellana', 2500.00, 'Oficina diáfana recién reformada en edificio representativo. Vistas espectaculares.', 'Madrid', 1, 200.00, 4, 2, 10, 2010, '2025-11-11 10:42:10', 1),
(7, 1, 1, 'anuncio7.jpg', 'Apartamento con vistas a la Torre Eiffel', 'Apartamento romántico en París', 550000.00, 'Precioso apartamento a reformar con vistas laterales a la Torre Eiffel.', 'París', 2, 55.00, 1, 1, 3, 1900, '2025-11-11 10:42:10', 4);

-- --------------------------------------------------------

--
-- Table structure for table `estilos`
--

CREATE TABLE `estilos` (
  `IdEstilo` int(11) NOT NULL,
  `Nombre` varchar(255) NOT NULL,
  `Descripcion` text DEFAULT NULL,
  `Fichero` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `estilos`
--

INSERT INTO `estilos` (`IdEstilo`, `Nombre`, `Descripcion`, `Fichero`) VALUES
(1, 'Estándar', 'Estilo por defecto de la aplicación', './estilos/style.css'),
(2, 'Modo noche', 'Estilo oscuro para entornos con poca luz', './estilos/dark.css'),
(3, 'Alto contraste', 'Estilo con colores de alto contraste', './estilos/contrast.css'),
(4, 'Letra grande', 'Estilo con tamaño de fuente aumentado', './estilos/big.css'),
(5, 'Contraste + Letra grande', 'Combinación de alto contraste y letra grande', './estilos/contrast-big.css');

-- --------------------------------------------------------

--
-- Table structure for table `fotos`
--

CREATE TABLE `fotos` (
  `IdFoto` int(11) NOT NULL,
  `Titulo` varchar(255) DEFAULT NULL,
  `Foto` varchar(255) NOT NULL,
  `Alternativo` varchar(255) NOT NULL,
  `Anuncio` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fotos`
--

INSERT INTO `fotos` (`IdFoto`, `Titulo`, `Foto`, `Alternativo`, `Anuncio`) VALUES
(1, 'Cocina reformada', 'a1-1.jpg', 'Cocina moderna totalmente equipada con electrodomésticos', 1),
(2, 'Baño principal', 'a1-2.jpg', 'Baño completo con plato de ducha y mampara', 1),
(3, 'Jardín', 'a1-3.jpg', 'Amplio jardín con césped natural y zona de barbacoa', 1),
(4, 'Piscina', 'a3-1.jpg', 'Piscina privada de 8x4 metros con iluminación nocturna', 3),
(5, 'Jardín', 'a3-2.jpg', 'Amplio jardín con césped natural y zona de barbacoa', 3);

-- --------------------------------------------------------

--
-- Table structure for table `mensajes`
--

CREATE TABLE `mensajes` (
  `IdMensaje` int(11) NOT NULL,
  `TMensaje` smallint(6) NOT NULL,
  `Texto` varchar(4000) NOT NULL,
  `Anuncio` int(11) NOT NULL,
  `UsuOrigen` int(11) NOT NULL,
  `UsuDestino` int(11) NOT NULL,
  `FRegistro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mensajes`
--

INSERT INTO `mensajes` (`IdMensaje`, `TMensaje`, `Texto`, `Anuncio`, `UsuOrigen`, `UsuDestino`, `FRegistro`) VALUES
(1, 1, 'Hola, ¿sigue disponible el piso en Alicante? Gracias.', 1, 3, 2, '2025-11-11 10:42:10'),
(2, 2, 'Me gustaría concertar una visita para el local de Madrid la próxima semana.', 2, 2, 3, '2025-11-11 10:42:10'),
(3, 3, 'Le ofrezco 140.000 por el piso de Alicante.', 1, 5, 2, '2025-11-11 10:42:10'),
(4, 1, '¿El ático en Gràcia admite mascotas?', 4, 6, 3, '2025-11-11 10:42:10');

-- --------------------------------------------------------

--
-- Table structure for table `paises`
--

CREATE TABLE `paises` (
  `IdPais` int(11) NOT NULL,
  `NomPais` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `paises`
--

INSERT INTO `paises` (`IdPais`, `NomPais`) VALUES
(1, 'España'),
(2, 'Francia'),
(3, 'Portugal'),
(4, 'Italia'),
(5, 'Alemania');

-- --------------------------------------------------------

--
-- Table structure for table `solicitudes`
--

CREATE TABLE `solicitudes` (
  `IdSolicitud` int(11) NOT NULL,
  `Anuncio` int(11) NOT NULL,
  `Texto` varchar(4000) NOT NULL,
  `Nombre` varchar(200) NOT NULL,
  `Email` varchar(254) NOT NULL,
  `Direccion` text DEFAULT NULL,
  `Telefono` varchar(20) DEFAULT NULL,
  `Color` varchar(50) DEFAULT NULL,
  `Copias` int(11) DEFAULT NULL,
  `Resolucion` int(11) DEFAULT NULL,
  `Fecha` date DEFAULT NULL,
  `IColor` tinyint(1) DEFAULT NULL,
  `IPrecio` tinyint(1) DEFAULT NULL,
  `FRegistro` timestamp NOT NULL DEFAULT current_timestamp(),
  `Coste` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tiposanuncios`
--

CREATE TABLE `tiposanuncios` (
  `IdTAnuncio` smallint(6) NOT NULL,
  `NomTAnuncio` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tiposanuncios`
--

INSERT INTO `tiposanuncios` (`IdTAnuncio`, `NomTAnuncio`) VALUES
(1, 'Venta'),
(2, 'Alquiler');

-- --------------------------------------------------------

--
-- Table structure for table `tiposmensajes`
--

CREATE TABLE `tiposmensajes` (
  `IdTMensaje` smallint(6) NOT NULL,
  `NomTMensaje` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tiposmensajes`
--

INSERT INTO `tiposmensajes` (`IdTMensaje`, `NomTMensaje`) VALUES
(1, 'Solicitar Información'),
(2, 'Concertar Visita'),
(3, 'Hacer Oferta'),
(4, 'Otras Consultas');

-- --------------------------------------------------------

--
-- Table structure for table `tiposviviendas`
--

CREATE TABLE `tiposviviendas` (
  `IdTVivienda` smallint(6) NOT NULL,
  `NomTVivienda` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tiposviviendas`
--

INSERT INTO `tiposviviendas` (`IdTVivienda`, `NomTVivienda`) VALUES
(1, 'Piso'),
(2, 'Chalet'),
(3, 'Adosado'),
(4, 'Local Comercial'),
(5, 'Garaje'),
(6, 'Oficina');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `IdUsuario` int(11) NOT NULL,
  `NomUsuario` varchar(15) NOT NULL,
  `Clave` varchar(15) NOT NULL,
  `Email` varchar(254) NOT NULL,
  `Sexo` smallint(6) NOT NULL,
  `FNacimiento` date NOT NULL,
  `Ciudad` varchar(255) NOT NULL,
  `Pais` int(11) NOT NULL,
  `Foto` varchar(255) DEFAULT NULL,
  `FRegistro` timestamp NOT NULL DEFAULT current_timestamp(),
  `Estilo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`IdUsuario`, `NomUsuario`, `Clave`, `Email`, `Sexo`, `FNacimiento`, `Ciudad`, `Pais`, `Foto`, `FRegistro`, `Estilo`) VALUES
(1, 'admin', 'admin123', 'admin@pibd.com', 1, '1980-01-01', 'Alicante', 1, 'user1.jpg', '2025-11-11 10:42:09', 1),
(2, 'pepe_lopez', 'pepepass', 'pepe@email.com', 1, '1990-05-15', 'Madrid', 1, 'user2.jpg', '2025-11-11 10:42:09', 1),
(3, 'maria_garcia', 'mariapass', 'maria@email.com', 0, '1985-11-20', 'Valencia', 1, 'user3.jpg', '2025-11-11 10:42:09', 2),
(4, 'jean_pierre', 'jeanpass', 'jean@email.fr', 1, '1992-03-10', 'Paris', 2, NULL, '2025-11-11 10:42:09', 3),
(5, 'asier', 'pass123', 'asier@email.com', 1, '2000-01-01', 'Barcelona', 1, NULL, '2025-11-11 10:42:09', 4),
(6, 'arnau', 'daw2025', 'arnau@email.com', 1, '2001-02-02', 'Girona', 1, NULL, '2025-11-11 10:42:09', 5);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `anuncios`
--
ALTER TABLE `anuncios`
  ADD PRIMARY KEY (`IdAnuncio`),
  ADD KEY `TAnuncio` (`TAnuncio`),
  ADD KEY `TVivienda` (`TVivienda`),
  ADD KEY `Pais` (`Pais`),
  ADD KEY `Usuario` (`Usuario`);

--
-- Indexes for table `estilos`
--
ALTER TABLE `estilos`
  ADD PRIMARY KEY (`IdEstilo`);

--
-- Indexes for table `fotos`
--
ALTER TABLE `fotos`
  ADD PRIMARY KEY (`IdFoto`),
  ADD KEY `Anuncio` (`Anuncio`);

--
-- Indexes for table `mensajes`
--
ALTER TABLE `mensajes`
  ADD PRIMARY KEY (`IdMensaje`),
  ADD KEY `TMensaje` (`TMensaje`),
  ADD KEY `Anuncio` (`Anuncio`),
  ADD KEY `UsuOrigen` (`UsuOrigen`),
  ADD KEY `UsuDestino` (`UsuDestino`);

--
-- Indexes for table `paises`
--
ALTER TABLE `paises`
  ADD PRIMARY KEY (`IdPais`);

--
-- Indexes for table `solicitudes`
--
ALTER TABLE `solicitudes`
  ADD PRIMARY KEY (`IdSolicitud`),
  ADD KEY `Anuncio` (`Anuncio`);

--
-- Indexes for table `tiposanuncios`
--
ALTER TABLE `tiposanuncios`
  ADD PRIMARY KEY (`IdTAnuncio`);

--
-- Indexes for table `tiposmensajes`
--
ALTER TABLE `tiposmensajes`
  ADD PRIMARY KEY (`IdTMensaje`);

--
-- Indexes for table `tiposviviendas`
--
ALTER TABLE `tiposviviendas`
  ADD PRIMARY KEY (`IdTVivienda`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`IdUsuario`),
  ADD UNIQUE KEY `NomUsuario` (`NomUsuario`),
  ADD KEY `Pais` (`Pais`),
  ADD KEY `Estilo` (`Estilo`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `anuncios`
--
ALTER TABLE `anuncios`
  MODIFY `IdAnuncio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `estilos`
--
ALTER TABLE `estilos`
  MODIFY `IdEstilo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `fotos`
--
ALTER TABLE `fotos`
  MODIFY `IdFoto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `mensajes`
--
ALTER TABLE `mensajes`
  MODIFY `IdMensaje` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `paises`
--
ALTER TABLE `paises`
  MODIFY `IdPais` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `solicitudes`
--
ALTER TABLE `solicitudes`
  MODIFY `IdSolicitud` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tiposanuncios`
--
ALTER TABLE `tiposanuncios`
  MODIFY `IdTAnuncio` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tiposmensajes`
--
ALTER TABLE `tiposmensajes`
  MODIFY `IdTMensaje` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tiposviviendas`
--
ALTER TABLE `tiposviviendas`
  MODIFY `IdTVivienda` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `IdUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `anuncios`
--
ALTER TABLE `anuncios`
  ADD CONSTRAINT `anuncios_ibfk_1` FOREIGN KEY (`TAnuncio`) REFERENCES `tiposanuncios` (`IdTAnuncio`),
  ADD CONSTRAINT `anuncios_ibfk_2` FOREIGN KEY (`TVivienda`) REFERENCES `tiposviviendas` (`IdTVivienda`),
  ADD CONSTRAINT `anuncios_ibfk_3` FOREIGN KEY (`Pais`) REFERENCES `paises` (`IdPais`),
  ADD CONSTRAINT `anuncios_ibfk_4` FOREIGN KEY (`Usuario`) REFERENCES `usuarios` (`IdUsuario`) ON DELETE CASCADE;

--
-- Constraints for table `fotos`
--
ALTER TABLE `fotos`
  ADD CONSTRAINT `fotos_ibfk_1` FOREIGN KEY (`Anuncio`) REFERENCES `anuncios` (`IdAnuncio`) ON DELETE CASCADE;

--
-- Constraints for table `mensajes`
--
ALTER TABLE `mensajes`
  ADD CONSTRAINT `mensajes_ibfk_1` FOREIGN KEY (`TMensaje`) REFERENCES `tiposmensajes` (`IdTMensaje`),
  ADD CONSTRAINT `mensajes_ibfk_2` FOREIGN KEY (`Anuncio`) REFERENCES `anuncios` (`IdAnuncio`) ON DELETE CASCADE,
  ADD CONSTRAINT `mensajes_ibfk_3` FOREIGN KEY (`UsuOrigen`) REFERENCES `usuarios` (`IdUsuario`),
  ADD CONSTRAINT `mensajes_ibfk_4` FOREIGN KEY (`UsuDestino`) REFERENCES `usuarios` (`IdUsuario`);

--
-- Constraints for table `solicitudes`
--
ALTER TABLE `solicitudes`
  ADD CONSTRAINT `solicitudes_ibfk_1` FOREIGN KEY (`Anuncio`) REFERENCES `anuncios` (`IdAnuncio`) ON DELETE CASCADE;

--
-- Constraints for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`Pais`) REFERENCES `paises` (`IdPais`),
  ADD CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`Estilo`) REFERENCES `estilos` (`IdEstilo`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
