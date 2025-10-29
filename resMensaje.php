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

// --- Verificar que el formulario se envió por POST ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('mensaje.php');
}

// --- Capturar datos ---
$user = trim($_POST['user'] ?? '');
$tipoMensaje = trim($_POST['tipo'] ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');
$ad = trim($_POST['anuncio'] ?? '');
$id = (int)$ad; // Convertir a número entero

$anuncios = include './services/datos_anuncios.php';

$clave = ($id % 2 === 0) ? 'par' : 'impar';
$anuncio = $anuncios[$clave];

extract($anuncio);

// --- Validaciones ---
$errors = [];
$params = [];

if ($tipoMensaje === '') {
    $errors['err_tipo_empty'] = 1;
}
if ($mensaje === '') {
    $errors['err_mensaje_empty'] = 1;
}

// --- Si hay errores, redirigimos ---
if (!empty($errors)) {
    $params = $errors;

    // Mantener valores introducidos
    if ($tipoMensaje !== '') $params['val_tipo'] = $tipoMensaje;
    if ($mensaje !== '') $params['val_mensaje'] = $mensaje;
    if ($user !== '') $params['user'] = $user;
    if ($ad !== '') $params['id'] = $ad;

    redirigir('mensaje.php', $params);
}

// --- Si todo está correcto, mostrar la respuesta ---
$titulo = "Resultado mensajes";
$encabezado = "Mensaje enviado - Pisos e Inmuebles";
include 'cabecera.php';

// Opcional: transformar el tipo a texto más amigable
$tiposAmigables = [
    'info' => 'Más información',
    'cita' => 'Solicitud de cita',
    'oferta' => 'Comunicar una oferta'
];
$tipoMostrar = $tiposAmigables[$tipoMensaje] ?? $tipoMensaje;
?>
<section id="resultMensaje">
    <h2>Mensaje enviado con éxito</h2>
    <ul class="listaRespuesta">
        <li><strong>Tipo de mensaje:</strong> <?= htmlspecialchars($tipoMostrar) ?></li>
        <li><strong>Contenido del mensaje:</strong> <?= nl2br(htmlspecialchars($mensaje)) ?></li>
        <li><strong>Anuncio:</strong> <?= htmlspecialchars($nombre) ?></li>
        <li><strong>Remitente:</strong> <?= htmlspecialchars($user) ?></li>
        <li><strong>Destinatario:</strong> <?= htmlspecialchars($usuario) ?></li>
    </ul>
</section>
<?php
include 'pie.php';
?>
