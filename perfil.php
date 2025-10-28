<?php
    // 1. CONTROL DE ACCESO (OBLIGATORIO)
    // Si no hay 'user' en la URL, el usuario no está logueado.
    if (!isset($_GET['user'])) {
        // Redirigimos a la página principal con un error
        header("Location: ./index.php?error=acceso_denegado");
        exit;
    }

    // 2. Si llegamos aquí, el usuario SÍ está "logueado".
    // Guardamos su nombre de forma segura.
    $username = htmlspecialchars($_GET['user']);

    // 3. DEFINIR VARIABLES PARA LA CABECERA
    $titulo = "Perfil de " . $username;
    $encabezado = "¡Bienvenido, " . $username . "!";
    $scripts = ['main.js']; // (Recuerda que cabecera.php ya filtra login.js)

    // 4. INCLUIR LA CABECERA
    // cabecera.php verá que $_GET['user'] existe
    // y mostrará automáticamente el menú de logueado.
    include 'cabecera.php';
?>
        <ul id="listadoPerfil">
            <li>
                <section id="datosPersonales">
                    <h2>Mis datos personales</h2>
                    <ul>
                        <li><p>Nombre</p></li>
                        <li><p>Apellidos</p></li>
                        <li><p>Dirección</p></li>
                        <li><p>Codigo Postal</p></li>
                    </ul>
                    <button><p>Modificar datos</p></button>
                </section>
            </li>
            <li>
                <section id="enlacesUsuario">
                    <h2>Mis opciones</h2>
                    <ul>
                        <li><a href="#userAnuncios">Mis anuncios</a></li>
                        <li><a href="./userMensajes.php<?php echo $userQueryParam; ?>">Mis mensajes</a></li>
                        <li class="noMovil"><a href="./publicar_anuncio.php<?php echo $userQueryParam; ?>">Publicar anuncio</a></li>
                        <li><a href="./solicitar_folleto.php<?php echo $userQueryParam; ?>">Solicitar folleto</a></li>
                        <li class="botonRojo"><a href="./404.php<?php echo $userQueryParam; ?>">Darse de baja</a></li>
                    </ul>
                </section>
            </li>
        </ul>

        <section id="userAnuncios">
            <h2>Mis anuncios</h2>
            <ul>
                <li>
                    <article>
                        <figure>
                            <a href="./anuncio.php"><img src="./img/a1.jpeg" alt="anuncio1"></a>
                        </figure>
                        <a href="./anuncio.php"><h2>Anuncio 1 de Usuario</h2></a>
                        <hr>
                        <p><time datetime="2015-12-17">17 de Diciembre de 2015</time></p>
                        <p>Córdoba</p>
                        <p>España</p>
                        <p>150.000€</p>
                    </article>
                </li>
                <li>
                    <article>
                        <figure>
                            <a href="./anuncio.php"><img src="./img/a2.png" alt="anuncio1"></a>
                        </figure>
                        <a href="./anuncio.php"><h2>Anuncio 2 de Usuario</h2></a>
                        <hr>
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