<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user'])) {
    header("Location: ./index.php?error=acceso_denegado");
    exit;
}
// --- Recuperar usuario ---

$anuncio = $_GET['id'] ?? ''; 
$prevTipo = $_GET['val_tipo'] ?? '';
$prevMensaje = $_GET['val_mensaje'] ?? '';

$titulo = "Enviar Mensaje";
$encabezado = "Mensaje - Pisos e Inmuebles";



// --- Leer errores y valores previos desde la URL ---
$errTipoEmpty = isset($_GET['err_tipo_empty']);
$errMensajeEmpty = isset($_GET['err_mensaje_empty']);
require 'cabecera.php';
?>
<section class="forms">
    <h2>Enviar mensaje al anunciante</h2>
    <form action="./resMensaje.php" id="formMensaje" method="post">
        <!-- Pasamos el anuncio mediante hidden -->
        <input type="hidden" name="anuncio" value="<?= htmlspecialchars($anuncio) ?>">

        <fieldset class="search">
            <label for="tipo">Tipo de mensaje:</label>
            <select name="tipo" id="tipoMensaje">
                <option value="">Seleccione una opción</option>
                <option value="info" <?= $prevTipo === 'info' ? 'selected' : '' ?>>Más información</option>
                <option value="cita" <?= $prevTipo === 'cita' ? 'selected' : '' ?>>Solicitar una cita</option>
                <option value="oferta" <?= $prevTipo === 'oferta' ? 'selected' : '' ?>>Comunicar una oferta</option>
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
