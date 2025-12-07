<?php
/**
 * respuesta_mis_datos.php
 * Procesa la modificación de datos de usuario.
 * Integra borrado de foto tras confirmación de contraseña.
 */

session_start();
require_once 'services/flashdata.php';
require_once 'services/validar_usuario.php';
require_once 'services/recordarme.php';
require_once 'services/gestor_imagenes.php';

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

// 1. Verificar acceso
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ./login.php");
    exit;
}
$userId = $_SESSION['user_id'];
$errors = [];

// 2. Recoger datos
$formData = $_POST;
$currentPwdInput = $_POST['current_pwd'] ?? '';

// Normalizar fecha
$fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
$fecha_sql = null;
if (!empty($fecha_nacimiento)) {
    $parts = preg_split('/[\/\-.\s]+/', trim($fecha_nacimiento));
    if (count($parts) === 3) {
        $d = (int)$parts[0]; $m = (int)$parts[1]; $y = (int)$parts[2];
        if (checkdate($m, $d, $y)) {
            $fecha_sql = sprintf('%04d-%02d-%02d', $y, $m, $d);
        }
    } else {
        $fecha_sql = $fecha_nacimiento;
    }
}
if (!empty($fecha_sql)) {
    $formData['fecha_nacimiento'] = $fecha_sql;
}

// Validación de datos (formato email, usuario, etc.)
$errors = validar_datos_usuario($formData, false);

// 3. CONEXIÓN A BD
$config = parse_ini_file('config.ini');
if (!$config) die("Error crítico: No se encuentra config.ini");
@$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
if ($mysqli->connect_errno) die("Error de conexión BD");
$mysqli->set_charset('utf8mb4');

// Obtener contraseña actual de la BD para verificar
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
     $mysqli->close(); session_destroy();
     header("Location: ./login.php?error=usuario_no_encontrado"); exit;
}

// 4. VERIFICAR CONTRASEÑA ACTUAL (OBLIGATORIO)
if ($currentPwdInput === '') {
    $errors['err_pwd_old'] = "Debes introducir tu contraseña actual para confirmar los cambios.";
} elseif (!password_verify($currentPwdInput, $dbData['Clave'])) { 
    $errors['err_pwd_old'] = "Contraseña actual incorrecta.";
}

// Comprobar nombre usuario duplicado
$newUser = $formData['user'];
$originalUser = $formData['original_user'];
if ($newUser !== $originalUser && empty($errors['err_user'])) {
    $sqlCheck = "SELECT IdUsuario FROM USUARIOS WHERE NomUsuario = ? AND IdUsuario != ?";
    if ($stmt = $mysqli->prepare($sqlCheck)) {
        $stmt->bind_param("si", $newUser, $userId);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) $errors['err_user'] = "El nombre de usuario ya está en uso.";
        $stmt->close();
    }
}

// GESTIÓN DE ERRORES: Si algo falló, no guardamos NADA.
if (!empty($errors)) {
    foreach ($errors as $key => $msg) {
        if ($key === 'err_pwd1') $key = 'err_pwd_new';
        if ($key === 'err_match') $key = 'err_pwd_match';
        flash_set($key, $msg);
    }
    flash_set('val_user', $formData['user']);
    flash_set('val_email', $formData['email']);
    flash_set('val_sexo', $formData['sexo']);
    flash_set('val_fecha', $formData['fecha_nacimiento']); // Input original
    flash_set('val_ciudad', $formData['ciudad']);
    flash_set('val_pais', $formData['pais']);
    
    $mysqli->close();
    redirigirMisDatos();
}

// --- SI LLEGAMOS AQUÍ, LA CONTRASEÑA ES CORRECTA Y LOS DATOS VÁLIDOS ---

$fieldsToUpdate = [];
$params = [];
$types = '';
$cambioSensible = false;

