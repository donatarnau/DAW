<?php
/*
 * Fichero: login.php
 * (Reemplaza a login.html)
 */

// 1. GESTIONAR ERRORES PARA ESTA PÁGINA
// (Usamos variables locales para no chocar con la cabecera)
$loginErrorUser = isset($_GET['err_user']);
$loginErrorPass = isset($_GET['err_pass']);
$loginErrorLogin = isset($_GET['err_login']);
$loginValueUser = isset($_GET['val_user']) ? htmlspecialchars($_GET['val_user']) : '';

// 2. DEFINIR VARIABLES PARA LA CABECERA
$titulo = "Iniciar sesión";
$encabezado = "Inicio de Sesión - Pisos e Inmuebles";
// Incluimos la cabecera
include 'cabecera.php';
?>

<section class="forms">
    <h2>Complete los siguientes campos:</h2>
    
    <form action="./services/control_acceso.php" method="post" class="auth" id="login-form">

        <input type="hidden" name="login_source" value="login.php">

        <input type="text" name="user" placeholder="Nombre de usuario" id="login-user" value="<?php echo $loginValueUser; ?>">
        <?php if ($loginErrorUser) echo '<label class="fl-ad" style="display:flex; color:red;">Rellena este campo</label>'; ?>

        <input type="password" name="pwd" placeholder="Contraseña" id="login-pass">
        <?php if ($loginErrorPass) echo '<label class="fl-ad" style="display:flex; color:red;">Rellena este campo</label>'; ?>
        
        <?php if ($loginErrorLogin) echo '<label class="fl-ad" style="display:flex; color:red;">Usuario o contraseña incorrectos</label>'; ?>

        <button type="submit" id="login-btn">Iniciar sesión</button>
    </form>
    <a href="./register.php">¿No tienes cuenta? Regístrate pinchando aquí</a>
</section>

<?php
    // Incluimos el pie de página
    include 'pie.php';
?>