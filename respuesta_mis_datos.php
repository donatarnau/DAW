<?php
/**
 * respuesta_mis_datos.php
 * Procesa la modificación de datos de usuario con validaciones, 
 * gestión de cookies y HASHEO de contraseñas.
 */

session_start();
require_once 'services/flashdata.php';
require_once 'services/validar_usuario.php';
require_once 'services/recordarme.php';

function redirigirMisDatos($params = []) {
    $host = $_SERVER['HTTP_HOST'];
    $uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\'); 
    if ($uri === '/' || $uri === '\\') $uri = '';
    $queryString = '';
    if (!empty($params)) {
        $queryString = '?' . http_build_query($params);
    }
    header("Location: http://$host$uri/mis_datos.php$queryString");
    exit; 
}

// 1. Verificar acceso y método
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ./login.php");
    exit;
}
$userId = $_SESSION['user_id'];
$errors = [];

// 2. Recoger datos
$formData = $_POST;
$currentPwdInput = $_POST['current_pwd'] ?? '';

// --- VALIDACIÓN DE DATOS (Modo edición: contraseñas opcionales) ---
$errors = validar_datos_usuario($formData, false);

// 3. CONEXIÓN A LA BD
$config = parse_ini_file('config.ini');
if (!$config) die("Error crítico: No se encuentra config.ini");

@$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
if ($mysqli->connect_errno) {
    die("Error de conexión a la BD: " . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

// Obtener la contraseña REAL actual (HASH) y nombre de usuario
$dbData = null;
$stmt = $mysqli->prepare("SELECT NomUsuario, Clave FROM USUARIOS WHERE IdUsuario = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 1) {
    $dbData = $res->fetch_assoc();
}
$stmt->close();

if (!$dbData) {
     $mysqli->close();
     session_destroy();
     header("Location: ./login.php?error=usuario_no_encontrado");
     exit;
}

// --- A. VERIFICAR CONTRASEÑA ACTUAL (Con Hash) ---
if ($currentPwdInput === '') {
    $errors['err_pwd_old'] = "Debes introducir tu contraseña actual para confirmar los cambios.";
} elseif (!password_verify($currentPwdInput, $dbData['Clave'])) { 
    // CORREGIDO: Usamos password_verify para comparar la entrada con el hash de la BD
    $errors['err_pwd_old'] = "Contraseña actual incorrecta.";
}

// B. Comprobar si el nuevo nombre de usuario está ya en uso
$newUser = $formData['user'];
$originalUser = $formData['original_user'];

if ($newUser !== $originalUser && empty($errors['err_user'])) {
    $sqlCheck = "SELECT IdUsuario FROM USUARIOS WHERE NomUsuario = ? AND IdUsuario != ?";
    if ($stmt = $mysqli->prepare($sqlCheck)) {
        $stmt->bind_param("si", $newUser, $userId);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors['err_user'] = "El nombre de usuario ya está en uso.";
        }
        $stmt->close();
    }
}

// 4. GESTIÓN DE ERRORES Y REDIRECCIÓN
if (!empty($errors)) {
    foreach ($errors as $key => $msg) {
        if ($key === 'err_pwd1') $key = 'err_pwd_new';
        if ($key === 'err_match') $key = 'err_pwd_match';
        flash_set($key, $msg);
    }
    // Repoblar campos
    flash_set('val_user', $formData['user']);
    flash_set('val_email', $formData['email']);
    flash_set('val_sexo', $formData['sexo']);
    flash_set('val_fecha', $formData['fecha_nacimiento']);
    flash_set('val_ciudad', $formData['ciudad']);
    flash_set('val_pais', $formData['pais']);
    
    $mysqli->close();
    redirigirMisDatos();
}

// 5. CONSTRUCCIÓN DE LA QUERY DE ACTUALIZACIÓN
$fieldsToUpdate = [];
$params = [];
$types = '';
$cambioSensible = false;

// Nombre de Usuario
if ($newUser !== $formData['original_user']) {
    $fieldsToUpdate[] = "NomUsuario = ?";
    $params[] = $newUser;
    $types .= 's';
    $cambioSensible = true;
}

// --- C. NUEVA CONTRASEÑA (Con Hash) ---
$newPwd = $formData['new_pwd'] ?? '';
if (!empty($newPwd)) {
    $fieldsToUpdate[] = "Clave = ?";
    // CORREGIDO: Hasheamos la nueva contraseña antes de guardarla
    $params[] = password_hash($newPwd, PASSWORD_DEFAULT);
    $types .= 's';
    $cambioSensible = true;
}

// Otros campos
$newEmail = $formData['email'];
if ($newEmail !== $formData['original_email']) {
    $fieldsToUpdate[] = "Email = ?"; $params[] = $newEmail; $types .= 's';
}

$newSexo = (int)($formData['sexo'] ?? 0);
if ($newSexo != $formData['original_sexo']) {
    $fieldsToUpdate[] = "Sexo = ?"; $params[] = $newSexo; $types .= 'i';
}

$newFecha = $formData['fecha_nacimiento'] ?? '';
if ($newFecha !== $formData['original_fecha']) {
    $fieldsToUpdate[] = "FNacimiento = ?"; $params[] = $newFecha; $types .= 's';
}

$newCiudad = $formData['ciudad'] ?? ''; 
$fieldsToUpdate[] = "Ciudad = ?"; $params[] = $newCiudad; $types .= 's';

$newPais = (int)($formData['pais'] ?? 1); 
$fieldsToUpdate[] = "Pais = ?"; $params[] = $newPais; $types .= 'i';

// Foto (Simulación)
$newFotoName = $formData['original_foto'] ?? null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $newFotoName = basename($_FILES['foto']['name']);
}
$fieldsToUpdate[] = "Foto = ?";
$params[] = $newFotoName;
$types .= 's';


// 6. EJECUTAR ACTUALIZACIÓN
if (empty($fieldsToUpdate)) {
    flash_set('success_msg', "No se detectaron cambios, los datos se mantuvieron.");
    $mysqli->close();
    redirigirMisDatos();
}

$sql = "UPDATE USUARIOS SET " . implode(', ', $fieldsToUpdate) . " WHERE IdUsuario = ?";
$params[] = $userId;
$types .= 'i';

if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        
        // 7. GESTIÓN DE SEGURIDAD (Logout si cambia pass/user)
        if ($cambioSensible) {
            // 1. Invalidar tokens en servidor
            if (function_exists('invalidarTokensUsuario')) {
                invalidarTokensUsuario($formData['original_user']);
            }
            // 2. Borrar cookie navegador
            if (function_exists('borrarCookieRecordarme')) {
                borrarCookieRecordarme(); 
            }
            
            // 3. Destruir sesión y forzar login
            session_unset();
            session_destroy();

            $host = $_SERVER['HTTP_HOST'];
            $uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            header("Location: http://$host$uri/login.php?mensaje=Datos actualizados. Por seguridad, inicia sesión de nuevo.");
            exit;

        } else {
            // Actualizar sesión si solo cambiaron datos normales
            $_SESSION['user'] = $newUser;
            flash_set('success_msg', "Datos actualizados correctamente.");
        }
        
    } else {
        flash_set('success_msg', "Error al actualizar los datos: " . $stmt->error);
    }

    $stmt->close();
} else {
    flash_set('success_msg', "Error al preparar la sentencia: " . $mysqli->error);
}

$mysqli->close();
redirigirMisDatos();
?>