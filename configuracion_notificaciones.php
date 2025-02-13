<?php
include 'views/header.php';
include 'includes/db.php'; // Conexión a la base de datos

// Verificar si el usuario tiene permisos
if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] !== 'administrador' && $_SESSION['rol'] !== 'vendedor')) {
    die("Acceso no autorizado");
}

$mensaje_stock = "";
$mensaje_caducidad = "";

// Procesar formulario para actualizar el stock mínimo y la activación de la alarma
if (isset($_POST['actualizar_stock'])) {
    if (isset($_POST['stock_minimo']) && is_numeric($_POST['stock_minimo']) && $_POST['stock_minimo'] > 0) {
        $stock_minimo = intval($_POST['stock_minimo']);
        $alarma_activada = isset($_POST['alarma_activada']) ? 1 : 0;

        $query_check = "SELECT id FROM configuracion WHERE id = 1";
        $result = $db->query($query_check);

        if ($result->num_rows > 0) {
        $query_update = "UPDATE configuracion SET stock_minimo = ?, alarma_activada = ? where id= 1";
        $stmt = $db->prepare($query_update);
        $stmt->bind_param("ii", $stock_minimo, $alarma_activada);
        } else {
            $query_insert = "INSERT INTO configuracion (id, stock_minimo = ?, alarma_activada = ?) VALUES (1, ?, ?)";
            $stmt = $db->prepare($query_insert);
            $stmt->bind_param("is", $alarma_activada, $alarma_caducidad);
        }

        if ($stmt->execute()) {
            $mensaje_stock = "<p style='color: green;'>Configuración actualizada correctamente.</p>";
        } else {
            $mensaje_stock = "<p style='color: red;'>Error al actualizar la configuración.</p>";
        }
    } else {
        $mensaje_stock = "Ingrese un número válido para el stock mínimo.";
    }
}

if (isset($_POST['actualizar_caducidad'])) {
    
    $cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : null;
    $periodo = isset($_POST['periodo']) ? $_POST['periodo'] : null;
    $temporalidad = isset($_POST['temporalidad']) ? $_POST['temporalidad'] : null;

    $limite_cantidad = isset($_POST['limite_cantidad']) ? intval($_POST['limite_cantidad']) : null;
    $limite_periodo = isset($_POST['limite_periodo']) ? $_POST['limite_periodo'] : null;

    $alarma_caducidad =$cantidad."_". $periodo."_". $temporalidad;
    $limite_caducidad = $limite_cantidad."_".$limite_periodo;
    $alarma_activada = isset($_POST['alarma_activada']) ? 1 : 0;

        // Verificar si existe el registro con ID = 2
    $query_check = "SELECT id FROM configuracion WHERE id = 2";
    $result = $db->query($query_check);

    if ($result->num_rows > 0) {
        // Si existe, actualizar el registro
        $query_update = "UPDATE configuracion SET alarma_activada = ?, alarma_caducidad = ?, limite_caducidad = ? WHERE id = 2";
        $stmt = $db->prepare($query_update);
        $stmt->bind_param("iss", $alarma_activada, $alarma_caducidad, $limite_caducidad);
    } else {
        // Si no existe, insertar un nuevo registro con id = 2
        $query_insert = "INSERT INTO configuracion (id, alarma_activada, alarma_caducidad, limite_caducidad) VALUES (2, ?, ?, ?)";
        $stmt = $db->prepare($query_insert);
        $stmt->bind_param("iss", $alarma_activada, $alarma_caducidad, $limite_caducidad);
    }

    if ($stmt->execute()) {
        $mensaje_caducidad = "<p style='color: green;'>Configuración actualizada correctamente.</p>";
    } else {
        $mensaje_caducidad = "<p style='color: red;'>Error al actualizar la configuración.</p>";
    }

}


// Obtener los valores actuales de stock mínimo y estado de la alarma
$query_config = "SELECT stock_minimo, alarma_activada FROM configuracion where id = 1";
$result_config = $db->query($query_config);
$row_config = $result_config->fetch_assoc();
$stock_minimo_actual = $row_config ? $row_config['stock_minimo'] : 1;
$alarma_stock_activada_actual = $row_config ? $row_config['alarma_activada'] : 1;


