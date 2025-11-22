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

$usuarioId = $_SESSION['user_id'];

$id = $_GET['id'];
$idAnuncio = isset($_GET['anuncio']) ? (int)$_GET['anuncio'] : 0;

// --- 2. CONEXIÓN A LA BD ---
$config = parse_ini_file('../config.ini');
if (!$config) die("Error al leer config.ini");
@$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
if ($mysqli->connect_errno) die("Error de conexión a la BD: " . $mysqli->connect_error);

$sqlCheck = "SELECT IdAnuncio FROM ANUNCIOS WHERE IdAnuncio = ? AND Usuario = ?";
$stmt = $mysqli->prepare($sqlCheck);
$stmt->bind_param("ii", $idAnuncio, $usuarioId);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    // El anuncio no existe o no pertenece al usuario
    header("Location: ../index.php?wrong=Error+al+eliminar");
    $stmt->close();
    $mysqli->close();
    exit;
}
$stmt->close();


$sqlFotos = "DELETE FROM fotos WHERE IdFoto = ?";

if ($stmt = $mysqli->prepare($sqlFotos)) {
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
    // 4. Redirigir con mensaje de éxito
    header("Location: ../perfil.php?temp=Foto+eliminada+con+exito");
    } else {
        header("Location: ../perfil.php?wrong=Error+al+eliminar");
    }
}
$stmt->close();
$mysqli->close();
exit();
?>
