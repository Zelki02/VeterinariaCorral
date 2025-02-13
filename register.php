<?php
session_start();
// Incluye la conexión a la base de datos
include 'includes/db.php'; // Asegúrate de que esta ruta es correcta

// Verificar si el usuario es administrador
$is_admin = isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        body {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #FFB74D; /* Naranja agradable */
        }

        .left-section {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding-left: 50px;
            color: #fff;
            width: 50%;
        }

        .left-section img {
            position: absolute;
            top: 20px;
            left: 20px;
            width: 250px;
            height: auto;
        }

        .left-section h1 {
            font-size: 60px;
            margin: 0;
        }

        .right-section {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 50%;
        }

        .register-box {
            background-color: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            height: 650px; /* Ajusta el tamaño según sea necesario */
        }

        .register-box h2 {
            margin-bottom: 20px;
            color: #FFB74D;
        }

        .register-box label {
            display: block;
            margin-bottom: 5px;
        }

        .register-box input,
        .register-box select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .register-box button {
            width: 100%;
            padding: 10px;
            background-color: #FFB74D;
            border: none;
            color: #fff;
            border-radius: 5px;
            cursor: pointer;
        }

        .register-box button:hover {
            background-color: #FFA726;
        }

        /* Estilo del enlace para iniciar sesión */
        .register-box p {
            text-align: center;
            margin-top: 20px;
        }

        .register-box a {
            color: #FFB74D;
            text-decoration: underline;
        }

    </style>
</head>
<body>

<div class="left-section">
    <a href="index.php">
        <img src="img/logo.png" alt="Logo">
    </a>
    <h1>¡Hola!</h1>
    <h1>¡Qué gusto verte!</h1>
</div>


    <div class="right-section">
        <div class="register-box">
            <h2>Crea tu cuenta</h2>
            <p>
                ¿Ya tienes tu cuenta? <a href="login.php">Inicia sesión</a>
            </p>

            <!-- Mensajes de error si hay alguno -->
            <?php if (isset($_GET['error'])): ?>
                <p style="color:red;"><?php echo $_GET['error']; ?></p>
            <?php endif; ?>

            <form action="procesar_registro.php" method="POST" onsubmit="return validarFormulario()">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" required>

                <label for="apellidos">Apellidos:</label>
                <input type="text" id="apellidos" name="apellidos" required>

                <label for="email">Correo Electrónico:</label>
                <input type="email" id="email" name="email" required>

                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>

                <label for="confirm_password">Confirmar Contraseña:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>

                <label for="telefono">Teléfono:</label>
                <input type="tel" id="telefono" name="telefono" required>

                <label for="direccion">Dirección:</label>
                <input type="text" id="direccion" name="direccion" required>

                <?php if ($is_admin): ?>
                    <label for="rol">Rol:</label>
                    <select id="rol" name="rol" required>
                        <option value="cliente">Cliente</option>
                        <option value="vendedor">Vendedor</option>
                        <option value="administrador">Administrador</option>
                    </select><br><br>
                <?php else: ?>
                    <input type="hidden" name="rol" value="cliente">
                <?php endif; ?>

                <button type="submit">Registrar</button>
            </form>

            <div style="text-align: center; margin-top: 20px;">
                <h3>O regístrate con</h3>
                <a href="<?php echo $client->createAuthUrl(); ?>">
                    <button style="background-color: #4285F4; color: white; border: none; border-radius: 5px; padding: 10px 20px; cursor: pointer;">
                        Google
                    </button>
                </a>
            </div>
        </div>
    </div>

    <script>
        function validarFormulario() {
            var password = document.getElementById("password").value;
            var confirmPassword = document.getElementById("confirm_password").value;
            if (password !== confirmPassword) {
                alert("Las contraseñas no coinciden.");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
