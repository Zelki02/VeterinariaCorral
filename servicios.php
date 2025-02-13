<?php
include 'views/header.php';
include 'includes/db.php'; // Conexión a la base de datos

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: login.php");
    exit();
}

// Manejar la inserción de un nuevo servicio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre_servicio'])) {
    $nombre_servicio = trim($_POST['nombre_servicio']);
    $descripcion = trim($_POST['descripcion']);
    
    if (!empty($nombre_servicio)) {
        $sql = "INSERT INTO servicios (nombre_servicio, descripcion) VALUES (?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("ss", $nombre_servicio, $descripcion);
        if ($stmt->execute()) {
            $mensaje = "Servicio agregado exitosamente.";
        } else {
            $mensaje = "Error al agregar el servicio: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $mensaje = "El nombre del servicio es obligatorio.";
    }
}

// Manejar la eliminación de un servicio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_servicio'])) {
    $id_servicio = intval($_POST['eliminar_servicio']);
    $sql = "DELETE FROM servicios WHERE id_servicio = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $id_servicio);
    if ($stmt->execute()) {
        $mensaje = "Servicio eliminado exitosamente.";
    } else {
        $mensaje = "Error al eliminar el servicio: " . $stmt->error;
    }
    $stmt->close();
}

// Obtener todos los servicios existentes
$sql = "SELECT id_servicio, nombre_servicio, descripcion FROM servicios";
$resultado = $db->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/estilos.css">
    <title>Administrar Servicios</title>
    <style>
        body { font-family: Arial, sans-serif; }
        h1 { text-align: center; margin-top: 20px; }
        .form-container { width: 50%; margin: auto; padding: 20px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 5px; margin-top: 20px; }
        .form-container label { display: block; margin-bottom: 10px; font-weight: bold; }
        .form-container input, .form-container textarea { width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 3px; }
        .form-container button { background-color: #a67c52; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer; }
        .form-container button:hover { background-color: #45a049; }
        table { width: 80%; margin: auto; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 12px; text-align: left; }
        th { background-color: #d9a066; color: white; }
        tr:hover { background-color: #f2f2f2; }
        .mensaje { text-align: center; font-size: 16px; color: green; margin-top: 10px; }
        .eliminar-form { margin: 0; padding: 0; }
        .eliminar-form button { background-color: #e74c3c; color: white; padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer; }
        .eliminar-form button:hover { background-color: #c0392b; }
    </style>
</head>
<body>

<h1>Administrar Servicios</h1>

<?php if (isset($mensaje)): ?>
    <p class="mensaje"><?php echo htmlspecialchars($mensaje); ?></p>
<?php endif; ?>

<div class="form-container">
    <form action="servicios.php" method="POST">
        <label for="nombre_servicio">Nombre del Servicio:</label>
        <input type="text" id="nombre_servicio" name="nombre_servicio" required>
        
        <label for="descripcion">Descripción del Servicio:</label>
        <textarea id="descripcion" name="descripcion" rows="4"></textarea>
        
        <button type="submit">Agregar Servicio</button>
    </form>
</div>

<table>
    <thead>
        <tr>
            <th>ID Servicio</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($resultado->num_rows > 0): ?>
            <?php while ($row = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id_servicio']); ?></td>
                    <td><?php echo htmlspecialchars($row['nombre_servicio']); ?></td>
                    <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
                    <td>
                        <form class="eliminar-form" action="servicios.php" method="POST">
                            <input type="hidden" name="eliminar_servicio" value="<?php echo $row['id_servicio']; ?>">
                            <button type="submit">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">No se encontraron servicios.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>

<?php
$resultado->free();
$db->close();
include 'views/footer.php';
?>
