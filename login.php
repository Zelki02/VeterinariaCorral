<?php
session_start();
// Incluye la conexión a la base de datos
include 'includes/db.php';  // Asegúrate de que esta ruta es correcta

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $contraseña = $_POST['contraseña'];

    // Verificar las credenciales del usuario
    $sql = "SELECT id_usuario, contraseña, rol FROM usuarios WHERE email = ?";
    $stmt = $db->prepare($sql); // Cambiar $conn por $db
    if ($stmt === false) {
        die("Error en la preparación de la consulta: " . $db->error);
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id_usuario, $hashed_password, $rol);
    
    if ($stmt->num_rows > 0) {
        $stmt->fetch();
        // Verificar la contraseña
        if (password_verify($contraseña, $hashed_password)) {
            // Guardar datos en la sesión
            $_SESSION['usuario_id'] = $id_usuario;
            $_SESSION['rol'] = $rol;
            // Redirigir según el rol del usuario
            header("Location: index.php");
            exit();
        } else {
            echo "Contraseña incorrecta.";
        }
    } else {
        echo "No se encontró un usuario con ese correo.";
    }
    $stmt->close();
}

// Cerrar la conexión
$db->close(); // Cambiar $conn por $db
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión</title>
    <link rel="stylesheet" href="css/estilos.css"> <!-- Asegúrate de que la ruta es correcta -->
    <style>
        body {
            display: flex;
            min-height: 100vh;
            margin: 0;
            font-family: 'Open Sans', sans-serif;
            background-color: #FFB74D;
            position: relative;
        }
        .logo {
            position: absolute;
            top: 20px;
            left: 20px;
        }
        .logo img {
            max-width: 250px; /* Ajusta según el tamaño deseado */
        }
        .login-container {
            width: 300px; /* Ancho fijo */
            height: 450px; /* Alto fijo */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            padding-right:40px;
            background-color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            position: absolute;
            right: 100px;
            top: 100px;
        }
        .login-container h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .login-container label {
            margin-top: 10px;
            font-weight: bold;
        }
        .login-container input[type="email"],
        .login-container input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        .login-container input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #e74c3c;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .login-container input[type="submit"]:hover {
            background-color: #c0392b;
        }
        .welcome-message {
            position: absolute;
            top: 200px; /* Ajusta según la altura deseada */
            left: 100px; /* Ajusta según la posición deseada */
            color: #333;
            font-size: 100px;
            font-weight: bold;
            padding: 20px;
        }

        .welcome-message p {
            margin: 0; /* Elimina el margen predeterminado */
        }

.welcome-message p:first-child {
    margin-bottom: 5px; /* Ajusta este valor para el espaciado deseado */
}
/* Ejemplo de estilo para el enlace */
a {
    color: #4D94FF; /* Color azul complementario */
    text-decoration: underline; /* Subrayado para mayor claridad */
}

    </style>
</head>
<body>

<a href="index.php" style="text-decoration: none;">
    <div class="logo">
        <img src="img/logo.png" alt="Logo de la Veterinaria">
    </div>
</a>


    <div class="welcome-message">
        <p>¡Hola!</p>
        <p>¡Qué gusto verte!</p>
    </div>

    <div class="login-container">
        <h2>Iniciar Sesión</h2>
        <p style="text-align: center; margin-top: 20px;">
            ¿Nuevo usuario? <a href="register.php" style="color: #; text-decoration: underline;">Crear una cuenta</a>
        </p>
        <form action="" method="POST">
            <label for="email">Correo Electrónico:</label>
            <input type="email" name="email" required>

            <label for="contraseña">Contraseña:</label>
            <input type="password" name="contraseña" required>

            <input type="submit" value="Iniciar Sesión">
        </form>
    </div>

</body>
</html>
