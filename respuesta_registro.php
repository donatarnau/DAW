<?php
session_start();
require_once 'services/flashdata.php';
require_once 'services/validar_usuario.php';
require_once 'services/gestor_imagenes.php';

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

// 3. NORMALIZAR FECHA
$fecha_sql = null;
if (!empty($fecha_nacimiento)) {
    $parts = preg_split('/[\/\-.\s]+/', trim($fecha_nacimiento));
    if (count($parts) === 3) {
        $d = (int)$parts[0]; $m = (int)$parts[1]; $y = (int)$parts[2];
        if ($y > 0 && $m >= 1 && $m <= 12 && $d >= 1 && $d <= 31) {
            $fecha_sql = sprintf('%04d-%02d-%02d', $y, $m, $d);
        }
    } else {
        $fecha_sql = $fecha_nacimiento;
    }
}

// 4. VALIDAR
$postNorm = $_POST;
if (!empty($fecha_sql)) {
    $postNorm['fecha_nacimiento'] = $fecha_sql;
}
$errors = validar_datos_usuario($postNorm, true);

// 5. GESTIÓN DE ERRORES
if (!empty($errors)) {
    foreach ($errors as $flag => $msg) flash_set($flag, $msg);
    flash_set('val_user', $user);
    flash_set('val_email', $email);
    flash_set('val_sexo', $sexo);
    flash_set('val_fecha', $fecha_nacimiento);
    flash_set('val_ciudad', $ciudad);
    flash_set('val_pais', $pais);
    redirigir('register.php');
} else {
    // --- TODO CORRECTO ---
    $config = parse_ini_file('config.ini');
    if (!$config) die("Error crítico: No se encuentra config.ini");

    @$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
    if ($mysqli->connect_errno) die("Error BD: " . $mysqli->connect_error);
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

    // Insertar usuario (Inicialmente sin foto)
    $estiloDefecto = 1;
    $fotoInicial = null; 
    $sqlInsert = "INSERT INTO USUARIOS (NomUsuario, Clave, Email, Sexo, FNacimiento, Ciudad, Pais, Foto, Estilo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $mysqli->prepare($sqlInsert)) {
        $sexoInt = (int)$sexo;
        $paisInt = (int)$pais;
        if ($paisInt === 0) $paisInt = 1; 
        $pwdHash = password_hash($pwd1, PASSWORD_DEFAULT);

        $stmt->bind_param("sssissisi", $user, $pwdHash, $email, $sexoInt, $fecha_sql, $ciudad, $paisInt, $fotoInicial, $estiloDefecto);
        
        if ($stmt->execute()) {
            $newUserId = $mysqli->insert_id; // ID GENERADO
            $stmt->close();

            $nombreFinalFoto = ""; 

            // --- PROCESAR FOTO ---
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                
                $nombreBase = 'perfil' . $newUserId;
                $directorioDestino = './img/perfiles/';

                $resFoto = subir_y_convertir_a_jpg($_FILES['foto'], $directorioDestino, $nombreBase);

                if ($resFoto['ok']) {
                    $nombreFinalFoto = $resFoto['fileName']; // ej: perfil8.jpg
                    
                    // Actualizar BD con el nombre real
                    $sqlUpdate = "UPDATE USUARIOS SET Foto = ? WHERE IdUsuario = ?";
                    if ($stmtUp = $mysqli->prepare($sqlUpdate)) {
                        $stmtUp->bind_param("si", $nombreFinalFoto, $newUserId);
                        $stmtUp->execute();
                        $stmtUp->close();
                    }
                }
            }

            $mysqli->close();
            
            // ÉXITO
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
                    <li><strong>Ciudad:</strong> <?php echo htmlspecialchars($ciudad); ?></li>
                    <li><strong>Foto de perfil:</strong><br>
                        <?php if (!empty($nombreFinalFoto)): ?>
                            <img src="./img/perfiles/<?php echo htmlspecialchars($nombreFinalFoto); ?>" alt="Tu foto de perfil" style="max-width: 200px; border-radius: 10px; border: 2px solid #ccc; margin-top: 10px;">
                        <?php else: ?>
                            <span style="color: grey; font-style: italic;">No has subido ninguna foto (o hubo un error). Se usará la imagen por defecto.</span>
                        <?php endif; ?>
                    </li>
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