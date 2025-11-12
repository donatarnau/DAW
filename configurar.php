<?php
// Página para seleccionar estilo alternativo.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- 1. Requiere que el usuario esté logueado ---
// Verificamos 'user_id' que es lo que guarda control_acceso.php
if (!isset($_SESSION['user_id'])) {
    header('Location: ./login.php');
    exit;
}

// Guardamos el ID del usuario para las consultas
$userId = $_SESSION['user_id'];

// --- 2. Conexión a la Base de Datos ---
// Leemos la configuración del .ini (asumiendo que configurar.php está en la raíz, como login.php)
$config_path = './config.ini'; 
if (!file_exists($config_path)) {
    die("Error crítico: No se encuentra config.ini");
}
$config = parse_ini_file($config_path);

@$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
if ($mysqli->connect_errno) {
    die("Error de conexión a la BD: " . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');


// --- 3. Obtener TODOS los estilos disponibles de la BD ---
$allStyles = [];
$sqlEstilos = "SELECT IdEstilo, Nombre, Fichero FROM ESTILOS";
if ($result = $mysqli->query($sqlEstilos)) {
    while ($row = $result->fetch_assoc()) {
        $allStyles[] = $row;
    }
    $result->free();
}

// --- 4. Procesar envío (Guardar en BD) ---
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ahora el valor que recibimos es el IdEstilo (un número)
    $selId = isset($_POST['style']) ? (int)$_POST['style'] : 0;

    // Validar que el IdEstilo exista en nuestra lista
    $validStyle = null;
    foreach ($allStyles as $style) {
        if ($style['IdEstilo'] == $selId) {
            $validStyle = $style;
            break;
        }
    }

    if ($validStyle) {
        // --- GUARDAR EN BD ---
        // Actualizamos la tabla USUARIOS con el nuevo IdEstilo
        $stmt = $mysqli->prepare("UPDATE USUARIOS SET Estilo = ? WHERE IdUsuario = ?");
        $stmt->bind_param("ii", $validStyle['IdEstilo'], $userId);
        
        if ($stmt->execute()) {
            // --- ACTUALIZAR SESIÓN (para cambio inmediato) ---
            // control_acceso.php guarda la ruta en $_SESSION['style']
            $_SESSION['style'] = $validStyle['Fichero'];
            $message = 'Preferencia guardada: ' . htmlspecialchars($validStyle['Nombre']);
        } else {
            $message = 'Error al guardar la preferencia.';
        }
        $stmt->close();
    } else {
        $message = 'Selección inválida.';
    }
}

// --- 5. Obtener el estilo ACTUAL del usuario ---
$currentUserStyleId = 1; // Por defecto 'Estándar' (ID=1)
$stmt = $mysqli->prepare("SELECT Estilo FROM USUARIOS WHERE IdUsuario = ?");
$stmt->bind_param("i", $userId);
if ($stmt->execute()) {
    $stmt->bind_result($styleIdFromDB);
    if ($stmt->fetch()) {
        $currentUserStyleId = $styleIdFromDB;
    }
    $stmt->close();
}

// Variables para la cabecera
$titulo = 'Configurar estilos';
$encabezado = 'Configurar apariencia';
require 'cabecera.php'; // cabecera.php usará el $_SESSION['style'] actualizado
?>

    <section class="forms">
        <h2>Selecciona un estilo alternativo</h2>

        <?php if ($message !== ''): ?>
            <p class="info-msg"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form method="post" action="./configurar.php" class="lista-estilos">
            <fieldset>
                <legend>Estilos disponibles</legend>
                
                <?php foreach ($allStyles as $style): ?>
                    <?php
                    $styleId = (int)$style['IdEstilo'];
                    $label = htmlspecialchars($style['Nombre']);
                    // Marcamos el 'checked' comparando con el ID actual del usuario
                    $checked = ($currentUserStyleId === $styleId) ? 'checked' : '';
                    ?>
                    <div>
                        <label>
                            <input type="radio" name="style" value="<?php echo $styleId; ?>" <?php echo $checked; ?>>
                            <?php echo $label; ?>
                        </label>
                    </div>
                <?php endforeach; ?>
                
            </fieldset>

            <button type="submit">Guardar preferencia</button>
        </form>

        <p><a href="./perfil.php">Volver al perfil</a></p>
    </section>

<?php
require 'pie.php';
$mysqli->close(); // Cerrar la conexión
?>