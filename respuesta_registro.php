<?php
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

// 1. Comprobar que se reciben datos por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Si se accede directamente, redirigir al formulario
    redirigir('register.php');
}

// 2. Recuperar los datos del formulario ($_POST)
// Usamos trim() para eliminar espacios en blanco al inicio/final
$user = isset($_POST['user']) ? trim($_POST['user']) : '';
$pwd1 = isset($_POST['pwd']) ? $_POST['pwd'] : ''; // No usamos trim en contraseñas
$pwd2 = isset($_POST['pwd2']) ? $_POST['pwd2'] : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$sexo = $_POST['sexo'] ?? '';
$fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
$ciudad = $_POST['ciudad'] ?? '';
$pais = $_POST['pais'] ?? '';
// Ignoramos $_FILES['foto'] según la práctica

// 3. Realizar las Validaciones Requeridas por la Práctica
$errors = []; // Array para guardar los errores
$params = []; // Array para los parámetros de redirección

// a) Comprobar campos obligatorios (usuario, pwd1, pwd2)
if ($user === '') {
    $errors['err_user'] = 1; // Flag de error para usuario vacío
}
if ($pwd1 === '') {
    $errors['err_pwd1'] = 1; // Flag de error para contraseña 1 vacía
}
if ($pwd2 === '') {
    $errors['err_pwd2'] = 1; // Flag de error para contraseña 2 vacía
}

// b) Comprobar si las contraseñas coinciden (solo si ambas se introdujeron)
if ($pwd1 !== '' && $pwd2 !== '' && $pwd1 !== $pwd2) {
    $errors['err_match'] = 1; // Flag de error para contraseñas no coincidentes
}

// 4. Decidir qué hacer según las validaciones
if (!empty($errors)) {
    // --- HAY ERRORES ---
    // Preparamos los parámetros para la URL de redirección
    $params = $errors; // Incluimos todos los flags de error

    // Añadimos los valores introducidos por el usuario (excepto contraseñas)
    if ($user !== '') $params['val_user'] = $user;
    if ($email !== '') $params['val_email'] = $email;
    if ($sexo !== '') $params['val_sexo'] = $sexo;
    if ($fecha_nacimiento !== '') $params['val_fecha'] = $fecha_nacimiento;
    if ($ciudad !== '') $params['val_ciudad'] = $ciudad;
    if ($pais !== '') $params['val_pais'] = $pais;
    
    // Redirigimos DE VUELTA a register.php con los errores y valores
    redirigir('register.php', $params);

} else {
    // --- TODO CORRECTO ---
    // Mostramos la página de confirmación

    // Definimos variables para la cabecera
    $titulo = "Registro Completado";
    $encabezado = "Usuario Registrado - Pisos e Inmuebles";
    
    // Incluimos la cabecera (mostrará la versión pública)
    include 'cabecera.php';
?>
    <!-- <main> lo abre cabecera.php -->
    <section id="resreg">
        <h2>¡Registro completado con éxito!</h2>
        <p>Tus datos han sido registrados:</p>
        
        <ul>
            <li><strong>Nombre de usuario:</strong> <?php echo htmlspecialchars($user); ?></li>
            <li><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></li>
            <li><strong>Sexo:</strong> <?php echo htmlspecialchars($sexo); ?></li>
            <li><strong>Fecha de nacimiento:</strong> <?php echo htmlspecialchars($fecha_nacimiento); ?></li>
            <li><strong>Ciudad:</strong> <?php echo htmlspecialchars($ciudad); ?></li>
            <li><strong>País:</strong> <?php echo htmlspecialchars($pais); ?></li>
            <li><em>(La contraseña no se muestra por seguridad.)</em></li>
            <li><em>(La foto de perfil no se procesa en esta práctica.)</em></li>
        </ul>

        <p><a href="login.php">Ahora puedes iniciar sesión con tu nueva cuenta.</a></p>
    </section>
<?php
    include 'pie.php';
}
?>