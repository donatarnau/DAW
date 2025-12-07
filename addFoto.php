<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user'])) {
        header("Location: ./index.php?error=acceso_denegado");
        exit;
    }

    require_once 'services/flashdata.php';

    // 1. Leer Errores y Valores Anteriores
    $errAnuncioEmpty = flash_get('err_anuncio_empty');
    $errAltEmpty     = flash_get('err_alt_empty');
    $errAltShort     = flash_get('err_alt_short');
    $errTitEmpty     = flash_get('err_tit_empty');
    $errAltInvalidStart = flash_get('err_alt_invalid_start');
    $msg_error = flash_get('wrong');

    // Obtener valores previos
    $prevAnuncio = flash_get('val_anuncio') ?? '';
    $prevAlt = flash_get('val_alt') ?? '';
    $prevTit = flash_get('val_tit') ?? '';

    $userId = $_SESSION['user_id'];
    $username = htmlspecialchars($_SESSION['user']);

    $titulo = "Añadir foto al anuncio";
    $encabezado = "Añadir foto al anuncio - Pisos e Inmuebles";

    // Detectar si venimos de "Crear Anuncio" o "Ver Anuncio" (parámetro GET id)
    $idAnuncioURL = $_GET['id'] ?? '';
    
    // Si hay un ID en la URL, tiene prioridad para el bloqueo, sino usamos el del flashdata
    $idSeleccionado = $idAnuncioURL ?: $prevAnuncio;

    // 2. CONEXIÓN BD
    $config = parse_ini_file('config.ini');
    if (!$config) die("Error al leer config.ini");
    @$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
    if ($mysqli->connect_errno) die("Error BD");

    // Cargar anuncios del usuario
    $anuncios = [];
    $tituloAnuncioBloqueado = '';

    $sql = "SELECT IdAnuncio, Titulo FROM ANUNCIOS WHERE Usuario = ? ORDER BY FRegistro DESC";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $anuncios[] = $row;
            // Si coincide con el seleccionado, guardamos el título para mostrarlo
            if ($row['IdAnuncio'] == $idSeleccionado) {
                $tituloAnuncioBloqueado = $row['Titulo'];
            }
        }
        $stmt->close();
    }
    $mysqli->close();

    // Modo bloqueado: Si venimos redirigidos con un ID concreto
    $modoBloqueado = !empty($idSeleccionado);

    require 'cabecera.php';
?>
<section class="forms">
    <h2>Añadir foto a anuncio</h2>
    
    <?php if ($msg_error): ?>
        <p class="error-msg" style="text-align:center"><?= htmlspecialchars($msg_error) ?></p>
    <?php endif; ?>

    <!-- IMPORTANTE: id="busqueda" restaurado para CSS y enctype añadido para funcionalidad -->
    <form action="./respuestaFoto.php" id="busqueda" method="post" enctype="multipart/form-data">
        <fieldset class="search">
            <legend>Selecciona el anuncio</legend>
            
            <!-- Select: Si está bloqueado se muestra disabled pero visualmente correcto -->
            <select name="anuncio_select" id="param-anuncio" <?= $modoBloqueado ? 'disabled' : '' ?>>
                <?php if ($modoBloqueado): ?>
                    <option value="<?= htmlspecialchars($idSeleccionado) ?>" selected>
                        <?= htmlspecialchars($tituloAnuncioBloqueado) ?>
                    </option>
                <?php else: ?>
                    <option value="">Despliega para ver tus anuncios</option>
                    <?php foreach ($anuncios as $anuncioItem): ?>
                        <option value="<?= $anuncioItem['IdAnuncio'] ?>" <?= ($prevAnuncio == $anuncioItem['IdAnuncio']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($anuncioItem['Titulo']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            
            <!-- Si el select está disabled, NO se envía. Necesitamos un input hidden con el valor real -->
            <?php if ($modoBloqueado): ?>
                <input type="hidden" name="anuncio" value="<?= htmlspecialchars($idSeleccionado) ?>">
            <?php else: ?>
                <!-- Script simple para asegurar que el select envíe "anuncio" si no está disabled -->
                <script>document.getElementById('param-anuncio').name = "anuncio";</script>
            <?php endif; ?>

            <?php if ($errAnuncioEmpty): ?>
                <p class="error-msg">Por favor, selecciona un anuncio.</p>
            <?php endif; ?>

            <label for="reg-foto">Selecciona la foto</label>
            <input type="file" name="foto" accept="image/*" id="reg-foto">

            <label for="param-tit">Titulo de la foto</label>
            <input type="text" id="param-tit" name="tit" value="<?= htmlspecialchars($prevTit) ?>">
            <?php if ($errTitEmpty): ?>
                <p class="error-msg">El título es obligatorio.</p>
            <?php endif; ?>

            <label for="param-alt">Texto alternativo</label>
            <input type="text" id="param-alt" name="alt" value="<?= htmlspecialchars($prevAlt) ?>">
            <?php if ($errAltEmpty): ?>
                <p class="error-msg">El texto alternativo es obligatorio.</p>
            <?php elseif ($errAltShort): ?>
                <p class="error-msg">El texto alternativo debe tener al menos 10 caracteres.</p>
            <?php elseif ($errAltInvalidStart): ?>
                <p class="error-msg">El texto alternativo no debe comenzar con "texto", "imagen", "foto"...</p>
            <?php endif; ?>

        </fieldset>
        <button type="submit" id="btnBuscar">Subir</button>
    </form>
</section>
<?php
    require 'pie.php';
?>