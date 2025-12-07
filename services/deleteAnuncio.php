<?php
// services/deleteAnuncio.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php?error=acceso_denegado");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../index.php");
    exit();
}

$idAnuncio = (int)$_GET['id'];
$usuarioId = $_SESSION['user_id'];

$config = parse_ini_file('../config.ini');
if (!$config) die("Error config");
@$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
if ($mysqli->connect_errno) die("Error BD");

// 1. Verificar propiedad y obtener Foto Principal
$sqlCheck = "SELECT FPrincipal FROM ANUNCIOS WHERE IdAnuncio = ? AND Usuario = ?";
if ($stmt = $mysqli->prepare($sqlCheck)) {
    $stmt->bind_param("ii", $idAnuncio, $usuarioId);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        // ES PROPIETARIO. PROCEDEMOS A LIMPIAR FICHEROS.
        
        // A. Borrar Foto Principal
        $fPrincipal = $row['FPrincipal'];
        if ($fPrincipal && $fPrincipal !== 'no_image.png' && $fPrincipal !== 'sinfoto.jpg') {
            $rutaP = '../img/' . $fPrincipal;
            if (file_exists($rutaP)) unlink($rutaP);
        }
        
        // B. Obtener y Borrar todas las Fotos de la Galería
        $sqlGaleria = "SELECT Foto FROM FOTOS WHERE Anuncio = ?";
        if ($stmtG = $mysqli->prepare($sqlGaleria)) {
            $stmtG->bind_param("i", $idAnuncio);
            $stmtG->execute();
            $resG = $stmtG->get_result();
            while ($fotoRow = $resG->fetch_assoc()) {
                $fGaleria = $fotoRow['Foto'];
                if ($fGaleria && $fGaleria !== 'no_image.png') {
                    $rutaG = '../img/' . $fGaleria;
                    if (file_exists($rutaG)) unlink($rutaG);
                }
            }
            $stmtG->close();
        }
        
        // C. Borrar Anuncio de la BD 
        // (El ON DELETE CASCADE de MySQL borrará las filas de FOTOS automáticamente,
        // pero nosotros ya hemos borrado los ficheros físicos arriba).
        $stmtDel = $mysqli->prepare("DELETE FROM ANUNCIOS WHERE IdAnuncio = ?");
        $stmtDel->bind_param("i", $idAnuncio);
        if ($stmtDel->execute()) {
            header("Location: ../perfil.php?temp=" . urlencode("Anuncio y sus fotos eliminados correctamente"));
        } else {
            header("Location: ../perfil.php?wrong=Error+al+borrar+registro");
        }
        $stmtDel->close();
        
    } else {
        header("Location: ../index.php?wrong=No+tienes+permiso");
    }
    $stmt->close();
}

$mysqli->close();
exit();
?>