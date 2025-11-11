<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // 1. CONTROL DE ACCESO (Solo usuarios logueados)
    if (!isset($_SESSION['user']) || !isset($_SESSION['user_id'])) {
        header("Location: ./login.php?error=debes_iniciar_sesion");
        exit;
    }

    // 2. OBTENER ID DEL ANUNCIO (Desde la URL, ej: ver_mensajes_anuncio.php?id=5)
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        die("Error: No se ha especificado un anuncio válido.");
    }

    $userId = $_SESSION['user_id'];
    $anuncioId = (int)$_GET['id'];

    // 3. CONEXIÓN A LA BD
    $config = parse_ini_file('config.ini');
    if (!$config) die("Error al leer config.ini");
    @$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
    if ($mysqli->connect_errno) die("Error de conexión a la BD: ". $mysqli->connect_error);

    // 4. OBTENER INFO BÁSICA DEL ANUNCIO Y VERIFICAR PROPIETARIO
    $anuncio = null;
    $sqlAnuncio = "SELECT Titulo, FPrincipal, Alternativo, Usuario FROM ANUNCIOS WHERE IdAnuncio = ?";
    if ($stmt = $mysqli->prepare($sqlAnuncio)) {
        $stmt->bind_param("i", $anuncioId);
        $stmt->execute();
        $stmt->bind_result($tituloAnuncio, $fotoAnuncio, $altAnuncio, $propietarioId);
        
        if ($stmt->fetch()) {
            $anuncio = [
                'Titulo' => $tituloAnuncio,
                'Foto' => $fotoAnuncio,
                'Alt' => $altAnuncio,
                'Propietario' => $propietarioId
            ];
        }
        $stmt->close();
    } else {
        die("Error al preparar la consulta del anuncio: " . $mysqli->error);
    }

    // 5. VALIDAR QUE EL ANUNCIO EXISTA Y PERTENEZCA AL USUARIO
    if ($anuncio === null) {
        die("Error: El anuncio no existe.");
    }
    if ($anuncio['Propietario'] !== $userId) {
        die("Error: No tienes permisos para ver los mensajes de este anuncio.");
    }

    // 6. OBTENER MENSAJES DEL ANUNCIO
    $mensajesRecibidos = [];
    $sqlMensajes = "SELECT M.Texto, M.FRegistro, TM.NomTMensaje, U_Origen.NomUsuario 
                    FROM MENSAJES M
                    JOIN TIPOSMENSAJES TM ON M.TMensaje = TM.IdTMensaje
                    JOIN USUARIOS U_Origen ON M.UsuOrigen = U_Origen.IdUsuario
                    WHERE M.Anuncio = ?
                    ORDER BY M.FRegistro DESC"; // Mensajes más nuevos primero

    if ($stmt = $mysqli->prepare($sqlMensajes)) {
        $stmt->bind_param("i", $anuncioId);
        $stmt->execute();
        $resMensajes = $stmt->get_result();
        while ($row = $resMensajes->fetch_assoc()) {
            $mensajesRecibidos[] = $row;
        }
        $totalMensajes = $resMensajes->num_rows; // Contamos el total
        $stmt->close();
    } else {
        die("Error en la consulta de mensajes: " . $mysqli->error);
    }

    $mysqli->close();
    
    // 7. RENDERIZAR VISTA
    $titulo = "Mensajes del Anuncio";
    $encabezado = "Mensajes Recibidos en tu Anuncio";
    require 'cabecera.php';
?>

    <section id="info_anuncio_mensajes">
        <h3>Anuncio: "<?php echo htmlspecialchars($anuncio['Titulo']); ?>"</h3>
        <?php if (!empty($anuncio['Foto'])): ?>
            <img src="img/<?php echo htmlspecialchars($anuncio['Foto']); ?>" alt="<?php echo htmlspecialchars($anuncio['Alt']); ?>" style="max-height: 100px; border-radius: 5px;">
        <?php endif; ?>
    </section>

    <section id="lista_mensajes_anuncio">
        <h2>Total de Mensajes Recibidos: <?php echo $totalMensajes; ?></h2>
        
        <?php if ($totalMensajes == 0): ?>
            <p>Este anuncio aún no ha recibido ningún mensaje.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($mensajesRecibidos as $msg): ?>
                <li>
                    <article class="mensaje-item">
                        <h4 class="msg-tipo"><?php echo htmlspecialchars($msg['NomTMensaje']); ?></h4>
                        <p class="msg-de">De: <strong><?php echo htmlspecialchars($msg['NomUsuario']); ?></strong></p>
                        <p class="msg-contenido">"<?php echo htmlspecialchars($msg['Texto']); ?>"</p>
                        <p class="msg-fecha"><time datetime="<?php echo $msg['FRegistro']; ?>">
                            <?php echo date("d/m/Y \a \l\a\s H:i", strtotime($msg['FRegistro'])); ?>
                        </time></p>
                    </article>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

<?php
    require 'pie.php';
?>