<?php
session_start();
require_once 'includes/db.php';

header('Content-Type: application/json');

if (isset($_SESSION['usuario_id']) && ($_SESSION['rol'] === 'administrador' || $_SESSION['rol'] === 'vendedor')) {
    $query_config = "SELECT stock_minimo FROM configuracion LIMIT 1";
$result_config = $db->query($query_config);

if ($result_config && $result_config->num_rows > 0) {
    $row_config = $result_config->fetch_assoc();
    $stock_minimo = $row_config['stock_minimo'];
}

    $query = "SELECT nombre_producto FROM productos WHERE cantidad_stock = $stock_minimo";
    $result = $db->query($query);

    $productos = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row['nombre_producto'];
        }
    }

    echo json_encode(['productos' => $productos]);
} else {
    echo json_encode(['error' => 'No autorizado']);
}
?> 