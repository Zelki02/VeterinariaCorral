<?php
include 'views/header.php';
include 'includes/db.php'; // Conexión a la base de datos
require_once('vendor/autoload.php'); // Asegúrate de incluir el autoload de Composer

$pdf = new \TCPDF();

// Comprobar si el usuario ha iniciado sesión y si es administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php");
    exit();
}

// Variables para mensajes
$mensaje_error = '';
$mensaje_exito = '';

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener las fechas del formulario
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $fecha_fin = $_POST['fecha_fin'] ?? '';

    if (empty($fecha_inicio) || empty($fecha_fin)) {
        $mensaje_error = 'Por favor, seleccione ambas fechas.';
    } elseif ($fecha_inicio > $fecha_fin) {
        $mensaje_error = 'La fecha de inicio no puede ser mayor que la fecha final.';
    } else {
        // Consultar las facturas en el rango de fechas
        $sql = "SELECT 
        ventas.fecha_venta,
        productos.nombre_producto AS producto,
        detalle_venta.cantidad,
        detalle_venta.precio AS precio_unitario,
        detalle_venta.subtotal,
        ventas.total AS total_venta
        FROM 
        ventas
        INNER JOIN 
        detalle_venta ON ventas.id_venta = detalle_venta.id_venta
        INNER JOIN 
        productos ON detalle_venta.id_producto = productos.id_producto
        WHERE 
        DATE(ventas.fecha_venta) BETWEEN ? AND ?
        ORDER BY 
        ventas.fecha_venta DESC;";

        $stmt = $db->prepare($sql);
        $stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Crear el objeto TCPDF
            $pdf = new TCPDF();
            $pdf->AddPage();
            $pdf->SetFont('helvetica', '', 12);

            // Título
            $pdf->Cell(0, 10, 'Facturas entre ' . $fecha_inicio . ' y ' . $fecha_fin, 0, 1, 'C');
            $pdf->Ln(5);

            // Encabezado de la tabla
            $pdf->Cell(40, 10, 'Fecha Venta', 1);
            $pdf->Cell(40, 10, 'Producto', 1);
            $pdf->Cell(40, 10, 'Cantidad', 1);
            $pdf->Cell(40, 10, 'Precio Unitario', 1);
            $pdf->Cell(40, 10, 'Subtotal', 1);
            $pdf->Cell(40, 10, 'Total Venta', 1);
            $pdf->Ln();

            // Mostrar los resultados en la tabla
            while ($row = $result->fetch_assoc()) {
                $pdf->Cell(40, 10, $row['fecha_venta'], 1);
                $pdf->Cell(40, 10, $row['producto'], 1);
                $pdf->Cell(40, 10, $row['cantidad'], 1);
                $pdf->Cell(40, 10, '$' . number_format($row['precio_unitario'], 2), 1);
                $pdf->Cell(40, 10, '$' . number_format($row['subtotal'], 2), 1);
                $pdf->Cell(40, 10, '$' . number_format($row['total_venta'], 2), 1);
                $pdf->Ln();
            }

            // Guardar el archivo PDF
            $nombre_pdf = 'facturas_' . date('Ymd_His') . '.pdf';
            $pdf->Output(__DIR__ . '/facturas/' . $nombre_pdf, 'F'); // Guarda en una carpeta 'facturas' en tu servidor

            // Mostrar mensaje de éxito
            $mensaje_exito = "Facturas generadas correctamente. <a href='/Veterinaria/facturas/{$nombre_pdf}' target='_blank'>Ver PDF</a>.";
        } else {
            $mensaje_error = 'No se encontraron facturas en el rango de fechas seleccionado.';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Facturas</title>
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

        .form-container {
            display: flex;
            flex-direction: column;
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        input[type="password"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 25px;
            width: 100%;
            outline: none;
            transition: border-color 0.3s ease;
        }

        input[type="password"]:focus {
            border-color: #1abc9c;
        }

        input[type="submit"] {
            padding: 10px 15px;
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

        .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .success-message {
            color: green;
            font-size: 14px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<main>
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
                <a href="ver_mis_datos.php">Mi Cuenta</a> <span>➤</span> Administrar Facturas
            </div>

    <div class="content-container">
        <h2>Administrar Facturas</h2>

        <?php if ($mensaje_error): ?>
            <div class="error-message"><?php echo $mensaje_error; ?></div>
        <?php elseif ($mensaje_exito): ?>
            <div class="success-message"><?php echo $mensaje_exito; ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="form-container">
            <label for="fecha_inicio">Fecha de Inicio:</label>
            <input type="date" id="fecha_inicio" name="fecha_inicio" required>
            <br><br>

            <label for="fecha_fin">Fecha Final:</label>
            <input type="date" id="fecha_fin" name="fecha_fin" required>
            <br><br>

            <input type="submit" value="Generar Facturas en PDF">
        </form>
    </div>
</main>
<?php
    $db->close();
    include 'views/footer.php';
    ?>
</body>
</html>
