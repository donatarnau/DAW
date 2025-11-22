<?php
// delete.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php?error=acceso_denegado");
    exit;
}

// 1. Verificar si se envió el ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../index.php?msg=ID no válido");
    exit();
}

$id = $_GET['id'];
$usuarioId = $_SESSION['user_id'];

// --- 2. CONEXIÓN A LA BD ---
$config = parse_ini_file('../config.ini');
if (!$config) die("Error al leer config.ini");
@$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
if ($mysqli->connect_errno) die("Error de conexión a la BD: " . $mysqli->connect_error);

// comprueba primero con un select que el usuario es el propieatario del anuncio antes de borrarlo

$sqlCheck = "SELECT * FROM ANUNCIOS WHERE IdAnuncio = ? AND Usuario = ?";
$stmt = $mysqli->prepare($sqlCheck);
$stmt->bind_param("ii", $id, $usuarioId);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    // El anuncio no existe o no pertenece al usuario
    header("Location: ../index.php?wrong=Este+anuncio+no+es+tuyo");
    $stmt->close();
    $mysqli->close();
    exit;
}
$stmt->close();



$sqlAnuncios = "DELETE FROM anuncios WHERE IdAnuncio = ?";

if ($stmt = $mysqli->prepare($sqlAnuncios)) {
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
    // 4. Redirigir con mensaje de éxito
    header("Location: ../index.php?temp=Anuncio+eliminado+con+exito");
    } else {
        header("Location: ../index.php?wrong=Error+al+eliminar");
    }
}
$stmt->close();
$mysqli->close();
exit();
?>
