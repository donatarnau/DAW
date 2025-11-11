<?php
    $titulo = "Buscar Anuncios";
    $encabezado = "Busqueda Avanzada - Pisos e Inmuebles";
    require 'cabecera.php';

    // Recuperamos los valores enviados si existen
    $tipoAnuncio = $_GET['tipoAnuncio'] ?? '';
    $tipoVivienda = $_GET['tipoVivienda'] ?? '';
    $ciudad = $_GET['ciudad'] ?? '';
    $pais = $_GET['pais'] ?? '';
    $minPrecio = $_GET['minPrecio'] ?? '';
    $maxPrecio = $_GET['maxPrecio'] ?? '';
    $fecha_pub = $_GET['fecha_pub'] ?? '';

    $errEmpty = isset($_GET['err_empty']);


    // 1. CONEXIÓN Y PAÍSES
    $config = parse_ini_file('config.ini');
    if (!$config) die("Error al leer config.ini");
    @$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
    if ($mysqli->connect_errno) die("Error de conexión a la BD: " . $mysqli->connect_error);

    // --- 1. PAISES ---
    $paises = [];
    $sqlPaises = "SELECT IdPais, NomPais FROM PAISES ORDER BY NomPais";
    if ($stmt = $mysqli->prepare($sqlPaises)) {
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $paises[] = $row;
        }
        $stmt->close();
    }

    // --- 2. TIPOS DE VIVIENDA ---
    $tiposVivienda = [];
    $sqlTiposVivienda = "SELECT IdTVivienda, NomTVivienda FROM TIPOSVIVIENDAS ORDER BY NomTVivienda";
    if ($stmt = $mysqli->prepare($sqlTiposVivienda)) {
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $tiposVivienda[] = $row;
        }
        $stmt->close();
    }

    // --- 3. TIPOS DE ANUNCIO ---
    $tiposAnuncio = [];
    $sqlTiposAnuncio = "SELECT IdTAnuncio, NomTAnuncio FROM TIPOSANUNCIOS ORDER BY NomTAnuncio";
    if ($stmt = $mysqli->prepare($sqlTiposAnuncio)) {
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $tiposAnuncio[] = $row;
        }
        $stmt->close();
    }

    // Cerramos conexión, ya tenemos todos los datos
    $mysqli->close();

?>
<section class="forms">
    <h2>Buscar Anuncios</h2>
    <form action="./resBuscar.php" id="busqueda" method="get">
        <fieldset class="search">
            <legend>Rellena al menos un campo</legend>

            <?php if ($errEmpty): ?>
                <p class="error-msg">Debes rellenar al menos un campo para realizar la búsqueda.</p>
            <?php endif; ?>
            
            <label id="tipoAnuncio">Tipo de Anuncio</label>
            <select name="tipoAnuncio" id="param-anuncio">
                <option value="">Seleccione un tipo de anuncio</option>
                <?php if (isset($tiposAnuncio) && is_array($tiposAnuncio)): ?>
                    <?php foreach ($tiposAnuncio as $tipoAnuncioItem): ?>
                        <option value="<?php echo $tipoAnuncioItem['IdTAnuncio']; ?>" 
                            <?php if ($tipoAnuncio == $tipoAnuncioItem['IdTAnuncio']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($tipoAnuncioItem['NomTAnuncio']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>

            <label id="tipoVivienda">Tipo de Vivienda</label>
            <select name="tipoVivienda" id="param-vivienda">
                <option value="">Seleccione un tipo de vivienda</option>
                <?php if (isset($tiposVivienda) && is_array($tiposVivienda)): ?>
                    <?php foreach ($tiposVivienda as $tipoViviendaItem): ?>
                        <option value="<?php echo $tipoViviendaItem['IdTVivienda']; ?>" 
                            <?php if ($tipoVivienda == $tipoViviendaItem['IdTVivienda']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($tipoViviendaItem['NomTVivienda']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>

            <label id="ciudad">Ciudad</label>
            <input type="text" name="ciudad" placeholder="Ciudad" id="param-ciudad" value="<?= htmlspecialchars($ciudad) ?>">

            <label id="pais">País</label>
            <select name="pais" id="param-pais">
                <option value="">Seleccione un país</option>
                <?php if (isset($paises) && is_array($paises)): ?>
                    <?php foreach ($paises as $paisItem): ?>
                        <option value="<?php echo $paisItem['IdPais']; ?>" 
                            <?php if ($pais == $paisItem['IdPais']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($paisItem['NomPais']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>

            <label for="minPrecio">Precio mínimo</label>
            <input type="text" id="minPrecio" name="minPrecio" value="<?= htmlspecialchars($minPrecio) ?>">
            
            <label for="maxPrecio">Precio máximo</label>
            <input type="text" id="maxPrecio" name="maxPrecio" value="<?= htmlspecialchars($maxPrecio) ?>">

            <label for="fecha_pub">Fecha de publicación</label>
            <input type="text" id="fecha_pub" name="fecha_pub" value="<?= htmlspecialchars($fecha_pub) ?>">

        </fieldset>
        <button type="submit" id="btnBuscar">Buscar</button>
    </form>
</section>
<?php
    require 'pie.php';
?>
