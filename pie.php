    </main>

    <?php
    // Panel "Últimos anuncios visitados" en el footer con la MISMA estructura que el listado de index
    require_once __DIR__ . '/services/ultimos_anuncios.php';
    $ultimos = ua_obtener();
    ?>

    <section id="ultimosAnuncios" aria-labelledby="ua-titulo" class="mini-panel">
        <h2 id="ua-titulo">Últimos anuncios visitados</h2>

        <?php if (empty($ultimos)): ?>
            <ul>
                <li>
                    <article>
                        <p style="padding:1rem 0;color:#666;">Aún no has visitado ningún anuncio.</p>
                    </article>
                </li>
            </ul>
        <?php else: ?>
            <ul>
                <?php foreach ($ultimos as $it): ?>
                    <?php $url = './anuncio.php?id=' . urlencode((string)$it['id']); ?>
                    <li>
                        <article>
                            <figure>
                                <a href="<?= htmlspecialchars($url) ?>">
                                    <img src="<?= htmlspecialchars($it['foto']) ?>" alt="<?= htmlspecialchars($it['nombre']) ?>">
                                </a>
                            </figure>

                            <a href="<?= htmlspecialchars($url) ?>">
                                <h2><?= htmlspecialchars($it['nombre']) ?></h2>
                            </a>

                            <hr>

                            <!-- misma secuencia de <p> que en portada: ciudad, país, precio -->
                            <p><?= htmlspecialchars($it['ciudad']) ?></p>
                            <p><?= htmlspecialchars($it['pais']) ?></p>
                            <p><?= number_format((float)$it['precio'], 0, ',', '.') ?> €</p>
                        </article>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <footer>
        <ul>
            <li><a href="mailto:adg67@alu.ua.es">Arnau Donat García</a></li>
            <li><a href="mailto:agdm5@alu.ua.es">Asier García de Mateos Ocaña</a></li>
            <li><p>Desarrollo de Aplicaciones Web</p></li>
            <li><a href="./accesibilidad.php">Declaración de Accesibilidad</a></li>
        </ul>
    </footer>
    
</body>
</html>