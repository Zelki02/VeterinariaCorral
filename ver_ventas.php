<?php
// Incluye la conexión a la base de datos
include 'includes/db.php';
include 'views/header.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php");
    exit();
}
// Variables para manejo de filtros
$fecha_seleccionada = isset($_POST['fecha_venta']) ? $_POST['fecha_venta'] : date('Y-m-d');

// Consulta para obtener ventas filtradas por fecha
$query_ventas = "SELECT v.id_venta, v.fecha_venta, v.total, u.nombre AS cliente, v.id_vendedor 
                 FROM ventas v
                 LEFT JOIN usuarios u ON v.id_cliente = u.id_usuario
                 WHERE DATE(v.fecha_venta) = '$fecha_seleccionada'
                 ORDER BY v.fecha_venta DESC";

$resultado = mysqli_query($db, $query_ventas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Ventas</title>
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
            background-color: #d9a066;
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
        <h1>Historial de Ventas</h1>
        <form method="POST" action="ver_ventas.php">
            <label for="fecha_venta">Seleccionar fecha:</label>
            <input type="date" id="fecha_venta" name="fecha_venta" value="<?php echo $fecha_seleccionada; ?>">
            <button type="submit">Filtrar</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID Venta</th>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>Total</th>
                    <th>Detalles</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($resultado) > 0): ?>
                    <?php while ($venta = mysqli_fetch_assoc($resultado)): ?>
                        <tr>
                            <td><?php echo $venta['id_venta']; ?></td>
                            <td><?php echo $venta['fecha_venta']; ?></td>
                            <td><?php echo $venta['cliente'] ?: 'No registrado'; ?></td>
                            <td><?php echo number_format($venta['total'], 2); ?> MXN</td>
                            <td><a href="detalles_venta.php?id_venta=<?php echo $venta['id_venta']; ?>">Ver Detalles</a></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No se encontraron ventas para la fecha seleccionada.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<?php
include 'views/footer.php';
?>