<?php
include 'views/header.php';
include 'includes/db.php'; // Conexión a la base de datos

$busqueda = isset($_POST['busqueda']) ? $_POST['busqueda'] : '';

// Consulta para obtener el historial de servicios de una mascota
$sql = "SELECT 
            mascotas.nombre_mascota,
            servicios.nombre_servicio,
            servicios_mascotas.fecha_inicio AS fecha_inicio,
            servicios_mascotas.progreso AS progreso,
            servicios_mascotas.estado_servicio AS estado
        FROM 
            mascotas
        JOIN 
            servicios_mascotas ON mascotas.id_mascota = servicios_mascotas.id_mascota
        JOIN 
            servicios ON servicios.id_servicio = servicios_mascotas.id_servicio
        WHERE 
            mascotas.id_usuario = ?";


if (!empty($busqueda)) {
    $sql .= " AND mascotas.nombre_mascota LIKE ?";
}

$stmt = $db->prepare($sql);

if (!empty($busqueda)) {
    $searchParam = '%' . $busqueda . '%';
    $stmt->bind_param("is", $_SESSION['usuario_id'], $searchParam);
} else {
    $stmt->bind_param("i", $_SESSION['usuario_id']);
}

$stmt->execute();
$resultado = $stmt->get_result();

$stmt->close();
$db->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Servicios</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        /* Aquí van tus estilos CSS */
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
            padding: 0;
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

        .bread {
            font-size: 20px;
            margin-left: 10px;
            color: #333;
            font-weight: bold;
        }

        .content-container {
            display: flex;
            flex-direction: column;
            flex: 1;
            padding: 0 20px;
        }

        /* Estilos de la tabla */
        table {
            width: 150%; /* O puedes usar un valor en píxeles o porcentaje, como '800px' */
            max-width: 1200px; /* Controla el ancho máximo de la tabla */
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 15px;
            text-align: left;
        }

        th {
            background-color: #34495e;
            color: #fff;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #ddd;
        }

        input[type="submit"] {
            padding: 8px 15px;
            background-color: #e74c3c;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        input[type="submit"]:hover {
            background-color: #c0392b;
            transform: scale(1.05);
        }

        input[type="text"] {
        padding: 10px; /* Espaciado dentro del input */
        border: 1px solid #ccc; /* Color del borde */
        border-radius: 25px; /* Borde redondeado */
        width: 80%; /* Ancho ajustable según tus preferencias */
        outline: none; /* Elimina el borde azul al hacer clic */
        transition: border-color 0.3s ease; /* Efecto suave al cambiar el borde */
    }

    input[type="text"]:focus {
        border-color: #1abc9c; /* Color del borde cuando se enfoca */
    }
    </style>
</head>
<body>
    <main>
        <!-- Menú lateral -->
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

        <!-- Contenedor de contenido (breadcrumb + mascotas) -->
        <div class="content-container">
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <a href="ver_mis_datos.php">Mi Cuenta</a> <span>➤</span> Historial de Servicios
            </div>

            <div class="bread">
                <h2>Historial de Servicios</h2>
            </div>
            <!-- Tabla del historial de servicios -->
<table>
    <thead>
        <tr>
            <th colspan="4">

                <!-- Formulario de búsqueda -->
           <input type="text" id="busqueda" name="busqueda" placeholder="Buscar por nombre de mascota" value="<?php echo htmlspecialchars($busqueda); ?>">

</form>
            </th>
        </tr>
        <table id="resultados">
    <thead>
        <tr>
            <th>Nombre Mascota</th>
            <th>Servicio</th>
            <th>Fecha</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($resultado->num_rows > 0): ?>
            <?php while ($row = $resultado->fetch_assoc()): ?>
                <tr>
                            <td><?php echo htmlspecialchars($row['nombre_mascota']); ?></td>
                            <td><?php echo htmlspecialchars($row['nombre_servicio']); ?></td>
                            <td><?php echo htmlspecialchars($row['fecha_inicio']); ?></td>
                            <td><?php echo htmlspecialchars($row['progreso']); ?></td>
                        </tr>

            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">No se encontraron servicios.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
        </div>
    </main>

    <?php
    include 'views/footer.php';
    ?>
    <script>
    document.getElementById('busqueda').addEventListener('input', function() {
    var busqueda = this.value;

    // Crear un objeto XMLHttpRequest
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'buscar_servicios.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

    // Manejar la respuesta
    xhr.onload = function() {
        if (xhr.status === 200) {
            // Actualizar la tabla con los resultados recibidos
            document.querySelector('#resultados tbody').innerHTML = xhr.responseText;
        }
    };

    // Enviar la solicitud con el término de búsqueda
    xhr.send('busqueda=' + encodeURIComponent(busqueda));
});

</script>

</body>
</html>