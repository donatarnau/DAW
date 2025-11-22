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

// AHORA CERRAREMOS LA CONEXIÓN AL FINAL


require_once 'services/ultimos_anuncios.php';
ua_actualizar($anuncioCookie['id'],$anuncioCookie);

// 6. Configurar la cabecera
$titulo = "Eliminar anuncio - " . htmlspecialchars($anuncio['Titulo']);
$encabezado = "Confirmar eliminación del anuncio";
require 'cabecera.php';

?>
        <section id="anuncioDetalle">
            
            <article class="delete-confirmation">
                <h3>¿Estás seguro de que quieres eliminar este anuncio?</h3>
                <a class="btn" href="./userAnuncio.php?id=<?= urlencode($id)?>">No, volver atrás</a>
                <a class="btn rojo" href="./services/deleteAnuncio.php?id=<?= urlencode($id)?>">Sí, eliminar anuncio<br>Esta acción no se puede deshacer</a>
            </article>
    
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

            <h3>Número de fotos: (<?= count($fotos)+1 ?>)</h3>

        </section> 
<?php
$mysqli->close();
require 'pie.php';
?>