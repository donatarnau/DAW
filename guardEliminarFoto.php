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

$id = (int)$_GET['id']; // Convertir a número entero -> Id de la foto a eliminar
$idAnuncio = isset($_GET['anuncio']) ? (int)$_GET['anuncio'] : 0;

// 2. CONEXIÓN A BD
$config = parse_ini_file('config.ini');
if (!$config) die("Error crítico de configuración.");
@$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
if ($mysqli->connect_errno) die("Error BD");
$mysqli->set_charset('utf8mb4');

// 3. USAR LÓGICA COMÚN
require_once 'services/logic_fotos.php';
$datos = obtener_datos_fotos($mysqli, $idAnuncio);
$anuncio = $datos['anuncio'];
// NO CERRAMOS LA CONEXIÓN AQUÍ

// 4. Comprobar si se encontró el anuncio
if (empty($anuncio)) {
    header("Location: ./index.php?error=anuncio_no_encontrado");
    exit;
}

$sqlFoto = "SELECT * FROM FOTOS WHERE IdFoto = ? AND Anuncio = ?";
$stmt = $mysqli->prepare($sqlFoto);
$stmt->bind_param("ii", $id, $idAnuncio);
$stmt->execute();
$resFoto = $stmt->get_result();
$foto = $resFoto->fetch_assoc();
$stmt->close();

// AHORA CERRAREMOS LA CONEXIÓN AL FINAL


// 6. Configurar la cabecera
$titulo = "Eliminar foto - " . htmlspecialchars($anuncio['Titulo']);
$encabezado = "Confirmar eliminación de la Foto";
require 'cabecera.php';

?>
        <section id="anuncioDetalle">
            
            <article class="delete-confirmation">
                <h3>¿Estás seguro de que quieres eliminar esta foto?</h3>
                <a class="btn" href="./anuncio.php?id=<?= urlencode($idAnuncio)?>">No, volver atrás</a>
                <a class="btn rojo" href="./services/deleteFoto.php?id=<?= urlencode($id)?>&anuncio=<?= urlencode($idAnuncio)?>">Sí, eliminar foto<br>Esta acción no se puede deshacer</a>
            </article>
    
            <img src="./img/<?php echo htmlspecialchars($foto['Foto']); ?>" alt="<?php echo htmlspecialchars($foto['Alternativo']); ?>">
            <h2 class="anuncio-titulo"><?= htmlspecialchars($foto['Titulo']) . ' - ' . htmlspecialchars($anuncio['Titulo']) ?></h2>

        </section> 
<?php
$mysqli->close();
require 'pie.php';
?>