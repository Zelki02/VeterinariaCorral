<?php
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cita = $_POST['id_cita'];
    $nuevo_estatus = $_POST['nuevo_estatus'];

    // Actualizar el estatus de la cita en la base de datos
    $sql = "UPDATE citas SET estatus = ? WHERE id_cita = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("si", $nuevo_estatus, $id_cita);

    if ($stmt->execute()) {
        header("Location: administrar_citas.php");
        exit();
    } else {
        echo "Error al actualizar el estatus de la cita: " . $stmt->error;
    }

    $stmt->close();
    $db->close();
}
?>
