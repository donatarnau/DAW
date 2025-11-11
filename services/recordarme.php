<?php
// Ruta al archivo donde guardamos los tokens válidos
define('TOKENS_FILE', __DIR__ . '/../data/token.json');
require_once __DIR__ . '/estilos.php';

function redirect($pagina, $params = []) {
    session_write_close();
    $host = $_SERVER['HTTP_HOST'];
    // Ajuste para salir de la carpeta 'services' si es necesario en la redirección
    $parent_dir = dirname($_SERVER['PHP_SELF']); // e.g., /daw/services
    //$parent_dir = dirname($current_dir);          // e.g., /daw
    
    // Si $parent_dir es / o \, lo dejamos vacío para evitar doble barra //
    if ($parent_dir === '/' || $parent_dir === '\\') $parent_dir = '';

    $queryString = '';
    if (!empty($params)) {
        $queryString = '?' . http_build_query($params);
    }
    // Redirigimos a la raíz del proyecto + la página destino
    header("Location: http://$host$parent_dir/$pagina$queryString");
    exit; 
}

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


            // 5. --- AUTENTICACIÓN CON MYSQL ---

            // a) Leer configuración (está un nivel más arriba, en ../config.ini)
            $config_path = './config.ini';
            if (!file_exists($config_path)) {
                // Si falla esto, es crítico. Podrías redirigir con un error especial.
                die("Error crítico: No se encuentra config.ini");
            }
            $config = parse_ini_file($config_path);

            // b) Conectar
            @$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
            if ($mysqli->connect_errno) {
                // En producción no mostrarías el error real al usuario, pero para depurar ayuda.
                die("Error de conexión a la BD: " . $mysqli->connect_error);
            }

            // c) Preparar la consulta
            // Buscamos el usuario y hacemos JOIN con ESTILOS para obtener directamente el fichero CSS
            $sql = "SELECT U.IdUsuario, U.NomUsuario, E.Fichero AS EstiloFichero 
                    FROM USUARIOS U 
                    LEFT JOIN ESTILOS E ON U.Estilo = E.IdEstilo 
                    WHERE U.NomUsuario = ?";

            if ($stmt = $mysqli->prepare($sql)) {
                $stmt->bind_param("s", $registro['usuario']);
                $stmt->execute();
                // Vinculamos las variables de resultado
                $stmt->bind_result($dbId, $dbUser, $dbStyleFile);
                
                // Intentamos obtener una fila
                if ($stmt->fetch()) {

                    $_SESSION['user'] = $dbUser;
                    $_SESSION['user_id'] = $dbId; // ¡MUY IMPORTANTE para futuras consultas!
                    
                    // 2. Guardar el estilo en sesión si existe
                    if (!empty($dbStyleFile)) {
                        $_SESSION['style'] = $dbStyleFile; 
                    }

                    // Cerrar recursos antes de redirigir
                    $stmt->close();
                    $mysqli->close();

                    // 4. Redirigir a la zona privada (normalmente index.php detecta la sesión)
                    redirect('index.php');
                } else {
                    // Usuario no encontrado
                    $loginFailed = true;
                }
                $stmt->close();
            } else {
                die("Error al preparar la consulta: " . $mysqli->error);
            }
            $mysqli->close();


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
