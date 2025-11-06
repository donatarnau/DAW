<?php
/*
 * Fichero: services/control_acceso.php
 *
 * Script inteligente que gestiona AMBOS formularios.
 * Redirige al 'index.php' si el login es correcto.
 * Redirige a la página de origen ('index.php' o 'login.php') si hay un error.
 */

session_start();

require_once 'estilos.php';
require_once 'usuarios.php';
require_once 'recordarme.php';
require_once 'flashdata.php';

/* --- Función redirigir (sin cambios) --- */
function redirigir($pagina, $params = []) {
    session_write_close();
    $host = $_SERVER['HTTP_HOST'];
    $current_dir = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    $uri = dirname($current_dir); 
    if ($uri === '/' || $uri === '\\') $uri = '';
    $queryString = '';
    if (!empty($params)) {
        $queryString = '?' . http_build_query($params);
    }
    header("Location: http://$host$uri/$pagina$queryString");
    exit; 
}
/* --- Fin función redirigir --- */

// 1. Verificar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('index.php');
}

// 2. ¡NUEVO! Determinar la página de origen en caso de error
// Usamos un valor por defecto ('index.php') por si acaso.
// Página a la que devolveremos al usuario en caso de error (por defecto index.php)
$failure_page = isset($_POST['login_source']) ? $_POST['login_source'] : 'index.php';


// 3. Recoger datos y preparar parámetros
$user = isset($_POST['user']) ? trim($_POST['user']) : '';
$pass = isset($_POST['pwd']) ? trim($_POST['pwd']) : '';
$params = [];
$hasError = false;

// 4. Mantener el valor del usuario (sticky form) y validar campos vacíos
if ($user !== '') {
    // Guardamos el valor de usuario en flashdata para mostrarlo en la siguiente petición
    flash_set('val_user', $user);
}

if ($user === '') {
    flash_set('err_user', 1);
    $hasError = true;
}
if ($pass === '') {
    flash_set('err_pass', 1);
    $hasError = true;
}

// Si hay errores de validación, redirigir a la PÁGINA DE ORIGEN (sin usar parámetros en la URL)
if ($hasError) {
    redirigir($failure_page);
}

// 6. Autenticar (sólo si la validación pasó)
if (isset($usuariosPermitidos[$user]) && $usuariosPermitidos[$user] === $pass) {

    // AUTENTICACIÓN CORRECTA

    $_SESSION['user'] = $user;

    // Guardar el estilo del usuario en la sesión
    $style = obtenerEstiloParaUsuario($user);
    if ($style !== null) {
        $_SESSION['style'] = $style;
    }

    if (!empty($_POST['recordarme'])) {
        crearCookieRecordarme($user);
    }
    
    // ÉXITO: Redirigir SIEMPRE a index.php con el usuario
    redirigir('index.php');

} else {
    
    // FRACASO: Datos incorrectos. Redirigir a la PÁGINA DE ORIGEN
    // Usamos flashdata para indicar error genérico de login
    flash_set('err_login', 1);
    redirigir($failure_page);
}
?>