<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require_once 'services/flashdata.php';

    // Función de redirección local para evitar bucles
    function redirigir_local($pagina) {
        $host = $_SERVER['HTTP_HOST'];
        $uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\'); 
        if ($uri === '/' || $uri === '\\') $uri = '';
        header("Location: http://$host$uri/$pagina");
        exit; 
    }

    // --- 1. CONTROL DE ACCESO ---
    // Corrección del bucle de redirección: Si hay 'user' pero no 'user_id', la sesión es inválida.
    if (isset($_SESSION['user']) && !isset($_SESSION['user_id'])) {
        session_unset();
        session_destroy();
        header("Location: ./login.php?error=sesion_caducada");
        exit;
    }

    if (!isset($_SESSION['user_id'])) {
        header("Location: ./login.php?error=debes_iniciar_sesion");
        exit;
    }

    $userId = $_SESSION['user_id'];
    $username = $_SESSION['user']; // Definimos username para el alt de la imagen

    // --- 2. LEER FLASHDATA ---
    $err_user = flash_get('err_user');
    $err_pwd_new = flash_get('err_pwd_new'); // Corregido nombre variable para coincidir con lógica original
    $err_pwd_match = flash_get('err_pwd_match');
    $err_pwd_old = flash_get('err_pwd_old');
    $err_email = flash_get('err_email');
    $err_sexo = flash_get('err_sexo');
    $err_fecha = flash_get('err_fecha');
    $mensaje_exito = flash_get('success_msg');
    $mensaje_error = flash_get('wrong'); // Para errores generales

    // --- 3. CONEXIÓN A LA BD ---
    $config = parse_ini_file('config.ini');
    if (!$config) die("Error al leer config.ini");
    @$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
    if ($mysqli->connect_errno) die("Error de conexión a la BD: " . $mysqli->connect_error);

    // --- 4. OBTENER DATOS ---
    $paises = [];
    if ($res = $mysqli->query("SELECT IdPais, NomPais FROM PAISES ORDER BY NomPais")) {
        while ($row = $res->fetch_assoc()) $paises[] = $row;
        $res->close();
    }

    // Datos del usuario
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
    
    $mysqli->close();

    // --- 5. PREPARAR VALORES (Sticky Form o BD) ---
    $prevUser   = htmlspecialchars(flash_get('val_user') ?? $usuario['NomUsuario']);
    $prevEmail  = htmlspecialchars(flash_get('val_email') ?? $usuario['Email']);
    $prevSexo   = flash_get('val_sexo') ?? $usuario['Sexo'];
    
    $fechaBD = flash_get('val_fecha') ?? $usuario['FNacimiento'];
    // Formatear fecha para el input (dd/mm/aaaa) si viene de BD
    if (!empty($fechaBD) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaBD)) {
        $fechaObj = DateTime::createFromFormat('Y-m-d', $fechaBD);
        $prevFecha = $fechaObj ? $fechaObj->format('d/m/Y') : htmlspecialchars($fechaBD);
    } else {
        $prevFecha = htmlspecialchars($fechaBD);
    }
    
    $prevCiudad = htmlspecialchars(flash_get('val_ciudad') ?? $usuario['Ciudad']);
    $prevPais   = flash_get('val_pais') ?? $usuario['Pais'];
    $prevFoto   = $usuario['Foto'];

    // --- 6. RENDERIZAR ---
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
        
        <?php if ($mensaje_error): ?>
             <p class="error-msg" style="text-align:center"><?php echo htmlspecialchars($mensaje_error); ?></p>
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
            
            <?php if (!empty($prevFoto) && $prevFoto !== 'no_image.png'): ?>
                <!-- Muestra la foto actual -->
                <img src="img/perfiles/<?php echo htmlspecialchars($prevFoto); ?>?t=<?=time()?>" alt="Foto de perfil de <?php echo $username; ?>" class="perfil-foto">
                <br>
                <!-- Botón para eliminar foto (NO ES SUBMIT, ES TYPE BUTTON) -->
                <button type="button" id="btn-borrar-foto" style="margin-top: 5px; background-color: #c0392b; width: auto; padding: 5px 10px; font-size: 1.2rem;">Eliminar foto</button>
            <?php endif; ?>
            
            <!-- Input oculto para marcar el borrado (0=no, 1=sí) -->
            <input type="hidden" name="borrar_foto_flag" id="borrar_foto_flag" value="0">
            
            <input type="file" name="foto" accept="image/*" id="reg-foto">

            <hr style="margin: 20px 0;">
            <label for="current_pwd">Contraseña actual para confirmar: *</label>
            <input type="password" name="current_pwd" id="current_pwd" placeholder="Tu contraseña actual">
            <?php if (!empty($err_pwd_old)) echo '<p class="error-msg">' . htmlspecialchars($err_pwd_old) . '</p>'; ?>
            
            <button type="submit">Guardar cambios</button>

            <!-- Inputs ocultos para detectar cambios -->
            <input type="hidden" name="original_user" value="<?php echo htmlspecialchars($usuario['NomUsuario']); ?>">
            <input type="hidden" name="original_pwd" value="<?php echo htmlspecialchars($usuario['Clave']); ?>">
            <input type="hidden" name="original_sexo" value="<?php echo htmlspecialchars($usuario['Sexo']); ?>">
            <input type="hidden" name="original_fecha" value="<?php echo htmlspecialchars($usuario['FNacimiento']); ?>">
            <input type="hidden" name="original_email" value="<?php echo htmlspecialchars($usuario['Email']); ?>">
            <input type="hidden" name="original_foto" value="<?php echo htmlspecialchars($usuario['Foto'] ?? ''); ?>">

        </form>
        <p style="text-align:center; margin-top:20px;"><a href="perfil.php">Cancelar y volver al perfil</a></p>
    </section>

    <!-- Script para gestionar el botón de eliminar foto visualmente -->
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const btnBorrar = document.getElementById('btn-borrar-foto');
        const inputFlag = document.getElementById('borrar_foto_flag');
        const imagenPerfil = document.querySelector('.perfil-foto');

        if (btnBorrar) {
            btnBorrar.addEventListener('click', function() {
                // 1. Ocultar la imagen visualmente
                if (imagenPerfil) imagenPerfil.style.display = 'none';
                
                // 2. Ocultar el botón de borrar
                btnBorrar.style.display = 'none';
                
                // 3. Marcar el input oculto a 1
                inputFlag.value = '1';
            });
        }
    });
    </script>

<?php
    require 'pie.php';
?>