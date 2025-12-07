<?php
// services/deleteFoto.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php?error=acceso_denegado");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../index.php?msg=ID no válido");
    exit();
}

$idFoto = (int)$_GET['id'];
$idAnuncio = isset($_GET['anuncio']) ? (int)$_GET['anuncio'] : 0;
$usuarioId = $_SESSION['user_id'];

// --- CONEXIÓN ---
$config = parse_ini_file('../config.ini');
if (!$config) die("Error config");
@$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
if ($mysqli->connect_errno) die("Error BD");

// 1. Verificar propiedad y obtener nombre de archivo
// Hacemos JOIN con ANUNCIOS para asegurar que el usuario es dueño del anuncio al que pertenece la foto
$sqlCheck = "SELECT F.Foto 
             FROM FOTOS F
             JOIN ANUNCIOS A ON F.Anuncio = A.IdAnuncio
             WHERE F.IdFoto = ? AND A.Usuario = ?";

if ($stmt = $mysqli->prepare($sqlCheck)) {
    $stmt->bind_param("ii", $idFoto, $usuarioId);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        $nombreFoto = $row['Foto'];
        
        // 2. Borrar fichero físico del disco
        // Nota: estamos en /services/, las fotos están en ../img/
        if ($nombreFoto && $nombreFoto !== 'no_image.png') {
            $ruta = '../img/' . $nombreFoto;
            if (file_exists($ruta)) {
                unlink($ruta);
            }
        }
        
        // 3. Borrar registro de la BD
        $stmtDel = $mysqli->prepare("DELETE FROM FOTOS WHERE IdFoto = ?");
        $stmtDel->bind_param("i", $idFoto);
        
        if ($stmtDel->execute()) {
            $stmtDel->close();
            header("Location: ../userFotos.php?id=" . $idAnuncio . "&temp=" . urlencode("Foto eliminada"));
        } else {
            header("Location: ../userFotos.php?id=$idAnuncio&wrong=Error+SQL");
        }
        
    } else {
        // No se encontró la foto o no pertenece al usuario
        header("Location: ../index.php?wrong=Error+de+permisos");
    }
    $stmt->close();
} else {
    header("Location: ../index.php?wrong=Error+DB");
}

$mysqli->close();
exit();
?>