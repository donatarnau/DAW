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

// --- 3. PREPARAMOS LOS ULTIMOS ANUNCIOS ---
$anuncio = [];
$fotos = [];
$sqlAnuncios = "SELECT 
    A.IdAnuncio,
    A.Titulo AS TituloAnuncio,
    F.IdFoto,
    F.Titulo AS TituloFoto,
    F.Foto,
    F.Alternativo
FROM ANUNCIOS A
LEFT JOIN FOTOS F ON F.Anuncio = A.IdAnuncio
WHERE A.IdAnuncio = ?";

if ($stmt = $mysqli->prepare($sqlAnuncios)) {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $anuncio = [
            'Nombre' => $row['TituloAnuncio'],
        ];
        $fotos[] = [
            'IdFoto' => $row['IdFoto'],
            'Titulo' => $row['TituloFoto'],
            'Foto' => './img/' . $row['Foto'],
            'Alternativo' => $row['Alternativo'],
        ];
    }
    $stmt->close();
}

// Cerramos conexión, ya tenemos todos los datos
$mysqli->close();



// 5. Configurar la cabecera
$titulo = "Fotos - " . $anuncio['Nombre'];
$encabezado = "Fotos - " . $anuncio['Nombre'];
require 'cabecera.php';

?>
        <section id="lastUploaded">
            <?php if (empty($fotos)): ?>
                <p class="no-results">No hay fotos para este anuncio.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($fotos as $foto): ?>
                        <li>
                            <article>
                                <figure>
                                    <?php if (!empty($foto['Foto'])): ?>
                                        <img src="<?php echo htmlspecialchars($foto['Foto']); ?>" alt="<?php echo htmlspecialchars($foto['Alternativo']); ?>">
                                    <?php else: ?>
                                        <img src="./img/no_image.png" alt="Sin imagen disponible">
                                    <?php endif; ?>
                                    
                                </figure>
                                <h2><?php echo htmlspecialchars($foto['Titulo']); ?></h2>
                                <hr>
                                <p><?php echo htmlspecialchars($foto['Alternativo']); ?></p>
                            </article>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

        </section>
<?php
require 'pie.php';
?>
