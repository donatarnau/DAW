<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user'])) {
    header("Location: ./index.php?error=acceso_denegado");
    exit;
}

// --- NUEVO: CONEXIÓN A BD PARA OBTENER TIPOS DE MENSAJE ---
$config = parse_ini_file('config.ini');
if (!$config) die("Error al leer config.ini");
@$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
if ($mysqli->connect_errno) die("Error de conexión a la BD: " . $mysqli->connect_error);

$tipos_mensaje = [];
// Consultamos la tabla TiposMensajes
$sql_tipos = "SELECT IdTMensaje, NomTMensaje FROM TIPOSMENSAJES ORDER BY NomTMensaje";
if ($res = $mysqli->query($sql_tipos)) {
    while ($row = $res->fetch_assoc()) {
        $tipos_mensaje[] = $row;
    }
    $res->close();
}
$mysqli->close();
// --- FIN CONEXIÓN ---

// --- Recuperar datos GET (sin cambios) ---
$anuncio = $_GET['id'] ?? ''; 
// $prevTipo ahora recibirá un ID (ej. 1, 2) en lugar de 'info', 'cita'
$prevTipo = $_GET['val_tipo'] ?? ''; 
$prevMensaje = $_GET['val_mensaje'] ?? '';

$titulo = "Enviar Mensaje";
$encabezado = "Mensaje - Pisos e Inmuebles";

// --- Leer errores y valores previos desde la URL (sin cambios) ---
$errTipoEmpty = isset($_GET['err_tipo_empty']);
$errMensajeEmpty = isset($_GET['err_mensaje_empty']);
require 'cabecera.php';
?>
<section class="forms">
    <h2>Enviar mensaje al anunciante</h2>
    <form action="./resMensaje.php" id="formMensaje" method="post">
        <input type="hidden" name="anuncio" value="<?= htmlspecialchars($anuncio) ?>">

        <fieldset class="search">
            <label for="tipo">Tipo de mensaje:</label>
            
            <select name="tipo" id="tipoMensaje">
                <option value="">Seleccione una opción</option>
                <?php foreach ($tipos_mensaje as $tipo): ?>
                    <option value="<?php echo $tipo['IdTMensaje']; ?>" 
                        <?php if ($prevTipo == $tipo['IdTMensaje']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($tipo['NomTMensaje']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($errTipoEmpty): ?>
                <p class="error-msg">Debe seleccionar un tipo de mensaje.</p>
            <?php endif; ?>
            <br>

            <label for="mensaje">Mensaje:</label><br>
            <textarea name="mensaje" id="mensaje" rows="5" cols="40"><?= htmlspecialchars($prevMensaje) ?></textarea>
            <?php if ($errMensajeEmpty): ?>
                <p class="error-msg">El campo mensaje no puede estar vacío.</p>
            <?php endif; ?>
            <br>

            <button class="btnContraste" type="submit" id="msg-boton">Enviar</button>
        </fieldset>
    </form>
</section>
<?php
require 'pie.php';
?>