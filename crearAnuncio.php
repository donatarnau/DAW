<?php
$titulo = "Crear anuncio";
$encabezado = "Crear anuncio - Pisos e Inmuebles";
include 'cabecera.php';

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
?>

<section class="forms">
    <h2>Publica tu anuncio</h2>
    <form action="./respuestaPublicar.php" id="busqueda" method="post">
        <input type="hidden" name="user" value="<?= htmlspecialchars($username) ?>">
        <fieldset class="search">
            <legend>Rellena todos los campos obligatorios</legend>

            <!-- Tipo de anuncio -->
            <label id="tipoAnuncio">Tipo de Anuncio</label>
            <select name="tipoAnuncio" id="param-anuncio">
                <option value="">Elige un tipo</option>
                <option value="alquiler" <?= $prevTipoAnuncio === 'alquiler' ? 'selected' : '' ?>>Alquiler</option>
                <option value="venta" <?= $prevTipoAnuncio === 'venta' ? 'selected' : '' ?>>Venta</option>
            </select>
            <?php if ($errTipoAnuncio): ?>
                <p class="error-msg">Debe seleccionar un tipo de anuncio.</p>
            <?php endif; ?>

            <!-- Tipo de vivienda -->
            <label id="tipoVivienda">Tipo de Vivienda</label>
            <select name="tipoVivienda" id="param-vivienda">
                <option value="">Elige un tipo</option>
                <option value="obraNueva" <?= $prevTipoVivienda === 'obraNueva' ? 'selected' : '' ?>>Obra nueva</option>
                <option value="vivienda" <?= $prevTipoVivienda === 'vivienda' ? 'selected' : '' ?>>Vivienda</option>
                <option value="oficina" <?= $prevTipoVivienda === 'oficina' ? 'selected' : '' ?>>Oficina</option>
                <option value="local" <?= $prevTipoVivienda === 'local' ? 'selected' : '' ?>>Local</option>
                <option value="garaje" <?= $prevTipoVivienda === 'garaje' ? 'selected' : '' ?>>Garaje</option>
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
            <input type="text" name="pais" id="param-pais" value="<?= htmlspecialchars($prevPais) ?>">
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

<?php include 'pie.php'; ?>
