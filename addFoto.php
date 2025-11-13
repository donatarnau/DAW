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
    $prevAlt = isset($_GET['val_alt']) ? htmlspecialchars($_GET['val_alt']) : '';
    $prevDesc = isset($_GET['val_desc']) ? htmlspecialchars($_GET['val_desc']) : '';

    $userId = $_SESSION['user_id'];
    $username = htmlspecialchars($_SESSION['user']);

    $titulo = "Añadir foto al anuncio";
    $encabezado = "Añadir foto al anuncio - Pisos e Inmuebles";

    $idAnuncio = $_GET['id'] ?? '';

    // 1. CONEXIÓN
    $config = parse_ini_file('config.ini');
    if (!$config) die("Error al leer config.ini");
    @$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
    if ($mysqli->connect_errno) die("Error de conexión a la BD: " . $mysqli->connect_error);

    // --- 1. PAISES ---
    $anuncios = [];
    $actualAd = '';

    if (empty($idAnuncio)) { // si no hay anuncio en la URL, ponemos todos en la lista
        
        $sqlAnuncios = "SELECT IdAnuncio, Titulo
                        FROM ANUNCIOS
                        WHERE Usuario = ?
                        ORDER BY FRegistro DESC";
        if ($stmt = $mysqli->prepare($sqlAnuncios)) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $anuncios[] = $row;
            }
            $stmt->close();
        }
    }else{ // si hay anuncio en la URL, lo ponemos como seleccionado
        $sql = "SELECT 
                Titulo
                FROM ANUNCIOS
                WHERE IdAnuncio = ?";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("i", $idAnuncio);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $actualAd = $row;
            }
            $stmt->close();
        }
    }
    // Cerramos conexión, ya tenemos todos los datos
    $mysqli->close();



    $nomAnuncio = $_GET['nom'] ?? '';

    if (!empty($idAnuncio)) {
        $prevAnuncio = $idAnuncio;
    }

    $modoBloqueado = !empty($idAnuncio);
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
                        <?= htmlspecialchars($actualAd['Titulo']) ?>
                    </option>
                <?php else: ?>
                    <option value="">Despliega para ver tus anuncios</option>
                    <?php if (isset($anuncios) && is_array($anuncios)): ?>
                        <?php foreach ($anuncios as $anuncioItem): ?>
                            <option value="<?php echo $anuncioItem['IdAnuncio']; ?>">
                                <?php echo htmlspecialchars($anuncioItem['Titulo']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
