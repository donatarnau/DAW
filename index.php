<?php
/*
 * Fichero: index.php
 * (Reemplaza a index.html)
 */

// 1. DEFINIR VARIABLES PARA LA CABECERA
$titulo = "Bienvenido a PI - Pisos e Inmuebles";
$encabezado = "Pisos e Inmuebles";

// 2. Incluimos la cabecera
// cabecera.php se encargará de:
// - Mostrar el menú (público o logueado)
// - Si es público Y estamos en index.php, mostrará los errores del popup
include 'cabecera.php';
?>
        <section id="lastUploaded">
            <h2>ULTIMOS ANUNCIOS</h2>
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
                <li>
                    <article>
                        <figure>
                            <a href="<?php echo $loggedIn ? './anuncio.php' . $userQueryParam . '&id=3' : './login.php'; ?>"><img src="./img/a1.jpeg" alt="anuncio1"></a>
                        </figure>
                        <a href="<?php echo $loggedIn ? './anuncio.php' . $userQueryParam . '&id=3' : './login.php'; ?>"><h2>
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
                            <a href="<?php echo $loggedIn ? './anuncio.php' . $userQueryParam . '&id=4' : './login.php'; ?>"><img src="./img/a2.png" alt="anuncio1"></a>
                        </figure>
                        <a href="<?php echo $loggedIn ? './anuncio.php' . $userQueryParam . '&id=4' : './login.php'; ?>"><h2>
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
                            <a href="<?php echo $loggedIn ? './anuncio.php' . $userQueryParam . '&id=5' : './login.php'; ?>"><img src="./img/a1.jpeg" alt="anuncio1"></a>
                        </figure>
                        <a href="<?php echo $loggedIn ? './anuncio.php' . $userQueryParam . '&id=5' : './login.php'; ?>"><h2>
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