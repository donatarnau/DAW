<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Prueba de Conexión a la BD</title>
    <style> body { font-family: sans-serif; padding: 20px; } </style>
</head>
<body>
    <h1>Probando la conexión con la Base de Datos 'pibd'...</h1>

    <?php
    // 1. Leer el fichero de configuración (Práctica 9, Apartado 4.6)
    $config = parse_ini_file('config.ini');
    if (!$config) {
        die("<h2>Error: No se pudo leer el archivo 'config.ini'</h2>");
    }

    // 2. Conectar a la BD usando mysqli (Práctica 9, Apartado 4.3.2)
    // Se usa la interfaz orientada a objetos del ejemplo [cite: 1170-1172]
    // Se suprime el error con @ para controlarlo manualmente [cite: 1171]
    @$mysqli = new mysqli(
        $config['Server'],
        $config['User'],
        $config['Password'],
        $config['Database']
    );

    // 3. Comprobar si hay un error de conexión [cite: 1178]
    if ($mysqli->connect_errno) {
        echo "<h2>¡Error al conectar!</h2>";
        echo "<p>Error: " . $mysqli->connect_error . "</p>";
        echo "<p>Asegúrate de que:
                <ul>
                    <li>El servidor XAMPP (MySQL) esté encendido.</li>
                    <li>La contraseña en 'config.ini' para el usuario 'wwwdata' sea correcta.</li>
                    <li>El usuario 'wwwdata' tenga permisos (SELECT, INSERT...).</li>
                    <li>La base de datos 'pibd' exista.</li>
                </ul>
              </p>";
        die(); // Termina el script si no hay conexión
    }

    echo "<h2>¡Conexión exitosa!</h2>";
    echo "<p>Conectado a la base de datos '<b>" . $config['Database'] . "</b>' como '<b>" . $config['User'] . "</b>'.</p>";

    // 4. Hacer una consulta de prueba (SELECT)
    $sentencia = "SELECT NomUsuario, Email, Estilo FROM USUARIOS";
    if ($resultado = $mysqli->query($sentencia)) {

        echo "<h3>Prueba de SELECT en la tabla 'USUARIOS':</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>NomUsuario</th><th>Email</th><th>IdEstilo</th></tr>";

        // 5. Recorrer los resultados [cite: 1195]
        while ($fila = $resultado->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $fila['NomUsuario'] . "</td>";
            echo "<td>" . $fila['Email'] . "</td>";
            echo "<td>" . $fila['Estilo'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";

        // 6. Liberar resultado [cite: 1226]
        $resultado->close();
    } else {
        echo "<h2>Error al ejecutar la consulta:</h2>";
        echo "<p>" . $mysqli->error . "</p>";
    }

    // 7. Cerrar la conexión [cite: 1228]
    $mysqli->close();
    ?>

</body>
</html>