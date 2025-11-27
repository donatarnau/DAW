<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require_once 'services/flashdata.php';
    // Se incluye recordarme.php por si hace falta la función de borrarCookieRecordarme

    // Función de redirección
    function redirigir_local($pagina) {
        $host = $_SERVER['HTTP_HOST'];
        $uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\'); 
        if ($uri === '/' || $uri === '\\') $uri = '';
        header("Location: http://$host$uri/$pagina");
        exit; 
    }

    // --- 1. LÓGICA DE VALIDACIÓN (BORRADA - ahora está en respuesta_mis_datos.php) ---
    // La lógica de procesamiento POST ha sido movida a respuesta_mis_datos.php

    // --- 2. LÓGICA DE VISTA ---

    // 2a. CONTROL DE ACCESO (Solo usuarios logueados)
    if (!isset($_SESSION['user']) || !isset($_SESSION['user_id'])) {
        header("Location: ./login.php?error=debes_iniciar_sesion");
        exit;
    }

    $userId = $_SESSION['user_id'];

    // 2b. LEER FLASHDATA
    $err_user = flash_get('err_user');
    $err_pwd_new = flash_get('err_pwd_new');
    $err_pwd_match = flash_get('err_pwd_match');
    $err_pwd_old = flash_get('err_pwd_old');
    $err_email = flash_get('err_email');
    $err_sexo = flash_get('err_sexo');
    $err_fecha = flash_get('err_fecha');
    $mensaje_exito = flash_get('success_msg');


    // 2c. CONEXIÓN A LA BD
    $config = parse_ini_file('config.ini');
    if (!$config) die("Error al leer config.ini");
    @$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
    if ($mysqli->connect_errno) die("Error de conexión a la BD: " . $mysqli->connect_error);

    // 2d. OBTENER PAÍSES (para el desplegable)
    $paises = [];
    if ($res = $mysqli->query("SELECT IdPais, NomPais FROM PAISES ORDER BY NomPais")) {
        while ($row = $res->fetch_assoc()) $paises[] = $row;
        $res->close();
    }

    // 2e. OBTENER DATOS PARA EL FORMULARIO
    $usuario = null;
    $sqlUser = "SELECT NomUsuario, Clave, Email, Sexo, FNacimiento, Ciudad, Pais, Foto FROM USUARIOS WHERE IdUsuario = ?";
    if ($stmt = $mysqli->prepare($sqlUser)) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 1) {
            $usuario = $res->fetch_assoc();
        } else {
            session_destroy();
            header("Location: ./login.php?error=usuario_no_encontrado");
            exit;
        }
        $stmt->close();
    } else {
        die("Error en la consulta de usuario: " . $mysqli->error);
    }
    
    // Usar valores de POST si falló la validación, sino, usar los de la BD
    $prevUser   = htmlspecialchars(flash_get('val_user') ?? $usuario['NomUsuario']);
    $prevEmail  = htmlspecialchars(flash_get('val_email') ?? $usuario['Email']);
    $prevSexo   = flash_get('val_sexo') ?? $usuario['Sexo'];
    
    // Convertir fecha de BD (YYYY-MM-DD) a formato dd/mm/aaaa para mostrar
    $fechaBD = flash_get('val_fecha') ?? $usuario['FNacimiento'];
    if (!empty($fechaBD) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaBD)) {
        $fechaObj = DateTime::createFromFormat('Y-m-d', $fechaBD);
        if ($fechaObj) {
            $prevFecha = $fechaObj->format('d/m/Y');
        } else {
            $prevFecha = htmlspecialchars($fechaBD);
        }
    } else {
        $prevFecha = htmlspecialchars($fechaBD);
    }
    
    $prevCiudad = htmlspecialchars(flash_get('val_ciudad') ?? $usuario['Ciudad']);
    $prevPais   = flash_get('val_pais') ?? $usuario['Pais'];
    $prevFoto   = $usuario['Foto'];

    $mysqli->close();

    // 2f. CONFIGURACIÓN DEL FORMULARIO
    $titulo = "Mis Datos";
    $encabezado = "Mis Datos Personales";
    require 'cabecera.php';
