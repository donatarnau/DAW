<?php
/**
 * respuesta_folleto.php
 * Calcula el coste real basado en las fotos de la BD e inserta la solicitud.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. CONTROL DE ACCESO
if (!isset($_SESSION['user'])) {
    header("Location: ./index.php?error=acceso_denegado");
    exit;
}

require_once 'services/flashdata.php';

// 2. CONEXIÓN A LA BD
$config = parse_ini_file('config.ini');
if (!$config) die("Error crítico: No se encuentra config.ini");

@$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
if ($mysqli->connect_errno) {
    die("Error de conexión a la BD: " . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

// 3. RECOGIDA DE DATOS DEL FORMULARIO
// Usamos valores por defecto seguros
$anuncioId = isset($_POST['anuncio']) ? (int)$_POST['anuncio'] : 0;
$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$texto = trim($_POST['texto'] ?? '');

// Dirección (concatenamos para guardar en un solo campo de texto como pide la estructura SQL habitual)
$calle = $_POST['calle'] ?? '';
$numero = $_POST['numero'] ?? '';
$piso = $_POST['piso'] ?? '';
$puerta = $_POST['puerta'] ?? '';
$cp = $_POST['cp'] ?? '';
$localidad = $_POST['localidad'] ?? '';
$provincia = $_POST['provincia'] ?? '';
$paisInput = $_POST['pais'] ?? '';

$direccionCompleta = "$calle, $numero. $piso $puerta. CP: $cp. $localidad ($provincia), $paisInput";

// Detalles del pedido
$copias = (int)($_POST['copias'] ?? 1);
if ($copias < 1) $copias = 1;

$colorPortada = $_POST['color_portada'] ?? '#000000';
$resolucion = (int)($_POST['resolucion'] ?? 150);
$fechaRecepcion = $_POST['fecha_recepcion'] ?? null;
if (empty($fechaRecepcion)) $fechaRecepcion = date('Y-m-d', strtotime('+7 days')); // Fecha por defecto si no se indica

// Convertir radios y checkboxes a formato DB (1/0)
$tipoImpresion = $_POST['tipo_impresion'] ?? 'bn'; // 'color' o 'bn'
$esColor = ($tipoImpresion === 'color');
$iColor = $esColor ? 1 : 0;

$mostrarPrecio = isset($_POST['mostrar_precio']) ? 1 : 0;

// 4. OBTENER DATOS DEL ANUNCIO Y CONTAR FOTOS
$tituloAnuncio = "Anuncio desconocido";
$numFotos = 0;

// A. Obtener título
if ($stmt = $mysqli->prepare("SELECT Titulo FROM ANUNCIOS WHERE IdAnuncio = ?")) {
    $stmt->bind_param("i", $anuncioId);
    $stmt->execute();
    $stmt->bind_result($tituloAnuncio);
    $stmt->fetch();
    $stmt->close();
}

// B. Contar fotos reales
if ($stmt = $mysqli->prepare("SELECT COUNT(*) FROM FOTOS WHERE Anuncio = ?")) {
    $stmt->bind_param("i", $anuncioId);
    $stmt->execute();
    $stmt->bind_result($numFotos);
    $stmt->fetch();
    $stmt->close();
}

// 5. CÁLCULO DE COSTES
// Parámetros de configuración (fáciles de modificar)
const TARIFA_ENVIO = 10.0;
const FOTOS_POR_PAGINA = 3; 
const RECARGO_COLOR_FOTO = 0.5;
const RECARGO_RES_ALTA_FOTO = 0.2; // Si resolución > 300 dpi

// Cálculos
$numPaginas = ($numFotos > 0) ? ceil($numFotos / FOTOS_POR_PAGINA) : 1; // Mínimo 1 página aunque no haya fotos

// Precio por página según cantidad
$precioPorPagina = 0;
if ($numPaginas < 5) {
    $precioPorPagina = 2.0;
} elseif ($numPaginas <= 10) {
    $precioPorPagina = 1.8;
} else {
    $precioPorPagina = 1.6;
}

$costePaginas = $numPaginas * $precioPorPagina;
$costeColor = $esColor ? ($numFotos * RECARGO_COLOR_FOTO) : 0;
$costeResolucion = ($resolucion > 300) ? ($numFotos * RECARGO_RES_ALTA_FOTO) : 0;

$costeUnitario = TARIFA_ENVIO + $costePaginas + $costeColor + $costeResolucion;
$costeTotal = $costeUnitario * $copias;

// 6. INSERCIÓN EN LA BD
$insertedId = 0;
$errorInsert = "";

$sqlInsert = "INSERT INTO SOLICITUDES (Anuncio, Texto, Nombre, Email, Direccion, Telefono, Color, Copias, Resolucion, Fecha, IColor, IPrecio, Coste) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

if ($stmt = $mysqli->prepare($sqlInsert)) {
    // Tipos: i (int), s (string), s, s, s, s, s, i, i, s, i, i, d (double/decimal)
    $stmt->bind_param(
        "issssssiisiid", 
        $anuncioId, $texto, $nombre, $email, $direccionCompleta, $telefono, 
        $colorPortada, $copias, $resolucion, $fechaRecepcion, $iColor, $mostrarPrecio, $costeTotal
    );
    
    try {
        // Intentamos ejecutar la consulta
        if ($stmt->execute()) {
            $insertedId = $stmt->insert_id;
        } else {
            // Si no da excepción pero falla por otra cosa (raro en mysqli moderno con excepciones activadas)
             header("Location: solicitar_folleto.php?error=error_interno");
             exit;
        }
    } catch (mysqli_sql_exception $e) {
        // AQUÍ CAPTURAMOS EL ERROR FATAL
        // Si falla la clave foránea (el anuncio no existe) o cualquier error SQL:
        
        // Cerramos conexiones limpiamente antes de irnos
        $stmt->close(); 
        $mysqli->close();
        
        // Redirigimos al usuario al formulario
        header("Location: solicitar_folleto.php?error=datos_invalidos");
        exit; // Detenemos la ejecución inmediatamente
    }

    $stmt->close();
} else {
    // Error al preparar la consulta (error de sintaxis SQL, etc.)
    header("Location: solicitar_folleto.php?error=error_tecnico");
    exit;
}

$mysqli->close();

// 7. RENDERIZAR VISTA
$titulo = "Solicitud Registrada";
$encabezado = "Respuesta Solicitud Folleto";
require 'cabecera.php';
?>

<section id="resFolleto">
    <?php if ($errorInsert): ?>
        <div style="background-color: #ffebee; color: #c62828; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <h2>Error</h2>
            <p><?php echo htmlspecialchars($errorInsert); ?></p>
            <p><a href="solicitar_folleto.php">Volver a intentar</a></p>
        </div>
    <?php else: ?>

        <section>
            <h2>Solicitud completada con éxito (ID: <?php echo $insertedId; ?>)</h2>
            <p>Hemos registrado tu solicitud. A continuación, te mostramos el desglose detallado del coste y los datos registrados.</p>
        </section>

        <section>
            <h3>Datos del Anuncio</h3>
            <p><strong>Anuncio:</strong> <?php echo htmlspecialchars($tituloAnuncio); ?></p>
            <p><strong>Fotos detectadas:</strong> <?php echo $numFotos; ?></p>
            <p><strong>Páginas calculadas:</strong> <?php echo $numPaginas; ?> (a razón de <?php echo FOTOS_POR_PAGINA; ?> fotos/pág)</p>
        </section>

        <section id="detalleCoste" >
            <h3>Detalle del Coste</h3>
            <table class="tabla">
                    <tr>
                        <th>Concepto</th>
                        <th>Cálculo</th>
                        <th>Subtotal</th>
                    </tr>
                    <tr>
                        <td>Procesamiento y envío</td>
                        <td>Tarifa fija</td>
                        <td><?php echo number_format(TARIFA_ENVIO, 2); ?> €</td>
                    </tr>
                    <tr>
                        <td>Impresión de páginas</td>
                        <td><?php echo $numPaginas; ?> págs x <?php echo $precioPorPagina; ?> €</td>
                        <td><?php echo number_format($costePaginas, 2); ?> €</td>
                    </tr>
                    <?php if ($esColor): ?>
                    <tr>
                        <td>Suplemento Color</td>
                        <td><?php echo $numFotos; ?> fotos x <?php echo RECARGO_COLOR_FOTO; ?> €</td>
                        <td><?php echo number_format($costeColor, 2); ?> €</td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($resolucion > 300): ?>
                    <tr>
                        <td>Suplemento Alta Resolución</td>
                        <td><?php echo $numFotos; ?> fotos x <?php echo RECARGO_RES_ALTA_FOTO; ?> €</td>
                        <td><?php echo number_format($costeResolucion, 2); ?> €</td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td>Coste Unitario</td>
                        <td><?php echo number_format($costeUnitario, 2); ?> €</td>
                    </tr>
                    <tr>
                        <td>Cantidad (Copias)</td>
                        <td>x <?php echo $copias; ?></td>
                    </tr>
                    <tr>
                        <th>COSTE TOTAL</th>
                        <th><?php echo number_format($costeTotal, 2); ?> €</th>
                    </tr>
            </table>
        </section>
        <!--
        <section>
            <h3>Datos de Envío Registrados</h3>
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($nombre); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                <p><strong>Dirección completa:</strong> <?php echo htmlspecialchars($direccionCompleta); ?></p>
                <p><strong>Fecha recepción deseada:</strong> <?php echo htmlspecialchars($fechaRecepcion); ?></p>
        </section>
        -->
        <nav>
            <ul>
                <li><a href="./index.php">Volver al inicio</a></li>
                <li><a href="./solicitar_folleto.php">Solicitar otro folleto</a></li>
            </ul>
        </nav>

    <?php endif; ?>
</section>

<?php
require 'pie.php';
?>