// Obtener los valores actuales de caducidad y estado de la alarma
$query = "SELECT alarma_caducidad, limite_caducidad, alarma_activada FROM configuracion WHERE id = 2";
$result = $db->query($query);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $alarma_caducidad = $row['alarma_caducidad'];
    $limite_caducidad = $row['limite_caducidad'];
    $alarma_activada_actual = $row['alarma_activada'];
    
    // Separar los valores
    list($cantidad_actual, $periodo_actual, $temporalidad_actual) = explode("_", $alarma_caducidad);
    list($limite_cantidad, $limite_periodo) = explode("_", $limite_caducidad);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar Notificaciones</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            font-family: 'Open Sans', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
        }

        main {
            flex: 1;
            display: flex;
            max-width: 1200px;
            margin: 20px auto;
            margin-left: 20px;
        }

        .menu {
            width: 220px;
            background-color: #2c3e50;
            color: #fff;
            padding: 40px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .menu h3 {
            font-size: 18px;
            margin-bottom: 30px;
            color: #ecf0f1;
            text-align: center;
            border-bottom: 1px solid #ecf0f1;
            padding-bottom: 10px;
        }

        .menu ul {
            list-style-type: none;
            padding: 0;
        }

        .menu ul li {
            margin-bottom: 15px;
        }

        .menu ul li a {
            color: #ecf0f1;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            display: block;
            padding: 10px 15px;
            background-color: #34495e;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .menu ul li a:hover {
            background-color: #1abc9c;
            color: #fff;
        }

        .content-container {
            display: flex;
            flex-direction: column;
            flex: 1;
            padding: 0 20px;
        }

        .form-container {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            max-width: 600px;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 25px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 25px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 4px;
            bottom: 3.5px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #4CAF50;
        }

        input:checked + .slider:before {
            transform: translateX(24px);
        }

        .form-row {
    display: flex;
    gap: 20px; /* Espacio entre las columnas */
}

.form-column {
    flex: 1; /* Hace que ambas columnas tengan el mismo ancho */
    display: flex;
    flex-direction: column;
}

        .boton-editar {
    display: inline-block;
    padding: 10px 20px; /* Tamaño del botón */
    background-color: #1abc9c; /* Color de fondo */
    color: white; /* Color del texto */
    text-decoration: none; /* Quitar subrayado */
    font-size: 16px; /* Tamaño del texto */
    border-radius: 5px; /* Bordes redondeados */
    transition: background-color 0.3s ease; /* Transición suave */
    text-align: center; /* Centrar el texto */
    font-weight: bold; /* Texto en negrita */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Sombra */
}

.boton-editar:hover {
    background-color: #16a085; /* Color cuando pasas el mouse */
    box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15); /* Sombra más intensa al hacer hover */
}

.boton-editar:active {
    background-color: #148f77; /* Color al hacer clic */
    transform: translateY(2px); /* Efecto de presionar el botón */
}

.breadcrumb {
    font-size: 14px;
    margin: 0 0 20px 10px;
    color: #555;
}

.breadcrumb a {
    text-decoration: none;
    color: #1abc9c;
    font-weight: bold;
}

.breadcrumb span {
    margin: 0 10px;
    color: #999;
}

    </style>
</head>
<body>
    <main>
    <div class="menu">
            <h3>Configuraciones</h3>
            <ul>
                <li><a href="ver_mis_datos.php">Mi Cuenta</a></li>
                <?php if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador'):?>
                <li><a href="ver_mis_mascotas.php">Ver Mis Mascotas</a></li>
                <li><a href="agregar_mascota.php">Agregar Mascota</a></li>
                <li><a href="historial_servicios.php">Historial de Servicios</a></li>
                <li><a href="agendar_cita.php">Agendar Cita</a></li>
                <?php endif; ?>
                <?php if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] == 'administrador'):?>
                <li><a href="facturas.php">Facturas y Pagos</a></li>
                <?php endif; ?>
                <li><a href="cambiar_contrasena.php">Cambiar Contraseña</a></li>
                <li><a href="configuracion_notificaciones.php">Configuración de Notificaciones</a></li>
                <li><a href="soporte.php">Soporte o Ayuda</a></li>
            </ul>
        </div>

        

        <div class="content-container">
        <div class="breadcrumb">
                <a href="ver_mis_datos.php">Mi Cuenta</a> <span>➤</span> Configuracion de Notificaciones
            </div>

        <div class="form-container">
        <h2>Notificaciones Stock Bajo</h2>
        <form method="POST">
    <label for="stock_minimo">Stock Mínimo para Notificación:</label>
    <input type="number" name="stock_minimo" id="stock_minimo" value="<?php echo $stock_minimo_actual; ?>" required>
    <br><br>

    <label for="alarma_activada">Activar Alarma:</label>
    <label class="switch">
        <input type="checkbox" name="alarma_activada" id="alarma_activada" <?php echo $alarma_stock_activada_actual ? 'checked' : ''; ?>>
        <span class="slider"></span>
    </label>
    <br><br>
    <?php echo $mensaje_stock; ?>
    <!-- Botón de envío con el mismo diseño -->
    <button type="submit" class="boton-editar" name="actualizar_stock">Actualizar</button>
