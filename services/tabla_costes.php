<?php
/*
 * Fichero: services/tabla_costes.php
 *
 * Versión simple: Solo calcula e imprime el HTML de la tabla.
 * NO se preocupa de botones, enlaces o visibilidad.
 */

// --- 1. Definiciones y Funciones de Cálculo ---
define('TARIFA_ENVIO', 10);
define('RECARGO_COLOR_FOTO', 0.5);
define('RECARGO_RES_ALTA_FOTO', 0.2);

function formatEuroPHP($n) {
    return number_format($n, 2, ',', '.') . ' €';
}

$BLOQUES_PAGINAS = [
    ['max' => 4,    'precio' => 2.0],
    ['max' => 10,   'precio' => 1.8],
    ['max' => null, 'precio' => 1.6]
];

function costePaginasPHP($n) {
    global $BLOQUES_PAGINAS;
    $restante = $n; $acumulado = 0; $total = 0;
    foreach ($BLOQUES_PAGINAS as $bloque) {
        $limiteBloque = ($bloque['max'] === null) ? $restante : $bloque['max'] - $acumulado;
        $enEsteBloque = max(0, min($restante, $limiteBloque));
        $total += $enEsteBloque * $bloque['precio'];
        $restante -= $enEsteBloque;
        $acumulado = ($bloque['max'] === null) ? $acumulado + $enEsteBloque : $bloque['max'];
        if ($restante <= 0) break;
    }
    return $total;
}

function costeTotalPHP($paginas, $fotos, $esColor, $resAlta) {
    $base = TARIFA_ENVIO;
    $paginasCoste = costePaginasPHP($paginas);
    $colorCoste = ($esColor ? RECARGO_COLOR_FOTO : 0) * $fotos;
    $resCoste = ($resAlta ? RECARGO_RES_ALTA_FOTO : 0) * $fotos;
    return $base + $paginasCoste + $colorCoste + $resCoste;
}

$VALORES_PAGINAS = range(1, 15);
$COLUMNAS = [
    ['label' => 'B/N 150-300 dpi',   'color' => false, 'alta' => false],
    ['label' => 'B/N 450-900 dpi',   'color' => false, 'alta' => true],
    ['label' => 'Color 150-300 dpi', 'color' => true,  'alta' => false],
    ['label' => 'Color 450-900 dpi', 'color' => true,  'alta' => true]
];
// --- Fin de funciones ---


// --- 2. Generación del HTML (SÓLO LA TABLA) ---

echo '<section class="tabla">';
echo '<table>';

// THEAD
echo '<thead><tr>';
echo '<th>Páginas</th>';
echo '<th>Fotos</th>';
foreach ($COLUMNAS as $col) {
    echo '<th>' . htmlspecialchars($col['label']) . '</th>';
}
echo '</tr></thead>';

// TBODY
echo '<tbody>';
foreach ($VALORES_PAGINAS as $p) {
    $f = $p * 3;
    echo '<tr>';
    echo '<td>' . $p . '</td>';
    echo '<td>' . $f . '</td>';
    foreach ($COLUMNAS as $col) {
        $total = costeTotalPHP($p, $f, $col['color'], $col['alta']);
        echo '<td>' . formatEuroPHP($total) . '</td>';
    }
    echo '</tr>';
}
echo '</tbody>';
echo '</table>';
echo '</section>';

?>