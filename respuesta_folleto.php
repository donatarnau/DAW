<?php

    echo "<script>console.log('Valor USUARIO:', " . json_encode($username) . ");</script>";


    // --- Configuración y Funciones de Cálculo (Misma lógica que en la página de solicitud) ---
    $TARIFA_ENVIO = 10; // Coste fijo base por procesamiento y envío
    $BLOQUES_PAGINAS = [
        ['max' => 4,   'precio' => 2.0],
        ['max' => 10,  'precio' => 1.8],
        ['max' => INF, 'precio' => 1.6]
    ];
    $RECARGO_COLOR_FOTO = 0.5;
    $RECARGO_RES_ALTA_FOTO = 0.2;

    // Valores ficticios para la simulación (ya que no hay BD)
    $num_paginas_ficticio = 8;
    $num_fotos_ficticio = 24; // (ej. 3 fotos por página * 8 páginas)

    function calcular_coste_paginas($n, $bloques) {
        $restante = $n;
        $acumulado = 0;
        $total = 0;
        foreach ($bloques as $bloque) {
            $limite = ($bloque['max'] === INF) ? INF : $bloque['max'] - $acumulado;
            $cantidad = max(0, min($restante, $limite));
            $total += $cantidad * $bloque['precio'];
            $restante -= $cantidad;
            $acumulado = ($bloque['max'] === INF) ? $acumulado + $cantidad : $bloque['max'];
            if ($restante <= 0) break;
        }
        return $total;
    }

    // --- Recogida de datos del formulario ---
    // Se usa $_REQUEST para que funcione tanto si el formulario usa GET como POST.
    // Se recomienda usar filter_input o validar/sanitizar adecuadamente en un entorno real.
    $nombre = $_REQUEST['nombre'] ?? '';
    $email = $_REQUEST['email'] ?? '';
    $telefono = $_REQUEST['telefono'] ?? '';
    $texto_adicional = $_REQUEST['texto'] ?? '';

    $calle = $_REQUEST['calle'] ?? '';
    $numero = $_REQUEST['numero'] ?? '';
    $piso = $_REQUEST['piso'] ?? '';
    $puerta = $_REQUEST['puerta'] ?? '';
    $cp = $_REQUEST['cp'] ?? '';
    $localidad = $_REQUEST['localidad'] ?? '';
    $provincia = $_REQUEST['provincia'] ?? '';
    $pais = $_REQUEST['pais'] ?? '';

    $anuncio_id = $_REQUEST['anuncio'] ?? '';
    // Simulación de nombre de anuncio según ID
    $nombre_anuncio = "Anuncio no especificado";
    if ($anuncio_id == '1') $nombre_anuncio = "Piso en venta - Centro ciudad";
    elseif ($anuncio_id == '2') $nombre_anuncio = "Apartamento - Primera línea de playa";

    $num_copias = (int)($_REQUEST['copias'] ?? 1);
    $num_copias = max(1, min(99, $num_copias)); // Asegurar rango 1-99

    $color_portada = $_REQUEST['color_portada'] ?? '#000000';
    $resolucion = (int)($_REQUEST['resolucion'] ?? 150);
    $fecha_deseada = $_REQUEST['fecha_deseada'] ?? '';
    $impresion = $_REQUEST['impresion'] ?? 'color';
    $mostrar_precio = $_REQUEST['mostrar_precio'] ?? 'si';

    // --- Cálculos de Coste ---
    $es_color = ($impresion === 'color');
    $es_alta_res = ($resolucion > 300);

    $coste_base_envio = $TARIFA_ENVIO;
    $coste_paginas = calcular_coste_paginas($num_paginas_ficticio, $BLOQUES_PAGINAS);
    $coste_fotos_color = ($es_color ? $RECARGO_COLOR_FOTO : 0) * $num_fotos_ficticio;
    $coste_fotos_res = ($es_alta_res ? $RECARGO_RES_ALTA_FOTO : 0) * $num_fotos_ficticio;

    $coste_unitario = $coste_base_envio + $coste_paginas + $coste_fotos_color + $coste_fotos_res;
    $coste_total_final = $coste_unitario * $num_copias;

    // --- Inicio de la Salida HTML ---
    $titulo = "Respuesta folleto";
    $encabezado = "Respuesta Folleto - Pisos e Inmuebles";
    require 'cabecera.php';
