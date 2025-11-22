<?php
function redirigir($pagina, $params = []) {
    $host = $_SERVER['HTTP_HOST'];
    $uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    if ($uri === '/' || $uri === '\\') $uri = '';
    $queryString = '';
    if (!empty($params)) {
        $queryString = '?' . http_build_query($params);
    }
    header("Location: http://$host$uri/$pagina$queryString");
    exit;
}

// Verificar que el formulario se envió por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('addFoto.php');
}

// Recoger los datos del formulario
$anuncio = isset($_POST['anuncio']) ? trim($_POST['anuncio']) : '';
$nombreAnuncio = isset($_POST['nombreAnuncio']) ? trim($_POST['nombreAnuncio']) : '';
$alt = isset($_POST['alt']) ? trim($_POST['alt']) : '';
$tit = isset($_POST['tit']) ? trim($_POST['tit']) : '';
// Ignoramos $_FILES['foto'] según la práctica

// Validaciones
$errors = [];
$params = [];

// Validar campo anuncio (obligatorio)
if ($anuncio === '') {
    $errors['err_anuncio_empty'] = 1;
}

if ($tit === '') {
    $errors['err_tit_empty'] = 1;
}


// Validar texto alternativo (obligatorio y >= 10 caracteres)
if ($alt === '') {
    $errors['err_alt_empty'] = 1;
} elseif (mb_strlen($alt) < 10) {
    $errors['err_alt_short'] = 1;
}

// validaciones alt extra

$altLower = mb_strtolower($alt);
if (preg_match('/^(texto|imagen|imagen de|foto|foto de)/u', $altLower)) {
    $errors['err_alt_invalid_start'] = 1;
}



// Comprobar si hay errores
if (!empty($errors)) {
    // --- HAY ERRORES ---
    $params = $errors;

    // Mantener los valores introducidos
    if ($anuncio !== '') $params['val_anuncio'] = $anuncio;
    if ($alt !== '') $params['val_alt'] = $alt;
    if ($nombreAnuncio !== '') $params['val_nomAnuncio'] = $nombreAnuncio;
    if ($tit !== '') $params['val_tit'] = $tit;
    $params['id'] = $anuncio;

    // Redirigir de nuevo al formulario con errores
    redirigir('addFoto.php', $params);

} else {

    // LOGICA DE SUBIDA DE LA FOTO -> VAMOS A ASIGNAR NOMBRES SEGUN ALGORITMO:

    // SI LA FOTOPRINCIPAL ES "sinfoto.jpg", entonces la foto que se sube pasa a ser la fotoprincipal
    // SI LA FOTOPRINCIPAL NO ES "sinfoto.jpg", entonces la foto que se sube se añade a la tabla fotos con el id del anuncio correspondiente

    // en esta practica no se sube el archivo FILE, solo se sube a la db el texto

    // 1. CONEXIÓN
    $config = parse_ini_file('config.ini');
    if (!$config) die("Error al leer config.ini");
    @$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
    if ($mysqli->connect_errno) die("Error de conexión a la BD: " . $mysqli->connect_error);

    require_once 'services/logic_fotos.php';
    $datos = obtener_datos_fotos($mysqli, $anuncio);
    $ad = $datos['anuncio'];
    $fotos = $datos['fotos'];

    if($ad['FPrincipal'] === 'sinfoto.jpg'){
        // ACTUALIZAR EL CAMPO FOTOPRINCIPAL DEL ANUNCIO
        $sqlUpdate = "UPDATE ANUNCIOS SET FPrincipal = ?, Alternativo = ? WHERE IdAnuncio = ?";
        if ($stmt = $mysqli->prepare($sqlUpdate)) {
            // Asignar un nombre único a la foto, por ejemplo usando el ID del anuncio y un timestamp
            $nuevoNombreFoto = 'anuncio' . $ad['Id'] . '.jpg'; // Ajusta la extensión según el tipo de archivo
            $stmt->bind_param("ssi", $nuevoNombreFoto, $alt, $ad['Id']);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        // INSERTAR NUEVA FOTO EN LA TABLA FOTOS
        // vamos a calcular el indice:

        $indice = count($fotos) + 1;

        $sqlInsert = "INSERT INTO FOTOS (Anuncio, Foto, Alternativo, Titulo) VALUES (?, ?, ?, ?)";
        if ($stmt = $mysqli->prepare($sqlInsert)) {
            $nuevoNombreFoto = 'a' . $ad['Id'] . '-' . $indice . '.jpg'; // Ajusta la extensión según el tipo de archivo
            $stmt->bind_param("isss", $ad['Id'], $nuevoNombreFoto, $alt, $tit);
            $stmt->execute();
            $stmt->close();
        }
    }


    $mysqli->close();

    $parametro = [];
    $parametro['id'] = $anuncio;
    $parametro['temp'] = "Foto añadida correctamente a " . htmlspecialchars($ad['Titulo']);

    // Redirigir al perfil del usuario tras la operación

    redirigir('userAnuncio.php', $parametro);

}
?>
