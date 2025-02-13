<?php
session_start();
include 'includes/db.php';

if (isset($_SESSION['rol']) && ($_SESSION['rol'] === 'administrador' || $_SESSION['rol'] === 'vendedor')) {
    $query = "SELECT COUNT(*) AS nuevas FROM notificaciones WHERE visto = 0";
    $result = $db->query($query);
    $data = $result->fetch_assoc();
    echo json_encode(['nuevas' => $data['nuevas'] > 0]);
} else {
    echo json_encode(['nuevas' => false]);
}
?>
