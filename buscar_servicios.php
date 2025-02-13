<?php
session_start();
include 'includes/db.php';

$busqueda = isset($_POST['busqueda']) ? $_POST['busqueda'] : '';
$origen = isset($_POST['origen']) ? $_POST['origen'] : '';

// Verificar el origen de la solicitud
if ($origen === 'administrar_citas') {
    // Buscar citas desde la página administrar_citas.php
    $sql = "SELECT 
                citas.id_cita,
                citas.fecha_cita,
                servicios.nombre_servicio,
                citas.estatus,
                mascotas.nombre_mascota,
                usuarios.nombre AS nombre_usuario
            FROM 
                citas
            JOIN 
                servicios ON citas.id_servicio = servicios.id_servicio
            JOIN 
                mascotas ON citas.id_mascota = mascotas.id_mascota
            JOIN 
                usuarios ON citas.id_usuario = usuarios.id_usuario
            WHERE 
                mascotas.nombre_mascota LIKE ?"; // Solo se busca por nombre de mascota

    // Preparar y ejecutar la consulta
    $stmt = $db->prepare($sql);
    $searchParam = '%' . $busqueda . '%';
    $stmt->bind_param("s", $searchParam);
    $stmt->execute();
    $resultado = $stmt->get_result();

    // Generar las filas de la tabla para administrar citas
    if ($resultado->num_rows > 0) {
        while ($row = $resultado->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['id_cita']) . '</td>';
            echo '<td>' . htmlspecialchars($row['fecha_cita']) . '</td>';
            echo '<td>' . htmlspecialchars($row['nombre_servicio']) . '</td>';
            echo '<td>
                    <form method="POST" action="actualizar_estatus.php">
                        <select name="nuevo_estatus" class="status-select">
                            <option value="pendiente"' . ($row['estatus'] == 'pendiente' ? ' selected' : '') . '>Pendiente</option>
                            <option value="completada"' . ($row['estatus'] == 'completada' ? ' selected' : '') . '>Completada</option>
                            <option value="cancelada"' . ($row['estatus'] == 'cancelada' ? ' selected' : '') . '>Cancelada</option>
                        </select>
                  </td>';
            echo '<td>' . htmlspecialchars($row['nombre_mascota']) . '</td>';
            echo '<td>' . htmlspecialchars($row['nombre_usuario']) . '</td>';
            echo '<td>
                    <input type="hidden" name="id_cita" value="' . $row['id_cita'] . '">
                    <button type="submit" class="update-btn">Actualizar</button>
                  </form>
                  </td>';
            echo '</tr>';
        }
    }
     else {
        echo '<tr><td colspan="7">No se encontraron citas.</td></tr>';
    }
} else {
    // Buscar servicios desde la vista actual de servicios para usuarios
    $sql = "SELECT 
                mascotas.nombre_mascota,
                servicios.nombre_servicio,
                servicios_mascotas.fecha_inicio AS fecha_inicio,
                servicios_mascotas.progreso AS progreso,
                servicios_mascotas.estado_servicio AS estado
            FROM 
                mascotas
            JOIN 
                servicios_mascotas ON mascotas.id_mascota = servicios_mascotas.id_mascota
            JOIN 
                servicios ON servicios.id_servicio = servicios_mascotas.id_servicio
            WHERE 
                mascotas.id_usuario = ?";

    if (!empty($busqueda)) {
        $sql .= " AND mascotas.nombre_mascota LIKE ?";
    }

    $stmt = $db->prepare($sql);

    if (!empty($busqueda)) {
        $searchParam = '%' . $busqueda . '%';
        $stmt->bind_param("is", $_SESSION['usuario_id'], $searchParam);
    } else {
        $stmt->bind_param("i", $_SESSION['usuario_id']);
    }

    $stmt->execute();
    $resultado = $stmt->get_result();

    // Generar las filas de la tabla para la búsqueda de servicios
    if ($resultado->num_rows > 0) {
        while ($row = $resultado->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['nombre_mascota']) . '</td>';
            echo '<td>' . htmlspecialchars($row['nombre_servicio']) . '</td>';
            echo '<td>' . htmlspecialchars($row['fecha_inicio']) . '</td>';
            echo '<td>' . htmlspecialchars($row['progreso']) . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="4">No se encontraron servicios.</td></tr>';
    }
}

$stmt->close();
$db->close();
?>
