<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user'])) {
    header("Location: ./index.php?error=acceso_denegado");
    exit;
}

// --- CONEXIÓN BD (Para cargar tipos de mensaje) ---
$config = parse_ini_file('config.ini');
if (!$config) die("Error al leer config.ini");
@$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
if ($mysqli->connect_errno) die("Error de conexión a la BD: " . $mysqli->connect_error);
$mysqli->set_charset('utf8mb4');

$tipos_mensaje = [];
if ($res = $mysqli->query("SELECT IdTMensaje, NomTMensaje FROM TIPOSMENSAJES ORDER BY NomTMensaje")) {
    while ($row = $res->fetch_assoc()) {
        $tipos_mensaje[] = $row;
    }
    $res->close();
}
$mysqli->close();

// --- FLASHDATA (Errores y Valores) ---
require_once 'services/flashdata.php';

$anuncioId = $_GET['id'] ?? ''; // El ID del anuncio siempre viene por GET (o se mantiene)
if ($anuncioId === '') {
    header("Location: ./index.php"); // Si no hay anuncio, volver al inicio
    exit;
}

$err_tipo = flash_get('err_tipo');
$err_mensaje = flash_get('err_mensaje');

$prevTipo = flash_get('val_tipo') ?? '';
$prevMensaje = flash_get('val_mensaje') ?? '';

$titulo = "Enviar Mensaje";
$encabezado = "Mensaje - Pisos e Inmuebles";
require 'cabecera.php';
?>
<section class="forms">
    <h2>Enviar mensaje al anunciante</h2>
    
    <form action="./resMensaje.php" id="formMensaje" method="post">
        <input type="hidden" name="anuncio" value="<?= htmlspecialchars($anuncioId) ?>">

        <fieldset class="search">
            <label for="tipoMensaje">Tipo de mensaje:</label>
            <select name="tipo" id="tipoMensaje">
                <option value="">Seleccione una opción</option>
                <?php foreach ($tipos_mensaje as $tipo): ?>
                    <option value="<?php echo $tipo['IdTMensaje']; ?>" 
                        <?php if ($prevTipo == $tipo['IdTMensaje']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($tipo['NomTMensaje']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($err_tipo): ?>
                <p class="error-msg"><?php echo htmlspecialchars($err_tipo); ?></p>
            <?php endif; ?>
            <br>

            <label for="mensaje">Mensaje:</label><br>
            <textarea name="mensaje" id="mensaje" rows="5" cols="40"><?php echo htmlspecialchars($prevMensaje); ?></textarea>
            <?php if ($err_mensaje): ?>
                <p class="error-msg"><?php echo htmlspecialchars($err_mensaje); ?></p>
            <?php endif; ?>
            <br>

            <button class="btnContraste" type="submit" id="msg-boton">Enviar</button>
        </fieldset>
    </form>
</section>
<?php
require 'pie.php';
?>