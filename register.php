<?php
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (isset($_SESSION['user'])) { header("Location: ./index.php?error=acceso_denegado"); exit; }

    // 1. CONEXIÓN Y PAÍSES
    $config = parse_ini_file('config.ini');
    if (!$config) die("Error config");
    @$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
    if ($mysqli->connect_errno) die("Error BD");
    $mysqli->set_charset('utf8mb4');
    
    $paises = [];
    if ($res = $mysqli->query("SELECT IdPais, NomPais FROM PAISES ORDER BY NomPais")) {
        while ($row = $res->fetch_assoc()) $paises[] = $row;
        $res->close();
    }
    $mysqli->close();

    // 2. FLASHDATA
    require_once 'services/flashdata.php';
    
    // Recuperamos mensajes de error (cadenas de texto o null)
    $err_user = flash_get('err_user');
    $err_pwd1 = flash_get('err_pwd1');
    // En registro usamos 'err_pwd2' para la confirmación
    $err_pwd2 = flash_get('err_pwd2'); 
    $err_email = flash_get('err_email');
    $err_sexo = flash_get('err_sexo');
    $err_fecha = flash_get('err_fecha');
    
    // Recuperamos valores previos
    $prevUser   = htmlspecialchars(flash_get('val_user') ?? '');
    $prevEmail  = htmlspecialchars(flash_get('val_email') ?? '');
    $prevSexo   = flash_get('val_sexo') ?? '';
    $prevFecha  = htmlspecialchars(flash_get('val_fecha') ?? '');
    $prevCiudad = htmlspecialchars(flash_get('val_ciudad') ?? '');
    $prevPais   = flash_get('val_pais') ?? '';

    // 3. CONFIGURACIÓN FORMULARIO
    $formAction = "./respuesta_registro.php";
    $submitButtonText = "Registrarse";

    // 4. RENDER
    $titulo = "Registrarse";
    $encabezado = "Nuevo Usuario";
    require 'cabecera.php';
?>
    <section class="forms">
        <h2>Registro de nuevo usuario</h2>
        <!-- Importante: novalidate para probar la validación PHP -->
        <?php require 'services/form_usuario.php'; ?>
    </section>
<?php
    require 'pie.php';
?>