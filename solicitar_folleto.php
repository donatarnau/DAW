<?php

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user'])) {
        header("Location: ./index.php?error=acceso_denegado");
        exit;
    }

    $userId = $_SESSION['user_id'];
    $username = htmlspecialchars($_SESSION['user']);
    // 2. Si llegamos aquí, el usuario SÍ está "logueado".
    // Guardamos su nombre de forma segura.

    // 3. DEFINIR VARIABLES PARA LA CABECERA
    $titulo = "Solicitar folleto";
    $encabezado = "Solicitar Folleto - Pisos e Inmuebles";
    require 'cabecera.php';



        // --- 2. CONEXIÓN A LA BD ---
    $config = parse_ini_file('config.ini');
    if (!$config) die("Error al leer config.ini");
    @$mysqli = new mysqli($config['Server'], $config['User'], $config['Password'], $config['Database']);
    if ($mysqli->connect_errno) die("Error de conexión a la BD: " . $mysqli->connect_error);

    // --- 4. OBTENER ANUNCIOS DEL USUARIO ---
    // Necesitamos JOIN con PAISES para mostrar el nombre del país.
    // Seleccionamos solo los campos necesarios para el listado simplificado.
    $anuncios = [];
    $sqlAnuncios = "SELECT A.IdAnuncio, A.Titulo 
                    FROM ANUNCIOS A
                    WHERE A.Usuario = ?";

    if ($stmt = $mysqli->prepare($sqlAnuncios)) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $anuncios[] = $row;
        }
        $stmt->close();
    }
    
    // Cerramos conexión, ya tenemos todos los datos
    $mysqli->close();


?>
    <section class="forms" id="folleto">
      <h2>Solicitud de folleto publicitario impreso</h2>
      <p>
        En PI - Pisos e Inmuebles ofrecemos el servicio de impresión y envío de
        folletos publicitarios personalizados de tus anuncios. El coste del servicio
        dependerá de las características que elijas (número de páginas, fotos,
        tipo de impresión y resolución). Además, se aplica un coste fijo de
        procesamiento y envío independiente de la cantidad solicitada.
      </p>

      <section class="tabla">
        <h3>Tarifas</h3>
        <table>
          <tr><th>Concepto</th><th>Tarifa</th></tr>
          <tr><td>Coste procesamiento y envío</td><td>10 €</td></tr>
          <tr><td>&lt; 5 páginas</td><td>2 € por pág.</td></tr>
          <tr><td>entre 5 y 10 páginas</td><td>1.8 € por pág.</td></tr>
          <tr><td>&gt; 10 páginas</td><td>1.6 € por pág.</td></tr>
          <tr><td>Blanco y negro</td><td>0 €</td></tr>
          <tr><td>Color</td><td>0.5 € por foto</td></tr>
          <tr><td>Resolución &lt;= 300 dpi</td><td>0 € por foto</td></tr>
          <tr><td>Resolución &gt; 300 dpi</td><td>0.2 € por foto</td></tr>
        </table>
      </section>

      <section class="tabla" aria-labelledby="tituloCostes">
        <h3 id="tituloCostes">Posibles costes según páginas, fotos, color y resolución</h3>
        
        <div id="contenedorTablaCostes" role="region" aria-live="polite">
            <?php require './services/tabla_costes.php';?>
        </div>
      </section>

      <form action="./respuesta_folleto.php" id="solfolleto" method="post">
        <input type="hidden" name="user" value="<?php echo $username; ?>">
        <fieldset class="search">
          <legend>Datos de envío</legend>

          <label for="nombre">Nombre completo:</label>
          <input type="text" id="nombre" name="nombre" />

          <label for="email">Correo electrónico:</label>
          <input type="text" id="email" name="email" />

          <label for="telefono">Teléfono (opcional):</label>
          <input type="text" id="telefono" name="telefono" />

          <label for="texto">Información adicional (opcional, máx. 4000 caracteres):</label>
          <textarea id="texto" name="texto" rows="4"></textarea>

          <fieldset>
            <legend>Dirección postal</legend>
            <label for="calle">Calle:</label>
            <input type="text" id="calle" name="calle" />

            <label for="numero">Número:</label>
            <input type="text" id="numero" name="numero" />

            <label for="piso">Piso:</label>
            <input type="text" id="piso" name="piso" />

            <label for="puerta">Puerta:</label>
            <input type="text" id="puerta" name="puerta" />

            <label for="cp">Código Postal:</label>
            <input type="text" id="cp" name="cp" />

            <label for="localidad">Localidad:</label>
            <input type="text" id="localidad" name="localidad" />

            <label for="provincia">Provincia:</label>
            <input type="text" id="provincia" name="provincia" />

            <label for="pais">País:</label>
            <input type="text" id="pais" name="pais" />
          </fieldset>
        </fieldset>

        <fieldset class="search">
          <legend>Características del folleto</legend>

          <label for="anuncio">Selecciona tu anuncio:</label>
            <select name="anuncio" id="anuncio">
                <option value="">Despliega para ver tus anuncios</option>
                <?php if (isset($anuncios) && is_array($anuncios)): ?>
                    <?php foreach ($anuncios as $anuncioItem): ?>
                        <option value="<?php echo $anuncioItem['IdAnuncio']; ?>">
                            <?php echo htmlspecialchars($anuncioItem['Titulo']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>

          <label for="copias">Número de copias (1-99):</label>
          <input type="text" id="copias" name="copias" />

          <label for="color_portada">Color de la portada:</label>
          <input type="color" id="color_portada" name="color_portada" value="#000000" />

          <label for="resolucion">Resolución de las fotos:</label>
          <select id="resolucion" name="resolucion">
            <option value="150" selected>150 DPI</option>
            <option value="300">300 DPI</option>
            <option value="450">450 DPI</option>
            <option value="600">600 DPI</option>
            <option value="750">750 DPI</option>
            <option value="900">900 DPI</option>
          </select>

          <label for="fecha_recepcion">Fecha de recepción deseada (opcional):</label>
          <input type="text" id="fecha_recepcion" name="fecha_recepcion" />

          <fieldset id="opciones_impresion">
            <legend>Opciones de impresión</legend>

            <label>Tipo de impresión:</label>
            <input type="radio" id="color" name="tipo_impresion" value="color" />
            <label for="color">Color</label>
            <input type="radio" id="bn" name="tipo_impresion" value="bn" />
            <label for="bn">Blanco y negro</label>

            <label for="mostrar_precio">Incluir precio en el folleto</label>
            <input type="checkbox" id="mostrar_precio" name="mostrar_precio" />
          </fieldset>
        </fieldset>

        <button type="submit">Solicitar folleto</button>
      </form>
    </section>

<?php
    require 'pie.php';
?>