<?php
/**
 * * Función auxiliar para procesar subidas de imágenes:
 * 1. Valida que sea una imagen real.
 * 2. Gestiona transparencias (PNG/GIF).
 * 3. Convierte y guarda siempre como .JPG.
 */

/**
 * Sube una imagen, la convierte a JPG y la guarda con el nombre especificado.
 *
 * @param array  $fileInput    El array $_FILES['nombre_campo']
 * @param string $directorio   Ruta al directorio de destino (con barra final, ej: './img/perfiles/')
 * @param string $nombreBase   Nombre del archivo SIN extensión (ej: 'perfil4' o 'a1-3')
 * * @return array ['ok' => bool, 'fileName' => string, 'msg' => string]
 */
function subir_y_convertir_a_jpg($fileInput, $directorio, $nombreBase) {
    // 1. Validar que no hubo error en la subida básica
    if (!isset($fileInput) || $fileInput['error'] !== UPLOAD_ERR_OK) {
        // Códigos de error comunes: 4 = no file uploaded
        if ($fileInput['error'] === UPLOAD_ERR_NO_FILE) {
            return ['ok' => false, 'msg' => 'No se ha seleccionado ningún archivo.'];
        }
        return ['ok' => false, 'msg' => 'Error al subir el archivo (Código: ' . $fileInput['error'] . ').'];
    }

    // 2. Verificar que es una imagen válida usando getimagesize
    $infoImagen = getimagesize($fileInput['tmp_name']);
    if ($infoImagen === false) {
        return ['ok' => false, 'msg' => 'El archivo subido no es una imagen válida.'];
    }

    $tipoImagen = $infoImagen[2]; // Índice 2 contiene la constante del tipo (IMAGETYPE_JPEG, etc.)
    $ancho = $infoImagen[0];
    $alto = $infoImagen[1];
    
    // 3. Cargar la imagen en memoria según su tipo original
    $imagenOrigen = null;

    switch ($tipoImagen) {
        case IMAGETYPE_JPEG:
            $imagenOrigen = imagecreatefromjpeg($fileInput['tmp_name']);
            break;
        case IMAGETYPE_PNG:
            $imagenOrigen = imagecreatefrompng($fileInput['tmp_name']);
            break;
        case IMAGETYPE_GIF:
            $imagenOrigen = imagecreatefromgif($fileInput['tmp_name']);
            break;
        case IMAGETYPE_WEBP:
            // Verificamos si la versión de PHP/GD soporta WebP
            if (function_exists('imagecreatefromwebp')) {
                $imagenOrigen = imagecreatefromwebp($fileInput['tmp_name']);
            } else {
                return ['ok' => false, 'msg' => 'El servidor no soporta imágenes WEBP.'];
            }
            break;
        default:
            return ['ok' => false, 'msg' => 'Formato de imagen no soportado. Usa JPG, PNG o GIF.'];
    }

    if (!$imagenOrigen) {
        return ['ok' => false, 'msg' => 'Error interno al procesar la imagen original.'];
    }

    // 4. Crear un lienzo nuevo TrueColor para el JPG final
    $imagenFinal = imagecreatetruecolor($ancho, $alto);

    // 5. Gestionar la transparencia (importante para PNG/GIF)
    // Rellenamos el fondo de blanco, porque JPG no soporta transparencia.
    $fondoBlanco = imagecolorallocate($imagenFinal, 255, 255, 255);
    imagefill($imagenFinal, 0, 0, $fondoBlanco);

    // Copiamos la imagen original sobre el fondo blanco
    imagecopy($imagenFinal, $imagenOrigen, 0, 0, 0, 0, $ancho, $alto);

    // 6. Generar la ruta de destino final con extensión .jpg
    // Aseguramos que el directorio tenga la barra final '/'
    $directorio = rtrim($directorio, '/') . '/';
    
    // Verificamos que el directorio exista, si no, intentamos crearlo (opcional, pero recomendado)
    if (!file_exists($directorio)) {
        if (!mkdir($directorio, 0755, true)) {
            imagedestroy($imagenOrigen);
            imagedestroy($imagenFinal);
            return ['ok' => false, 'msg' => 'No se pudo crear el directorio de destino.'];
        }
    }

    $nombreArchivoFinal = $nombreBase . '.jpg';
    $rutaCompleta = $directorio . $nombreArchivoFinal;

    // 7. Guardar como JPG (Calidad 90 es un buen balance)
    $resultado = imagejpeg($imagenFinal, $rutaCompleta, 90);

    // 8. Liberar memoria
    imagedestroy($imagenOrigen);
    imagedestroy($imagenFinal);

    if ($resultado) {
        return [
            'ok' => true, 
            'fileName' => $nombreArchivoFinal, // Devolvemos solo el nombre para guardar en BD
            'msg' => 'Imagen subida correctamente.'
        ];
    } else {
        return ['ok' => false, 'msg' => 'Error al guardar el archivo JPG en el disco.'];
    }
}
?>