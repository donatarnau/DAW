<?php
// userAnuncio.php

// 1. Control de acceso básico
if (!isset($_GET['id'])) {
    header("Location: ./index.php?error=falta_id");
    exit;
}

$id = (int)$_GET['id']; // Convertir a número entero

// 2. Cargar los datos desde el fichero externo
$anuncios = include './services/datos_anuncios.php';

// 3. Seleccionar el anuncio según si el id es par o impar
$clave = ($id % 2 === 0) ? 'par' : 'impar';
$anuncio = $anuncios[$clave];

// 4. Extraer las variables para usarlas directamente
extract($anuncio);

// 5. Configurar la cabecera
$titulo = "Anuncio - " . htmlspecialchars($nombre);
$encabezado = "Detalle del anuncio";

include 'cabecera.php';
?>
        <section id="anuncioDetalle">
            <h2 class="pill">Tipo de anuncio: <?= htmlspecialchars($tipo) ?></h2>
            <h2 class="pill">Tipo de vivienda: <?= htmlspecialchars($vivienda) ?></h2>
            
            <picture class="anuncio-hero">
                <img src="<?= htmlspecialchars($foto) ?>" alt="Foto principal del inmueble">
            </picture>

            <h2 class="anuncio-titulo"><?= htmlspecialchars($nombre) . ' - ' . $username?></h2>

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
            <a class="btn" href="./addFoto.php?user=<?= urlencode($username) . '&id='. urlencode($id) . '&nom=' . urlencode($nombre) ?>">Añadir foto</a>
        </section> 
        <section id="mensajesAnuncio" class="tipomensajes">
            <h2>Mensajes recibidos</h2>
            <ul>
                <li>
                    <article>
                        <h3 id="tipoMensaje">Solicitud de cita</h3>
                        <p class="content">Lorem ipsum dolor sit amet consectetur adipisicing elit. Consequuntur ex id repellendus autem, harum eum architecto rerum obcaecati ut aperiam accusantium, voluptate tenetur saepe reiciendis in quibusdam, doloribus atque. Culpa.</p>
                        <p><time datetime="2015-12-17">17 de Diciembre de 2015</time></p>
                        <p class="usuarioDelMensaje">Pablo Juarez Peydró</p>
                    </article>
                </li>
                <li>
                    <article>
                        <h3 id="tipoMensaje">Oferta de compra</h3>
                        <p class="content">Lorem ipsum dolor sit amet consectetur adipisicing elit. Consequuntur ex id repellendus autem, harum eum architecto rerum obcaecati ut aperiam accusantium, voluptate tenetur saepe reiciendis in quibusdam, doloribus atque. Culpa.</p>
                        <p><time datetime="2015-12-17">17 de Diciembre de 2015</time></p>
                        <p class="usuarioDelMensaje">Arnau Donat García</p>
                    </article>
                </li>
            </ul>
        </section> 
<?php
include 'pie.php';
?>
