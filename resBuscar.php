<?php
    // --- FUNCIONES AUXILIARES ---
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

    // --- VALIDACIÓN DE BÚSQUEDA (solo si llega con parámetros GET) ---
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET)) {
        $campos = [
            'tipoAnuncio' => $_GET['tipoAnuncio'] ?? '',
            'tipoVivienda' => $_GET['tipoVivienda'] ?? '',
            'ciudad' => $_GET['ciudad'] ?? '',
            'pais' => $_GET['pais'] ?? '',
            'minPrecio' => $_GET['minPrecio'] ?? '',
            'maxPrecio' => $_GET['maxPrecio'] ?? '',
            'fecha_pub' => $_GET['fecha_pub'] ?? '',
        ];

        // Comprobamos si todos los campos están vacíos
        $todosVacios = true;
        foreach ($campos as $valor) {
            if (trim($valor) !== '') {
                $todosVacios = false;
                break;
            }
        }

        if ($todosVacios) {
            // Redirigir con error
            $params = ['err_empty' => 1];
            redirigir('buscar.php', $params);
        }
    }

    // --- VARIABLES Y CARGA DE CABECERA ---
    $titulo = "Buscar Anuncios";
    $encabezado = "Busqueda Avanzada - Pisos e Inmuebles";
    require 'cabecera.php';

    // Recuperamos los valores enviados si existen
    $tipoAnuncio = $_GET['tipoAnuncio'] ?? '';
    $tipoVivienda = $_GET['tipoVivienda'] ?? '';
    $ciudad = $_GET['ciudad'] ?? '';
    $pais = $_GET['pais'] ?? '';
    $minPrecio = $_GET['minPrecio'] ?? '';
    $maxPrecio = $_GET['maxPrecio'] ?? '';
    $fecha_pub = $_GET['fecha_pub'] ?? '';

    // --- Detectar error desde redirección ---
    $errEmpty = isset($_GET['err_empty']);

    // 1. CONEXIÓN
    $config = parse_ini_file('config.ini');
    if (!$config) die("Error al leer config.ini");
    @$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
    if ($mysqli->connect_errno) die("Error de conexión a la BD: " . $mysqli->connect_error);

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

    // OBTENEMOS LOS ANUNCIOS QUE CUMPLEN CON LOS CRITERIOS DE BUSQUEDA

    $ultimosAnuncios = [];
    $sqlAnuncios = "SELECT A.IdAnuncio, A.Titulo, A.Precio, A.FRegistro, A.Ciudad, P.NomPais, A.FPrincipal , A.Alternativo
                    FROM ANUNCIOS A
                    JOIN PAISES P ON A.Pais = P.IdPais
                    WHERE 1=1";

    $params = [];
    $types  = "";

    // Filtros dinámicos
    if ($tipoAnuncio !== '') {
        $sqlAnuncios .= " AND A.TAnuncio = ?";
        $types .= "i";
        $params[] = $tipoAnuncio;
    }

    if ($tipoVivienda !== '') {
        $sqlAnuncios .= " AND A.TVivienda = ?";
        $types .= "i";
        $params[] = $tipoVivienda;
    }

    if ($ciudad !== '') {
        $sqlAnuncios .= " AND A.Ciudad LIKE ?";
        $types .= "s";
        $params[] = "%$ciudad%";
    }

    if ($pais !== '') {
        $sqlAnuncios .= " AND A.Pais = ?";
        $types .= "i";
        $params[] = $pais;
    }

    if ($minPrecio !== '') {
        $sqlAnuncios .= " AND A.Precio >= ?";
        $types .= "d";
        $params[] = $minPrecio;
    }

    if ($maxPrecio !== '') {
        $sqlAnuncios .= " AND A.Precio <= ?";
        $types .= "d";
        $params[] = $maxPrecio;
    }

    if ($fecha_pub !== '') {
        $sqlAnuncios .= " AND DATE(A.FRegistro) = ?";
        $types .= "s";
        $params[] = $fecha_pub;
    }

    // Ordenar por fecha descendente
    $sqlAnuncios .= " ORDER BY A.FRegistro DESC";

    if ($stmt = $mysqli->prepare($sqlAnuncios)) {
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $ultimosAnuncios[] = $row;
        }
        $stmt->close();
    }

    // Cerramos conexión, ya tenemos todos los datos
    $mysqli->close();
?>
<section class="forms">
    <h2>Buscar Anuncios</h2>
    <form action="./resBuscar.php" id="busqueda" method="get">
        <fieldset class="search">
            <legend>Rellena al menos un campo</legend>

            <?php if ($errEmpty): ?>
                <p class="error-msg">Debes rellenar al menos un campo para realizar la búsqueda.</p>
            <?php endif; ?>
            
            <label id="tipoAnuncio">Tipo de Anuncio</label>
            <select name="tipoAnuncio" id="param-anuncio">
                <option value="">Seleccione un tipo de anuncio</option>
                <?php if (isset($tiposAnuncio) && is_array($tiposAnuncio)): ?>
                    <?php foreach ($tiposAnuncio as $tipoAnuncioItem): ?>
                        <option value="<?php echo $tipoAnuncioItem['IdTAnuncio']; ?>" 
                            <?php if ($tipoAnuncio == $tipoAnuncioItem['IdTAnuncio']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($tipoAnuncioItem['NomTAnuncio']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>

            <label id="tipoVivienda">Tipo de Vivienda</label>
            <select name="tipoVivienda" id="param-vivienda">
                <option value="">Seleccione un tipo de vivienda</option>
                <?php if (isset($tiposVivienda) && is_array($tiposVivienda)): ?>
                    <?php foreach ($tiposVivienda as $tipoViviendaItem): ?>
                        <option value="<?php echo $tipoViviendaItem['IdTVivienda']; ?>" 
                            <?php if ($tipoVivienda == $tipoViviendaItem['IdTVivienda']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($tipoViviendaItem['NomTVivienda']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>

            <label id="ciudad">Ciudad</label>
            <input type="text" name="ciudad" placeholder="Ciudad" id="param-ciudad" value="<?= htmlspecialchars($ciudad) ?>">

            <label id="pais">País</label>
            <select name="pais" id="param-pais">
                <option value="">Seleccione un país</option>
                <?php if (isset($paises) && is_array($paises)): ?>
                    <?php foreach ($paises as $paisItem): ?>
                        <option value="<?php echo $paisItem['IdPais']; ?>" 
                            <?php if ($pais == $paisItem['IdPais']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($paisItem['NomPais']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>

            <label for="minPrecio">Precio mínimo</label>
            <input type="text" id="minPrecio" name="minPrecio" value="<?= htmlspecialchars($minPrecio) ?>">
            
            <label for="maxPrecio">Precio máximo</label>
            <input type="text" id="maxPrecio" name="maxPrecio" value="<?= htmlspecialchars($maxPrecio) ?>">

            <label for="fecha_pub">Fecha de publicación</label>
            <input type="text" id="fecha_pub" name="fecha_pub" value="<?= htmlspecialchars($fecha_pub) ?>">

        </fieldset>
        <button type="submit" id="btnBuscar">Buscar</button>
    </form>
</section>
<section id="resultadosBuscar">
    <h2>Resultados de la búsqueda</h2>
    <?php if (empty($ultimosAnuncios)): ?>
        <p class="no-results">No se han encontrado anuncios.</p>
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
