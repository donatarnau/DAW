<?php
/*
 * Fichero: services/control_acceso.php
 *
 * Script inteligente que gestiona AMBOS formularios.
 * Redirige al 'index.php' si el login es correcto.
 * Redirige a la página de origen ('index.php' o 'login.php') si hay un error.
 */

require_once 'usuarios.php';

/* --- Función redirigir (sin cambios) --- */
function redirigir($pagina, $params = []) {
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
$failure_page = isset($_POST['login_source']) ? $_POST['login_source'] : 'index.php';


// 3. Recoger datos y preparar parámetros
$user = isset($_POST['user']) ? trim($_POST['user']) : '';
$pass = isset($_POST['pwd']) ? trim($_POST['pwd']) : '';
$params = [];
$hasError = false;

// 4. Mantener el valor del usuario (sticky form)
if ($user !== '') {
    $params['val_user'] = $user;
}

// 5. Validar campos vacíos
if ($user === '') {
    $params['err_user'] = 1; // Error específico de usuario
    $hasError = true;
}
if ($pass === '') {
    $params['err_pass'] = 1; // Error específico de contraseña
    $hasError = true;
}

// Si hay errores de validación, redirigir a la PÁGINA DE ORIGEN
if ($hasError) {
    redirigir($failure_page, $params);
}

// 6. Autenticar (sólo si la validación pasó)
if (isset($usuariosPermitidos[$user]) && $usuariosPermitidos[$user] === $pass) {
    
    // ÉXITO: Redirigir SIEMPRE a index.php con el usuario
    redirigir('index.php', ['user' => $user]);

} else {
    
    // FRACASO: Datos incorrectos. Redirigir a la PÁGINA DE ORIGEN
    $params['err_login'] = 1; // Error genérico de login
    redirigir($failure_page, $params);
}
?>