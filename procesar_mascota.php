<?php
// Incluir la conexión a la base de datos
include 'includes/db.php';
session_start();

// Verificar si el usuario está logueado y es un cliente
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'cliente') {
    header("Location: login.php");
    exit();
}

// Verificar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos del formulario
    $nombre_mascota = $db->real_escape_string($_POST['nombre_mascota']);
    $especie = $db->real_escape_string($_POST['especie']);
    $raza = $db->real_escape_string($_POST['raza']);
    $edad = (int) $_POST['edad'];
    $peso = (float) $_POST['peso'];
    $sexo = $db->real_escape_string($_POST['sexo']);
    $id_usuario = $_SESSION['id_usuario'];

    // Insertar la nueva mascota en la base de datos
    $query = "INSERT INTO mascotas (id_usuario, nombre_mascota, especie, raza, edad, peso, sexo) 
              VALUES ('$id_usuario', '$nombre_mascota', '$especie', '$raza', '$edad', '$peso', '$sexo')";

    if ($db->query($query) === TRUE) {
        echo "Mascota agregada correctamente";
    } else {
        echo "Error: " . $db->error;
    }

    // Redirigir a otra página si es necesario
    // header("Location: perfil_mascotas.php");
} else {
    echo "No se recibieron datos";
}

?>
