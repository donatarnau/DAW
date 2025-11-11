<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require_once 'services/flashdata.php';

    // Función de redirección (necesaria para el bloque POST)
    function redirigir_local($pagina) {
        $host = $_SERVER['HTTP_HOST'];
        $uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\'); 
        if ($uri === '/' || $uri === '\\') $uri = '';
        header("Location: http://$host$uri/$pagina");
        exit; 
    }

    // --- 1. LÓGICA DE VALIDACIÓN (SI SE ENVÍA EL FORMULARIO POR POST) ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        // 1a. Recuperar datos
        $user = isset($_POST['user']) ? trim($_POST['user']) : '';
        $pwd1 = isset($_POST['pwd']) ? $_POST['pwd'] : '';
        $pwd2 = isset($_POST['pwd2']) ? $_POST['pwd2'] : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $sexo = $_POST['sexo'] ?? '';
        $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
        $ciudad = $_POST['ciudad'] ?? '';
        $pais = $_POST['pais'] ?? '';

        // 1b. Validaciones (MISMAS que en respuesta_registro.php)
        $errors = []; 
        if ($user === '') $errors['err_user'] = 1;
        if ($pwd1 === '') $errors['err_pwd1'] = 1;
        if ($pwd2 === '') $errors['err_pwd2'] = 1;
        if ($pwd1 !== '' && $pwd2 !== '' && $pwd1 !== $pwd2) $errors['err_match'] = 1;
        // (Aquí se podrían añadir más validaciones para email, fecha, etc. si se quisiera)

        // 1c. Decidir
        if (!empty($errors)) {
            // --- HAY ERRORES ---
            foreach ($errors as $flag => $val) flash_set($flag, $val);
            flash_set('val_user', $user);
            flash_set('val_email', $email);
            flash_set('val_sexo', $sexo);
            flash_set('val_fecha', $fecha_nacimiento);
            flash_set('val_ciudad', $ciudad);
            flash_set('val_pais', $pais);
            
            redirigir_local('mis_datos.php'); // Redirige de vuelta a esta misma página
        } else {
            // --- TODO CORRECTO ---
            // (Aquí irá el UPDATE en la BD en la próxima práctica)
            
            // Redirigir de vuelta sin mensajes (como pediste)
            redirigir_local('mis_datos.php');
        }
    }
    // --- FIN DE LA LÓGICA DE VALIDACIÓN ---


    // --- 2. LÓGICA DE VISTA (SE EJECUTA SIEMPRE EN GET, O DESPUÉS DE REDIRIGIR) ---

    // 2a. CONTROL DE ACCESO (Solo usuarios logueados)
    if (!isset($_SESSION['user']) || !isset($_SESSION['user_id'])) {
        header("Location: ./login.php?error=debes_iniciar_sesion");
        exit;
    }

    $userId = $_SESSION['user_id'];

    // 2b. LEER FLASHDATA (para saber si venimos de un error)
    $errorUserEmpty = (bool) flash_get('err_user');
    $errorPwd1Empty = (bool) flash_get('err_pwd1');
    $errorPwd2Empty = (bool) flash_get('err_pwd2');
    $errorPwdMatch = (bool) flash_get('err_match');
    $hasErrors = $errorUserEmpty || $errorPwd1Empty || $errorPwd2Empty || $errorPwdMatch;

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
    if ($hasErrors) {
        // Si hay un error, cargamos los datos que el usuario envió (desde flashdata)
        $prevUser   = htmlspecialchars(flash_get('val_user') ?? '');
        $prevEmail  = htmlspecialchars(flash_get('val_email') ?? '');
        $prevSexo   = flash_get('val_sexo') ?? '';
        $prevFecha  = htmlspecialchars(flash_get('val_fecha') ?? '');
        $prevCiudad = htmlspecialchars(flash_get('val_ciudad') ?? '');
        $prevPais   = flash_get('val_pais') ?? '';
        
        // La foto no se puede repoblar, así que la cargamos de la BD
        $stmtFoto = $mysqli->prepare("SELECT Foto FROM USUARIOS WHERE IdUsuario = ?");
        $stmtFoto->bind_param("i", $userId);
        $stmtFoto->execute();
        $stmtFoto->bind_result($dbFoto);
        $stmtFoto->fetch();
        $prevFoto = $dbFoto;
        $stmtFoto->close();

    } else {
        // Si no hay error, cargamos los datos frescos de la BD
        $sqlUser = "SELECT NomUsuario, Email, Sexo, FNacimiento, Ciudad, Pais, Foto FROM USUARIOS WHERE IdUsuario = ?";
        if ($stmt = $mysqli->prepare($sqlUser)) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->bind_result($dbUser, $dbEmail, $dbSexo, $dbFecha, $dbCiudad, $dbPais, $dbFoto);
            
            if ($stmt->fetch()) {
                $prevUser   = htmlspecialchars($dbUser);
                $prevEmail  = htmlspecialchars($dbEmail);
                $prevSexo   = $dbSexo;
                $prevFecha  = $dbFecha;
                $prevCiudad = htmlspecialchars($dbCiudad);
                $prevPais   = $dbPais;
                $prevFoto   = $dbFoto;
            } else {
                session_destroy();
                header("Location: ./login.php?error=usuario_no_encontrado");
                exit;
            }
            $stmt->close();
        } else {
            die("Error en la consulta de usuario: " . $mysqli->error);
        }
    }

    $mysqli->close();

    // 2f. CONFIGURACIÓN DEL FORMULARIO
    $formAction = "mis_datos.php"; // Apunta a sí mismo
    $submitButtonText = "Guardar cambios"; 

    // 2g. RENDERIZAR LA VISTA
    $titulo = "Mis Datos";
    $encabezado = "Mis Datos Personales";
    require 'cabecera.php';
?>

    <section class="forms">
        <h2>Mis datos registrados:</h2>
        
        <p>Aquí puedes ver y modificar los datos con los que te registraste.</p>
        
        <?php require 'services/form_usuario.php'; ?>

    </section>

<?php
    require 'pie.php';
?>