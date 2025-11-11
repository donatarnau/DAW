<form action="<?php echo $formAction; ?>" method="post" class="auth" enctype="multipart/form-data">
    
    <label for="reg-user">Nombre de usuario: *</label>
    <input type="text" name="user" placeholder="Nombre de usuario" id="reg-user" value="<?php echo $prevUser; ?>">
    <?php if (isset($errorUserEmpty) && $errorUserEmpty) echo '<p class="error-msg">Por favor, introduce un nombre de usuario.</p>'; ?>

    <label for="reg-pwd1">Contraseña: *</label>
    <input type="password" name="pwd" placeholder="Contraseña" id="reg-pwd1"> 
    <?php if (isset($errorPwd1Empty) && $errorPwd1Empty) echo '<p class="error-msg">Por favor, introduce una contraseña.</p>'; ?>

    <label for="reg-pwd2">Repetir contraseña: *</label>
    <input type="password" name="pwd2" placeholder="Repetir contraseña" id="reg-pwd2">
    <?php if (isset($errorPwd2Empty) && $errorPwd2Empty) echo '<p class="error-msg">Por favor, repite la contraseña.</p>'; ?>
    <?php if (isset($errorPwdMatch) && $errorPwdMatch) echo '<p class="error-msg">Las contraseñas no coinciden.</p>'; ?>
    
    <label for="reg-email">Dirección de email:</label>
    <input type="text" name="email" placeholder="Dirección de email" id="reg-email" value="<?php echo $prevEmail; ?>">

    <label for="reg-sexo">Sexo:</label>
    <select name="sexo" id="reg-sexo">
        <option value="" <?php if ($prevSexo === '') echo 'selected'; ?>>Sexo</option>
        <option value="0" <?php if ($prevSexo == '0') echo 'selected'; ?>>Mujer</option>
        <option value="1" <?php if ($prevSexo == '1') echo 'selected'; ?>>Hombre</option>
        <option value="2" <?php if ($prevSexo == '2') echo 'selected'; ?>>Otro</option>
    </select>

    <label for="fecha_nacimiento">Fecha de nacimiento:</label>
    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo $prevFecha; ?>">

    <label for="reg-ciudad">Ciudad de residencia:</label>
    <input type="text" name="ciudad" placeholder="Ciudad de residencia" id="reg-ciudad" value="<?php echo $prevCiudad; ?>">

    <label for="reg-pais">País de residencia:</label>
    <select name="pais" id="reg-pais">
        <option value="">Seleccione un país</option>
        <?php if (isset($paises) && is_array($paises)): ?>
            <?php foreach ($paises as $paisItem): ?>
                <option value="<?php echo $paisItem['IdPais']; ?>" 
                    <?php if ($prevPais == $paisItem['IdPais']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($paisItem['NomPais']); ?>
                </option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>
    
    <label for="reg-foto">Foto de perfil (opcional):</label>
    <?php if (isset($prevFoto) && !empty($prevFoto)): ?>
        <div style="margin-bottom: 10px;">
            <small>Foto actual: <?php echo htmlspecialchars($prevFoto); ?></small>
        </div>
    <?php endif; ?>
    <input type="file" name="foto" accept="image/*" id="reg-foto">
    
    <button type="submit"><?php echo $submitButtonText; ?></button>
</form>