<?php
    $titulo = "Anuncio (Página modelo)";
    $encabezado = "Anuncio de propiedad - Pisos e Inmuebles";
    $scripts = ['main.js'];
    include 'cabecera.php';
?>
        <section id="anuncioDetalle">
            <h2 class="pill">Tipo de anuncio: Venta</h2>
            <h2 class="pill">Tipo de vivienda: Piso</h2>
            
            <picture class="anuncio-hero">
                <img src="img/a1.jpeg" alt="Foto principal del inmueble">
            </picture>

            <h2 class="anuncio-titulo">Ático luminoso en el centro</h2>

            <p class="anuncio-descripcion"><strong>Descripción:</strong> Ático reformado con terraza, 3 habitaciones, 2 baños, cocina equipada y salón amplio.</p>

            <ul class="meta-list">
                <li><strong>Fecha:</strong> 22/09/2025</li>
                <li><strong>Ciudad:</strong> Alicante</li>
                <li><strong>País:</strong> España</li>
                <li class="precio"><strong>Precio:</strong> 250.000 €</li>
            </ul>

            <h3>Características</h3>
            <ul class="ficha">
                <li>Superficie: 120 m²</li>
                <li>Número de habitaciones: 3</li>
                <li>Número de baños: 2</li>
                <li>Planta: 5ª</li>
                <li>Año de construcción: 2015</li>
            </ul>

            <h3>Fotos</h3>
            <section class="anuncio-galeria">
                <figure>
                <img src="img/a1.jpeg" alt="Ejemplo1">
                <figcaption>Ejemplo1</figcaption>
                </figure>
                <figure>
                <img src="img/a2.png" alt="Ejemplo2">
                <figcaption>Ejemplo2</figcaption>
                </figure>
            </section>

            <a class="btn" href="./mensaje.php">Enviar mensaje</a>
        </section> 
<?php
    include 'pie.php';
?>