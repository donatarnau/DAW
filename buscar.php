<?php
    $titulo = "Buscar Anuncios";
    $encabezado = "Busqueda Avanzada - Pisos e Inmuebles";
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
            <form action="./res_buscar.php" id="busqueda">
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
                <button type="submit" id="btnBuscar">Buscar</button>
            </form>
        </section>
<?php
    include 'pie.php';
?>