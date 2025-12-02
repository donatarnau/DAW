<?php

function leerAnunciosFormatoPropio($rutaFichero) {
    $contenido = file_get_contents($rutaFichero);

    // Separar bloques entre #ANUNCIO y #FIN
    preg_match_all('/#ANUNCIO(.*?)#FIN/s', $contenido, $coincidencias);

    $anuncios = [];

    foreach ($coincidencias[1] as $bloque) { // [1] contiene el contenido entre las etiquetas
        $lineas = array_map('trim', explode("\n", trim($bloque)));

        $id = null;
        $persona = null;
        $comentario = "";

        $capturandoComentario = false;

        foreach ($lineas as $linea) {

            if (strpos($linea, 'Id:') === 0) {
                $id = trim(substr($linea, strlen('Id:')));
            }
            else if (strpos($linea, 'Persona:') === 0) {
                $persona = trim(substr($linea, strlen('Persona:')));
            }
            else if (strpos($linea, 'Comentario:') === 0) {
                $capturandoComentario = true;
                continue; // La línea siguiente empieza el comentario
            }
            else if ($capturandoComentario) {
                // Permitir comentarios de varias líneas
                $comentario .= $linea . "\n";
            }
        }

        // Guardar anuncio sin salto final
        $anuncios[] = [
            'id' => $id,
            'persona' => $persona,
            'comentario' => trim($comentario)
        ];
    }

    return $anuncios;
}
?>