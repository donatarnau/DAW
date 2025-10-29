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

// --- Solo permitir POST ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('crearAnuncio.php');
}

$id = '3'; // Aquí se debería generar o recuperar el ID del anuncio creado

// --- Recuperar los valores ---
$user = trim($_POST['user'] ?? '');
$tipoAnuncio = trim($_POST['tipoAnuncio'] ?? '');
$tipoVivienda = trim($_POST['tipoVivienda'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$ciudad = trim($_POST['ciudad'] ?? '');
$pais = trim($_POST['pais'] ?? '');
$precio = trim($_POST['precio'] ?? '');
$fecha = trim($_POST['fecha_pub'] ?? '');
$valores = $_POST['caracteristicas'] ?? [];

// --- Validaciones ---
$errors = [];

if ($tipoAnuncio === '') $errors['err_tipoAnuncio'] = 1;
if ($tipoVivienda === '') $errors['err_tipoVivienda'] = 1;
if ($nombre === '') $errors['err_nombre'] = 1;
if ($ciudad === '') $errors['err_ciudad'] = 1;
if ($pais === '') $errors['err_pais'] = 1;
if ($precio === '') $errors['err_precio'] = 1;
if ($fecha === '') $errors['err_fecha'] = 1;

// --- Si hay errores, redirigimos con los valores previos ---
if (!empty($errors)) {
    $params = $errors;
    $params['val_tipoAnuncio'] = $tipoAnuncio;
    $params['val_tipoVivienda'] = $tipoVivienda;
    $params['val_nombre'] = $nombre;
    $params['val_ciudad'] = $ciudad;
    $params['val_pais'] = $pais;
    $params['val_precio'] = $precio;
    $params['val_fecha'] = $fecha;
    if ($user !== '') $params['user'] = $user;

    redirigir('crearAnuncio.php', $params);
}

// --- Procesar características ---
$nombresCaracteristicas = [
    'Superficie: ',
    'Número de habitaciones: ',
    'Número de baños: ',
    'Planta: ',
    'Año de construcción: '
];

$caracteristicas = [];
foreach ($valores as $i => $valor) {
    $valor = trim($valor);
    if ($valor !== '') {
        $caracteristicas[] = $nombresCaracteristicas[$i] . $valor;
    }
}

// --- Cabecera ---
$titulo = "Anuncio - " . htmlspecialchars($nombre);
$encabezado = "Detalle del anuncio";
include 'cabecera.php';
?>

<section id="resultMensaje">
    <h2>Anuncio publicado con éxito</h2>
    <ul class="listaRespuesta">
        <li><strong>Nombre: </strong><?= htmlspecialchars($nombre) ?></li>
        <li><strong>Tipo de anuncio: </strong><?= htmlspecialchars($tipoAnuncio) ?></li>
        <li><strong>Tipo de vivienda: </strong><?= htmlspecialchars($tipoVivienda) ?></li>
        <li><strong>Fecha de publicación: </strong><?= htmlspecialchars($fecha) ?></li>
        <li><strong>Ciudad: </strong><?= htmlspecialchars($ciudad) ?></li>
        <li><strong>País: </strong><?= htmlspecialchars($pais) ?></li>
        <li><strong>Precio: </strong><?= htmlspecialchars($precio) ?></li>
        <li><strong>Características:</strong></li>
        <ul>
            <?php foreach ($caracteristicas as $c): ?>
                <li><?= htmlspecialchars($c) ?></li>
            <?php endforeach; ?>
        </ul>
    </ul>
    <a class="btn" href="./addFoto.php?user=<?= urlencode($username) . '&id='. urlencode($id) . '&nom=' . urlencode($nombre) ?>">Añadir foto</a>
</section>

<?php include 'pie.php'; ?>
