<?php
include 'views/header.php';
include 'includes/db.php';

if (isset($_SESSION['rol']) && ($_SESSION['rol'] === 'administrador' || $_SESSION['rol'] === 'vendedor')) {
    $query = "SELECT * FROM notificaciones WHERE visto = 0 ORDER BY fecha DESC";
    $result = $db->query($query);
    
    // Marcar todas las notificaciones como vistas
    $db->query("UPDATE notificaciones SET visto = 1 WHERE visto = 0");
} else {
    echo "Acceso no autorizado";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notificaciones</title>
    <link rel="stylesheet" href="css/estilos.css"> <!-- Vincula el archivo CSS -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .notificaciones-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .notificacion {
            border-bottom: 1px solid #eee;
            padding: 10px;
        }
        .notificacion:last-child {
            border-bottom: none;
        }
        .fecha {
            font-size: 0.8em;
            color: #777;
        }
        @media (max-width: 600px) {
            .notificaciones-container {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="notificaciones-container">
        <h1>Notificaciones</h1>
        <?php if ($result->num_rows > 0): ?>
            <ul>
                <?php while ($notificacion = $result->fetch_assoc()): ?>
                    <li class="notificacion">
                        <strong><?php echo htmlspecialchars($notificacion['mensaje']); ?></strong>
                        <div class="fecha"><?php echo htmlspecialchars($notificacion['fecha']); ?></div>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No hay notificaciones nuevas.</p>
        <?php endif; ?>
    </div>
</body>
</html>
