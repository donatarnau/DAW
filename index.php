<?php
/*
 * Fichero: index.php
 * (Reemplaza a index.html)
 */

// 1. DEFINIR VARIABLES PARA LA CABECERA
$titulo = "Bienvenido a PI - Pisos e Inmuebles";
$encabezado = "Pisos e Inmuebles";

// --- 2. CONEXIÓN A LA BD ---
$config = parse_ini_file('config.ini');
if (!$config) die("Error al leer config.ini");
@$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
if ($mysqli->connect_errno) die("Error de conexión a la BD: " . $mysqli->connect_error);

// --- 3. PREPARAMOS LOS ULTIMOS ANUNCIOS ---
$ultimosAnuncios = [];
$sqlAnuncios = "SELECT A.IdAnuncio, A.Titulo, A.Precio, A.FRegistro, A.Ciudad, P.NomPais, A.FPrincipal , A.Alternativo
                FROM ANUNCIOS A
                JOIN PAISES P ON A.Pais = P.IdPais
                ORDER BY A.FRegistro DESC
                LIMIT 5";

if ($stmt = $mysqli->prepare($sqlAnuncios)) {
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $ultimosAnuncios[] = $row;
    }
    $stmt->close();
}

// Cerramos conexión, ya tenemos todos los datos
$mysqli->close();







require 'cabecera.php';
?>
        <section id="lastUploaded">
            <h2>ULTIMOS ANUNCIOS</h2>
            <?php if (empty($ultimosAnuncios)): ?>
                <p class="no-results">Aún no hay publicado ningún anuncio.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($ultimosAnuncios as $anuncio): ?>
                        <li>
                            <article>
                                <figure>
                                    <!-- Enlace al detalle del anuncio -->
                                    <a href="./anuncio.php?id=<?php echo $anuncio['IdAnuncio']; ?>">
                                        <?php if (!empty($anuncio['FPrincipal'])): ?>
                                            <img src="./img/<?php echo htmlspecialchars($anuncio['FPrincipal']); ?>" alt="<?php echo htmlspecialchars($anuncio['Alternativo']); ?>">
                                        <?php else: ?>
                                            <img src="./img/no_image.png" alt="Sin imagen disponible">
                                        <?php endif; ?>
                                    </a>
                                </figure>
                                <a href="./anuncio.php?id=<?php echo $anuncio['IdAnuncio']; ?>">
                                    <h2><?php echo htmlspecialchars($anuncio['Titulo']); ?></h2>
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