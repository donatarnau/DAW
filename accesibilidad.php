<?php
    $titulo = "Declaración de Accesibilidad - Pisos e Inmuebles";
    $encabezado = "PI - Declaración de Accesibilidad";
    include 'cabecera.php';
?>
        <section id="access">
            <section>
            <h2 id="etiquetado-semantico">Etiquetado semántico</h2>
            <p>El sitio utiliza etiquetas HTML5 semánticas (<code>&lt;header&gt;</code>, <code>&lt;main&gt;</code>, <code>&lt;article&gt;</code>, <code>&lt;nav&gt;</code>, <code>&lt;footer&gt;</code>) y respeta su jerarquía semántica para mejorar la estructura y comprensión del contenido por parte de lectores de pantalla y motores de búsqueda.</p>
            </section>
            <hr>
            <section>
                <h2 id="texto-alternativo">Texto alternativo de las imágenes</h2>
                <p>Todas las imágenes incluyen el atributo <code>alt</code> con descripciones breves y claras.<br>Estas descripciones se mostrarán si la imagen falla en cargar.</p>
            </section>
            <hr>
            <section>
                <h2 id="uso-de-colores">Uso de los colores</h2>
                <p>
                    Para la presentación estándar se han elegido los siguientes colores:
                </p>
                <p class="color_1">#120045 - Azul marino</p>
                <p class="color_2">#f3f3f3 - Gris claro</p>
                <p class="color_3">#cccccc - Gris oscuro</p>
                <p class="color_4">#ffffff - Blanco puro</p>
                <p class="color_5">#000000 - Negro puro</p>
                <p>Los elementos se muestran en uno de estos 5 colores, los cuales se complementan en la presentación de la web para hacerla agradable y accesible al ojo humano.</p>
            </section>
            <hr>
            <section>
                <h2 id="hoja-de-estilo-accesible">Hojas de estilo accesibles</h2>
                <p>Se han implementado 4 modos accesibles que el usuario puede activar desde el navegador para disfrutar de una experiencia más personalizada y adecuada a sus necesidades.</p>
                <p class="m1">Modo Noche</p>
                <p>Cambia los colores de la web por una paleta de colores oscuros, en concreto variaciones del negro, gris y azul.</p>
                <p class="m2">Modo Alto Contraste</p>
                <p>Este modo emplea un azul más llamativo como color principal, reduciendo la paleta de colores a este azul y el blanco puro. Los elementos se muestran en uno de estos colores y utilizan el otro color para generar contraste, tanto en forma de reborde exterior como de color de fondo.</p>
                <p class="m3">Modo Lectura</p>
                <p>Este modo aumenta el tamaño de la letra en toda la página web sin aumentar el tamaño del resto de elementos. Además, al activar este modo, la tipografía "Poiret One" que usamos en algunos encabezados se sustituye por "Cal sans" para aumentar la legibilidad de la página.</p>
                <p class="m4">Modo Lectura Contrastada</p>
                <p>Este modo combina las funcionalidades de los modos Lectura y Alto Contraste.</p>
                <br><br>
                <p>
                    Para activar cualquiera de estos modos de visualización, pulsa la tecla <code>alt</code> o <code>f10</code> en el navegador.
                    <br>
                    Después, pulsa <code>Ver</code> en la barra superior y por último selecciona el estilo que desees dentro de la sección <code>Page Style</code>.
                </p>
            </section>
        </section>
<?php
    include 'pie.php';
?>