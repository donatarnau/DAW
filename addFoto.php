<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user'])) {
        header("Location: ./index.php?error=acceso_denegado");
        exit;
    }

    // --- 1. Leer Errores y Valores Anteriores desde la URL ---
    $errAnuncioEmpty = isset($_GET['err_anuncio_empty']);
    $errAltEmpty     = isset($_GET['err_alt_empty']);
    $errAltShort     = isset($_GET['err_alt_short']);


    // Obtener valores previos
    $prevAnuncio = isset($_GET['val_anuncio']) ? htmlspecialchars($_GET['val_anuncio']) : '';
    $prevNomAnuncio = isset($_GET['val_nomAnuncio']) ? htmlspecialchars($_GET['val_nomAnuncio']) : '';
    $prevAlt = isset($_GET['val_alt']) ? htmlspecialchars($_GET['val_alt']) : '';
    $prevDesc = isset($_GET['val_desc']) ? htmlspecialchars($_GET['val_desc']) : '';


    $titulo = "Añadir foto al anuncio";
    $encabezado = "Añadir foto al anuncio - Pisos e Inmuebles";

    $idAnuncio = $_GET['id'] ?? '';
    $nomAnuncio = $_GET['nom'] ?? '';

    if (!empty($idAnuncio)) {
        $prevAnuncio = $idAnuncio;
    }
    if (!empty($nomAnuncio)) {
        $prevNomAnuncio = $nomAnuncio;
    }

    $modoBloqueado = !empty($idAnuncio) || !empty($prevNomAnuncio);
    require 'cabecera.php';
?>
<section class="forms">
    <h2>Añadir foto a anuncio</h2>
    <form action="./respuestaFoto.php" id="busqueda" method="post">
        <fieldset class="search">
            <legend>Selecciona el anuncio</legend>
            <select name="anuncio" id="param-anuncio" <?= $modoBloqueado ? 'disabled' : '' ?>>
                <?php if ($modoBloqueado): ?>
                    <option value="<?= htmlspecialchars($prevAnuncio) ?>" selected>
                        <?= htmlspecialchars($prevNomAnuncio) ?>
                    </option>
                <?php else: ?>
                    <option value="">Elige un anuncio</option>
                    <option value="1" <?= $prevAnuncio === '1' ? 'selected' : '' ?>>Anuncio 1 de Usuario</option>
                    <option value="2" <?= $prevAnuncio === '2' ? 'selected' : '' ?>>Anuncio 2 de Usuario</option>
                <?php endif; ?>
            </select>
            <?php if ($errAnuncioEmpty): ?>
                <p class="error-msg">Por favor, selecciona un anuncio.</p>
            <?php endif; ?>

            <?php if ($modoBloqueado): ?>
                <!-- Campo oculto para enviar el id al servidor -->
                <input type="hidden" name="anuncio" value="<?= htmlspecialchars($prevAnuncio) ?>">
                <input type="hidden" name="nombreAnuncio" value="<?= htmlspecialchars($prevNomAnuncio) ?>">
            <?php endif; ?>

            <label for="foto">Selecciona la foto</label>
            <input type="file" name="foto" accept="image/*" id="reg-foto">

            <label for="alt">Texto alternativo</label>
            <input type="text" id="param-alt" name="alt" value="<?php echo $prevAlt; ?>">
            <?php if ($errAltEmpty): ?>
                <p class="error-msg">El texto alternativo es obligatorio.</p>
            <?php elseif ($errAltShort): ?>
                <p class="error-msg">El texto alternativo debe tener al menos 10 caracteres.</p>
            <?php endif; ?>
            <label for="desc">Descripción</label>
            <input type="text" id="param-desc" name="desc" value="<?php echo $prevDesc; ?>">

        </fieldset>
        <button type="submit" id="btnBuscar">Subir</button>
    </form>
</section>
<?php
    require 'pie.php';
?>
