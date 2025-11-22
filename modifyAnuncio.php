<?php

$titulo = "Editar anuncio";
$encabezado = "Editar anuncio - Pisos e Inmuebles";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user'])) {
    header("Location: ./index.php?error=acceso_denegado");
    exit;
}

// --- Leer errores y valores previos si hay redirección ---
$errTipoAnuncio = isset($_GET['err_tipoAnuncio']);
$errTipoVivienda = isset($_GET['err_tipoVivienda']);
$errNombre = isset($_GET['err_nombre']);
$errCiudad = isset($_GET['err_ciudad']);
$errCiudadNom = isset($_GET['err_ciudad_nom']);
$errPais = isset($_GET['err_pais']);
$errPrecio = isset($_GET['err_precio']);
$errPrecioNum = isset($_GET['err_precio_num']);

$errDescripcion = isset($_GET['err_descripcion']);
$errSuperficie = isset($_GET['err_superficie']);
$errHabitaciones = isset($_GET['err_habitaciones']);
$errBanyos = isset($_GET['err_banyos']);
$errPlanta = isset($_GET['err_planta']);
$errAnyo = isset($_GET['err_anyo']);
$errAnyoFuturo = isset($_GET['err_anyo_futuro']);

$prevTipoAnuncio = '';
$prevTipoVivienda = '';
$prevNombre = '';
$prevCiudad = '';
$prevPais = '';
$prevPrecio = '';
$prevDescripcion = '';
$prevSuperficie = '';
$prevHabitaciones = '';
$prevBanyos = '';
$prevPlanta = '';
$prevAnyo = '';


// 1. CONEXIÓN
$config = parse_ini_file('config.ini');
if (!$config) die("Error al leer config.ini");
@$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
if ($mysqli->connect_errno) die("Error de conexión a la BD: " . $mysqli->connect_error);

// EXTRAEMOS LOS DATOS DEL ANUNCIO A MODIFICAR
if (!isset($_GET['id'])) {
    header("Location: ./index.php?error=falta_id");
    exit;
}

$idAnuncio = $_GET['id'];
$sqlAnuncio = "SELECT 
    * FROM ANUNCIOS WHERE IdAnuncio = ?";
if ($stmt = $mysqli->prepare($sqlAnuncio)) {
    $stmt->bind_param("i", $idAnuncio);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $prevTipoAnuncio = $row['TAnuncio'];
        $prevTipoVivienda = $row['TVivienda'];
        $prevNombre = $row['Titulo'];
        $prevDescripcion = $row['Texto'];
        $prevPrecio = $row['Precio'];
        $prevCiudad = $row['Ciudad'];
        $prevPais = $row['Pais'];
        $prevSuperficie = $row['Superficie'];
        $prevHabitaciones = $row['NHabitaciones'];
        $prevBanyos = $row['NBanyos'];
        $prevPlanta = $row['Planta'];
        $prevAnyo = $row['Anyo'];
    }
    $stmt->close();
}


$prevTipoAnuncio = $_GET['val_tipoAnuncio'] ?? $prevTipoAnuncio;
$prevTipoVivienda = $_GET['val_tipoVivienda'] ?? $prevTipoVivienda;
$prevNombre = $_GET['val_nombre'] ?? $prevNombre;
$prevCiudad = $_GET['val_ciudad'] ?? $prevCiudad;
$prevPais = $_GET['val_pais'] ?? $prevPais;
$prevPrecio = $_GET['val_precio'] ?? $prevPrecio;
$prevDescripcion = $_GET['val_descripcion'] ?? $prevDescripcion;
$prevSuperficie = $_GET['val_superficie'] ?? $prevSuperficie;
$prevHabitaciones = $_GET['val_habitaciones'] ?? $prevHabitaciones;
$prevBanyos = $_GET['val_banyos'] ?? $prevBanyos;
$prevPlanta = $_GET['val_planta'] ?? $prevPlanta;
$prevAnyo = $_GET['val_anyo'] ?? $prevAnyo;



// vamos a guardar los valores para la logica de actualizacion (optimización)

// --- 1. PAISES ---
$paises = [];
$sqlPaises = "SELECT IdPais, NomPais FROM PAISES ORDER BY NomPais";
if ($stmt = $mysqli->prepare($sqlPaises)) {
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $paises[] = $row;
    }
    $stmt->close();
}

// --- 2. TIPOS DE VIVIENDA ---
$tiposVivienda = [];
$sqlTiposVivienda = "SELECT IdTVivienda, NomTVivienda FROM TIPOSVIVIENDAS ORDER BY NomTVivienda";
if ($stmt = $mysqli->prepare($sqlTiposVivienda)) {
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $tiposVivienda[] = $row;
    }
    $stmt->close();
}

// --- 3. TIPOS DE ANUNCIO ---
$tiposAnuncio = [];
$sqlTiposAnuncio = "SELECT IdTAnuncio, NomTAnuncio FROM TIPOSANUNCIOS ORDER BY NomTAnuncio";
if ($stmt = $mysqli->prepare($sqlTiposAnuncio)) {
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $tiposAnuncio[] = $row;
    }
    $stmt->close();
}

$mysqli->close();



require 'cabecera.php';
?>
<section class="forms">
    <h2>Modifica tu anuncio</h2>
    <form action="./respuestaModificar.php" id="busqueda" method="post">
        <input type="hidden" name="idAd" value="<?php echo htmlspecialchars($idAnuncio); ?>">

            <?php require 'services/formAnuncio.php'; ?>

        </fieldset>
        <button type="submit" id="btnBuscar">Aplicar modificaciones</button>
    </form>
</section>

<?php require 'pie.php'; ?>
