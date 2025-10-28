<?php
    $titulo = "Resultados de la búsqueda";
    $encabezado = "Resultado Búsqueda - Pisos e Inmuebles";
    $scripts = ['main.js', 'fastLogin.js', 'buscar.js'];
    include 'cabecera.php';
?>
        <dialog id="avisoBuscar" class="aviso">
            <h2>Error en la búsqueda</h2>
            <div id="reglas">

            </div>
            <button id="closeModalBuscar">Cerrar</button>
        </dialog>
        <section class="forms">
            <h2>Buscar Anuncios</h2>
            <form action="./resBuscar.php" id="busqueda">
                <fieldset  class="search">
                    <legend>Rellena al menos un campo</legend>
                    <label id="tipoAnuncio">Tipo de Anuncio</label>
                    <select name="tipoAnuncio" id="param-anuncio">
                        <option value="">Elige un tipo</option>
                        <option value="alquiler">Alquiler</option>
                        <option value="venta">Venta</option>
                    </select>

                    <label id="tipoVivienda">Tipo de Vivienda</label>
                    <select name="tipoVivienda" id="param-vivienda">
                        <option value="">Elige un tipo</option>
                        <option value="obraNueva">Obra nueva</option>
                        <option value="vivienda">Vivienda</option>
                        <option value="oficina">Oficina</option>
                        <option value="local">Local</option>
                        <option value="garaje">Garaje</option>
                    </select>

                    <label id="ciudad">Ciudad</label>
                    <input type="text" name="ciudad" placeholder="Ciudad" id="param-ciudad">

                    <label id="pais">País</label>
                    <input type="text" name="pais" placeholder="País" id="param-pais">

                    <label for="minPrecio">Precio mínimo</label>
                    <input type="text" id="minPrecio" name="minPrecio">
                    <label for="maxPrecio">Precio máximo</label>
                    <input type="text" id="maxPrecio" name="maxPrecio">

                    <label for="fecha_pub">Fecha de publicación</label>
                    <input type="text" id="fecha_pub" name="fecha_pub">

                </fieldset>
                <button type="submit">Buscar</button>
            </form>
        </section>
        <section id="resultadosBuscar">
            <h2>Resultados de la búsqueda</h2>
            <ul>
                <li>
                    <article>
                        <figure>
                            <a href="./login.php"><img src="./img/a1.jpeg" alt="anuncio1"></a>
                        </figure>
                        <a href="./login.php"><h2>Este anuncio lleva a login.html</h2></a>
                        <p><time datetime="2015-12-17">17 de Diciembre de 2015</time></p>
                        <p>Córdoba</p>
                        <p>España</p>
                        <p>150.000€</p>
                    </article>
                </li>
                <li>
                    <article>
                        <figure>
                            <a href="./anuncio.php"><img src="./img/a2.png" alt="anuncio2"></a>
                        </figure>
                        <a href="./anuncio.php"><h2>Este anuncio lleva a anuncio.html</h2></a>
                        <p><time datetime="2015-12-17">17 de Diciembre de 2015</time></p>
                        <p>Córdoba</p>
                        <p>España</p>
                        <p>150.000€</p>
                    </article>
                </li>
            </ul>
        </section>
<?php
    include 'pie.php';
?>