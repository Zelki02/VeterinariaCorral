<?php
include 'includes/db.php'; // Asegúrate de que la ruta es correcta

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    
    // Verificar si la contraseña y la confirmación coinciden
    if ($_POST['password'] !== $_POST['confirm_password']) {
        header("Location: register.php?error=Las contraseñas no coinciden");
        exit();
    }

    // Hashear la contraseña
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Asignar rol
    $rol = 'cliente'; // Rol predeterminado para registros externos

    // Inserción en la base de datos
    $sql = "INSERT INTO usuarios (nombre, apellidos, email, contraseña, telefono, direccion, rol) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    
    if ($stmt === false) {
        die("Error en la preparación de la consulta: " . $db->error);
    }

    $stmt->bind_param("sssssss", $nombre, $apellidos, $email, $hashed_password, $telefono, $direccion, $rol);

    if ($stmt->execute()) {
        header("Location: login.php?mensaje=Registro exitoso. Por favor, inicia sesión.");
    } else {
        header("Location: register.php?error=Error al registrar el usuario: " . $stmt->error);
    }

    $stmt->close();
}

$db->close();
?>
