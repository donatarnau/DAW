<?php
/**
 * services/logic_fotos.php
 * Función reutilizable para obtener las fotos y datos básicos de un anuncio.
 * Se usa tanto en la vista pública (anuncioFotos.php) como en la privada (userFotos.php).
 */

function obtener_datos_fotos($mysqli, $idAnuncio) {
    $resultado = [
        'anuncio' => null,
        'fotos' => []
    ];

    // Consulta optimizada: Obtenemos datos del anuncio y sus fotos en una sola llamada
    $sql = "SELECT 
                A.IdAnuncio,
                A.Titulo AS TituloAnuncio,
                A.Usuario AS PropietarioId,
                A.FPrincipal AS FPrincipal,
                A.Alternativo AS PAlternativo,
                F.IdFoto,
                F.Titulo AS TituloFoto,
                F.Foto,
                F.Alternativo
            FROM ANUNCIOS A
            LEFT JOIN FOTOS F ON F.Anuncio = A.IdAnuncio
            WHERE A.IdAnuncio = ?";

    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $idAnuncio);
        $stmt->execute();
        $res = $stmt->get_result();
        
        while ($row = $res->fetch_assoc()) {
            // Si es la primera fila, guardamos los datos del anuncio
            if ($resultado['anuncio'] === null) {
                $resultado['anuncio'] = [
                    'Id' => $row['IdAnuncio'],
                    'Titulo' => $row['TituloAnuncio'],
                    'Propietario' => $row['PropietarioId'],
                    'FPrincipal' => $row['FPrincipal'],
                    'PAlternativo' => $row['PAlternativo']
                ];
            }

            // Si hay foto (LEFT JOIN puede traer NULL en campos de foto si no hay ninguna)
            if ($row['IdFoto'] !== null) {
                $resultado['fotos'][] = [
                    'IdFoto' => $row['IdFoto'],
                    'Titulo' => $row['TituloFoto'],
                    'Foto' => $row['Foto'],
                    'Alternativo' => $row['Alternativo']
                ];
            }
        }
        $stmt->close();
    }

    return $resultado;
}
?>