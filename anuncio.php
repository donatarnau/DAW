<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user'])) {
    header("Location: ./index.php?error=acceso_denegado");
    exit;
}

// 1. Control de acceso básico
if (!isset($_GET['id'])) {
    header("Location: ./index.php?error=falta_id");
    exit;
}

$id = (int)$_GET['id']; // Convertir a número entero

// 2. Cargar los datos desde el fichero externo
$anuncios = require './services/datos_anuncios.php';

// 3. Seleccionar el anuncio según si el id es par o impar
$clave = ($id % 2 === 0) ? 'par' : 'impar';
$anuncio = $anuncios[$clave];

// 4. Extraer las variables para usarlas directamente
extract($anuncio);

// 5. Configurar la cabecera
$titulo = "Anuncio - " . htmlspecialchars($nombre);
$encabezado = "Detalle del anuncio";
require 'cabecera.php';

?>
        <section id="anuncioDetalle">
            <h2 class="pill">Tipo de anuncio: <?= htmlspecialchars($tipo) ?></h2>
            <h2 class="pill">Tipo de vivienda: <?= htmlspecialchars($vivienda) ?></h2>
            
            <picture class="anuncio-hero">
                <img src="<?= htmlspecialchars($foto) ?>" alt="Foto principal del inmueble">
            </picture>

            <h2 class="anuncio-titulo"><?= htmlspecialchars($nombre) . ' - ' . htmlspecialchars($usuario)?></h2>

            <p class="anuncio-descripcion">
                <strong>Descripción:</strong> <?= htmlspecialchars($descripcion) ?>
            </p>

            <ul class="meta-list">
                <li><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($fecha)) ?></li>
                <li><strong>Ciudad:</strong> <?= htmlspecialchars($ciudad) ?></li>
                <li><strong>País:</strong> <?= htmlspecialchars($pais) ?></li>
                <li class="precio"><strong>Precio:</strong> <?= number_format($precio, 0, ',', '.') ?> €</li>
            </ul>

            <h3>Características</h3>
            <ul class="ficha">
                <?php foreach ($caracteristicas as $c): ?>
                    <li><?= htmlspecialchars($c) ?></li>
                <?php endforeach; ?>
            </ul>

            <h3>Fotos</h3>
            <section class="anuncio-galeria">
                <?php foreach ($fotos as $fotoData): ?>
                    <figure>
                        <img src="<?= htmlspecialchars($fotoData['src']) ?>" alt="<?= htmlspecialchars($fotoData['caption']) ?>">
                        <figcaption><?= htmlspecialchars($fotoData['caption']) ?></figcaption>
                    </figure>
                <?php endforeach; ?>
            </section>

            <a class="btn" href="./mensaje.php?id='<?= urlencode($id) ?>">Enviar mensaje</a>
        </section> 
<?php
require 'pie.php';
?>
