<?php
    $titulo = "Mensajes";
    $encabezado = "Mis mensajes - Pisos e Inmuebles";
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    // Usamos el user_id, que es más seguro para las consultas
    if (!isset($_SESSION['user_id'])) {
        header("Location: ./index.php?error=acceso_denegado");
        exit;
    }

    $userId = $_SESSION['user_id']; // ID del usuario actual

    // --- CONEXIÓN A LA BD ---
    $config = parse_ini_file('config.ini');
    if (!$config) die("Error al leer config.ini");
    @$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
    if ($mysqli->connect_errno) die("Error de conexión a la BD: " . $mysqli->connect_error);


    // --- 1. OBTENER MENSAJES RECIBIDOS (Donde soy el DESTINO) ---
    $mensajesRecibidos = [];
    $sqlRecibidos = "SELECT M.Texto, M.FRegistro, TM.NomTMensaje, U_Origen.NomUsuario 
                     FROM MENSAJES M
                     JOIN TIPOSMENSAJES TM ON M.TMensaje = TM.IdTMensaje
                     JOIN USUARIOS U_Origen ON M.UsuOrigen = U_Origen.IdUsuario
                     WHERE M.UsuDestino = ?
                     ORDER BY M.FRegistro DESC";
    
    if ($stmt = $mysqli->prepare($sqlRecibidos)) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $resRecibidos = $stmt->get_result();
        while ($row = $resRecibidos->fetch_assoc()) {
            $mensajesRecibidos[] = $row;
        }
        $totalRecibidos = $resRecibidos->num_rows;
        $stmt->close();
    } else {
        die("Error en la consulta de mensajes recibidos: " . $mysqli->error);
    }

    // --- 2. OBTENER MENSAJES ENVIADOS (Donde soy el ORIGEN) ---
    $mensajesEnviados = [];
    $sqlEnviados = "SELECT M.Texto, M.FRegistro, TM.NomTMensaje, U_Destino.NomUsuario 
                    FROM MENSAJES M
                    JOIN TIPOSMENSAJES TM ON M.TMensaje = TM.IdTMensaje
                    JOIN USUARIOS U_Destino ON M.UsuDestino = U_Destino.IdUsuario
                    WHERE M.UsuOrigen = ?
                    ORDER BY M.FRegistro DESC";

    if ($stmt = $mysqli->prepare($sqlEnviados)) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $resEnviados = $stmt->get_result();
        while ($row = $resEnviados->fetch_assoc()) {
            $mensajesEnviados[] = $row;
        }
        $totalEnviados = $resEnviados->num_rows;
        $stmt->close();
    } else {
        die("Error en la consulta de mensajes enviados: " . $mysqli->error);
    }

    $mysqli->close();
    
    // --- RENDERIZAR VISTA ---
    require 'cabecera.php';
?>
        <section id="usuarioMensaje">
            <section id="menuTipoMensaje">
                <h2>Tipo de Mensaje:</h2>
                <ul>
                    <li><a href="#mensajesIn">Recibidos (<?php echo $totalRecibidos; ?>)</a></li>
                    <li><a href="#mensajesOut">Enviados (<?php echo $totalEnviados; ?>)</a></li>
                </ul>
            </section>

            <section id="mensajesIn" class="tipomensajes">
                <h2>Mensajes Recibidos (<?php echo $totalRecibidos; ?>)</h2>
                <?php if ($totalRecibidos == 0): ?>
                    <p>No tienes mensajes recibidos.</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($mensajesRecibidos as $msg): ?>
                        <li>
                            <article>
                                <h3 id="tipoMensaje"><?php echo htmlspecialchars($msg['NomTMensaje']); ?></h3>
                                <p class="content"><?php echo htmlspecialchars($msg['Texto']); ?></p>
                                <p><time datetime="<?php echo $msg['FRegistro']; ?>">
                                    <?php echo date("d/m/Y H:i", strtotime($msg['FRegistro'])); ?>
                                </time></p>
                                <p class="usuarioDelMensaje">De: <strong><?php echo htmlspecialchars($msg['NomUsuario']); ?></strong></p>
                            </article>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>

            <section id="mensajesOut" class="tipomensajes">
                <h2>Mensajes Enviados (<?php echo $totalEnviados; ?>)</h2>
                <?php if ($totalEnviados == 0): ?>
                    <p>No has enviado ningún mensaje.</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($mensajesEnviados as $msg): ?>
                        <li>
                            <article>
                                <h3 id="tipoMensaje"><?php echo htmlspecialchars($msg['NomTMensaje']); ?></h3>
                                <p class="content"><?php echo htmlspecialchars($msg['Texto']); ?></p>
                                <p><time datetime="<?php echo $msg['FRegistro']; ?>">
                                    <?php echo date("d/m/Y H:i", strtotime($msg['FRegistro'])); ?>
                                </time></p>
                                <p class="usuarioDelMensaje">Para: <strong><?php echo htmlspecialchars($msg['NomUsuario']); ?></strong></p>
                            </article>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
        </section>
<?php
    require 'pie.php';
?>