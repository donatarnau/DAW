<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // 1. CONTROL DE ACCESO
    if (!isset($_SESSION['user']) || !isset($_SESSION['user_id'])) {
        header("Location: ./login.php?error=debes_iniciar_sesion");
        exit;
    }

    $userId = $_SESSION['user_id'];
    $username = htmlspecialchars($_SESSION['user']);

    // --- 2. CONEXIÓN A LA BD ---
    $config = parse_ini_file('config.ini');
    if (!$config) die("Error al leer config.ini");
    @$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
    if ($mysqli->connect_errno) die("Error de conexión a la BD: " . $mysqli->connect_error);

    // --- 3. OBTENER DATOS DEL PERFIL ---
    $userFoto = '';
    $userFechaRegistro = '';
    // Consulta para obtener foto y fecha de registro
    if ($stmt = $mysqli->prepare("SELECT Foto, FRegistro FROM USUARIOS WHERE IdUsuario = ?")) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($fotoDB, $fRegistroDB);
        if ($stmt->fetch()) {
            $userFoto = $fotoDB;
            // Formateamos la fecha de registro para que se vea bonita
            $userFechaRegistro = date("d/m/Y", strtotime($fRegistroDB));
        }
        $stmt->close();
    }

    // --- 4. OBTENER ANUNCIOS DEL USUARIO ---
    // Necesitamos JOIN con PAISES para mostrar el nombre del país.
    // Seleccionamos solo los campos necesarios para el listado simplificado.
    $anuncios = [];
    $sqlAnuncios = "SELECT A.IdAnuncio, A.Titulo, A.Precio, A.FRegistro, A.Ciudad, P.NomPais, A.FPrincipal 
                    FROM ANUNCIOS A
                    JOIN PAISES P ON A.Pais = P.IdPais
                    WHERE A.Usuario = ?
                    ORDER BY A.FRegistro DESC";

    if ($stmt = $mysqli->prepare($sqlAnuncios)) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $anuncios[] = $row;
        }
        $stmt->close();
    }
    
    // Cerramos conexión, ya tenemos todos los datos
    $mysqli->close();


    // --- 5. LÓGICA DE VISUALIZACIÓN (Cabecera, saludo, etc.) ---
    $titulo = "Perfil de " . $username;
    $hora = (int)date('H');
    $encabezado = '';
    if ($hora >= 6 && $hora <= 11) {
        $encabezado = "Buenos días $username";
    } elseif ($hora >= 12 && $hora <= 15) {
        $encabezado = "Hola $username";
    } elseif ($hora >= 16 && $hora <= 19) {
        $encabezado = "Buenas tardes $username";
    } else {
        $encabezado = "Buenas noches $username";
    }

    require 'cabecera.php';

    if(isset($_SESSION['ultima_visita'])) {
        $fechaVisita = new DateTime($_SESSION['ultima_visita']);
        echo '<p id="lastVisit">Visitaste esta página por última vez el '. $fechaVisita->format('d/m/Y \a \l\a\s H:i:s') .'</p>';
    }
?>
        <ul id="listadoPerfil">
            <li>
                <section id="datosPersonales">
                    <h2>Mi perfil público</h2>
                    <!-- Mostramos la foto si existe, si no, una por defecto o nada -->
                    <?php if (!empty($userFoto)): ?>
                        <img src="img/<?php echo htmlspecialchars($userFoto); ?>" alt="Foto de perfil de <?php echo $username; ?>" class="perfil-foto" style="max-width: 150px; border-radius: 50%;">
                    <?php else: ?>
                        <!-- Puedes poner una imagen placeholder aquí si quieres -->
                        <div class="no-foto" style="width: 100px; height: 100px; background: #ccc; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                            <span>Sin foto</span>
                        </div>
                    <?php endif; ?>
                    
                    <ul>
                        <li><strong>Usuario:</strong> <?php echo $username; ?></li>
                        <li><strong>Miembro desde:</strong> <?php echo $userFechaRegistro; ?></li>
                    </ul>
                    <!-- Se ha eliminado el botón de aquí -->
                </section>
            </li>
            <li>
                <section id="enlacesUsuario">
                    <h2>Mis opciones</h2>
                    <ul>
                        <!-- NUEVO: Enlace a modificar datos integrado en la lista -->
                        <li><a href="./mis_datos.php">Modificar mis datos</a></li>
                        <li><a href="#userAnuncios">Mis anuncios (<?php echo count($anuncios); ?>)</a></li>
                        <li><a href="./userMensajes.php">Mis mensajes</a></li>
                        <li class="noMovil"><a href="./crearAnuncio.php">Publicar anuncio</a></li>
                        <li><a href="./configurar.php">Configurar estilos</a></li>
                        <!-- Estas páginas aún no existen o no las hemos hecho, las dejo comentadas o como estaban -->
                        <!-- <li><a href="./solicitar_folleto.php">Solicitar folleto</a></li> -->
                        <!-- <li><a href="./addFoto.php">Añadir foto a anuncio</a></li> -->
                        <li class="botonRojo"><a href="./logout.php">Cerrar sesión</a></li>
                    </ul>
                </section>
            </li>
        </ul>

        <section id="userAnuncios">
            <h2>Mis anuncios publicados</h2>
            <?php if (empty($anuncios)): ?>
                <p>Aún no has publicado ningún anuncio.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($anuncios as $anuncio): ?>
                        <li>
                            <article>
                                <figure>
                                    <!-- Enlace al detalle del anuncio (a implementar por tu compañero) -->
                                    <a href="./ver_anuncio.php?id=<?php echo $anuncio['IdAnuncio']; ?>">
                                        <?php if (!empty($anuncio['FPrincipal'])): ?>
                                            <img src="./img/<?php echo htmlspecialchars($anuncio['FPrincipal']); ?>" alt="Foto principal del anuncio">
                                        <?php else: ?>
                                            <img src="./img/no_image.png" alt="Sin imagen disponible">
                                        <?php endif; ?>
                                    </a>
                                </figure>
                                <a href="./ver_anuncio.php?id=<?php echo $anuncio['IdAnuncio']; ?>">
                                    <h3><?php echo htmlspecialchars($anuncio['Titulo']); ?></h3>
                                </a>
                                <hr>
                                <!-- Formateamos la fecha del anuncio -->
                                <p><time datetime="<?php echo $anuncio['FRegistro']; ?>">
                                    <?php echo date("d/m/Y", strtotime($anuncio['FRegistro'])); ?>
                                </time></p>
                                <p><?php echo htmlspecialchars($anuncio['Ciudad']); ?></p>
                                <p><?php echo htmlspecialchars($anuncio['NomPais']); ?></p>
                                <p class="precio"><?php echo number_format($anuncio['Precio'], 0, ',', '.'); ?> €</p>
                            </article>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
<?php
    require 'pie.php';
?>