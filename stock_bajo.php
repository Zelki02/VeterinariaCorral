<?php
include 'views/header.php';
include 'includes/db.php'; // Conexión a la base de datos

$query_config = "SELECT stock_minimo FROM configuracion LIMIT 1";
$result_config = $db->query($query_config);

if ($result_config && $result_config->num_rows > 0) {
    $row_config = $result_config->fetch_assoc();
    $stock_minimo = $row_config['stock_minimo'];
}

// Umbral para considerar que el inventario está bajo
$umbral_stock = $stock_minimo;

// Número de productos por página
$productos_por_pagina = 15;

// Obtener el número de la página actual, o 1 si no está definida
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $productos_por_pagina;

// Consulta para obtener el total de productos con stock bajo
$sql_total = "SELECT COUNT(*) FROM productos WHERE cantidad_stock = ?";
$stmt_total = $db->prepare($sql_total);
$stmt_total->bind_param('i', $umbral_stock);
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$total_productos = $result_total->fetch_row()[0];
$stmt_total->close();

// Calcular el número total de páginas
$total_paginas = ceil($total_productos / $productos_por_pagina);

// Consulta para obtener los productos con stock bajo para la página actual
$sql = "SELECT nombre_producto, cantidad_stock, marca FROM productos WHERE cantidad_stock = ? LIMIT ?, ?";
$stmt = $db->prepare($sql);
$stmt->bind_param('iii', $umbral_stock, $offset, $productos_por_pagina);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos con Bajo Inventario</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        h1 {
            margin: 0;
        }

        main {
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #d9a066;
            color: white;
            text-transform: uppercase;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        td {
            background-color: #ffffff;
        }

        .no-data {
            font-size: 18px;
            color: #555;
            text-align: center;
            padding: 20px;
        }

        .table-container {
            max-width: 1000px;
            margin: 0 auto;
            overflow-x: auto;
        }

        /* Botón de acción */
        .button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            text-align: center;
            display: inline-block;
            margin: 20px 0;
            cursor: pointer;
        }

        .button:hover {
            background-color: #007bff;
        }

        /* Paginación */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .pagination a {
            margin: 0 5px;
            padding: 10px 15px;
            background-color: #d9a066;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .pagination a:hover {
            background-color: #45a049;
        }

        .pagination .active {
            background-color:rgb(169, 124, 79);
        }
    </style>
</head>
<body>
    <main>
        <div class="table-container">
            <?php if ($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Stock</th>
                            <th>Marca</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nombre_producto']); ?></td>
                                <td><?php echo $row['cantidad_stock']; ?></td>
                                <td><?php echo htmlspecialchars($row['marca']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <a href="?pagina=<?php echo $i; ?>" class="<?php echo $i == $pagina_actual ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
                <a href="inventario.php" class="button">Volver al Inventario</a>
            <?php else: ?>
                <p class="no-data">No hay productos con bajo inventario.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
<?php
include "views/footer.php";
$stmt->close();
$db->close();
?>