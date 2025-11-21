<?php
/**
 * resMensaje.php
 * Procesa el envío de un mensaje a un anunciante.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'services/flashdata.php';

function redirigirMensaje($idAnuncio, $params = []) {
    $host = $_SERVER['HTTP_HOST'];
    $uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\'); 
    if ($uri === '/' || $uri === '\\') $uri = '';
    
    // Añadimos el ID del anuncio a la redirección
    $queryString = '?id=' . urlencode($idAnuncio);
    if (!empty($params)) {
        $queryString .= '&' . http_build_query($params);
    }
    header("Location: http://$host$uri/mensaje.php$queryString");
    exit; 
}

// 1. CONTROL DE ACCESO
if (!isset($_SESSION['user']) || !isset($_SESSION['user_id'])) {
    header("Location: ./login.php?error=debes_iniciar_sesion");
    exit;
}

// 2. VERIFICAR MÉTODO POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ./index.php");
    exit;
}

$userId = $_SESSION['user_id']; // Remitente (UsuOrigen)
$username = $_SESSION['user'];

// 3. RECOGER DATOS
$tipoMensajeId = isset($_POST['tipo']) ? (int)$_POST['tipo'] : 0;
$texto = trim($_POST['mensaje'] ?? '');
$anuncioId = isset($_POST['anuncio']) ? (int)$_POST['anuncio'] : 0;

// 4. VALIDACIONES
$errors = [];

if ($anuncioId <= 0) {
    // Error crítico, sin anuncio no podemos hacer nada
    header("Location: ./index.php?error=anuncio_invalido");
    exit;
}

if ($tipoMensajeId <= 0) {
    $errors['err_tipo'] = "Debe seleccionar un tipo de mensaje.";
}

if ($texto === '') {
    $errors['err_mensaje'] = "El contenido del mensaje no puede estar vacío.";
}

if (!empty($errors)) {
    foreach ($errors as $key => $msg) {
        flash_set($key, $msg);
    }
    // Guardamos valores previos
    flash_set('val_tipo', $tipoMensajeId);
    flash_set('val_mensaje', $texto);
    
    redirigirMensaje($anuncioId);
}

// 5. CONEXIÓN Y LÓGICA DE BD
$config = parse_ini_file('config.ini');
if (!$config) die("Error crítico: No se encuentra config.ini");

@$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
if ($mysqli->connect_errno) {
    die("Error de conexión a la BD: " . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

// A. Obtener el Dueño del Anuncio (UsuDestino) y datos del anuncio
$usuDestino = 0;
$tituloAnuncio = '';
$nombreDestinatario = '';

$sqlAnuncio = "SELECT A.Usuario, A.Titulo, U.NomUsuario 
               FROM ANUNCIOS A 
               JOIN USUARIOS U ON A.Usuario = U.IdUsuario 
               WHERE A.IdAnuncio = ?";

if ($stmt = $mysqli->prepare($sqlAnuncio)) {
    $stmt->bind_param("i", $anuncioId);
    $stmt->execute();
    $stmt->bind_result($usuDestino, $tituloAnuncio, $nombreDestinatario);
    if (!$stmt->fetch()) {
        $stmt->close();
        $mysqli->close();
        die("El anuncio no existe.");
    }
    $stmt->close();
} else {
    $mysqli->close();
    die("Error al consultar anuncio.");
}

// B. Insertar el Mensaje
$sqlInsert = "INSERT INTO MENSAJES (TMensaje, Texto, Anuncio, UsuOrigen, UsuDestino) 
              VALUES (?, ?, ?, ?, ?)";

if ($stmt = $mysqli->prepare($sqlInsert)) {
    $stmt->bind_param("isiii", $tipoMensajeId, $texto, $anuncioId, $userId, $usuDestino);
    
    if ($stmt->execute()) {
        $stmt->close();
        
        // Recuperar nombre del tipo de mensaje para mostrarlo
        $nombreTipo = "Desconocido";
        if ($stmtTipo = $mysqli->prepare("SELECT NomTMensaje FROM TIPOSMENSAJES WHERE IdTMensaje = ?")) {
            $stmtTipo->bind_param("i", $tipoMensajeId);
            $stmtTipo->execute();
            $stmtTipo->bind_result($nombreTipo);
            $stmtTipo->fetch();
            $stmtTipo->close();
        }
        
        $mysqli->close();

        // 6. RENDERIZAR CONFIRMACIÓN
        $titulo = "Mensaje Enviado";
        $encabezado = "Confirmación de envío";
        require 'cabecera.php';
        ?>
        <section id="resultMensaje">
            <h2>Mensaje enviado con éxito</h2>
            <ul class="listaRespuesta">
                <li><strong>Destinatario:</strong> <?php echo htmlspecialchars($nombreDestinatario); ?></li>
                <li><strong>Anuncio:</strong> <?php echo htmlspecialchars($tituloAnuncio); ?></li>
                <li><strong>Tipo de mensaje:</strong> <?php echo htmlspecialchars($nombreTipo); ?></li>
                <li><strong>Contenido:</strong><br>
                    <em><?php echo nl2br(htmlspecialchars($texto)); ?></em>
                </li>
            </ul>
            <a class="btn" href="./anuncio.php?id=<?php echo $anuncioId; ?>">Volver al anuncio</a>
            <a class="btn" href="./userMensajes.php">Ver mis mensajes</a>
        </section>
        <?php
        require 'pie.php';
        exit;
        
    } else {
        die("Error al guardar el mensaje: " . $stmt->error);
    }
} else {
    die("Error al preparar la inserción: " . $mysqli->error);
}

$mysqli->close();
?>