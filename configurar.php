<?php
/**
 * configurar.php
 * Permite al usuario seleccionar y guardar su estilo preferido en la base de datos.
 * Actúa como formulario y como página de respuesta.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. CONTROL DE ACCESO
if (!isset($_SESSION['user']) || !isset($_SESSION['user_id'])) {
    header("Location: ./login.php?error=debes_iniciar_sesion");
    exit;
}

$userId = $_SESSION['user_id'];
$mensaje = '';

// 2. CONEXIÓN A LA BD
$config = parse_ini_file('config.ini');
if (!$config) die("Error crítico: No se encuentra config.ini");

@$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
if ($mysqli->connect_errno) {
    die("Error de conexión a la BD: " . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

// 3. OBTENER ESTILOS DISPONIBLES
$estilos = [];
if ($res = $mysqli->query("SELECT IdEstilo, Nombre, Fichero, Descripcion FROM ESTILOS ORDER BY IdEstilo ASC")) {
    while ($row = $res->fetch_assoc()) {
        $estilos[] = $row;
    }
    $res->close();
}

// 4. PROCESAR FORMULARIO (POST) -> "Respuesta Página Configurar"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevoEstiloId = isset($_POST['estilo']) ? (int)$_POST['estilo'] : 0;
    
    // Validar que el estilo existe en nuestro array cargado
    $estiloSeleccionado = null;
    foreach ($estilos as $est) {
        if ((int)$est['IdEstilo'] === $nuevoEstiloId) {
            $estiloSeleccionado = $est;
            break;
        }
    }

    if ($estiloSeleccionado) {
        // Actualizar en Base de Datos
        $stmtUpdate = $mysqli->prepare("UPDATE USUARIOS SET Estilo = ? WHERE IdUsuario = ?");
        $stmtUpdate->bind_param("ii", $nuevoEstiloId, $userId);
        
        if ($stmtUpdate->execute()) {
            // Actualizar variable de SESIÓN para que el cambio sea inmediato (requisito del enunciado)
            $_SESSION['style'] = $estiloSeleccionado['Fichero'];
            
            $mensaje = "¡Estilo actualizado correctamente a: " . htmlspecialchars($estiloSeleccionado['Nombre']) . "!";
        } else {
            $mensaje = "Error al guardar el estilo en la base de datos.";
        }
        $stmtUpdate->close();
    } else {
        $mensaje = "Estilo no válido.";
    }
}

// 5. OBTENER ESTILO ACTUAL (Para marcarlo en el formulario)
// Consultamos la BD para estar siempre sincronizados
$estiloActualId = 1; // Valor por defecto (Estándar)
$stmtUser = $mysqli->prepare("SELECT Estilo FROM USUARIOS WHERE IdUsuario = ?");
$stmtUser->bind_param("i", $userId);
$stmtUser->execute();
$stmtUser->bind_result($dbEstiloId);
if ($stmtUser->fetch()) {
    $estiloActualId = $dbEstiloId;
}
$stmtUser->close();

$mysqli->close();

// 6. RENDERIZAR PÁGINA
// La cabecera usará $_SESSION['style'], que acabamos de actualizar si hubo POST.
$titulo = "Configurar Estilo";
$encabezado = "Configuración de Apariencia";
require 'cabecera.php';
?>

<section class="forms">
    <h2>Selecciona tu estilo visual</h2>
    
    <?php if ($mensaje): ?>
        <div id="resreg"> <p><strong><?php echo $mensaje; ?></strong></p>
        </div>
    <?php endif; ?>

    <form action="configurar.php" method="post" class="lista-estilos">
        <fieldset>
            <legend>Estilos disponibles</legend>
            
            <?php foreach ($estilos as $est): ?>
                <?php 
                    $id = (int)$est['IdEstilo'];
                    $nombre = htmlspecialchars($est['Nombre']);
                    $desc = htmlspecialchars($est['Descripcion']);
                    $checked = ($id === $estiloActualId) ? 'checked' : '';
                ?>
                <div style="margin-bottom: 10px;">
                    <label style="display:inline-flex; align-items:center; gap:10px; width:100%; cursor:pointer;">
                        <input type="radio" name="estilo" value="<?php echo $id; ?>" <?php echo $checked; ?>>
                        <span>
                            <strong><?php echo $nombre; ?></strong>
                            <br>
                            <small style="font-weight:normal;"><?php echo $desc; ?></small>
                        </span>
                    </label>
                </div>
            <?php endforeach; ?>
            
        </fieldset>
        
        <button type="submit">Guardar preferencia</button>
    </form>
    
    <p style="text-align:center; margin-top:20px;">
        <a href="perfil.php">Volver a mi perfil</a>
    </p>
</section>

<?php
require 'pie.php';
?>