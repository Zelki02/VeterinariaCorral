<?php
// Incluye la conexión a la base de datos
include 'includes/db.php';

if (isset($_GET['busqueda'])) {
    $busqueda = mysqli_real_escape_string($db, $_GET['busqueda']);
    $origen = isset($_GET['origen']) ? $_GET['origen'] : '';

// Número de productos por página
if ($origen === 'punto_venta'){
    $productos_por_pagina = 10;
}else{
    $productos_por_pagina = 100;
}

// Obtener la página actual, por defecto será 1 si no se especifica
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$inicio = ($pagina_actual > 1) ? ($pagina_actual - 1) * $productos_por_pagina : 0;

    // Contar el total de productos que coinciden con la búsqueda
    $query_total = "
        SELECT COUNT(*) as total 
        FROM productos 
        WHERE nombre_producto LIKE '%$busqueda%' 
        OR id_producto LIKE '%$busqueda%'
    ";
    $result_total = mysqli_query($db, $query_total);
    $row_total = mysqli_fetch_assoc($result_total);
    $total_productos = $row_total['total'];

    // Calcular el número total de páginas
    $total_paginas = ceil($total_productos / $productos_por_pagina);

    // Consulta ajustada según el origen
    if ($origen === 'punto_venta') {
        $query = "
            SELECT id_producto, marca, nombre_producto, formato, precio 
            FROM productos 
            WHERE nombre_producto LIKE '%$busqueda%' 
            OR id_producto LIKE '%$busqueda%' 
            LIMIT $inicio, $productos_por_pagina
        ";
    } else {
        // Para inventario, seleccionar todos los campos
        $query = "
            SELECT * 
            FROM productos 
            WHERE nombre_producto LIKE '%$busqueda%' 
            OR id_producto LIKE '%$busqueda%' 
            LIMIT $inicio, $productos_por_pagina
        ";
    }

    $result = mysqli_query($db, $query);

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            if ($origen === 'punto_venta') {
                // Solo mostrar ciertos campos para "punto_venta"
                echo '<tr data-id="' . $row['id_producto'] . '">';
                echo '<td>' . $row['id_producto'] . '</td>';
                echo '<td>' . $row['marca'] . '</td>';
                echo '<td>' . $row['nombre_producto'] . '</td>';
                echo '<td>' . $row['formato'] . '</td>';
                echo '<td>' . $row['precio'] . '</td>';
            } else {
                // Mostrar todos los campos y botones de editar/eliminar para inventario
                echo '<tr data-id="' . $row['id_producto'] . '">';
                echo '<td>' . $row['id_producto'] . '</td>';
                echo '<td>' . $row['categoria'] . '</td>';
                echo '<td>' . $row['marca'] . '</td>';
                echo '<td>' . $row['nombre_producto'] . '</td>';
                echo '<td>' . $row['formato'] . '</td>';
                echo '<td>' . $row['precio'] . '</td>';
                echo '<td>' . $row['descripcion'] . '</td>';
                echo '<td>' . $row['iva'] . '</td>';
                echo '<td>' . $row['estatus'] . '</td>';
                echo '<td>' . $row['cantidad_stock'] . '</td>';
                echo '<td>';  // Asegúrate de que los botones están dentro de un <td>
                echo '<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editarModal" data-id="' . $row['id_producto'] . '">Editar</button>';
                echo ' <a href="inventario.php?eliminar=' . $row['id_producto'] . '" class="btn btn-danger">Eliminar</a>';
                echo '</td>';  // Cerrar el <td> correctamente
            }            
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='11'>No se encontraron productos</td></tr>";
    }

    // Paginación
    echo '<nav>';
    echo '<ul>';
    
    // Botón anterior
    if ($pagina_actual > 1) {
        echo '<li><a href="buscar_productos.php?pagina=' . ($pagina_actual - 1) . '&busqueda=' . urlencode($busqueda) . '">⬅</a></li>';
    }

    // Definir el rango de páginas visibles
    $rango = 3;
    $inicio_pagina = max(1, $pagina_actual - $rango);
    $fin_pagina = min($total_paginas, $pagina_actual + $rango);

    // Mostrar "1 ..." si no estamos cerca de la primera página
    if ($inicio_pagina > 1) {
        echo '<li><a href="buscar_productos.php?pagina=1&busqueda=' . urlencode($busqueda) . '">1</a></li>';
        if ($inicio_pagina > 2) {
            echo '<li>...</li>';
        }
    }

    // Mostrar "... última página" si no estamos cerca de la última página
    if ($fin_pagina < $total_paginas) {
        if ($fin_pagina < $total_paginas - 1) {
            echo '<li>...</li>';
        }
        echo '<li><a href="buscar_productos.php?pagina=' . $total_paginas . '&busqueda=' . urlencode($busqueda) . '">' . $total_paginas . '</a></li>';
    }

    // Botón siguiente
    if ($pagina_actual < $total_paginas) {
        echo '<li><a href="buscar_productos.php?pagina=' . ($pagina_actual + 1) . '&busqueda=' . urlencode($busqueda) . '">⮕</a></li>';
    }

    echo '</ul>';
    echo '</nav>';
}
?>
