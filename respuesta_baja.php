<?php
/**
 * respuesta_baja.php
 * Maneja todo el proceso de baja: 
 * 1. Muestra el resumen de datos y pide confirmación (GET).
 * 2. Verifica contraseña y elimina la cuenta (POST).
 */

session_start();
require_once 'services/recordarme.php'; // Para invalidarTokensUsuario y borrarCookieRecordarme

// 1. CONTROL DE ACCESO
if (!isset($_SESSION['user']) || !isset($_SESSION['user_id'])) {
    header("Location: ./login.php?error=debes_iniciar_sesion");
    exit;
}

$userId = $_SESSION['user_id'];
$error = '';

// 2. CONEXIÓN A LA BD
$config = parse_ini_file('config.ini');
if (!$config) die("Error crítico: No se encuentra config.ini");

@$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
if ($mysqli->connect_errno) {
    die("Error de conexión a la BD: " . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

// 3. PROCESAMIENTO DEL FORMULARIO (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passwordInput = $_POST['password'] ?? '';

    // A. Obtener la contraseña real del usuario
    $stmt = $mysqli->prepare("SELECT Clave, NomUsuario FROM USUARIOS WHERE IdUsuario = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($dbPass, $dbUser);
    if (!$stmt->fetch()) {
        $stmt->close();
        $mysqli->close();
        session_destroy();
        header("Location: ./login.php");
        exit;
    }
    $stmt->close();

    // B. Verificar contraseña
    if ($passwordInput !== $dbPass) {
        $error = "La contraseña introducida no es correcta.";
    } else {
        // C. BORRADO DE DATOS
        
        // C.1 Borrar mensajes (Origen o Destino) para evitar error de FK (si no hay CASCADE)
        $sqlMensajes = "DELETE FROM MENSAJES WHERE UsuOrigen = ? OR UsuDestino = ?";
        if ($stmtMsg = $mysqli->prepare($sqlMensajes)) {
            $stmtMsg->bind_param("ii", $userId, $userId);
            $stmtMsg->execute();
            $stmtMsg->close();
        }

        // C.2 Borrar Usuario (El CASCADE de la BD borrará Anuncios y Fotos)
        $sqlUser = "DELETE FROM USUARIOS WHERE IdUsuario = ?";
        if ($stmtUser = $mysqli->prepare($sqlUser)) {
            $stmtUser->bind_param("i", $userId);
            
            if ($stmtUser->execute()) {
                // ÉXITO: Limpieza de sesión y cookies
                
                // Invalidar tokens persistentes
                if (function_exists('invalidarTokensUsuario')) {
                    invalidarTokensUsuario($dbUser);
                }
                // Borrar cookie navegador
                if (function_exists('borrarCookieRecordarme')) {
                    borrarCookieRecordarme();
                }
                
                // Destruir sesión
                $_SESSION = [];
                if (ini_get("session.use_cookies")) {
                    $params = session_get_cookie_params();
                    setcookie(session_name(), '', time() - 42000,
                        $params["path"], $params["domain"],
                        $params["secure"], $params["httponly"]
                    );
                }
                session_destroy();
                
                $mysqli->close();
                
                // Redirigir al index con mensaje
                header("Location: ./index.php?mensaje=cuenta_eliminada");
                exit;
                
            } else {
                $error = "Error al eliminar el usuario: " . $stmtUser->error;
            }
            $stmtUser->close();
        } else {
            $error = "Error al preparar la eliminación.";
        }
    }
}

// 4. OBTENER DATOS PARA EL RESUMEN (GET o si falló el POST)
$anunciosResumen = [];
$totalAnuncios = 0;
$totalFotos = 0;

// Consulta: Listado de anuncios y conteo de fotos por anuncio
$sqlResumen = "SELECT A.Titulo, COUNT(F.IdFoto) as NumFotos 
               FROM ANUNCIOS A 
               LEFT JOIN FOTOS F ON A.IdAnuncio = F.Anuncio 
               WHERE A.Usuario = ? 
               GROUP BY A.IdAnuncio";

if ($stmt = $mysqli->prepare($sqlResumen)) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    
    while ($row = $res->fetch_assoc()) {
        $anunciosResumen[] = $row;
        $totalFotos += (int)$row['NumFotos'];
    }
    $totalAnuncios = count($anunciosResumen);
    $stmt->close();
}

$mysqli->close();

// 5. RENDERIZAR LA PÁGINA DE CONFIRMACIÓN
$titulo = "Darme de baja";
$encabezado = "Eliminar Cuenta";
require 'cabecera.php';
?>

<section class="forms" id="baja-cuenta">
    <h2>¿Estás seguro de que quieres darte de baja?</h2>
    
    <h2><strong>ADVERTENCIA: Esta acción es irreversible.</strong></h2>  
    
    <p>Si eliminas tu cuenta, se borrará toda la información asociada: tus datos, tus <strong><?php echo $totalAnuncios; ?> anuncios</strong> y tus <strong><?php echo $totalFotos; ?> fotos</strong>.</p>

    <p>Resumen de información que se eliminará:</p>
    
    <section class="tabla">
        <table>
            <thead>
                <tr>
                    <th>Título del Anuncio</th>
                    <th>Nº de Fotos</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($anunciosResumen)): ?>
                    <tr><td>No tienes anuncios publicados.</td></tr>
                <?php else: ?>
                    <?php foreach ($anunciosResumen as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['Titulo']); ?></td>
                            <td><?php echo $item['NumFotos']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>TOTALES:</th>
                    <th>
                        <?php echo $totalAnuncios; ?> Anuncios / <?php echo $totalFotos; ?> Fotos
                    </th>
                </tr>
            </tfoot>
        </table>
    </section>

    <form action="./respuesta_baja.php" method="post" class="auth">
            <legend>Confirmación de seguridad</legend> <br>
            
            <label for="pwd-confirm">Para confirmar el borrado definitivo de tu cuenta, introduce tu contraseña actual:</label>
            <input type="password" name="password" id="pwd-confirm" placeholder="Introduce tu contraseña" required>
            
            <?php if ($error): ?>
                <p class="error-msg"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            
            <a href="perfil.php" style="text-decoration: none; flex: 1;">
                <button type="button" style="width: 100%;">Cancelar</button>
            </a>
                
            <button type="submit" style="flex: 1; background-color: #c0392b;">Eliminar definitivamente</button>
    </form>
</section>

<?php
require 'pie.php';
?>