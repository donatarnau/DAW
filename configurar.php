<?php
// Página para seleccionar estilo alternativo. Accesible desde perfil.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Requiere que el usuario esté logueado
if (!isset($_SESSION['user'])) {
    header('Location: ./login.php');
    exit;
}

$estilosDir = __DIR__ . DIRECTORY_SEPARATOR . 'estilos';
$all = [];
if (is_dir($estilosDir)) {
    foreach (glob($estilosDir . DIRECTORY_SEPARATOR . '*.css') as $file) {
        $base = basename($file);
        // Excluir hojas base/responsive y print
        if (in_array($base, ['minimum.css','medium.css','maximum.css','print.css'])) continue;
        $all[] = $base;
    }
}

// Mapa de nombres legibles para los ficheros comunes
$labels = [
    'dark.css' => 'Modo noche',
    'contrast.css' => 'Alto contraste',
    'big.css' => 'Letra grande',
    'contrast-big.css' => 'Contraste + Letra grande'
];

// Procesar envío
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sel = isset($_POST['style']) ? $_POST['style'] : '';
    if ($sel === '') {
        unset($_SESSION['preferred_style']);
        $message = 'Preferencia restablecida a la opción por defecto.';
    } else {
        // Validar que exista en el listado
        if (in_array($sel, $all, true)) {
            $_SESSION['preferred_style'] = $sel;
            $message = 'Preferencia guardada: ' . (isset($labels[$sel]) ? $labels[$sel] : $sel);
        } else {
            $message = 'Selección inválida.';
        }
    }
}

$titulo = 'Configurar estilos';
$encabezado = 'Configurar apariencia';
require 'cabecera.php';
?>

    <section class="forms">
        <h2>Selecciona un estilo alternativo</h2>

        <?php if ($message !== ''): ?>
            <p class="info-msg"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form method="post" action="./configurar.php">
            <fieldset>
                <legend>Estilos disponibles</legend>
                <div>
                    <label>
                        <input type="radio" name="style" value="" <?php echo empty($_SESSION['preferred_style']) ? 'checked' : ''; ?>>
                        Por defecto (sin alternativo)
                    </label>
                </div>
                <?php foreach ($all as $file): ?>
                    <?php $label = isset($labels[$file]) ? $labels[$file] : ucfirst(str_replace(['-','.css'],' ', $file)); ?>
                    <div>
                        <label>
                            <input type="radio" name="style" value="<?php echo htmlspecialchars($file); ?>" <?php echo (isset($_SESSION['preferred_style']) && $_SESSION['preferred_style'] === $file) ? 'checked' : ''; ?>>
                            <?php echo htmlspecialchars($label); ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </fieldset>

            <button type="submit">Guardar preferencia</button>
        </form>

        <p><a href="./perfil.php">Volver al perfil</a></p>
    </section>

<?php
require 'pie.php';
?>
