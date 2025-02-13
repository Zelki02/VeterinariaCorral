<?php
include 'includes/db.php'; // Conexión a la base de datos

// Comprobar si el usuario ha iniciado sesión
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_mascota = $_POST['id_mascota'];

    // Eliminar la mascota de la base de datos
    $sql = "DELETE FROM mascotas WHERE id_mascota = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $id_mascota);
    $stmt->execute();

    // Redirigir de vuelta a la página de mis mascotas
    header("Location: ver_mis_mascotas.php");
    exit();
}

$stmt->close();
$db->close();
?>