?>

    <section class="forms">
        <h2>Modificar mis datos</h2>
        
        <?php if ($mensaje_exito): ?>
            <div id="resreg">
                <p><strong><?php echo htmlspecialchars($mensaje_exito); ?></strong></p>
            </div>
        <?php endif; ?>

        <form action="./respuesta_mis_datos.php" method="post" class="auth" enctype="multipart/form-data" novalidate>
            
            <label for="reg-user">Nombre de usuario: *</label>
            <input type="text" name="user" id="reg-user" value="<?php echo $prevUser; ?>">
            <?php if (!empty($err_user)) echo '<p class="error-msg">' . htmlspecialchars($err_user) . '</p>'; ?>

            <label for="reg-email">Dirección de email: *</label>
            <input type="text" name="email" id="reg-email" value="<?php echo $prevEmail; ?>">
            <?php if (!empty($err_email)) echo '<p class="error-msg">' . htmlspecialchars($err_email) . '</p>'; ?>

            
                <legend>Modificar contraseña (Opcional)</legend>
                <label for="reg-new-pwd1">Nueva Contraseña:</label>
                <input type="password" name="new_pwd" id="reg-new-pwd1" placeholder="Solo rellenar para cambiar"> 
                <?php if (!empty($err_pwd_new)) echo '<p class="error-msg">' . htmlspecialchars($err_pwd_new) . '</p>'; ?>

                <label for="reg-new-pwd2">Repetir Nueva Contraseña:</label>
                <input type="password" name="new_pwd2" id="reg-new-pwd2">
                <?php if (!empty($err_pwd_match)) echo '<p class="error-msg">' . htmlspecialchars($err_pwd_match) . '</p>'; ?>
            

            <label for="reg-sexo">Sexo: *</label>
            <select name="sexo" id="reg-sexo">
                <option value="0" <?php if ($prevSexo == '0') echo 'selected'; ?>>Mujer</option>
                <option value="1" <?php if ($prevSexo == '1') echo 'selected'; ?>>Hombre</option>
                <option value="2" <?php if ($prevSexo == '2') echo 'selected'; ?>>Otro</option>
            </select>
            <?php if (!empty($err_sexo)) echo '<p class="error-msg">' . htmlspecialchars($err_sexo) . '</p>'; ?>

            <label for="fecha_nacimiento">Fecha de nacimiento: *</label>
            <input type="text" id="fecha_nacimiento" name="fecha_nacimiento" placeholder="dd/mm/aaaa" value="<?php echo $prevFecha; ?>">
            <?php if (!empty($err_fecha)) echo '<p class="error-msg">' . htmlspecialchars($err_fecha) . '</p>'; ?>

            <label for="reg-ciudad">Ciudad de residencia:</label>
            <input type="text" name="ciudad" id="reg-ciudad" value="<?php echo $prevCiudad; ?>">

            <label for="reg-pais">País de residencia:</label>
            <select name="pais" id="reg-pais">
                <option value="">Seleccione un país</option>
                <?php foreach ($paises as $paisItem): ?>
                    <option value="<?php echo $paisItem['IdPais']; ?>" 
                        <?php if ($prevPais == $paisItem['IdPais']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($paisItem['NomPais']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <label for="reg-foto">Foto de perfil (opcional):</label>
            <?php if (!empty($prevFoto)): ?>
                <div style="margin-bottom: 10px;">
                    <small>Foto actual: <?php echo htmlspecialchars($prevFoto); ?></small>
                </div>
            <?php endif; ?>
            <input type="file" name="foto" accept="image/*" id="reg-foto">

            <hr style="margin: 20px 0;">
            <label for="current_pwd">Contraseña actual para confirmar: *</label>
            <input type="password" name="current_pwd" id="current_pwd" placeholder="Tu contraseña actual">
            <?php if (!empty($err_pwd_old)) echo '<p class="error-msg">' . htmlspecialchars($err_pwd_old) . '</p>'; ?>
            
            <button type="submit">Guardar cambios</button>

            <input type="hidden" name="original_user" value="<?php echo htmlspecialchars($usuario['NomUsuario']); ?>">
            <input type="hidden" name="original_pwd" value="<?php echo htmlspecialchars($usuario['Clave']); ?>">
            <input type="hidden" name="original_sexo" value="<?php echo htmlspecialchars($usuario['Sexo']); ?>">
            <input type="hidden" name="original_fecha" value="<?php echo htmlspecialchars($usuario['FNacimiento']); ?>">
            <input type="hidden" name="original_email" value="<?php echo htmlspecialchars($usuario['Email']); ?>">
            <input type="hidden" name="original_foto" value="<?php echo htmlspecialchars($usuario['Foto'] ?? ''); ?>">

        </form>
        <p style="text-align:center; margin-top:20px;"><a href="perfil.php">Cancelar y volver al perfil</a></p>
    </section>
<?php
    require 'pie.php';
?>