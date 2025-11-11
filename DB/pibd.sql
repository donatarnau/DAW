-- Script de creación de la base de datos 'pibd' con DATOS DE PRUEBA

-- Crear la base de datos si no existe y seleccionarla
CREATE DATABASE IF NOT EXISTS pibd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pibd;

-- ==================================================
-- 1. BORRADO DE TABLAS (Orden inverso para evitar errores de FK)
-- ==================================================
DROP TABLE IF EXISTS MENSAJES;
DROP TABLE IF EXISTS SOLICITUDES;
DROP TABLE IF EXISTS FOTOS;
DROP TABLE IF EXISTS ANUNCIOS;
DROP TABLE IF EXISTS USUARIOS;
DROP TABLE IF EXISTS TIPOSMENSAJES;
DROP TABLE IF EXISTS TIPOSVIVIENDAS;
DROP TABLE IF EXISTS TIPOSANUNCIOS;
DROP TABLE IF EXISTS ESTILOS;
DROP TABLE IF EXISTS PAISES;

-- ==================================================
-- 2. CREACIÓN DE TABLAS
-- ==================================================

-- Tabla PAISES
CREATE TABLE PAISES (
    IdPais INT AUTO_INCREMENT PRIMARY KEY,
    NomPais VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- Tabla ESTILOS
CREATE TABLE ESTILOS (
    IdEstilo INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(255) NOT NULL,
    Descripcion TEXT,
    Fichero VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- Tabla TIPOSANUNCIOS
CREATE TABLE TIPOSANUNCIOS (
    IdTAnuncio SMALLINT AUTO_INCREMENT PRIMARY KEY,
    NomTAnuncio VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- Tabla TIPOSVIVIENDAS
CREATE TABLE TIPOSVIVIENDAS (
    IdTVivienda SMALLINT AUTO_INCREMENT PRIMARY KEY,
    NomTVivienda VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- Tabla TIPOSMENSAJES
CREATE TABLE TIPOSMENSAJES (
    IdTMensaje SMALLINT AUTO_INCREMENT PRIMARY KEY,
    NomTMensaje VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- Tabla USUARIOS
CREATE TABLE USUARIOS (
    IdUsuario INT AUTO_INCREMENT PRIMARY KEY,
    NomUsuario VARCHAR(15) UNIQUE NOT NULL,
    Clave VARCHAR(15) NOT NULL,
    Email VARCHAR(254) NOT NULL,
    Sexo SMALLINT NOT NULL, -- 0: Mujer, 1: Hombre, 2: Otro
    FNacimiento DATE NOT NULL,
    Ciudad VARCHAR(255) NOT NULL,
    Pais INT NOT NULL,
    Foto VARCHAR(255),
    FRegistro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Estilo INT NOT NULL,
    FOREIGN KEY (Pais) REFERENCES PAISES(IdPais),
    FOREIGN KEY (Estilo) REFERENCES ESTILOS(IdEstilo)
) ENGINE=InnoDB;

-- Tabla ANUNCIOS
CREATE TABLE ANUNCIOS (
    IdAnuncio INT AUTO_INCREMENT PRIMARY KEY,
    TAnuncio SMALLINT NOT NULL,
    TVivienda SMALLINT NOT NULL,
    FPrincipal VARCHAR(255),
    Alternativo VARCHAR(255) NOT NULL,
    Titulo VARCHAR(255) NOT NULL,
    Precio DECIMAL(10, 2) NOT NULL,
    Texto TEXT NOT NULL,
    Ciudad VARCHAR(255) NOT NULL,
    Pais INT NOT NULL,
    Superficie DECIMAL(10, 2) NOT NULL,
    NHabitaciones INT NOT NULL,
    NBanyos INT NOT NULL,
    Planta INT NOT NULL,
    Anyo INT NOT NULL,
    FRegistro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Usuario INT NOT NULL,
    FOREIGN KEY (TAnuncio) REFERENCES TIPOSANUNCIOS(IdTAnuncio),
    FOREIGN KEY (TVivienda) REFERENCES TIPOSVIVIENDAS(IdTVivienda),
    FOREIGN KEY (Pais) REFERENCES PAISES(IdPais),
    FOREIGN KEY (Usuario) REFERENCES USUARIOS(IdUsuario) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabla FOTOS
CREATE TABLE FOTOS (
    IdFoto INT AUTO_INCREMENT PRIMARY KEY,
    Titulo VARCHAR(255),
    Foto VARCHAR(255) NOT NULL,
    Alternativo VARCHAR(255) NOT NULL,
    Anuncio INT NOT NULL,
    FOREIGN KEY (Anuncio) REFERENCES ANUNCIOS(IdAnuncio) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabla SOLICITUDES
CREATE TABLE SOLICITUDES (
    IdSolicitud INT AUTO_INCREMENT PRIMARY KEY,
    Anuncio INT NOT NULL,
    Texto VARCHAR(4000) NOT NULL,
    Nombre VARCHAR(200) NOT NULL,
    Email VARCHAR(254) NOT NULL,
    Direccion TEXT,
    Telefono VARCHAR(20),
    Color VARCHAR(50),
    Copias INT,
    Resolucion INT,
    Fecha DATE,
    IColor BOOLEAN,
    IPrecio BOOLEAN,
    FRegistro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Coste DECIMAL(10, 2),
    FOREIGN KEY (Anuncio) REFERENCES ANUNCIOS(IdAnuncio) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabla MENSAJES
CREATE TABLE MENSAJES (
    IdMensaje INT AUTO_INCREMENT PRIMARY KEY,
    TMensaje SMALLINT NOT NULL,
    Texto VARCHAR(4000) NOT NULL,
    Anuncio INT NOT NULL,
    UsuOrigen INT NOT NULL,
    UsuDestino INT NOT NULL,
    FRegistro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (TMensaje) REFERENCES TIPOSMENSAJES(IdTMensaje),
    FOREIGN KEY (Anuncio) REFERENCES ANUNCIOS(IdAnuncio) ON DELETE CASCADE,
    FOREIGN KEY (UsuOrigen) REFERENCES USUARIOS(IdUsuario),
    FOREIGN KEY (UsuDestino) REFERENCES USUARIOS(IdUsuario)
) ENGINE=InnoDB;


-- ==================================================
-- 3. INSERCIÓN DE DATOS DE PRUEBA
-- ==================================================

-- PAISES
INSERT INTO PAISES (NomPais) VALUES
('España'),
('Francia'),
('Portugal'),
('Italia'),
('Alemania');

-- ESTILOS
INSERT INTO ESTILOS (Nombre, Descripcion, Fichero) VALUES
('Estándar', 'Estilo por defecto de la aplicación', './estilos/style.css'),
('Modo noche', 'Estilo oscuro para entornos con poca luz', './estilos/dark.css'),
('Alto contraste', 'Estilo con colores de alto contraste', './estilos/contrast.css'),
('Letra grande', 'Estilo con tamaño de fuente aumentado', './estilos/big.css'),
('Contraste + Letra grande', 'Combinación de alto contraste y letra grande', './estilos/contrast-big.css');

-- TIPOSANUNCIOS
INSERT INTO TIPOSANUNCIOS (NomTAnuncio) VALUES
('Venta'),
('Alquiler');

-- TIPOSVIVIENDAS
INSERT INTO TIPOSVIVIENDAS (NomTVivienda) VALUES
('Piso'),
('Chalet'),
('Adosado'),
('Local Comercial'),
('Garaje'),
('Oficina');

-- TIPOSMENSAJES
INSERT INTO TIPOSMENSAJES (NomTMensaje) VALUES
('Solicitar Información'),
('Concertar Visita'),
('Hacer Oferta'),
('Otras Consultas');

-- USUARIOS (Actualizados con Asier y Arnau, y todos los estilos distribuidos)
INSERT INTO USUARIOS (NomUsuario, Clave, Email, Sexo, FNacimiento, Ciudad, Pais, Foto, Estilo) VALUES
('admin', 'admin123', 'admin@pibd.com', 1, '1980-01-01', 'Alicante', 1, 'user1.jpg', 1),      -- Estándar
('pepe_lopez', 'pepepass', 'pepe@email.com', 1, '1990-05-15', 'Madrid', 1, 'user2.jpg', 1),        -- Estándar
('maria_garcia', 'mariapass', 'maria@email.com', 0, '1985-11-20', 'Valencia', 1, 'user3.jpg', 2),  -- Modo noche
('jean_pierre', 'jeanpass', 'jean@email.fr', 1, '1992-03-10', 'Paris', 2, NULL, 3),               -- Alto contraste
('asier', 'pass123', 'asier@email.com', 1, '2000-01-01', 'Barcelona', 1, NULL, 4),               -- Letra grande
('arnau', 'daw2025', 'arnau@email.com', 1, '2001-02-02', 'Girona', 1, NULL, 5);                  -- Contraste + Letra grande

-- ANUNCIOS
INSERT INTO ANUNCIOS (TAnuncio, TVivienda, FPrincipal, Alternativo, Titulo, Precio, Texto, Ciudad, Pais, Superficie, NHabitaciones, NBanyos, Planta, Anyo, Usuario) VALUES
(1, 1, 'anuncio1.jpg', 'Foto principal del piso en el centro de Alicante', 'Piso céntrico en Alicante', 150000.00, 'Estupendo piso reformado en el centro de Alicante. Cerca de todos los servicios.', 'Alicante', 1, 95.50, 3, 2, 4, 1985, 2),
(2, 4, 'anuncio2.jpg', 'Fachada del local comercial en calle principal de Madrid', 'Local comercial zona Goya', 1200.00, 'Local a pie de calle en zona muy comercial. Ideal para cualquier negocio.', 'Madrid', 1, 120.00, 1, 1, 0, 2000, 3),
(1, 2, 'anuncio3.jpg', 'Vista exterior del chalet con jardín y piscina', 'Chalet de lujo con piscina', 450000.00, 'Espectacular chalet independiente en zona residencial tranquila. Jardín privado y piscina.', 'Valencia', 1, 250.00, 5, 3, 2, 2015, 2),
(2, 1, 'anuncio4.jpg', 'Salón luminoso del piso en alquiler en Barcelona', 'Ático luminoso en Gràcia', 950.00, 'Acogedor ático con terraza en el barrio de Gràcia. Ideal parejas.', 'Barcelona', 1, 65.00, 2, 1, 6, 1960, 3),
(1, 5, 'anuncio5.jpg', 'Plaza de garaje amplia y de fácil acceso', 'Plaza de garaje centro', 25000.00, 'Plaza de garaje amplia para coche grande y moto. Fácil maniobra.', 'Alicante', 1, 15.00, 0, 0, -2, 1995, 2),
(2, 6, NULL, 'Oficina diáfana con grandes ventanales', 'Oficina moderna en Castellana', 2500.00, 'Oficina diáfana recién reformada en edificio representativo. Vistas espectaculares.', 'Madrid', 1, 200.00, 4, 2, 10, 2010, 1),
(1, 1, NULL, 'Apartamento con vistas a la Torre Eiffel', 'Apartamento romántico en París', 550000.00, 'Precioso apartamento a reformar con vistas laterales a la Torre Eiffel.', 'París', 2, 55.00, 1, 1, 3, 1900, 4);

-- FOTOS
INSERT INTO FOTOS (Titulo, Foto, Alternativo, Anuncio) VALUES
('Cocina reformada', 'anuncio1_cocina.jpg', 'Cocina moderna totalmente equipada con electrodomésticos', 1),
('Baño principal', 'anuncio1_banyo.jpg', 'Baño completo con plato de ducha y mampara', 1),
('Jardín', 'anuncio3_jardin.jpg', 'Amplio jardín con césped natural y zona de barbacoa', 3),
('Piscina', 'anuncio3_piscina.jpg', 'Piscina privada de 8x4 metros con iluminación nocturna', 3);

-- MENSAJES
INSERT INTO MENSAJES (TMensaje, Texto, Anuncio, UsuOrigen, UsuDestino) VALUES
(1, 'Hola, ¿sigue disponible el piso en Alicante? Gracias.', 1, 3, 2),
(2, 'Me gustaría concertar una visita para el local de Madrid la próxima semana.', 2, 2, 3),
(3, 'Le ofrezco 140.000 por el piso de Alicante.', 1, 5, 2), -- Mensaje de asier
(1, '¿El ático en Gràcia admite mascotas?', 4, 6, 3);      -- Mensaje de arnau