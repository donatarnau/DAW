
        <fieldset class="search">
            <legend>Rellena todos los campos obligatorios</legend>

            <!-- Tipo de anuncio -->
            <label id="tipoAnuncio">Tipo de Anuncio</label>
            <select name="tipoAnuncio" id="param-anuncio">
                <option value="">Seleccione un tipo de anuncio</option>
                <?php if (isset($tiposAnuncio) && is_array($tiposAnuncio)): ?>
                    <?php foreach ($tiposAnuncio as $tipoAnuncioItem): ?>
                        <option value="<?php echo $tipoAnuncioItem['IdTAnuncio']; ?>" 
                            <?php if ($prevTipoAnuncio == $tipoAnuncioItem['IdTAnuncio']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($tipoAnuncioItem['NomTAnuncio']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <?php if ($errTipoAnuncio): ?>
                <p class="error-msg">Debe seleccionar un tipo de anuncio.</p>
            <?php endif; ?>

            <!-- Tipo de vivienda -->
            <label id="tipoVivienda">Tipo de Vivienda</label>
            <select name="tipoVivienda" id="param-vivienda">
                <option value="">Seleccione un tipo de vivienda</option>
                <?php if (isset($tiposVivienda) && is_array($tiposVivienda)): ?>
                    <?php foreach ($tiposVivienda as $tipoViviendaItem): ?>
                        <option value="<?php echo $tipoViviendaItem['IdTVivienda']; ?>" 
                            <?php if ($prevTipoVivienda == $tipoViviendaItem['IdTVivienda']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($tipoViviendaItem['NomTVivienda']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <?php if ($errTipoVivienda): ?>
                <p class="error-msg">Debe seleccionar un tipo de vivienda.</p>
            <?php endif; ?>

            <!-- Nombre -->
            <label id="nombre">Nombre</label>
            <input type="text" name="nombre" id="param-nombre" value="<?= htmlspecialchars($prevNombre) ?>">
            <?php if ($errNombre): ?>
                <p class="error-msg">Debe indicar un nombre para el anuncio.</p>
            <?php endif; ?>

            <!-- Descripcion -->
            <label id="descripcion">Descripción</label>
            <textarea name="texto" id="param-descripcion"><?= htmlspecialchars($prevDescripcion) ?></textarea>

            <?php if ($errDescripcion): ?>
                <p class="error-msg">Debe indicar una descripción para el anuncio.</p>
            <?php endif; ?>


            <!-- Ciudad -->
            <label id="ciudad">Ciudad</label>
            <input type="text" name="ciudad" id="param-ciudad" value="<?= htmlspecialchars($prevCiudad) ?>">
            <?php if ($errCiudad): ?>
                <p class="error-msg">Debe indicar la ciudad.</p>
            <?php endif; ?>
            <?php if ($errCiudadNom): ?>
                <p class="error-msg">La ciudad solo debe contener letras.</p>
            <?php endif; ?>

            <!-- País -->
            <label id="pais">País</label>
            <select name="pais" id="param-pais">
                <option value="">Seleccione un país</option>
                <?php if (isset($paises) && is_array($paises)): ?>
                    <?php foreach ($paises as $paisItem): ?>
                        <option value="<?php echo $paisItem['IdPais']; ?>" 
                            <?php if ($prevPais == $paisItem['IdPais']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($paisItem['NomPais']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <?php if ($errPais): ?>
                <p class="error-msg">Debe indicar el país.</p>
            <?php endif; ?>

            <!-- Precio -->
            <label for="precio">Precio</label>
            <input type="text" id="precio" name="precio" placeholder="En euros" value="<?= htmlspecialchars($prevPrecio) ?>">
            <?php if ($errPrecio): ?>
                <p class="error-msg">Debe indicar el precio del inmueble.</p>
            <?php endif; ?>
            <?php if ($errPrecioNum): ?>
                <p class="error-msg">El precio debe ser un número válido.</p>
            <?php endif; ?>

            <hr>
            <label class="subSection">Características</label>
            <label>Superficie:</label>
            <input type="text" name="Superficie" placeholder="En metros cuadrados" value="<?= htmlspecialchars($prevSuperficie) ?>">
            <?php if ($errSuperficie): ?>
                <p class="error-msg">La superficie debe indicarse únicamente con números.</p>
            <?php endif; ?>

            <label>Número de habitaciones:</label>
            <input type="text" name="NHabitaciones" value="<?= htmlspecialchars($prevHabitaciones) ?>">
            <?php if ($errHabitaciones): ?>
                <p class="error-msg">El número de habitaciones debe indicarse únicamente con números.</p>
            <?php endif; ?>

            <label>Número de baños:</label>
            <input type="text" name="NBanyos" value="<?= htmlspecialchars($prevBanyos) ?>">
            <?php if ($errBanyos): ?>
                <p class="error-msg">El número de baños debe indicarse únicamente con números.</p>
            <?php endif; ?>

            <label>Planta:</label>
            <input type="text" name="Planta" value="<?= htmlspecialchars($prevPlanta) ?>">
            <?php if ($errPlanta): ?>
                <p class="error-msg">La planta debe indicarse únicamente con números.</p>
            <?php endif; ?>

            <label>Año de construcción:</label>
            <input type="text" name="Anyo" value="<?= htmlspecialchars($prevAnyo) ?>">
            <?php if ($errAnyo): ?>
                <p class="error-msg">El año debe indicarse únicamente con números.</p>
            <?php endif; ?>
            <?php if ($errAnyoFuturo): ?>
                <p class="error-msg">El año de construcción no puede ser en el futuro.</p>
            <?php endif; ?>

