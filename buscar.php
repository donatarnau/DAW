<?php
    $titulo = "Buscar Anuncios";
    $encabezado = "Busqueda Avanzada - Pisos e Inmuebles";
    include 'cabecera.php';

    // Recuperamos los valores enviados si existen
    $tipoAnuncio = $_GET['tipoAnuncio'] ?? '';
    $tipoVivienda = $_GET['tipoVivienda'] ?? '';
    $ciudad = $_GET['ciudad'] ?? '';
    $pais = $_GET['pais'] ?? '';
    $minPrecio = $_GET['minPrecio'] ?? '';
    $maxPrecio = $_GET['maxPrecio'] ?? '';
    $fecha_pub = $_GET['fecha_pub'] ?? '';

    $errEmpty = isset($_GET['err_empty']);
?>
<section class="forms">
    <h2>Buscar Anuncios</h2>
    <form action="./resBuscar.php" id="busqueda" method="get">
        <input type="hidden" name="user" value="<?= htmlspecialchars($username) ?>">
        <fieldset class="search">
            <legend>Rellena al menos un campo</legend>

            <?php if ($errEmpty): ?>
                <p class="error-msg">Debes rellenar al menos un campo para realizar la búsqueda.</p>
            <?php endif; ?>
            
            <label id="tipoAnuncio">Tipo de Anuncio</label>
            <select name="tipoAnuncio" id="param-anuncio">
                <option value="">Elige un tipo</option>
                <option value="alquiler" <?= $tipoAnuncio === 'alquiler' ? 'selected' : '' ?>>Alquiler</option>
                <option value="venta" <?= $tipoAnuncio === 'venta' ? 'selected' : '' ?>>Venta</option>
            </select>

            <label id="tipoVivienda">Tipo de Vivienda</label>
            <select name="tipoVivienda" id="param-vivienda">
                <option value="">Elige un tipo</option>
                <option value="obraNueva" <?= $tipoVivienda === 'obraNueva' ? 'selected' : '' ?>>Obra nueva</option>
                <option value="vivienda" <?= $tipoVivienda === 'vivienda' ? 'selected' : '' ?>>Vivienda</option>
                <option value="oficina" <?= $tipoVivienda === 'oficina' ? 'selected' : '' ?>>Oficina</option>
                <option value="local" <?= $tipoVivienda === 'local' ? 'selected' : '' ?>>Local</option>
                <option value="garaje" <?= $tipoVivienda === 'garaje' ? 'selected' : '' ?>>Garaje</option>
            </select>

            <label id="ciudad">Ciudad</label>
            <input type="text" name="ciudad" placeholder="Ciudad" id="param-ciudad" value="<?= htmlspecialchars($ciudad) ?>">

            <label id="pais">País</label>
            <input type="text" name="pais" placeholder="País" id="param-pais" value="<?= htmlspecialchars($pais) ?>">

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
    include 'pie.php';
?>
