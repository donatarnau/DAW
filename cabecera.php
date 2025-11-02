<?php
/*
 * CABECERA.PHP (LÓGICA CORREGIDA)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once './services/recordarme.php';

if (!isset($_SESSION['user'])) {
    comprobarCookieRecordarme();
}

$loggedIn = isset($_SESSION['user']);
$username = $loggedIn ? htmlspecialchars($_SESSION['user']) : '';


// 2. ¡LÓGICA DE ERRORES CORREGIDA!
// Detectamos en qué página estamos
$currentPage = basename($_SERVER['PHP_SELF']);

$loginErrorUser = isset($_GET['err_user']);
$loginErrorPass = isset($_GET['err_pass']);
$loginErrorLogin = isset($_GET['err_login']);
$loginValueUser = isset($_GET['val_user']) ? htmlspecialchars($_GET['val_user']) : '';


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Página web de prueba para el desarrollo de la práctica de DAW">
    <meta name="author" content="Asier, Arnau">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cal+Sans&family=Poiret+One&display=swap" rel="stylesheet">

    <link href="./estilos/minimum.css" rel="stylesheet" type="text/css" media="screen and (max-width: 480px)">
    <link href="./estilos/medium.css" rel="stylesheet" type="text/css" media="screen and (min-width: 481px) and (max-width: 1024px)">
    <link href="./estilos/maximum.css" rel="stylesheet" type="text/css" media="screen and (min-width: 1025px)">

    <link rel="stylesheet" href="./icons/css/fontello.css">

    <link rel="alternate stylesheet" href="./estilos/dark.css" title="Modo noche">
    <link rel="alternate stylesheet" href="./estilos/contrast.css" title="Alto contraste">
    <link rel="alternate stylesheet" href="./estilos/big.css" title="Letra grande">
    <link rel="alternate stylesheet" href="./estilos/contrast-big.css" title="Contraste + Letra grande">
    <link rel="stylesheet" href="./estilos/print.css" media="print">

    <?php
    // Scripts comunes
    if (isset($scripts) && is_array($scripts)) {
        foreach ($scripts as $script) {
            // Desactivamos JS de validación para probar PHP    
            if ($script !== 'fastLogin.js' && $script !== 'login.js') {
                echo '<script src="./scripts/' . $script . '"></script>';
            }
        }
    }
    ?>
    <!--<script src="./scripts/main.js"></script>-->
    
    <title><?php echo isset($titulo) ? $titulo : 'Pisos e Inmuebles'; ?></title>
</head>
<body>
    <header>
        <section class="titulo">
            <h1><?php echo isset($encabezado) ? $encabezado : 'Pisos e Inmuebles'; ?></h1>
        </section>
        
        <?php if ($loggedIn): ?>

        <nav class="navPC">
            <ul>
                <li><a href="./index.php"><i class="icon-home"></i>Inicio</a></li>
                <li id="barraRapida">
                    <form action="./resBuscar.php" id="fastSearch" method="get">
                        <input type="text" name="ciudad" placeholder="Ciudad" id="fs">
                        <button id="fs-buscar" type="submit"><i class="icon-search"></i></button>
                        <input type="hidden" name="user" value="<?php echo $username; ?>">
                    </form>
                </li>
                <li><a href="./buscar.php">Formulario de búsqueda</a></li>
                <li><a href="./crearAnuncio.php">Publicar anuncio</a></li>
                <li><a href="./perfil.php">¡Bienvenido, <?php echo $username; ?>!</a></li>
            </ul>
        </nav>
        <nav class="navTablet">
            <article>
                <a href="./index.php"><i class="icon-home"></i></a>
                <input type="checkbox" id="menuMostrarTablet">
                <label for="menuMostrarTablet" id="etiquetaMostrarTablet"><i class="icon-search"></i><p>BUSCAR<br>ANUNCIOS</p></label>
                <label for="menuMostrarTablet" id="etiquetaOcultarTablet"><i class="icon-cancel"></i><p>CERRAR<br>PESTAÑA</p></label>
                <a href="./perfil.php"><i class="icon-user"></i><p>Bienvenido<br><?php echo $username; ?></p></a>
            </article>
            <ul class="desplegableMovil">
                <li>
                    <form action="./resBuscar.html" id="fastSearch">
                        <input type="text" name="ciudad" placeholder="Ciudad" id="fs">
                        <button id="fs-buscar" type="submit"><i class="icon-search"></i></button>
                        <input type="hidden" name="user" value="<?php echo $username; ?>">
                    </form>
                </li>
                <li><a href="./buscar.php">Formulario de búsqueda</a></li>
            </ul>
            <a href="./crearAnuncio.php" id="crearAnuncioBotonTablet"><i class="icon-plus"></i></a>  
        </nav>
        <nav class="navMovil">
            <article>
                <a href="./index.php"><i class="icon-home"></i></a>
                <input type="checkbox" id="menuMostrar">
                <label for="menuMostrar" id="etiquetaMostrar"><i class="icon-search"></i></label>
                <label for="menuMostrar" id="etiquetaOcultar"><i class="icon-cancel"></i></label>
                <a href="./perfil.php"><i class="icon-user"></i></a>
            </article>
            <ul class="desplegableMovil">
                <li>
                    <form action="./resBuscar.html" id="fastSearch">
                        <input type="text" name="ciudad" placeholder="Ciudad" id="fs">
                        <button id="fs-buscar" type="submit"><i class="icon-search"></i></button>
                        <input type="hidden" name="user" value="<?php echo $username; ?>">
                    </form>
                </li>
                <li><a href="./buscar.php">Formulario de búsqueda</a></li>
            </ul>
            <a href="./crearAnuncio.php" id="crearAnuncioBoton"><i class="icon-plus"></i></a>
        </nav>
        <?php else: ?>

        <nav class="navPC">
            <ul>
                <li><a href="./index.php"><i class="icon-home"></i>Inicio</a></li>
                <li id="barraRapida">
                    <form action="./resBuscar.php" id="fastSearch" method="get">
                        <input type="text" name="ciudad" placeholder="Ciudad" id="fs">
                        <button id="fs-buscar" type="submit"><i class="icon-search"></i></button>
                    </form>
                </li>
                <li><a href="./buscar.php">Formulario de búsqueda</a></li>
                <li id="mostrarLogin"><a href="./login.php">No estas identificado</a></li>
                <li id="loginHover">
                    <form action="./services/control_acceso.php" method="post" class="auth" id="login-form">
                        <label>Inicio de sesión rápido</label>
                        <input type="text" name="user" placeholder="Nombre de usuario" id="fl-user">
                        <?php if ($loginErrorUser) echo '<label class="fl-ad" style="display:flex; color:red;">Rellena este campo</label>'; ?>
                        <input type="password" name="pwd" placeholder="Contraseña" id="fl-pass">
                        <?php if ($loginErrorPass) echo '<label class="fl-ad" style="display:flex; color:red;">Rellena este campo</label>'; ?>
                        <?php if ($loginErrorLogin) echo '<label class="fl-ad" style="display:flex; color:red;">Usuario o contraseña incorrectos</label>'; ?>
                        <label>
                            <input type="checkbox" name="recordarme"> Recordarme en este equipo
                        </label>
                        <button type="submit">Iniciar sesión</button>
                    </form>
                </li>
            </ul>
        </nav>
        <nav class="navTablet">
            <article>
                <a href="./index.php"><i class="icon-home"></i></a>
                <input type="checkbox" id="menuMostrarTablet">
                <label for="menuMostrarTablet" id="etiquetaMostrarTablet"><i class="icon-search"></i><p>BUSCAR<br>ANUNCIOS</p></label>
                <label for="menuMostrarTablet" id="etiquetaOcultarTablet"><i class="icon-cancel"></i><p>CERRAR<br>PESTAÑA</p></label>
                <a href="./login.php"><i class="icon-login"></i><p>No estás<br>identificado</p></a>
            </article>
            <ul class="desplegableMovil">
                <li>
                    <form action="./resBuscar.php" id="fastSearch">
                    <input type="text" name="ciudad" placeholder="Ciudad" id="fs">
                    <button id="fs-buscar" type="submit"><i class="icon-search"></i></button>
                    </form>
                </li>
                <li><a href="./buscar.php">Formulario de búsqueda</a></li>
            </ul>   
        </nav>
        <nav class="navMovil">
            <article>
                <a href="./index.php"><i class="icon-home"></i></a>
                <input type="checkbox" id="menuMostrar">
                <label for="menuMostrar" id="etiquetaMostrar"><i class="icon-search"></i></label>
                <label for="menuMostrar" id="etiquetaOcultar"><i class="icon-cancel"></i></label>
                <a href="./login.php"><i class="icon-login"></i></a>
            </article>
            <ul class="desplegableMovil">
                <li>
                    <form action="./resBuscar.php" id="fastSearch">
                    <input type="text" name="ciudad" placeholder="Ciudad" id="fs">
                    <button id="fs-buscar" type="submit"><i class="icon-search"></i></button>
                    </form>
                </li>
                <li><a href="./buscar.php">Formulario de búsqueda</a></li>
            </ul>
        </nav>
        <?php endif; ?>
    </header>
    <main>