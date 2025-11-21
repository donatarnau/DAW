<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: ./index.php?error=acceso_denegado");
    exit;
}

// 1. Control de acceso básico
if (!isset($_GET['id'])) {
    header("Location: ./index.php?error=falta_id");
    exit;
}

$id = (int)$_GET['id']; // Convertir a número entero

// --- 2. CONEXIÓN A LA BD ---
$config = parse_ini_file('config.ini');
if (!$config) die("Error al leer config.ini");
@$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
if ($mysqli->connect_errno) die("Error de conexión a la BD: " . $mysqli->connect_error);

// --- 3. PREPARAMOS EL ANUNCIO ---
$anuncio = [];
$anuncioCookie = []; // para que funcione aqui tambien
$fotos = [];
$caracteristicas = [];
$sqlAnuncios = "SELECT 
    A.IdAnuncio, TA.NomTAnuncio AS TipoAnuncio, TV.NomTVivienda AS TipoVivienda,
    A.Titulo, A.Texto, A.Precio, A.Ciudad, P.NomPais, A.FPrincipal, A.Alternativo,
    A.FRegistro, A.Superficie, A.NHabitaciones, A.NBanyos, A.Planta, A.Anyo,
    U.NomUsuario AS NomUsuario, F.IdFoto, F.Titulo AS TituloFoto, F.Foto AS RutaFoto,
    F.Alternativo AS AlternativoFoto
FROM ANUNCIOS A
JOIN USUARIOS U ON A.Usuario = U.IdUsuario
JOIN PAISES P ON A.Pais = P.IdPais
JOIN TIPOSANUNCIOS TA ON A.TAnuncio = TA.IdTAnuncio
JOIN TIPOSVIVIENDAS TV ON A.TVivienda = TV.IdTVivienda
LEFT JOIN FOTOS F ON A.IdAnuncio = F.Anuncio
WHERE A.IdAnuncio = ?";

if ($stmt = $mysqli->prepare($sqlAnuncios)) {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $anuncio = [
            'IdAnuncio' => $row['IdAnuncio'], 'TipoAnuncio' => $row['TipoAnuncio'],
            'TipoVivienda' => $row['TipoVivienda'], 'Titulo' => $row['Titulo'],
            'Texto' => $row['Texto'], 'Precio' => $row['Precio'], 'Ciudad' => $row['Ciudad'],
            'NomPais' => $row['NomPais'], 'FPrincipal' => './img/' . $row['FPrincipal'],
            'Alternativo' => $row['Alternativo'], 'FRegistro' => $row['FRegistro'],
            'Usuario' => $row['NomUsuario'], 'Superficie' => $row['Superficie'],
            'NHabitaciones' => $row['NHabitaciones'], 'NBanyos' => $row['NBanyos'],
            'Planta' => $row['Planta'], 'Anyo' => $row['Anyo'],
        ];
        $anuncioCookie = [
            'id' => $row['IdAnuncio'],
            'nombre' => $row['Titulo'],
            'foto' => './img/' . $row['FPrincipal'],
            'ciudad' => $row['Ciudad'],
            'pais' => $row['NomPais'],
            'precio' => $row['Precio'],
        ];
        $caracteristicas = [
            'Superficie' => 'Superficie: ' . $row['Superficie'] . ' m²',
            'Habitaciones' => 'Número de habitaciones: ' . $row['NHabitaciones'],
            'Banyos' => 'Número de baños: ' . $row['NBanyos'],
            'Planta' => 'Planta: ' . $row['Planta'],
            'Anyo' => 'Año de construcción: ' . $row['Anyo'],
        ];
        if ($row['RutaFoto']) {
            $fotos[] = [
                'IdFoto' => $row['IdFoto'], 'Titulo' => $row['TituloFoto'],
                'Foto' => './img/' . $row['RutaFoto'], 'Alternativo' => $row['AlternativoFoto'],
            ];
        }
    }
    $stmt->close();
}
// NO CERRAMOS LA CONEXIÓN AQUÍ

// 4. Comprobar si se encontró el anuncio
if (empty($anuncio)) {
    header("Location: ./index.php?error=anuncio_no_encontrado");
    exit;
}

// --- 5. OBTENER MENSAJES DEL ANUNCIO (Lógica de mensajesAnuncio.php) ---
$mensajesRecibidos = [];
// Usamos la lógica de mensajesAnuncio.php
$sqlMensajes = "SELECT M.Texto, M.FRegistro, TM.NomTMensaje, U_Origen.NomUsuario 
                FROM MENSAJES M
                JOIN TIPOSMENSAJES TM ON M.TMensaje = TM.IdTMensaje
                JOIN USUARIOS U_Origen ON M.UsuOrigen = U_Origen.IdUsuario
                WHERE M.Anuncio = ?
                ORDER BY M.FRegistro DESC"; 

