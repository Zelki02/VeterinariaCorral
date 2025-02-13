<?php
require 'includes/db.php'; // Conexión a la base de datos

// Obtener la configuración de la alarma de caducidad
$query_config = "SELECT alarma_caducidad, limite_caducidad FROM configuracion WHERE id = 2";
$result_config = $db->query($query_config);
$config = $result_config->fetch_assoc();

$alarmaCaducidad = $config['alarma_caducidad']; // Ejemplo: "1_mes_despues"
$limiteCaducidad= $config['limite_caducidad'];

// Extraer la cantidad, periodo y temporalidad
list($cantidad, $periodo, $temporalidad) = explode('_', $alarmaCaducidad);
$cantidad = (int) $cantidad; // Convertir a número

list($limite_cantidad, $limite_periodo) = explode('_', $limiteCaducidad);
$cantidad = (int) $cantidad; // Convertir a número

// Obtener la fecha actual
$fechaHoy = new DateTime();

// Consulta de productos con fecha de caducidad
$sql = "
    SELECT 
        c.fecha_caducidad, 
        p.nombre_producto, 
        c.lote_producto
    FROM 
        caducidades_productos c
    JOIN 
        productos p 
    ON 
        c.id_articulo = p.id_producto
";

$resultado = $db->query($sql);

$productosCaducados = [];

while ($fila = $resultado->fetch_assoc()) {
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

// Devolver la respuesta en JSON
if (!empty($productosCaducados)) {
    echo json_encode(['caducados' => true, 'productos' => $productosCaducados]);
} else {
    echo json_encode(['caducados' => false]);
}
?>
