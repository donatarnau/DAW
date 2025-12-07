<?php
/**
 * respuestaFoto.php
 * Procesa la subida de fotos.
 */
session_start();
require_once 'services/flashdata.php';
require_once 'services/gestor_imagenes.php';

function redirigir($pagina, $params = []) {
    $host = $_SERVER['HTTP_HOST'];
    $uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    if ($uri === '/' || $uri === '\\') $uri = '';
    $queryString = '';
    if (!empty($params)) {
        $queryString = '?' . http_build_query($params);
    }
    header("Location: http://$host$uri/$pagina$queryString");
    exit;
}

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ./index.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Recoger datos
$idAnuncio = isset($_POST['anuncio']) ? (int)$_POST['anuncio'] : 0;
$tituloFoto = trim($_POST['tit'] ?? '');
$altFoto = trim($_POST['alt'] ?? '');

// Validaciones
$errors = [];
if ($idAnuncio <= 0) $errors['err_anuncio_empty'] = 1;
if ($tituloFoto === '') $errors['err_tit_empty'] = 1;
if ($altFoto === '') $errors['err_alt_empty'] = 1;
elseif (mb_strlen($altFoto) < 10) $errors['err_alt_short'] = 1;

$altLower = mb_strtolower($altFoto);
if (preg_match('/^(texto|imagen|imagen de|foto|foto de)/u', $altLower)) {
    $errors['err_alt_invalid_start'] = 1;
}

if (!empty($errors)) {
    foreach ($errors as $k => $v) flash_set($k, $v);
    flash_set('val_anuncio', $idAnuncio);
    flash_set('val_tit', $tituloFoto);
    flash_set('val_alt', $altFoto);
    redirigir('addFoto.php', ['id' => $idAnuncio]);
}

// Conexión
$config = parse_ini_file('config.ini');
if (!$config) die("Error config");
@$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
if ($mysqli->connect_errno) die("Error conexión BD");
$mysqli->set_charset('utf8mb4');

// Verificar propiedad del anuncio
$stmt = $mysqli->prepare("SELECT IdAnuncio, FPrincipal FROM ANUNCIOS WHERE IdAnuncio = ? AND Usuario = ?");
$stmt->bind_param("ii", $idAnuncio, $userId);
$stmt->execute();
$res = $stmt->get_result();
$anuncioData = $res->fetch_assoc();
$stmt->close();

if (!$anuncioData) {
    $mysqli->close();
    flash_set('wrong', "No tienes permiso sobre este anuncio o no existe.");
    redirigir('addFoto.php');
}

// Procesar imagen
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    
    // Calcular nombre: a{ID}-{N}.jpg
    $directorio = './img/';
    $prefijo = "a{$idAnuncio}-";
    $contador = 1;
    // Buscamos el primer hueco libre (a5-1.jpg, a5-2.jpg...)
    while (file_exists($directorio . $prefijo . $contador . ".jpg")) {
        $contador++;
    }
    $nombreBase = $prefijo . $contador;

    // Subir y convertir
    $resFoto = subir_y_convertir_a_jpg($_FILES['foto'], $directorio, $nombreBase);

    if ($resFoto['ok']) {
        $nombreFinal = $resFoto['fileName'];

        // Si la foto principal es la por defecto o está vacía, actualizamos esa
        if (empty($anuncioData['FPrincipal']) || $anuncioData['FPrincipal'] === 'sinfoto.jpg') {
            $sqlUp = "UPDATE ANUNCIOS SET FPrincipal = ?, Alternativo = ? WHERE IdAnuncio = ?";
            if ($stmt = $mysqli->prepare($sqlUp)) {
                $stmt->bind_param("ssi", $nombreFinal, $altFoto, $idAnuncio);
                $stmt->execute();
                $stmt->close();
            }
        } else {
            // Si ya tiene principal, va a la galería
            $sqlIns = "INSERT INTO FOTOS (Anuncio, Foto, Alternativo, Titulo) VALUES (?, ?, ?, ?)";
            if ($stmt = $mysqli->prepare($sqlIns)) {
                $stmt->bind_param("isss", $idAnuncio, $nombreFinal, $altFoto, $tituloFoto);
                $stmt->execute();
                $stmt->close();
            }
        }

        $mysqli->close();
        header("Location: ./userAnuncio.php?id=$idAnuncio&temp=" . urlencode("Foto añadida correctamente"));
        exit;

    } else {
        flash_set('wrong', "Error al procesar imagen: " . $resFoto['msg']);
        $mysqli->close();
        redirigir('addFoto.php', ['id' => $idAnuncio]);
    }

} else {
    $msg = "Error en la subida.";
    if (isset($_FILES['foto']['error']) && $_FILES['foto']['error'] == UPLOAD_ERR_NO_FILE) {
        $msg = "Debes seleccionar un archivo.";
    }
    flash_set('wrong', $msg);
    flash_set('val_anuncio', $idAnuncio);
    flash_set('val_tit', $tituloFoto);
    flash_set('val_alt', $altFoto);
    $mysqli->close();
    redirigir('addFoto.php', ['id' => $idAnuncio]);
}
?>