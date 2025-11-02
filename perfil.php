<?php
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user'])) {
        header("Location: ./index.php?error=acceso_denegado");
        exit;
    }
    // 1. CONTROL DE ACCESO (OBLIGATORIO)
    // Si no hay 'user' en la URL, el usuario no está logueado.


    // 2. Si llegamos aquí, el usuario SÍ está "logueado".
    // Guardamos su nombre de forma segura.
    $username = htmlspecialchars($_SESSION['user']);

    // 3. DEFINIR VARIABLES PARA LA CABECERA
    $titulo = "Perfil de " . $username;

    $hora = (int)date('H');
    $encabezado = '';
    $ultima_visita = '';

    if ($hora >= 6 && $hora <= 11) {
        $encabezado = "Buenos días $username";
    } elseif ($hora >= 12 && $hora <= 15) {
        $encabezado = "Hola $username";
    } elseif ($hora >= 16 && $hora <= 19) {
        $encabezado = "Buenas tardes $username";
    } else {
        // De 20 a 23 y de 0 a 5
        $encabezado = "Buenas noches $username";
    }

    if(isset($_SESSION['ultima_visita'])) {
        $fecha = new DateTime($_SESSION['ultima_visita']);
        $ultima_visita = $fecha->format('d/m/Y \a \l\a\s H:i:s');
    }

    // 4. INCLUIR LA CABECERA
    // cabecera.php verá que $_GET['user'] existe
    // y mostrará automáticamente el menú de logueado.
    require 'cabecera.php';

    if(!empty($ultima_visita)){
        echo '<p id="lastVisit">Visitaste esta página por última vez el '. $ultima_visita .'</p>';
    }
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
                        <li><a href="./userMensajes.php">Mis mensajes</a></li>
                        <li class="noMovil"><a href="./publicar_anuncio.php">Publicar anuncio</a></li>
                        <li><a href="./solicitar_folleto.php">Solicitar folleto</a></li>
                        <li><a href="./addFoto.php">Añadir foto a anuncio</a></li>
                        <li class="botonRojo"><a href="./logout.php">Cerrar sesión</a></li>
                        <li class="botonRojo"><a href="./404.php">Darse de baja</a></li>
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
                            <a href="<?php echo './userAnuncio.php?id=1'; ?>"><img src="./img/a1.jpeg" alt="anuncio1"></a>
                        </figure>
                        <a href="<?php echo './userAnuncio.php?id=1'; ?>"><h2>
                            Anuncio 1 de Usuario
                        </h2></a>
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
                            <a href="<?php echo './userAnuncio.php?id=2'; ?>"><img src="./img/a2.png" alt="anuncio1"></a>
                        </figure>
                        <a href="<?php echo './userAnuncio.php?id=2'; ?>"><h2>
                            Anuncio 2 de Usuario
                        </h2></a>
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
    require 'pie.php';
?>