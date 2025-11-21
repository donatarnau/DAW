<?php
session_start();
require_once 'services/flashdata.php';
require_once 'services/validar_usuario.php';

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

// 1. Comprobar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('register.php');
}

// 2. Recoger datos
$user = isset($_POST['user']) ? trim($_POST['user']) : '';
$pwd1 = isset($_POST['pwd']) ? $_POST['pwd'] : '';
$pwd2 = isset($_POST['pwd2']) ? $_POST['pwd2'] : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$sexo = $_POST['sexo'] ?? '';
$fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
$ciudad = trim($_POST['ciudad'] ?? '');
$pais = $_POST['pais'] ?? '';

$fotoName = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $fotoName = basename($_FILES['foto']['name']);
}

// 3. VALIDAR (Usamos la función estricta)
// true indica que estamos en modo Registro (validaciones obligatorias activas)
$errors = validar_datos_usuario($_POST, true);

// 4. GESTIÓN DE ERRORES
if (!empty($errors)) {
    // Guardar errores en flashdata
    foreach ($errors as $flag => $msg) {
        flash_set($flag, $msg);
    }
    // Guardar valores previos
    flash_set('val_user', $user);
    flash_set('val_email', $email);
    flash_set('val_sexo', $sexo);
    flash_set('val_fecha', $fecha_nacimiento);
    flash_set('val_ciudad', $ciudad);
    flash_set('val_pais', $pais);

    redirigir('register.php');

} else {
    // --- TODO CORRECTO: INSERCIÓN ---
    $config = parse_ini_file('config.ini');
    if (!$config) die("Error crítico: No se encuentra config.ini");

    @$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
    if ($mysqli->connect_errno) {
        die("Error de conexión a la BD: " . $mysqli->connect_error);
    }
    $mysqli->set_charset('utf8mb4');

    // Verificar duplicados
    $sqlCheck = "SELECT IdUsuario FROM USUARIOS WHERE NomUsuario = ?";
    if ($stmt = $mysqli->prepare($sqlCheck)) {
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->close();
            flash_set('err_user', "El nombre de usuario ya está en uso.");
            flash_set('val_user', $user);
            flash_set('val_email', $email);
            flash_set('val_sexo', $sexo);
            flash_set('val_fecha', $fecha_nacimiento);
            flash_set('val_ciudad', $ciudad);
            flash_set('val_pais', $pais);
            $mysqli->close();
            redirigir('register.php');
        }
        $stmt->close();
    }

    // Insertar usuario
    $estiloDefecto = 1;
    $sqlInsert = "INSERT INTO USUARIOS (NomUsuario, Clave, Email, Sexo, FNacimiento, Ciudad, Pais, Foto, Estilo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $mysqli->prepare($sqlInsert)) {
        $sexoInt = (int)$sexo;
        $paisInt = (int)$pais;
        if ($paisInt === 0) $paisInt = 1; 

        // HASHEAR CONTRASEÑA
        $pwdHash = password_hash($pwd1, PASSWORD_DEFAULT);

        $stmt->bind_param("sssisssii", $user, $pwdHash, $email, $sexoInt, $fecha_nacimiento, $ciudad, $paisInt, $fotoName, $estiloDefecto);
        
        if ($stmt->execute()) {
            $stmt->close();
            $mysqli->close();
            
            // Página de éxito
            $titulo = "Registro Completado";
            $encabezado = "Usuario Registrado";
            require 'cabecera.php';
            ?>
            <section id="resreg">
                <h2>¡Registro completado con éxito!</h2>
                <p>Tus datos han sido registrados:</p>
                
                <ul>
                    <li><strong>Nombre de usuario:</strong> <?php echo htmlspecialchars($user); ?></li>
                    <li><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></li>
                    <li><strong>Sexo:</strong> <?php 
                        if ($sexo == '0') echo 'Mujer';
                        elseif ($sexo == '1') echo 'Hombre';
                        elseif ($sexo == '2') echo 'Otro';
                    ?></li>
                    <li><strong>Fecha de nacimiento:</strong> <?php echo htmlspecialchars($fecha_nacimiento); ?></li>
                    <li><strong>Ciudad:</strong> <?php echo htmlspecialchars($ciudad); ?></li>
                    <li><strong>País:</strong> <?php echo htmlspecialchars($pais); // Nota: es el ID, idealmente harías un JOIN o query extra para sacar el nombre ?></li>
                </ul>
        
                <p><a href="login.php">Ahora puedes iniciar sesión con tu nueva cuenta.</a></p>
            </section>
            <?php
            require 'pie.php';
            exit;

        } else {
            die("Error al registrar: " . $stmt->error);
        }
    } else {
        die("Error al preparar sentencia: " . $mysqli->error);
    }
    $mysqli->close();
}
?>