<?php
/*
 * services/estilos.php
 * Lista de estilos (títulos = title de los <link alternate>) y asignación fija por usuario.
 */

$estilosDisponibles = [
    'Modo noche',
    'Alto contraste',
    'Letra grande',
    'Contraste + Letra grande',
];

// Asignación fija usuario → estilo (ajústalo a tu gusto)
$estiloPorUsuario = [
    'asier'    => '',
    'arnau'    => 'Alto contraste',
    'usuario1' => 'Modo noche',
    'admin'    => 'Contraste + Letra grande',
];

/**
 * Devuelve el título del estilo para $user, o null si no hay/ no es válido.
 */
function obtenerEstiloParaUsuario($user) {
    global $estiloPorUsuario, $estilosDisponibles;
    if (!isset($estiloPorUsuario[$user])) return null;
    $titulo = $estiloPorUsuario[$user];
    return in_array($titulo, $estilosDisponibles, true) ? $titulo : null;
}
