<?php
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

// --- Solo permitir POST ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('perfil.php');
}

$goodPost = true;
$newId = 0;

// --- Recuperar los valores ---
$tipoAnuncio = trim($_POST['tipoAnuncio'] ?? '');
$tipoVivienda = trim($_POST['tipoVivienda'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['texto'] ?? '');
$ciudad = trim($_POST['ciudad'] ?? '');
$pais = trim($_POST['pais'] ?? '');
$precio = trim($_POST['precio'] ?? '');
$superficie = trim($_POST['Superficie'] ?? '');
$habitaciones = trim($_POST['NHabitaciones'] ?? '');
$banyos = trim($_POST['NBanyos'] ?? '');
$planta = trim($_POST['Planta'] ?? '');
$anyo = trim($_POST['Anyo'] ?? '');
$id = trim($_POST['idAd'] ?? '');



// --- Validaciones ---
$errors = [];

// validar los campos

if ($tipoAnuncio === '') $errors['err_tipoAnuncio'] = 1;


if ($tipoVivienda === '') $errors['err_tipoVivienda'] = 1;


if ($nombre === '') $errors['err_nombre'] = 1;


if ($ciudad === '') $errors['err_ciudad'] = 1;
if (!preg_match('/^[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]+$/u', $ciudad)) {
    $errors['err_ciudad_nom'] = 1;
}

if ($pais === '') $errors['err_pais'] = 1;


if ($precio === '') {
    $errors['err_precio'] = 1;
} elseif (!preg_match('/^\d+(\.\d+)?$/', $precio)) {
    $errors['err_precio_num'] = 1;
}
if ($descripcion === '') $errors['err_descripcion'] = 1;


if ($superficie === '') {

} elseif (!preg_match('/^\d+(\.\d+)?$/', $superficie)) {
    $errors['err_superficie'] = 1;
}


if ($habitaciones === '') {

} elseif (!preg_match('/^\d+$/', $habitaciones)) {
    $errors['err_habitaciones'] = 1;
}
if ($banyos === '') {

} elseif (!preg_match('/^\d+$/', $banyos)) {
    $errors['err_banyos'] = 1;
}
if ($planta === '') {

} elseif (!preg_match('/^\d+$/', $planta)) {
    $errors['err_planta'] = 1;
}
if ($anyo === '') {

} elseif (!preg_match('/^\d{4}$/', $anyo)) {
    $errors['err_anyo'] = 1;
}
if((int)$anyo > (int)date('Y')) {
    $errors['err_anyo_futuro'] = 1;
}



// --- Si hay errores, redirigimos con los valores previos ---
if (!empty($errors)) {
    $params = $errors;
    $params['val_tipoAnuncio'] = $tipoAnuncio;
    $params['val_tipoVivienda'] = $tipoVivienda;
    $params['val_nombre'] = $nombre;
    $params['val_ciudad'] = $ciudad;
    $params['val_pais'] = $pais;
    $params['val_precio'] = $precio;
    $params['val_descripcion'] = $descripcion;
    $params['val_superficie'] = $superficie;
    $params['val_habitaciones'] = $habitaciones;
    $params['val_banyos'] = $banyos;
    $params['val_planta'] = $planta;
    $params['val_anyo'] = $anyo;
    $params['id'] = $_POST['idAd'] ?? '';

    redirigir('modifyAnuncio.php', $params);
    exit;
}

require_once './services/putAnuncio.php';
