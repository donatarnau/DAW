<?php
    $titulo = "Mensajes";
    $encabezado = "Mis mensajes - Pisos e Inmuebles";
    $scripts = ['main.js'];
    include 'cabecera.php';
?>
        <section id="usuarioMensaje">
            <section id="menuTipoMensaje">
                <h2>Tipo de Mensaje:</h2>
                <ul>
                    <li><a href="#mensajesIn">Recibidos</a></li>
                    <li><a href="#mensajesOut">Enviados</a></li>
                </ul>
            </section>

            <section id="mensajesIn" class="tipomensajes">
                <h2>Mensajes enviados</h2>
                <ul>
                    <li>
                        <article>
                            <h3 id="tipoMensaje">Más información</h3>
                            <p class="content">Lorem ipsum dolor sit amet consectetur adipisicing elit. Consequuntur ex id repellendus autem, harum eum architecto rerum obcaecati ut aperiam accusantium, voluptate tenetur saepe reiciendis in quibusdam, doloribus atque. Culpa.</p>
                            <p><time datetime="2015-12-17">17 de Diciembre de 2015</time></p>
                            <p class="usuarioDelMensaje">Asier García de Mateos Ocaña</p>
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

            <section id="mensajesOut" class="tipomensajes">
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
        </section>
<?php
    include 'pie.php';
?>