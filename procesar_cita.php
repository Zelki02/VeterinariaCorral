<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
include 'includes/db.php'; // Conexión a la base de datos

$usuario_id = $_SESSION['usuario_id'];

// Obtener el rol del usuario
$sql_rol = "SELECT rol FROM usuarios WHERE id_usuario = ?";
$stmt_rol = $db->prepare($sql_rol);
$stmt_rol->bind_param("i", $usuario_id);
$stmt_rol->execute();
$result_rol = $stmt_rol->get_result();
$row_rol = $result_rol->fetch_assoc();
$rol_usuario = $row_rol['rol']; // Asumiendo que el campo se llama 'rol'

// Obtener datos del formulario
$nombre_cliente = $_POST['nombre_cliente'] ?? null; // Para un nuevo cliente
$telefono_cliente = $_POST['telefono_cliente'] ?? null; // Para un nuevo cliente
$nombre_mascota = $_POST['nombre_mascota'] ?? null; // Para la nueva mascota
$fecha_cita = $_POST['fecha_cita']; // La fecha seleccionada en el formulario
$servicio_id = $_POST['servicio']; // ID del servicio seleccionado

// Comprobar si es un nuevo cliente y registrar
if ($nombre_cliente && $telefono_cliente) {
    // Registrar al cliente
    $sql_usuario = "INSERT INTO usuarios (nombre, telefono) VALUES (?, ?)";
    $stmt_usuario = $db->prepare($sql_usuario);
    $stmt_usuario->bind_param("ss", $nombre_cliente, $telefono_cliente);
    $stmt_usuario->execute();
    $usuario_id = $stmt_usuario->insert_id; // Obtener el ID del nuevo usuario
}

// Comprobar si es una nueva mascota y registrar
if ($nombre_mascota) {
    // Registrar la mascota
    $sql_mascota = "INSERT INTO mascotas (nombre_mascota, id_usuario) VALUES (?, ?)";
    $stmt_mascota = $db->prepare($sql_mascota);
    $stmt_mascota->bind_param("si", $nombre_mascota, $usuario_id);
    $stmt_mascota->execute();
    $mascota_id = $stmt_mascota->insert_id; // Obtener el ID de la nueva mascota
} else {
    // Suponiendo que se selecciona una mascota existente
    $mascota_id = $_POST['mascota']; // ID de mascota existente
}

// Insertar cita en la tabla citas
$sql_cita = "INSERT INTO citas (id_usuario, fecha_cita, id_servicio, estatus, id_mascota) VALUES (?, ?, ?, 'pendiente', ?)";
$stmt_cita = $db->prepare($sql_cita);
$stmt_cita->bind_param("issi", $usuario_id, $fecha_cita, $servicio_id, $mascota_id);

if ($stmt_cita->execute()) {
    // Obtener el ID de la nueva cita
    $id_cita = $stmt_cita->insert_id;

    // Insertar en la tabla servicios_mascotas
    $sql_servicio_mascota = "INSERT INTO servicios_mascotas (id_mascota, id_servicio, fecha_inicio, estado_servicio, progreso, id_cita) VALUES (?, ?, ?, 'Activo', 'pendiente', ?)";
    $stmt_servicio_mascota = $db->prepare($sql_servicio_mascota);
    $stmt_servicio_mascota->bind_param("iiss", $mascota_id, $servicio_id, $fecha_cita, $id_cita);
    
    if ($stmt_servicio_mascota->execute()) {
        // Crear notificación solo si el usuario no es administrador o trabajador
        if ($rol_usuario != 'administrador' && $rol_usuario != 'trabajador') {
            $mensaje = "Se ha agendado una cita para la mascota con ID $mascota_id.";
            $sql_notificacion = "INSERT INTO notificaciones (id_cita, usuario_id, mensaje, visto, fecha) VALUES (?, ?, ?, 0, NOW())";
            $stmt_notificacion = $db->prepare($sql_notificacion);
            $stmt_notificacion->bind_param("iis", $id_cita, $usuario_id, $mensaje);
            $stmt_notificacion->execute();
        }

        // Redirigir a administrar_citas.php
        if (!isset($_SESSION['usuario_id']) || ($rol_usuario !== 'administrador' && $rol_usuario !== 'trabajador')) {
            header("Location: administrar_citas.php");
            exit();
        } else {
            header("Location: historial_servicios.php");
            exit();
        }
        
    } else {
        echo "Error al insertar en la tabla servicios_mascotas: " . $stmt_servicio_mascota->error;
    }
} else {
    echo "Error al insertar en la tabla citas: " . $stmt_cita->error;
}

$stmt_cita->close();
$stmt_servicio_mascota->close();
$db->close();
?>
