<?php
include 'views/header.php';
include 'includes/db.php'; // Conexión a la base de datos

// Obtener la configuración de la alarma de caducidad
$query_config = "SELECT alarma_caducidad, limite_caducidad FROM configuracion WHERE id = 2";
$result_config = $db->query($query_config);
$config = $result_config->fetch_assoc();

$alarmaCaducidad = $config['alarma_caducidad']; // Ejemplo: "1_mes_despues"
$limiteCaducidad= $config['limite_caducidad'];

// Extraer cantidad, periodo y temporalidad
list($cantidad, $periodo, $temporalidad) = explode('_', $alarmaCaducidad);
$cantidad = (int) $cantidad; // Convertir a número

list($limite_cantidad, $limite_periodo) = explode('_', $limiteCaducidad);
$cantidad = (int) $cantidad; // Convertir a número

// Obtener la fecha actual
$fechaHoy = new DateTime();

// Consulta de productos con fecha de caducidad
$sql = "
    SELECT c.fecha_caducidad, p.nombre_producto, c.lote_producto
    FROM caducidades_productos c
    JOIN productos p ON c.id_articulo = p.id_producto
";
$result = $db->query($sql);

$productosCaducados = [];

while ($fila = $result->fetch_assoc()) {
    $fechaCaducidad = new DateTime($fila['fecha_caducidad']);
    
    // Calcular la fecha límite A PARTIR de la fecha de caducidad
    $fechaLimite = clone $fechaCaducidad;
    
    if ($temporalidad === 'despues') {

        if ($periodo === 'dia') {
            $fechaLimite->modify("+$cantidad days");
        } elseif ($periodo === 'semana') { // 👈 Nueva opción para semanas
            $fechaLimite->modify("+".($cantidad * 7)." days");
        } elseif ($periodo === 'mes') {
            $fechaLimite->modify("+$cantidad months");
        } elseif ($periodo === 'año') {
            $fechaLimite->modify("+$cantidad years");
        }
}else if($temporalidad === 'antes'){

    if ($periodo === 'dia') {
        $fechaLimite->modify("-$cantidad days");
    }  elseif ($periodo === 'semana') { // 👈 Nueva opción para semanas
        $fechaLimite->modify("-".($cantidad * 7)." days");
    } elseif ($periodo === 'mes') {
        $fechaLimite->modify("-$cantidad months");
    } elseif ($periodo === 'año') {
        $fechaLimite->modify("-$cantidad years");
    }
}

$fechaMaxima = clone $fechaLimite;

if ($limite_periodo === 'días') {
    $fechaMaxima->modify("+$limite_cantidad days");
} elseif ($limite_periodo === 'semanas') {
    $fechaMaxima->modify("+".($limite_cantidad * 7)." days");
} elseif ($limite_periodo === 'meses') {
    $fechaMaxima->modify("+$limite_cantidad months");
}

// Comparar si la fecha actual está dentro del rango de fechaLimite y fechaMaxima
if ($fechaHoy >= $fechaLimite && $fechaHoy <= $fechaMaxima) {
    $productosCaducados[] = $fila;
}
}

// Paginación
$productos_por_pagina = 15;
$total_productos = count($productosCaducados);
$total_paginas = ceil($total_productos / $productos_por_pagina);
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $productos_por_pagina;

// Obtener los productos para la página actual
$productosPagina = array_slice($productosCaducados, $offset, $productos_por_pagina);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos con Alarma de Caducidad</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        h1 { margin: 0; }
        main { padding: 20px; }
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
        tr:hover { background-color: #f1f1f1; }
        td { background-color: #ffffff; }
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
        .button:hover { background-color: #0056b3; }
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
        .pagination a:hover { background-color: #45a049; }
        .pagination .active { background-color: rgb(169, 124, 79); }
    </style>
</head>
<body>
    <main>
        <div class="table-container">
            <?php if (!empty($productosPagina)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Fecha de Caducidad</th>
                            <th>Lote</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productosPagina as $producto): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($producto['nombre_producto']); ?></td>
                                <td><?php echo htmlspecialchars($producto['fecha_caducidad']); ?></td>
                                <td><?php echo htmlspecialchars($producto['lote_producto']); ?></td>
                            </tr>
                        <?php endforeach; ?>
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
                <p class="no-data">No hay productos en el rango de la alarma de caducidad.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
<?php
include 'views/footer.php';
$db->close();
?>
