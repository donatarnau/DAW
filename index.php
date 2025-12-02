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

// haz un select de todos los id de anuncios de la base de datos
$idsAnuncios = [];
$sqlIds = "SELECT IdAnuncio FROM ANUNCIOS";
if ($stmt = $mysqli->prepare($sqlIds)) {
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $idsAnuncios[] = $row['IdAnuncio'];
    }
    $stmt->close();
}

// vamos a llamar a la funcion para leer los anuncios escogidos

require_once 'services/parserEscogidos.php';
require_once 'services/parserConsejos.php';

$anunciosEscogidos = leerAnunciosFormatoPropio('data/escogidos.propio');
$consejos = leerConsejos("data/consejos.json");

if (!empty($consejos)) {
    $consejo = $consejos[array_rand($consejos)];
}else{
    // Este es un valor por defecto en caso de que no se pueda leer el fichero

    $consejo = [
        'categoria' => 'Compra',
        'importancia' => 'Media',
        'descripcion' => 'Cuando compres una propiedad, asegúrate de revisar todos los documentos legales.'
    ];
}

$random =[];
$anuncioRandom = [];

if ($anunciosEscogidos === false) {
    // Manejar el error de lectura del archivo
    $anunciosEscogidos = [];
}

if (!empty($anunciosEscogidos)) {

    // vamos a comprobar que al menos un id de los anuncios escogidos existe en la base de datos
    $existe = false;
    foreach ($anunciosEscogidos as $anuncio) {
        if (in_array($anuncio['id'], $idsAnuncios)) {
            $existe = true;
            break;
        }
    }

    if (!$existe) {
        // si ninguno existe, dejamos el array vacío
        $anunciosEscogidos = [];

        // seleccionamos un anuncio de la base de datos al azar con un comentario genérico

        $idParche = $idsAnuncios[array_rand($idsAnuncios)];

        $random = [
            'id' => $idParche,
            'persona' => 'Asier García',
            'comentario' => '¡Gran anuncio! Muy recomendable.'
        ];
    }else{
        // haz un bucle hasta que encuentres un anuncio cuyo id exista en la base de datos
        do {
            $random = $anunciosEscogidos[array_rand($anunciosEscogidos)];
        } while (!in_array($random['id'], $idsAnuncios));

    }

    // una vez tenemos un anuncio válido, extraemos sus datos de la base de datos
    $sqlRandom = "SELECT A.IdAnuncio, A.Titulo, A.Precio, A.Ciudad, A.FPrincipal, A.Alternativo
                FROM ANUNCIOS A
                WHERE A.IdAnuncio = ?;";

    if ($stmt = $mysqli->prepare($sqlRandom)) {
        $stmt->bind_param("i", $random['id']);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $anuncioRandom = $row;
        }
        $stmt->close();
    }



    // extraemos los datos de la base de datos
}

// Cerramos conexión, ya tenemos todos los datos
$mysqli->close();

require 'cabecera.php';
?>
    <?php if (isset($_GET['temp'])): ?>

        <p class="temp-message greenM"><?= htmlspecialchars($_GET['temp']); ?></p>
       
    <?php endif; ?>
    <?php if (isset($_GET['wrong'])): ?>

        <p class="temp-message redM"><?= htmlspecialchars($_GET['wrong']); ?></p>
       
    <?php endif; ?>

        <section id="lastUploaded">
            <h2>ULTIMOS ANUNCIOS</h2>
            <?php if (empty($ultimosAnuncios)): ?>
                <p class="no-results">Aún no hay publicado ningún anuncio.</p>
            <?php else: ?>
                <ul>
                    <!-- AQUI HAY QUE PONER EL ANUNCIO ESCOGIDO -->
                    <?php if (!empty($random)): ?>
                        <li class="esc">
                            <article>
                                <figure>
                                    <!-- Enlace al detalle del anuncio -->
                                    <a href="./anuncio.php?id=<?php echo htmlspecialchars($anuncioRandom['IdAnuncio']); ?>">
                                        <?php if (!empty($anuncioRandom['FPrincipal'])): ?>
                                            <img src="./img/<?php echo htmlspecialchars($anuncioRandom['FPrincipal']); ?>" alt="<?php echo htmlspecialchars($anuncioRandom['Alternativo']); ?>">
                                        <?php else: ?>
                                            <img src="./img/no_image.png" alt="Sin imagen disponible">
                                        <?php endif; ?>
                                    </a>
                                </figure>
                                <a href="./anuncio.php?id=<?php echo htmlspecialchars($anuncioRandom['IdAnuncio']); ?>">
                                    <h2><?php echo htmlspecialchars($anuncioRandom['Titulo']); ?></h2>
                                </a>
                                <hr>
                                <br>
                                <p><?php echo htmlspecialchars($random['persona']); ?> opina: "<?php echo nl2br(htmlspecialchars($random['comentario'])); ?>"</p>
                                <br>
                            </article>
                        </li>
                    <?php endif; ?>
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
        <section id="consejoDelDia">
            <h2>CONSEJO DEL DÍA</h2>
            <hr class="hrIzq">
            <hr class="hrDer">
            <article>
                <h3>Consejo de <?php echo htmlspecialchars($consejo['categoria']); ?></h3>
                <p><?php echo htmlspecialchars($consejo['descripcion']); ?></p>
            </article>
        </section>
<?php
    require 'pie.php';
?>