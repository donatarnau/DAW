<?php
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (isset($_SESSION['user'])) { header("Location: ./index.php?error=acceso_denegado"); exit; }

    // 1. CONEXIÓN Y PAÍSES
    $config = parse_ini_file('config.ini');
    if (!$config) die("Error config");
    @$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
    if ($mysqli->connect_errno) die("Error BD");
    $paises = [];
    if ($res = $mysqli->query("SELECT IdPais, NomPais FROM PAISES ORDER BY NomPais")) {
        while ($row = $res->fetch_assoc()) $paises[] = $row;
        $res->close();
    }
    $mysqli->close();

    // 2. FLASHDATA (Errores y valores previos si falló la validación)
    require_once 'services/flashdata.php';
    $errorUserEmpty = (bool) flash_get('err_user');
    $errorPwd1Empty = (bool) flash_get('err_pwd1');
    $errorPwd2Empty = (bool) flash_get('err_pwd2');
    $errorPwdMatch  = (bool) flash_get('err_match');

    $prevUser   = htmlspecialchars(flash_get('val_user') ?? '');
    $prevEmail  = htmlspecialchars(flash_get('val_email') ?? '');
    $prevSexo   = flash_get('val_sexo') ?? '';
    $prevFecha  = htmlspecialchars(flash_get('val_fecha') ?? '');
    $prevCiudad = htmlspecialchars(flash_get('val_ciudad') ?? '');
    $prevPais   = flash_get('val_pais') ?? '';

    // 3. CONFIGURACIÓN FORMULARIO
    $formAction = "./respuesta_registro.php";
    $submitButtonText = "Registrarse";
    // NO definimos $userId para indicar modo registro

    // 4. RENDER
    $titulo = "Registrarse";
    $encabezado = "Nuevo Usuario";
    require 'cabecera.php';
?>
    <section class="forms">
        <h2>Registro de nuevo usuario</h2>
        <?php require 'services/form_usuario.php'; ?>
    </section>
<?php
    require 'pie.php';
?>