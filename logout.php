<?php
session_start();
require_once './services/recordarme.php';

borrarCookieRecordarme();

// 1. Borrar todas las variables de sesión
$_SESSION = [];

// 2. Si existe la cookie de sesión, eliminarla también
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),    // normalmente PHPSESSID
        '',
        time() - 42000,    // fecha en el pasado
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 3. Destruir la sesión en el servidor
session_destroy();

// 4. (Opcional) Borrar la cookie “recordarme” si la usas
if (function_exists('borrarCookieRecordarme')) {
    borrarCookieRecordarme();
}

// 5. Redirigir a la página principal
header("Location: ./index.php");
exit;
?>