// Preparar campos estándar
if ($newUser !== $formData['original_user']) {
    $fieldsToUpdate[] = "NomUsuario = ?"; $params[] = $newUser; $types .= 's';
    $cambioSensible = true;
}
$newPwd = $formData['new_pwd'] ?? '';
if (!empty($newPwd)) {
    $fieldsToUpdate[] = "Clave = ?"; 
    $params[] = password_hash($newPwd, PASSWORD_DEFAULT); 
    $types .= 's';
    $cambioSensible = true;
}
$newEmail = $formData['email'];
if ($newEmail !== $formData['original_email']) {
    $fieldsToUpdate[] = "Email = ?"; $params[] = $newEmail; $types .= 's';
}
$newSexo = (int)($formData['sexo'] ?? 0);
$originalSexo = (int)($formData['original_sexo'] ?? 0);
if ($newSexo != $originalSexo) {
    $fieldsToUpdate[] = "Sexo = ?"; $params[] = $newSexo; $types .= 'i';
}
$newFecha = $formData['fecha_nacimiento'] ?? ''; // Usa la fecha original del post si no se normalizó, o la sql
if (isset($fecha_sql) && $fecha_sql !== $formData['original_fecha']) {
    $fieldsToUpdate[] = "FNacimiento = ?"; $params[] = $fecha_sql; $types .= 's';
}
$newCiudad = $formData['ciudad'] ?? ''; 
$fieldsToUpdate[] = "Ciudad = ?"; $params[] = $newCiudad; $types .= 's';
$newPais = (int)($formData['pais'] ?? 1); 
$fieldsToUpdate[] = "Pais = ?"; $params[] = $newPais; $types .= 'i';


// --- 5. LÓGICA DE FOTO (SUBIDA O BORRADO) ---

$flagBorrarFoto = isset($_POST['borrar_foto_flag']) && $_POST['borrar_foto_flag'] == '1';
$seSubeFoto = isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK;

// CASO A: Subida de nueva foto (tiene prioridad sobre borrar)
if ($seSubeFoto) {
    $nombreBase = 'perfil' . $userId;
    $directorio = './img/perfiles/';
    
    $resFoto = subir_y_convertir_a_jpg($_FILES['foto'], $directorio, $nombreBase);
    
    if ($resFoto['ok']) {
        $nuevoNombreFoto = $resFoto['fileName']; // perfilX.jpg
        $fieldsToUpdate[] = "Foto = ?";
        $params[] = $nuevoNombreFoto;
        $types .= 's';
        
        // Limpieza de foto antigua si tenía otro nombre
        $fotoVieja = $formData['original_foto'] ?? '';
        if (!empty($fotoVieja) && $fotoVieja !== $nuevoNombreFoto && $fotoVieja !== 'no_image.png') {
            if (file_exists($directorio . $fotoVieja)) {
                @unlink($directorio . $fotoVieja);
            }
        }
    } else {
        flash_set('wrong', "Error al procesar la nueva foto: " . $resFoto['msg']);
    }
} 
// CASO B: Borrado de foto existente (si no se sube una nueva y se marcó el flag)
elseif ($flagBorrarFoto) {
    $fotoVieja = $formData['original_foto'] ?? '';
    
    if (!empty($fotoVieja) && $fotoVieja !== 'no_image.png') {
        // Borrar archivo físico
        $rutaVieja = './img/perfiles/' . $fotoVieja;
        if (file_exists($rutaVieja)) {
            @unlink($rutaVieja);
        }
        
        // Actualizar BD a NULL
        // Nota: para pasar NULL a bind_param, usamos una variable con valor null
        $valNull = null;
        $fieldsToUpdate[] = "Foto = ?";
        $params[] = $valNull; 
        $types .= 's';
    }
}

// 6. EJECUTAR ACTUALIZACIÓN
if (empty($fieldsToUpdate)) {
    flash_set('success_msg', "No se detectaron cambios.");
    $mysqli->close();
    redirigirMisDatos();
}

$sql = "UPDATE USUARIOS SET " . implode(', ', $fieldsToUpdate) . " WHERE IdUsuario = ?";
$params[] = $userId;
$types .= 'i';

if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param($types, ...$params);
    if ($stmt->execute()) {
        if ($cambioSensible) {
            if (function_exists('invalidarTokensUsuario')) invalidarTokensUsuario($formData['original_user']);
            if (function_exists('borrarCookieRecordarme')) borrarCookieRecordarme(); 
            session_unset(); session_destroy();
            header("Location: ./login.php?mensaje=Datos actualizados correctamente. Por favor, inicia sesión de nuevo.");
            exit;
        } else {
            $_SESSION['user'] = $newUser; // Actualizar sesión por si cambió usuario (aunque arriba logout si cambia)
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