<?php
/**
 * userFotos.php
 * Página PRIVADA para ver las fotos de un anuncio propio.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. CONTROL DE ACCESO
if (!isset($_SESSION['user']) || !isset($_SESSION['user_id'])) {
    header("Location: ./login.php?error=debes_iniciar_sesion");
    exit;
}

$userId = $_SESSION['user_id'];
$idAnuncio = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idAnuncio <= 0) {
    header("Location: ./perfil.php");
    exit;
}

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
$fotos = $datos['fotos'];

$mysqli->close();

// 4. VERIFICAR PROPIEDAD (Seguridad)
if (!$anuncio) {
    die("El anuncio no existe.");
}
if ($anuncio['Propietario'] !== $userId) {
    // Si el usuario intenta ver fotos de un anuncio que no es suyo a través de esta página privada
    header("Location: ./index.php?error=acceso_no_autorizado");
    exit;
}

// 5. RENDERIZAR
$titulo = "Gestionar Fotos - " . htmlspecialchars($anuncio['Titulo']);
$encabezado = "Fotos de mi anuncio";
require 'cabecera.php';
?>

<section id="lastUploaded">
        <h2><?php echo htmlspecialchars($anuncio['Titulo']); ?></h2>
        <p style="font-size: 1.8rem;">Total de fotos: <strong><?php echo count($fotos); ?></strong></p>
        <a href="addFoto.php?id=<?php echo $idAnuncio; ?>" class="btn">Añadir nueva foto</a>

    <?php if (empty($fotos)): ?>
        <p class="no-results">Este anuncio no tiene fotos actualmente.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($fotos as $foto): ?>
                <li>
                    <article>
                        <figure>
                            <?php if (!empty($foto['Foto'])): ?>
                                <img src="./img/<?php echo htmlspecialchars($foto['Foto']); ?>" alt="<?php echo htmlspecialchars($foto['Alternativo']); ?>">
                            <?php else: ?>
                                <img src="./img/no_image.png" alt="Sin imagen">
                            <?php endif; ?>
                        </figure>
                        
                        <!-- Título de la foto -->
                        <h2><?php echo htmlspecialchars($foto['Titulo'] ?: 'Sin título'); ?></h2>
                        <hr>
                        <p><?php echo htmlspecialchars($foto['Alternativo']); ?></p>
                        
                        <!-- Espacio reservado para botón de eliminar (próxima práctica) -->
                        <div style="margin-top: 15px; text-align: center;">
                            <a href="guardEliminarFoto.php?id=<?php echo $foto['IdFoto']; ?>&anuncio=<?php echo $idAnuncio; ?>" class="btn rojo" >Eliminar foto</a>
                        </div>
                    </article>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <a href="userAnuncio.php?id=<?php echo $idAnuncio; ?>" class="btn">Volver a mi anuncio</a>
</section>

<?php
require 'pie.php';
?>