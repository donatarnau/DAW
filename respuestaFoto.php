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

// Verificar que el formulario se envió por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('addFoto.php');
}

// Recoger los datos del formulario
$user = isset($_POST['user']) ? trim($_POST['user']) : '';
$anuncio = isset($_POST['anuncio']) ? trim($_POST['anuncio']) : '';
$nombreAnuncio = isset($_POST['nombreAnuncio']) ? trim($_POST['nombreAnuncio']) : '';
$alt = isset($_POST['alt']) ? trim($_POST['alt']) : '';
$desc = isset($_POST['desc']) ? trim($_POST['desc']) : '';
// Ignoramos $_FILES['foto'] según la práctica

// Validaciones
$errors = [];
$params = [];

// Validar campo anuncio (obligatorio)
if ($anuncio === '') {
    $errors['err_anuncio_empty'] = 1;
}

// Validar texto alternativo (obligatorio y >= 10 caracteres)
if ($alt === '') {
    $errors['err_alt_empty'] = 1;
} elseif (mb_strlen($alt) < 10) {
    $errors['err_alt_short'] = 1;
}

// Comprobar si hay errores
if (!empty($errors)) {
    // --- HAY ERRORES ---
    $params = $errors;

    // Mantener los valores introducidos
    if ($anuncio !== '') $params['val_anuncio'] = $anuncio;
    if ($alt !== '') $params['val_alt'] = $alt;
    if ($desc !== '') $params['val_desc'] = $desc;
    if ($nombreAnuncio !== '') $params['val_nomAnuncio'] = $nombreAnuncio;
    if ($user !== '') $params['user'] = $user;

    // Redirigir de nuevo al formulario con errores
    redirigir('addFoto.php', $params);

} else {
    // --- TODO CORRECTO ---
    // Aquí podrías guardar los datos o mover el archivo si se implementara
    // Por ahora, simplemente redirigimos al perfil del usuario
    redirigir('perfil.php',['user' => $user]);
}
?>
