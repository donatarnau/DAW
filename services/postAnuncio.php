<?php
// post.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: ./index.php?error=acceso_denegado");
    exit;
}

// --- 2. CONEXIÓN A LA BD ---
$config = parse_ini_file('./config.ini');
if (!$config) die("Error al leer config.ini");
@$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
if ($mysqli->connect_errno) die("Error de conexión a la BD: " . $mysqli->connect_error);

$userlog = $_SESSION['user_id'];


$sql = "INSERT INTO ANUNCIOS (
            TAnuncio,
            TVivienda,
            FPrincipal,
            Alternativo,
            Titulo,
            Precio,
            Texto,
            Ciudad,
            Pais,
            Superficie,
            NHabitaciones,
            NBanyos,
            Planta,
            Anyo,
            Usuario
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

$stmt = $mysqli->prepare($sql);

$fprincipal = "sinfoto.jpg";
$alternaivo = "no";

$stmt->bind_param(
    "iisssdssddiiiii", 
    $tipoAnuncio,    // i - smallint
    $tipoVivienda,   // i - smallint
    $fprincipal,     // s - varchar
    $alternaivo,    // s - varchar
    $nombre,         // s - varchar (Titulo)
    $precio,         // d - decimal(10,2)
    $descripcion,// s - text
    $ciudad,         // s - varchar
    $pais,           // i - int
    $superficie,     // d - decimal(10,2)
    $habitaciones,   // i - int
    $banyos,         // i - int
    $planta,         // i - int
    $anyo,           // i - int
    $userlog       // i - int
);

if ($stmt->execute()) {
    $newId = $mysqli->insert_id;
    $goodPost = true;
} else {
    $goodPost = false;
}

$stmt->close();
$mysqli->close();

?>
