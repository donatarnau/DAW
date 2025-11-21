<?php
/*
 * Fichero: services/control_acceso.php
 * Actualizado para autenticación contra MySQL (Práctica 9)
 */

session_start();

// Ya no necesitamos usuarios.php ni estilos.php, los datos están en la BD
require_once 'recordarme.php';
require_once 'flashdata.php';

/* --- Función redirigir --- */
function redirigir($pagina, $params = []) {
    session_write_close();
    $host = $_SERVER['HTTP_HOST'];
    // Ajuste para salir de la carpeta 'services' si es necesario en la redirección
    $current_dir = dirname($_SERVER['PHP_SELF']); // e.g., /daw/services
    $parent_dir = dirname($current_dir);          // e.g., /daw
    
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
/* --- Fin función redirigir --- */

// 1. Verificar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('index.php');
}

// 2. Determinar página de origen para errores
$failure_page = isset($_POST['login_source']) ? $_POST['login_source'] : 'index.php';

// 3. Recoger datos
$user = isset($_POST['user']) ? trim($_POST['user']) : '';
$pass = isset($_POST['pwd']) ? $_POST['pwd'] : ''; // Las contraseñas no se deben hacer trim a veces, pero para esta práctica vale.

// 4. Validaciones básicas (Sticky form y errores vacíos)
$hasError = false;
if ($user !== '') flash_set('val_user', $user);
if ($user === '') {
    flash_set('err_user', 1);
    $hasError = true;
}
if ($pass === '') {
    flash_set('err_pass', 1);
    $hasError = true;
}
if ($hasError) redirigir($failure_page);

// 5. --- AUTENTICACIÓN CON MYSQL ---

// a) Leer configuración (está un nivel más arriba, en ../config.ini)
$config_path = '../config.ini';
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
$sql = "SELECT U.IdUsuario, U.NomUsuario, U.Clave, E.Fichero AS EstiloFichero 
        FROM USUARIOS U 
        LEFT JOIN ESTILOS E ON U.Estilo = E.IdEstilo 
        WHERE U.NomUsuario = ?";

if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("s", $user);
    $stmt->execute();
    // Vinculamos las variables de resultado
    $stmt->bind_result($dbId, $dbUser, $dbPass, $dbStyleFile);
    
    // Intentamos obtener una fila
    if ($stmt->fetch()) {
        // ¡Usuario encontrado! Ahora verificamos la contraseña.
        // NOTA: En la práctica actual las contraseñas están en texto plano.
        // Si en el futuro las encriptas con password_hash(), aquí usarías password_verify($pass, $dbPass)
        if (password_verify($pass, $dbPass)) {
            // --- LOGIN CORRECTO ---
            
            // 1. Guardar datos en sesión
            $_SESSION['user'] = $dbUser;
            $_SESSION['user_id'] = $dbId; // ¡MUY IMPORTANTE para futuras consultas!
            
            // 2. Guardar el estilo en sesión si existe
            if (!empty($dbStyleFile)) {
                $_SESSION['style'] = $dbStyleFile; 
            }

            // 3. Gestionar "Recordarme"
            if (!empty($_POST['recordarme'])) {
                crearCookieRecordarme($dbUser);
            }

            // Cerrar recursos antes de redirigir
            $stmt->close();
            $mysqli->close();

            // 4. Redirigir a la zona privada (normalmente index.php detecta la sesión)
            redirigir('index.php');

        } else {
            // Contraseña incorrecta
            $loginFailed = true;
        }
    } else {
        // Usuario no encontrado
        $loginFailed = true;
    }
    $stmt->close();
} else {
    die("Error al preparar la consulta: " . $mysqli->error);
}
$mysqli->close();

// Si llegamos aquí es que falló el login (usuario no existe o pass incorrecta)
if (isset($loginFailed) && $loginFailed) {
    flash_set('err_login', 1);
    redirigir($failure_page);
}
?>