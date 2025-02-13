<?php
include 'views/header.php';
include 'includes/db.php'; // Conexión a la base de datos

// Comprobar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Consultar las mascotas del usuario
$sql = "SELECT id_mascota, nombre_mascota, especie, raza, edad, peso, sexo FROM mascotas WHERE id_usuario = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Mascotas</title>
    <link rel="stylesheet" href="css/estilos.css"> <!-- Asegúrate de que la ruta es correcta -->
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
                <a href="ver_mis_datos.php">Mi Cuenta</a> <span>➤</span> Ver mis Mascotas
            </div>

            <div class="bread">
                <h2>Información de mis Mascotas</h2>
            </div>

            <!-- Tabla de mascotas -->
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Especie</th>
                        <th>Raza</th>
                        <th>Edad</th>
                        <th>Peso</th>
                        <th>Sexo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado->num_rows > 0): ?>
                        <?php while ($row = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['nombre_mascota']; ?></td>
                                <td><?php echo $row['especie']; ?></td>
                                <td><?php echo $row['raza']; ?></td>
                                <td><?php echo $row['edad']; ?> años</td>
                                <td><?php echo $row['peso']; ?> Kilos</td>
                                <td><?php echo $row['sexo']; ?></td>
                                <td>
                                    <form action="eliminar_mascota.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="id_mascota" value="<?php echo $row['id_mascota']; ?>">
                                        <input type="submit" value="Eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar esta mascota?');">
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No tienes mascotas registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <?php
    $stmt->close();
    $db->close();
    include 'views/footer.php';
    ?>
</body>
</html>
