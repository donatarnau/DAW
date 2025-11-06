<?php
// Ruta al archivo donde guardamos los tokens válidos
define('TOKENS_FILE', __DIR__ . '/../data/token.json');
require_once __DIR__ . '/estilos.php';

// ===================================================
//  CREAR COOKIE RECORDARME
// ===================================================
function crearCookieRecordarme($username) {
    $token = bin2hex(random_bytes(32)); // Token aleatorio seguro
    $fecha = date('Y-m-d H:i:s');

    // Guardar token en archivo (o BD)
    $tokens = cargarTokens();
    $tokens[] = [
        'usuario' => $username,
        'token' => $token,
        'fecha' => $fecha
    ];
    guardarTokens($tokens);

    // Duración: 90 días desde hoy (no se renueva después)
    $expira = time() + (90 * 24 * 60 * 60);

    // Crear cookie segura
    setcookie(
        'recordarme',
        $token,
        [
            'expires' => $expira,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']), // solo por HTTPS si lo hay
            'httponly' => true,                   // inaccesible desde JS
            'samesite' => 'Lax'
        ]
    );
}

// ===================================================
//  COMPROBAR COOKIE RECORDARME
// ===================================================
function comprobarCookieRecordarme() {
    if (!isset($_COOKIE['recordarme'])) {
        return false;
    }

    $token = $_COOKIE['recordarme'];
    $tokens = cargarTokens();

    foreach ($tokens as &$registro) {
        if ($registro['token'] === $token) {
            // Comprobar si han pasado más de 90 días
            $fechaCreacion = strtotime($registro['fecha']);
            if (time() - $fechaCreacion > 90 * 24 * 60 * 60) {
                borrarCookieRecordarme();
                return false; // Caducada
            }

            // Iniciar sesión automática
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['user'] = $registro['usuario'];

            // Cargar el estilo del usuario al recordar por cookie
            $style = obtenerEstiloParaUsuario($_SESSION['user']);
            if ($style !== null) {
                $_SESSION['style'] = $style;
            }

            // Guardar la última visita anterior en la sesión
            $_SESSION['ultima_visita'] = $registro['fecha'];

            // Actualizar la fecha del registro a ahora
            $registro['fecha'] = date('Y-m-d H:i:s');

            // Guardar tokens actualizados
            guardarTokens($tokens);

            return true;
        }
    }
    unset($registro);
    return false;
}

// ===================================================
//  BORRAR COOKIE RECORDARME
// ===================================================
function borrarCookieRecordarme() {
    if (isset($_COOKIE['recordarme'])) {
        setcookie('recordarme', '', time() - 3600, '/');
    }
}

// ===================================================
//  FUNCIONES AUXILIARES
// ===================================================
function cargarTokens() {
    if (!file_exists(TOKENS_FILE)) {
        return [];
    }
    $json = file_get_contents(TOKENS_FILE);
    return json_decode($json, true) ?: [];
}

function guardarTokens($tokens) {
    file_put_contents(TOKENS_FILE, json_encode($tokens, JSON_PRETTY_PRINT));
}
?>
