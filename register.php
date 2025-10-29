<?php
    // --- 1. Leer Errores y Valores Anteriores desde la URL ---
    $errorUserEmpty = isset($_GET['err_user']);
    $errorPwd1Empty = isset($_GET['err_pwd1']);
    $errorPwd2Empty = isset($_GET['err_pwd2']);
    $errorPwdMatch = isset($_GET['err_match']);

    // Obtener valores previos
    $prevUser = isset($_GET['val_user']) ? htmlspecialchars($_GET['val_user']) : '';
    $prevEmail = isset($_GET['val_email']) ? htmlspecialchars($_GET['val_email']) : '';
    $prevSexo = isset($_GET['val_sexo']) ? htmlspecialchars($_GET['val_sexo']) : '';
    $prevFecha = isset($_GET['val_fecha']) ? htmlspecialchars($_GET['val_fecha']) : '';
    $prevCiudad = isset($_GET['val_ciudad']) ? htmlspecialchars($_GET['val_ciudad']) : '';
    $prevPais = isset($_GET['val_pais']) ? htmlspecialchars($_GET['val_pais']) : '';

    // --- 2. Definir Variables para la Cabecera ---
    $titulo = "Registrarse";
    $encabezado = "Nuevo Usuario - Pisos e Inmuebles";
    include 'cabecera.php';
?>

    <!-- <main> lo abre cabecera.php -->
        <section class="forms">
            <h2>Complete los siguientes campos:</h2>

            <form action="./respuesta_registro.php" method="post" class="auth" enctype="multipart/form-data">
                
                <label for="reg-user">Nombre de usuario: *</label>
                <input type="text" name="user" placeholder="Nombre de usuario" id="reg-user" value="<?php echo $prevUser; ?>">
                <?php if ($errorUserEmpty) echo '<p class="error-msg">Por favor, introduce un nombre de usuario.</p>'; ?>

                <label for="reg-pwd1">Contraseña: *</label>
                <input type="password" name="pwd" placeholder="Contraseña" id="reg-pwd1"> 
                <?php if ($errorPwd1Empty) echo '<p class="error-msg">Por favor, introduce una contraseña.</p>'; ?>

                <label for="reg-pwd2">Repetir contraseña: *</label>
                <input type="password" name="pwd2" placeholder="Repetir contraseña" id="reg-pwd2">
                <?php if ($errorPwd2Empty) echo '<p class="error-msg">Por favor, repite la contraseña.</p>'; ?>
                <?php if ($errorPwdMatch) echo '<p class="error-msg">Las contraseñas no coinciden.</p>'; ?>
                
                <label for="reg-email">Dirección de email:</label>
                <input type="text" name="email" placeholder="Dirección de email" id="reg-email" value="<?php echo $prevEmail; ?>">

                <label for="reg-sexo">Sexo:</label>
                <select name="sexo" id="reg-sexo">
                    <option value="" <?php if ($prevSexo == '') echo 'selected'; ?>>Sexo</option>
                    <option value="hombre" <?php if ($prevSexo == 'hombre') echo 'selected'; ?>>Hombre</option>
                    <option value="mujer" <?php if ($prevSexo == 'mujer') echo 'selected'; ?>>Mujer</option>
                    <option value="otro" <?php if ($prevSexo == 'otro') echo 'selected'; ?>>Otro</option>
                </select>

                <label for="fecha_nacimiento">Fecha de nacimiento:</label>
                <input type="text" id="fecha_nacimiento" name="fecha_nacimiento" placeholder="dd/mm/aaaa" value="<?php echo $prevFecha; ?>">

                <label for="reg-ciudad">Ciudad de residencia:</label>
                <input type="text" name="ciudad" placeholder="Ciudad de residencia" id="reg-ciudad" value="<?php echo $prevCiudad; ?>">

                <label for="reg-pais">País de residencia:</label>
                <input type="text" name="pais" placeholder="País de residencia" id="reg-pais" value="<?php echo $prevPais; ?>">
                
                <label for="reg-foto">Foto de perfil (opcional):</label>
                <input type="file" name="foto" accept="image/*" id="reg-foto">
                
                <button type="submit">Registrarse</button>
            </form>        
        </section>
<?php
    include 'pie.php';
?>