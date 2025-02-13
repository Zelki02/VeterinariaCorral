<?php
// Incluye la conexión a la base de datos
include 'includes/db.php';

// Obtiene el ID de la venta desde el parámetro GET de forma segura
$id_venta = isset($_GET['id_venta']) ? (int)$_GET['id_venta'] : 0;
$id_venta = mysqli_real_escape_string($db, $id_venta);  // Protección contra SQL Injection

// Consulta para obtener los detalles de la venta
$query_detalle = "SELECT dv.id_detalle_venta, 
                          p.nombre_producto AS producto, 
                          dv.id_producto,
                          dv.cantidad, 
                          dv.precio, 
                          dv.subtotal
                  FROM detalle_venta dv
                  LEFT JOIN productos p ON dv.id_producto = p.id_producto
                  WHERE dv.id_venta = $id_venta";


$resultado = mysqli_query($db, $query_detalle);

// Verifica si la consulta fue exitosa
if (!$resultado) {
    die('Error en la consulta: ' . mysqli_error($db));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de Venta</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #d9a066;
            color: white;
        }
        table tr:hover {
            background-color: #f1f1f1;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #d9a066;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        a:hover {
            background-color: #d9a066;
        }
        .no-details {
            text-align: center;
            font-size: 16px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Detalles de la Venta #<?php echo $id_venta; ?></h1>
        <table>
            <thead>
                <tr>
                    <th>Producto/Servicio</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($resultado) > 0): ?>
                    <?php while ($detalle = mysqli_fetch_assoc($resultado)): ?>
                        <tr>
                            <td>
                                <?php 
                                echo htmlspecialchars($detalle['producto']);
                                ?>
                            </td>
                            <td><?php echo $detalle['cantidad']; ?></td>
                            <td><?php echo number_format($detalle['precio'], 2); ?> MXN</td>
                            <td><?php echo number_format($detalle['subtotal'], 2); ?> MXN</td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr class="no-details">
                        <td colspan="4">No se encontraron detalles para esta venta.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <a href="ver_ventas.php">Regresar</a>
    </div>
</body>
</html>
