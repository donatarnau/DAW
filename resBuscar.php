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
            $params = ['err_empty' => 1, 'user' => $_GET['user'] ?? ''];
            redirigir('buscar.php', $params);
        }
    }

    // --- VARIABLES Y CARGA DE CABECERA ---
    $titulo = "Buscar Anuncios";
    $encabezado = "Busqueda Avanzada - Pisos e Inmuebles";
    include 'cabecera.php';

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
?>
<section class="forms">
    <h2>Buscar Anuncios</h2>
    <form action="./resBuscar.php" id="busqueda" method="get">
        <input type="hidden" name="user" value="<?= htmlspecialchars($username) ?>">
        <fieldset class="search">
            <legend>Rellena al menos un campo</legend>
            
            <label id="tipoAnuncio">Tipo de Anuncio</label>
            <select name="tipoAnuncio" id="param-anuncio">
                <option value="">Elige un tipo</option>
                <option value="alquiler" <?= $tipoAnuncio === 'alquiler' ? 'selected' : '' ?>>Alquiler</option>
                <option value="venta" <?= $tipoAnuncio === 'venta' ? 'selected' : '' ?>>Venta</option>
            </select>

            <label id="tipoVivienda">Tipo de Vivienda</label>
            <select name="tipoVivienda" id="param-vivienda">
                <option value="">Elige un tipo</option>
                <option value="obraNueva" <?= $tipoVivienda === 'obraNueva' ? 'selected' : '' ?>>Obra nueva</option>
                <option value="vivienda" <?= $tipoVivienda === 'vivienda' ? 'selected' : '' ?>>Vivienda</option>
                <option value="oficina" <?= $tipoVivienda === 'oficina' ? 'selected' : '' ?>>Oficina</option>
                <option value="local" <?= $tipoVivienda === 'local' ? 'selected' : '' ?>>Local</option>
                <option value="garaje" <?= $tipoVivienda === 'garaje' ? 'selected' : '' ?>>Garaje</option>
            </select>

            <label id="ciudad">Ciudad</label>
            <input type="text" name="ciudad" placeholder="Ciudad" id="param-ciudad" value="<?= htmlspecialchars($ciudad) ?>">

            <label id="pais">País</label>
            <input type="text" name="pais" placeholder="País" id="param-pais" value="<?= htmlspecialchars($pais) ?>">

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
    <ul>
        <li>
            <article>
                <figure>
                    <a href="<?php echo $loggedIn ? './anuncio.php' . $userQueryParam . '&id=1' : './login.php'; ?>"><img src="./img/a1.jpeg" alt="anuncio1"></a>
                </figure>
                <a href="<?php echo $loggedIn ? './anuncio.php' . $userQueryParam . '&id=1' : './login.php'; ?>"><h2>
                    <?php echo $loggedIn ? 'Ver detalle del anuncio' : 'Inicia sesión para ver'; ?>
                </h2></a>
                <hr>
                <p><time datetime="2015-12-17">17 de Diciembre de 2015</time></p>
                <p>Córdoba</p>
                <p>España</p>
                <p>150.000€</p>
            </article>
        </li>
        <li>
            <article>
                <figure>
                    <a href="<?php echo $loggedIn ? './anuncio.php' . $userQueryParam . '&id=2' : './login.php'; ?>"><img src="./img/a2.png" alt="anuncio1"></a>
                </figure>
                <a href="<?php echo $loggedIn ? './anuncio.php' . $userQueryParam . '&id=2' : './login.php'; ?>"><h2>
                    <?php echo $loggedIn ? 'Ver detalle del anuncio' : 'Inicia sesión para ver'; ?>
                </h2></a>
                <hr>
                <p><time datetime="2015-12-17">17 de Diciembre de 2015</time></p>
                <p>Córdoba</p>
                <p>España</p>
                <p>150.000€</p>
            </article>
        </li>
    </ul>
</section>
<?php
    include 'pie.php';
?>