?>
        <section id="resFolleto">
        <section>
            <h2>Solicitud de folleto registrada</h2>
            <p>¡Gracias! Hemos recibido tu solicitud de impresión y envío del folleto publicitario. 
            A continuación te mostramos el resumen de los datos proporcionados y el presupuesto final.</p>
        </section>

        <section>
            <h3>Resumen de la solicitud</h3>

            <h4>Datos de envío</h4>
            <table>
            <tbody>
                <tr><th scope="row">Nombre completo</th><td><?= htmlspecialchars($nombre) ?></td></tr>
                <tr><th scope="row">Correo electrónico</th><td><?= htmlspecialchars($email) ?></td></tr>
                <tr><th scope="row">Teléfono</th><td><?= htmlspecialchars($telefono) ?></td></tr>
                <tr><th scope="row">Información adicional</th><td><?= nl2br(htmlspecialchars($texto_adicional)) ?></td></tr>
            </tbody>
            </table>

            <h4>Dirección postal</h4>
            <table>
            <tbody>
                <tr><th scope="row">Calle</th><td><?= htmlspecialchars($calle) ?></td></tr>
                <tr><th scope="row">Número</th><td><?= htmlspecialchars($numero) ?></td></tr>
                <tr><th scope="row">Piso</th><td><?= htmlspecialchars($piso) ?></td></tr>
                <tr><th scope="row">Puerta</th><td><?= htmlspecialchars($puerta) ?></td></tr>
                <tr><th scope="row">Código Postal</th><td><?= htmlspecialchars($cp) ?></td></tr>
                <tr><th scope="row">Localidad</th><td><?= htmlspecialchars($localidad) ?></td></tr>
                <tr><th scope="row">Provincia</th><td><?= htmlspecialchars($provincia) ?></td></tr>
                <tr><th scope="row">País</th><td><?= htmlspecialchars($pais) ?></td></tr>
            </tbody>
            </table>

            <h4>Características del folleto</h4>
            <table>
            <tbody>
                <tr><th scope="row">Anuncio seleccionado</th><td><?= htmlspecialchars($nombre_anuncio) ?></td></tr>
                <tr><th scope="row">Número de copias</th><td><?= $num_copias ?></td></tr>
                <tr><th scope="row">Color de la portada</th><td>
                    <span style="display:inline-block; width:1em; height:1em; background-color:<?= htmlspecialchars($color_portada) ?>; border:1px solid #000; vertical-align:middle; margin-right:5px;"></span>
                    <?= htmlspecialchars($color_portada) ?>
                </td></tr>
                <tr><th scope="row">Resolución de las fotos (DPI)</th><td><?= $resolucion ?></td></tr>
                <tr><th scope="row">Fecha de recepción deseada</th><td><?= htmlspecialchars($fecha_deseada) ?></td></tr>
                <tr><th scope="row">Tipo de impresión</th><td><?= ($impresion === 'color') ? 'Color' : 'Blanco y negro' ?></td></tr>
                <tr><th scope="row">Incluir precio en el folleto</th><td><?= ($mostrar_precio === 'si') ? 'Sí' : 'No' ?></td></tr>
            </tbody>
            </table>
        </section>

        <section>
            <h3>Coste del servicio</h3>
            <p>
                El anuncio seleccionado consta de <strong><?= $num_paginas_ficticio ?> páginas</strong> 
                y <strong><?= $num_fotos_ficticio ?> fotos</strong>.
            </p>
            <table>
                <tr>
                    <th scope="row">Coste unitario del folleto</th>
                    <td><?= number_format($coste_unitario, 2, ',', '.') ?> €</td>
                </tr>
                <tr>
                    <th scope="row">Número de copias</th>
                    <td>x <?= $num_copias ?></td>
                </tr>
                <tr style="font-size: 1.2em; font-weight: bold; border-top: 2px solid #333;">
                    <th scope="row">COSTE TOTAL FINAL</th>
                    <td><?= number_format($coste_total_final, 2, ',', '.') ?> €</td>
                </tr>
            </table>
        </section>

        <nav>
            <ul>
            <li><a href="./index.php">Volver al inicio</a></li>
            <li><a href="./solicitar_folleto.php">Hacer otra solicitud</a></li>
            </ul>
        </nav>
        </section>
<?php
    require 'pie.php';
?>