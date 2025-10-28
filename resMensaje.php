<?php
    $titulo = "Resultado mensajes";
    $encabezado = "Mensaje enviado - Pisos e Inmuebles";
    $scripts = ['main.js'];
    include 'cabecera.php';
?>
        <section id="resultMensaje">
        <h2>Mensaje enviado con éxito</h2>
        <ul class="aviso">
            <li>
                Tipo de mensaje: Solicitud de cita
            </li>
            <li>
                Mensaje:  Lorem ipsum dolor sit amet consectetur adipisicing elit. Soluta repellat, unde laudantium, perferendis excepturi temporibus, quisquam at nulla asperiores omnis repudiandae non doloremque autem iusto recusandae consequatur minima ipsa beatae!
            </li>
        </ul>
        </section>
<?php
    include 'pie.php';
?>