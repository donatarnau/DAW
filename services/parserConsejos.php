<?php

function leerConsejos($rutaFichero) {
    $json = file_get_contents($rutaFichero);
    return json_decode($json, true); // Devuelve array asociativo
}

?>