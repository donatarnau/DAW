<!-- Se añade 'novalidate' para desactivar validación HTML5 y probar la de PHP -->
<form action="<?php echo $formAction; ?>" method="post" class="auth" enctype="multipart/form-data" novalidate>
    
    <label for="reg-user">Nombre de usuario: *</label>
    <input type="text" name="user" placeholder="Nombre de usuario (letras y números)" id="reg-user" value="<?php echo $prevUser; ?>">
    <?php if (!empty($err_user)) echo '<p class="error-msg">' . htmlspecialchars($err_user) . '</p>'; ?>

    <label for="reg-pwd1">Contraseña: *</label>
    <input type="password" name="pwd" placeholder="Contraseña (Min 6 car., Mayus, Minus, Num)" id="reg-pwd1"> 
    <?php if (!empty($err_pwd1)) echo '<p class="error-msg">' . htmlspecialchars($err_pwd1) . '</p>'; ?>

    <label for="reg-pwd2">Repetir contraseña: *</label>
    <input type="password" name="pwd2" placeholder="Repetir contraseña" id="reg-pwd2">
    <?php if (!empty($err_pwd2)) echo '<p class="error-msg">' . htmlspecialchars($err_pwd2) . '</p>'; ?>
    <?php if (!empty($err_match)) echo '<p class="error-msg">' . htmlspecialchars($err_match) . '</p>'; ?>
    
    <label for="reg-email">Dirección de email: *</label>
    <input type="text" name="email" placeholder="Dirección de email" id="reg-email" value="<?php echo $prevEmail; ?>">
    <?php if (!empty($err_email)) echo '<p class="error-msg">' . htmlspecialchars($err_email) . '</p>'; ?>

    <label for="reg-sexo">Sexo: *</label>
    <select name="sexo" id="reg-sexo">
        <option value="" <?php if ($prevSexo === '') echo 'selected'; ?>>Selecciona sexo</option>
        <option value="0" <?php if ($prevSexo === '0') echo 'selected'; ?>>Mujer</option>
        <option value="1" <?php if ($prevSexo === '1') echo 'selected'; ?>>Hombre</option>
        <option value="2" <?php if ($prevSexo === '2') echo 'selected'; ?>>Otro</option>
    </select>
    <?php if (!empty($err_sexo)) echo '<p class="error-msg">' . htmlspecialchars($err_sexo) . '</p>'; ?>

    <label for="fecha_nacimiento">Fecha de nacimiento: *</label>
    <input type="text" id="fecha_nacimiento" name="fecha_nacimiento" placeholder="dd/mm/aaaa" value="<?php echo $prevFecha; ?>">
    <?php if (!empty($err_fecha)) echo '<p class="error-msg">' . htmlspecialchars($err_fecha) . '</p>'; ?>

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