if ($stmt = $mysqli->prepare($sqlMensajes)) {
    $stmt->bind_param("i", $id); // Usamos $id (que es el IdAnuncio de esta página)
    $stmt->execute();
    $resMensajes = $stmt->get_result();
    $totalMensajes = $resMensajes->num_rows; // Contamos el total
    while ($row = $resMensajes->fetch_assoc()) {
        $mensajesRecibidos[] = $row;
    }
    $stmt->close();
} else {
    $totalMensajes = 0;
}
// AHORA CERRAREMOS LA CONEXIÓN AL FINAL

require_once 'services/ultimos_anuncios.php';
ua_actualizar($anuncioCookie['id'],$anuncioCookie);

// 6. Configurar la cabecera
$titulo = "Anuncio - " . htmlspecialchars($anuncio['Titulo']);
$encabezado = "Detalle del anuncio";
require 'cabecera.php';

?>
        <section id="anuncioDetalle">
            <h2 class="pill">Tipo de anuncio: <?= htmlspecialchars($anuncio['TipoAnuncio']) ?></h2>
            <h2 class="pill">Tipo de vivienda: <?= htmlspecialchars($anuncio['TipoVivienda']) ?></h2>
            
            <picture class="anuncio-hero">
                <img src="<?= htmlspecialchars($anuncio['FPrincipal']) ?>" alt="<?= htmlspecialchars($anuncio['Alternativo']) ?>">
            </picture>

            <h2 class="anuncio-titulo"><?= htmlspecialchars($anuncio['Titulo']) . ' - ' . htmlspecialchars($anuncio['Usuario']) ?></h2>

            <p class="anuncio-descripcion">
                <strong>Descripción:</strong> <?= htmlspecialchars($anuncio['Texto']) ?>
            </p>

            <ul class="meta-list">
                <li><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($anuncio['FRegistro'])) ?></li>
                <li><strong>Ciudad:</strong> <?= htmlspecialchars($anuncio['Ciudad']) ?></li>
                <li><strong>País:</strong> <?= htmlspecialchars($anuncio['NomPais']) ?></li>
                <li class="precio"><strong>Precio:</strong> <?= number_format($anuncio['Precio'], 0, ',', '.') ?> €</li>
            </ul>

            <h3>Características</h3>
            <ul class="ficha">
                <?php foreach ($caracteristicas as $c): ?>
                    <li><?= htmlspecialchars($c) ?></li>
                <?php endforeach; ?>
            </ul>

            <h3>Fotos</h3>
            <section class="anuncio-galeria">
                <?php foreach ($fotos as $fotoData): ?>
                    <figure>
                        <img src="<?= htmlspecialchars($fotoData['Foto']) ?>" alt="<?= htmlspecialchars($fotoData['Alternativo']) ?>">
                        <figcaption><?= htmlspecialchars($fotoData['Titulo'] ? $fotoData['Titulo'] : $fotoData['Alternativo']) ?></figcaption>
                    </figure>
                <?php endforeach; ?>
            </section>

            <a class="btn" href="./userFotos.php?id=<?= urlencode($id) ?>">Ver todas las fotos</a>
            <a class="btn" href="./addFoto.php?id=<?= urlencode($id)?>">Añadir foto</a>
        </section> 
        
        <section id="mensajesAnuncio" class="tipomensajes">
            <h2>Mensajes recibidos (<?php echo $totalMensajes; ?>)</h2>
                <ul>
                    <?php foreach ($mensajesRecibidos as $msg): ?>
                    <li>
                        <article>
                            <h3 id="tipoMensaje"><?php echo htmlspecialchars($msg['NomTMensaje']); ?></h3>
                            <p class="content"><?php echo htmlspecialchars($msg['Texto']); ?></p>
                            
                            <p><time datetime="<?php echo date('Y-m-d', strtotime($msg['FRegistro'])); ?>">
                                <?php echo date('d/m/Y', strtotime($msg['FRegistro'])); ?>
                            </time></p>
                            
                            <p class="usuarioDelMensaje"><?php echo htmlspecialchars($msg['NomUsuario']); ?></p>
                        </article>
                    </li>
                    <?php endforeach; ?>
                </ul>
        </section> 
<?php
$mysqli->close();
require 'pie.php';
?>