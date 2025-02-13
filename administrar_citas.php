<?php
// Incluye la conexión a la base de datos
include 'includes/db.php';
include 'views/header.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php");
    exit();
}
// Variables para manejo de filtros
$fecha_seleccionada = isset($_POST['fecha_cita']) ? $_POST['fecha_cita'] : date('Y-m-d');

// Consulta para obtener citas filtradas por fecha
$query_citas = "SELECT c.id_cita, c.fecha_cita, s.nombre_servicio, c.estatus, m.nombre_mascota, u.nombre AS nombre_usuario
                FROM citas c
                LEFT JOIN servicios s ON c.id_servicio = s.id_servicio
                LEFT JOIN mascotas m ON c.id_mascota = m.id_mascota
                LEFT JOIN usuarios u ON c.id_usuario = u.id_usuario
                WHERE DATE(c.fecha_cita) = '$fecha_seleccionada'
                ORDER BY c.fecha_cita DESC";


$resultado = mysqli_query($db, $query_citas);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Citas</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #555;
        }
        form {
            text-align: center;
            margin-bottom: 20px;
        }
        input[type="date"] {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            text-align: center;
            padding: 10px;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
            color: #555;
        }
        a {
            text-decoration: none;
            color: #007bff;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Historial de Citas</h1>
        <form method="POST" action="administrar_citas.php">
            <label for="fecha_cita">Seleccionar fecha:</label>
            <input type="date" id="fecha_cita" name="fecha_cita" value="<?php echo $fecha_seleccionada; ?>">
            <button type="submit">Filtrar</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID Cita</th>
                    <th>Fecha</th>
                    <th>Servicio</th>
                    <th>Estatus</th>
                    <th>Mascota</th>
                    <th>Usuario</th>
                    <th>Detalles</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($resultado) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($resultado)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id_cita']); ?></td>
                            <td><?php echo htmlspecialchars($row['fecha_cita']); ?></td>
                            <td><?php echo htmlspecialchars($row['nombre_servicio']); ?></td>
                            <td>
                                <form method="POST" action="actualizar_estatus.php">
                                    <select name="nuevo_estatus" class="status-select">
                                        <option value="pendiente" <?php echo ($row['estatus'] == 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                        <option value="en_proceso" <?php echo ($row['estatus'] == 'en_proceso') ? 'selected' : ''; ?>>En Proceso</option>
                                        <option value="completada" <?php echo ($row['estatus'] == 'completada') ? 'selected' : ''; ?>>Completada</option>
                                        <option value="cancelada" <?php echo ($row['estatus'] == 'cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                                    </select>
                            </td>
                            <td><?php echo htmlspecialchars($row['nombre_mascota']); ?></td>
                            <td><?php echo htmlspecialchars($row['nombre_usuario']); ?></td>
                            <td>
                                <input type="hidden" name="id_cita" value="<?php echo $row['id_cita']; ?>">
                                <button type="submit" class="update-btn">Actualizar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No se encontraron citas para la fecha seleccionada.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
// Cierra la conexión y incluye el pie de página
include 'views/footer.php';
mysqli_free_result($resultado);
mysqli_close($db);
?>