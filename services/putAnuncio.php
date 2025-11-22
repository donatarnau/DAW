<?php
// put.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: ./index.php?error=acceso_denegado");
    exit;
}

$userlog = (int)$_SESSION['user_id'];

// --- 2. CONEXIÓN A LA BD ---
$config = parse_ini_file('./config.ini');
if (!$config) die("Error al leer config.ini");
@$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
if ($mysqli->connect_errno) die("Error de conexión a la BD: " . $mysqli->connect_error);

$sql = "SELECT * FROM ANUNCIOS WHERE IdAnuncio = ? AND Usuario = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ii", $id, $userlog);
$stmt->execute();
$result = $stmt->get_result();
$anuncioActual = $result->fetch_assoc();
$stmt->close();

$campos = [];
$valores = [];
$tipos = '';


if ($tipoAnuncio != $anuncioActual['TAnuncio']) {
    $campos[] = "TAnuncio = ?";
    $valores[] = $tipoAnuncio;
    $tipos .= "i";
}
if ($tipoVivienda != $anuncioActual['TVivienda']) {
    $campos[] = "TVivienda = ?";
    $valores[] = $tipoVivienda;
    $tipos .= "i";
}
if ($nombre != $anuncioActual['Titulo']) {
    $campos[] = "Titulo = ?";
    $valores[] = $nombre;
    $tipos .= "s";
}
if ($descripcion != $anuncioActual['Texto']) {
    $campos[] = "Texto = ?";
    $valores[] = $descripcion;
    $tipos .= "s";
}
if ($ciudad != $anuncioActual['Ciudad']) {
    $campos[] = "Ciudad = ?";
    $valores[] = $ciudad;
    $tipos .= "s";
}
if ($pais != $anuncioActual['Pais']) {
    $campos[] = "Pais = ?";
    $valores[] = $pais;
    $tipos .= "i";
}
if ($precio != $anuncioActual['Precio']) {
    $campos[] = "Precio = ?";
    $valores[] = $precio;
    $tipos .= "d";
}

if ($superficie != $anuncioActual['Superficie']) {
    $campos[] = "Superficie = ?";
    $valores[] = $superficie;
    $tipos .= "d"; // decimal
}

if ($habitaciones != $anuncioActual['NHabitaciones']) {
    $campos[] = "NHabitaciones = ?";
    $valores[] = $habitaciones;
    $tipos .= "i"; // integer
}

if ($banyos != $anuncioActual['NBanyos']) {
    $campos[] = "NBanyos = ?";
    $valores[] = $banyos;
    $tipos .= "i";
}

if ($planta != $anuncioActual['Planta']) {
    $campos[] = "Planta = ?";
    $valores[] = $planta;
    $tipos .= "i";
}

if ($anyo != $anuncioActual['Anyo']) {
    $campos[] = "Anyo = ?";
    $valores[] = $anyo;
    $tipos .= "i";
}

if (!empty($campos)) {
    $sql = "UPDATE ANUNCIOS SET " . implode(", ", $campos) . " WHERE IdAnuncio = ?";
    $stmt = $mysqli->prepare($sql);

    $tipos .= "i"; // para el IdAnuncio
    $valores[] = $id;

    // Preparar los parámetros dinámicamente
    $stmt->bind_param($tipos, ...$valores);

    if ($stmt->execute()) {
        header("Location: ./userAnuncio.php?id=" . urlencode($id) . "&temp=Anuncio+actualizado+correctamente");
        exit;
    } else {
        header("Location: ./userAnuncio.php?id=" . urlencode($id) . "&wrong=Error+al+actualizar");
        exit;
    }

    $stmt->close();
} else {
    header("Location: ./userAnuncio.php?id=" . urlencode($id) . "&wrong=No+se+han+realizado+cambios");
    exit;
}


$mysqli->close();

?>