</form>

            </div>
            </div>

            <div class="content-container">
    <div class="form-container">
        <h2>Notificaciones de Caducidades</h2>
        <form method="POST">
            <div class="form-row">
                <!-- Primera columna -->
                <div class="form-column">
                <h3>Inicio de la Alerta</h3>
                    <div class="mb-3">
                        <label for="cantidad" class="form-label">Cantidad:</label>
                        <input type="number" class="form-control" id="cantidad" name="cantidad" value="<?php echo $cantidad_actual; ?>" required>
                        <br><br>
                    </div>

                    <div class="mb-3">
                        <label for="periodo" class="form-label">Periodo:</label>
                        <select class="form-select" id="periodo" name="periodo" required>
                            <option value="dia" <?php echo ($periodo_actual == 'dia') ? 'selected' : ''; ?>>Día</option>
                            <option value="semana" <?php echo ($periodo_actual == 'semana') ? 'selected' : ''; ?>>Semana</option>
                            <option value="mes" <?php echo ($periodo_actual == 'mes') ? 'selected' : ''; ?>>Mes</option>
                            <option value="año" <?php echo ($periodo_actual == 'año') ? 'selected' : ''; ?>>Año</option>
                        </select>
                        <br><br>
                    </div>

                    <div class="mb-3">
                        <label for="temporalidad" class="form-label">Temporalidad:</label>
                        <select class="form-select" id="temporalidad" name="temporalidad" required>
                            <option value="antes" <?php echo ($temporalidad_actual == "antes") ? 'selected' : ''; ?>>Antes</option>
                            <option value="despues" <?php echo ($temporalidad_actual == "despues") ? 'selected' : ''; ?>>Después</option>
                        </select>
                        <br><br>
                    </div>

                    <div class="mb-3">
                        <label for="alarma_activada">Activar Alarma:</label>
                        <label class="switch"> 
                            <input type="checkbox" name="alarma_activada" id="alarma_activada" <?php echo ($alarma_activada_actual) ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>


                <!-- Segunda columna -->
                <div class="form-column">
                <h3>Fin para atender la alerta</h3>
                    <div class="mb-3">
                        <label for="limite_cantidad">Límite de alerta (Cantidad):</label>
                        <input type="number" class="form-control" name="limite_cantidad" value="<?php echo $limite_cantidad; ?>" required>
                        <br><br>
                    </div>

                    <div class="mb-3">
                        <label for="limite_periodo">Límite de alerta (Período):</label>
                        <select class="form-select" name="limite_periodo" required>
                            <option value="días" <?php if ($limite_periodo == 'días') echo 'selected'; ?>>Días</option>
                            <option value="semanas" <?php if ($limite_periodo == 'semanas') echo 'selected'; ?>>Semanas</option>
                            <option value="meses" <?php if ($limite_periodo == 'meses') echo 'selected'; ?>>Mes</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Mensaje de éxito o error -->
            <?php echo $mensaje_caducidad; ?>

            <!-- Botón de envío -->
            <button type="submit" class="boton-editar" name="actualizar_caducidad">Actualizar</button>
        </form>
    </div>
</div>

    </main>
    <?php
    $result_config->close();
    $db->close();
    include 'views/footer.php';
    ?>
</body>
</html>
