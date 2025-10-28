<?php
    $titulo = "Enviar Mensaje";
    $encabezado = "Mensaje - Pisos e Inmuebles";
    $scripts = ['main.js', 'mensaje.js'];
    include 'cabecera.php';
?>
        <section class="forms">
            <h2>Enviar mensaje al anunciante</h2>
            <form action="./res_mensaje.php" id="formMensaje">
                <fieldset class="search">
                    <label for="tipo">Tipo de mensaje:</label>
                    <select name="tipo" id="tipoMensaje">
                        <option value="">Seleccione una opción</option>
                        <option value="info">Más información</option>
                        <option value="cita">Solicitar una cita</option>
                        <option value="oferta">Comunicar una oferta</option>
                    </select>
                    <br>
                    <label for="mensaje">Mensaje:</label>
                    <br>
                    <textarea name="mensaje" id="mensaje" rows="5" cols="40"></textarea>
                    <br>
                    <button class="btnContraste" type="submit" id="msg-boton">Enviar</button>
                </fieldset>
            </form>
        </section>
<?php
    include 'pie.php';
?>