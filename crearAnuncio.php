<?php

$titulo = "Crear anuncio";
$encabezado = "Crear anuncio - Pisos e Inmuebles";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user'])) {
    header("Location: ./index.php?error=acceso_denegado");
    exit;
}

date_default_timezone_set('Europe/Madrid');
$hoy = date('d-m-Y');

// --- Leer errores y valores previos si hay redirección ---
$errTipoAnuncio = isset($_GET['err_tipoAnuncio']);
$errTipoVivienda = isset($_GET['err_tipoVivienda']);
$errNombre = isset($_GET['err_nombre']);
$errCiudad = isset($_GET['err_ciudad']);
$errPais = isset($_GET['err_pais']);
$errPrecio = isset($_GET['err_precio']);
$errFecha = isset($_GET['err_fecha']);

$prevTipoAnuncio = $_GET['val_tipoAnuncio'] ?? '';
$prevTipoVivienda = $_GET['val_tipoVivienda'] ?? '';
$prevNombre = $_GET['val_nombre'] ?? '';
$prevCiudad = $_GET['val_ciudad'] ?? '';
$prevPais = $_GET['val_pais'] ?? '';
$prevPrecio = $_GET['val_precio'] ?? '';
$prevFecha = $_GET['val_fecha'] ?? $hoy;

// 1. CONEXIÓN
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

$mysqli->close();



require 'cabecera.php';
?>

<section class="forms">
    <h2>Publica tu anuncio</h2>
    <form action="./respuestaPublicar.php" id="busqueda" method="post">
        <fieldset class="search">
            <legend>Rellena todos los campos obligatorios</legend>

            <!-- Tipo de anuncio -->
            <label id="tipoAnuncio">Tipo de Anuncio</label>
            <select name="tipoAnuncio" id="param-anuncio">
                <option value="">Seleccione un tipo de anuncio</option>
                <?php if (isset($tiposAnuncio) && is_array($tiposAnuncio)): ?>
                    <?php foreach ($tiposAnuncio as $tipoAnuncioItem): ?>
                        <option value="<?php echo $tipoAnuncioItem['IdTAnuncio']; ?>" 
                            <?php if ($prevTipoAnuncio == $tipoAnuncioItem['IdTAnuncio']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($tipoAnuncioItem['NomTAnuncio']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <?php if ($errTipoAnuncio): ?>
                <p class="error-msg">Debe seleccionar un tipo de anuncio.</p>
            <?php endif; ?>

            <!-- Tipo de vivienda -->
            <label id="tipoVivienda">Tipo de Vivienda</label>
            <select name="tipoVivienda" id="param-vivienda">
                <option value="">Seleccione un tipo de vivienda</option>
                <?php if (isset($tiposVivienda) && is_array($tiposVivienda)): ?>
                    <?php foreach ($tiposVivienda as $tipoViviendaItem): ?>
                        <option value="<?php echo $tipoViviendaItem['IdTVivienda']; ?>" 
                            <?php if ($prevTipoVivienda == $tipoViviendaItem['IdTVivienda']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($tipoViviendaItem['NomTVivienda']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <?php if ($errTipoVivienda): ?>
                <p class="error-msg">Debe seleccionar un tipo de vivienda.</p>
            <?php endif; ?>

            <!-- Nombre -->
            <label id="nombre">Nombre</label>
            <input type="text" name="nombre" id="param-nombre" value="<?= htmlspecialchars($prevNombre) ?>">
            <?php if ($errNombre): ?>
                <p class="error-msg">Debe indicar un nombre para el anuncio.</p>
            <?php endif; ?>

            <!-- Ciudad -->
            <label id="ciudad">Ciudad</label>
            <input type="text" name="ciudad" id="param-ciudad" value="<?= htmlspecialchars($prevCiudad) ?>">
            <?php if ($errCiudad): ?>
                <p class="error-msg">Debe indicar la ciudad.</p>
            <?php endif; ?>

            <!-- País -->
            <label id="pais">País</label>
            <select name="pais" id="param-pais">
                <option value="">Seleccione un país</option>
                <?php if (isset($paises) && is_array($paises)): ?>
                    <?php foreach ($paises as $paisItem): ?>
                        <option value="<?php echo $paisItem['IdPais']; ?>" 
                            <?php if ($prevPais == $paisItem['IdPais']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($paisItem['NomPais']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <?php if ($errPais): ?>
                <p class="error-msg">Debe indicar el país.</p>
            <?php endif; ?>

            <!-- Precio -->
            <label for="precio">Precio</label>
            <input type="text" id="precio" name="precio" placeholder="En euros" value="<?= htmlspecialchars($prevPrecio) ?>">
            <?php if ($errPrecio): ?>
                <p class="error-msg">Debe indicar el precio del inmueble.</p>
            <?php endif; ?>

            <hr>
            <label class="subSection">Características</label>
            <label>Superficie:</label>
            <input type="text" name="caracteristicas[]" placeholder="En metros cuadrados">

            <label>Número de habitaciones:</label>
            <input type="text" name="caracteristicas[]">

            <label>Número de baños:</label>
            <input type="text" name="caracteristicas[]">

            <label>Planta:</label>
            <input type="text" name="caracteristicas[]">

            <label>Año de construcción:</label>
            <input type="text" name="caracteristicas[]">

            <!-- Fecha -->
            <label for="fecha_pub">Fecha de publicación</label>
            <input type="text" id="fecha_pub" name="fecha_pub" value="<?= htmlspecialchars($prevFecha) ?>" readonly>
            <?php if ($errFecha): ?>
                <p class="error-msg">La fecha de publicación es obligatoria.</p>
            <?php endif; ?>

            <br>
            <label> Nota: Las fotos se añadirán posteriormente</label>
        </fieldset>
        <button type="submit" id="btnBuscar">Crear anuncio</button>
    </form>
</section>

<?php require 'pie.php'; ?>
