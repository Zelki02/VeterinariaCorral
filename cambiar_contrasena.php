<?php
include 'views/header.php';
include 'includes/db.php'; // Conexión a la base de datos

// Comprobar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Definir variables para los mensajes de error y éxito
$mensaje_error = '';
$mensaje_exito = '';

// Consultar la contraseña actual del usuario
$sql = "SELECT contraseña FROM usuarios WHERE id_usuario = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($contrasena_actual_bd);
$stmt->fetch();
$stmt->close();

// Comprobar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si las claves existen en el array POST
    if (isset($_POST['contrasena_actual'], $_POST['contrasena_nueva'], $_POST['contrasena_nueva_confirmada'])) {
        $contrasena_actual = $_POST['contrasena_actual'];
        $contrasena_nueva = $_POST['contrasena_nueva'];
        $contrasena_nueva_confirmada = $_POST['contrasena_nueva_confirmada'];

        // Verificar que la contraseña actual coincida con la almacenada en la base de datos
        if (!password_verify($contrasena_actual, $contrasena_actual_bd)) {
            $mensaje_error = 'La contraseña actual no es correcta.';
        }
        // Verificar que la nueva contraseña sea diferente de la actual
        elseif ($contrasena_actual === $contrasena_nueva) {
            $mensaje_error = 'La nueva contraseña no puede ser la misma que la actual.';
        }
        // Verificar que las contraseñas nuevas coincidan
        elseif ($contrasena_nueva !== $contrasena_nueva_confirmada) {
            $mensaje_error = 'Las contraseñas nuevas no coinciden.';
        } else {
            // Hashear la nueva contraseña antes de guardarla
            $contrasena_nueva_hash = password_hash($contrasena_nueva, PASSWORD_DEFAULT);

            // Actualizar la contraseña en la base de datos
            $sql = "UPDATE usuarios SET contraseña = ? WHERE id_usuario = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("si", $contrasena_nueva_hash, $usuario_id);

            if ($stmt->execute()) {
                $mensaje_exito = 'Contraseña cambiada con éxito.';
            } else {
                $mensaje_error = 'Hubo un error al actualizar la contraseña. Intente nuevamente.';
            }
            $stmt->close();
        }
    } else {
        $mensaje_error = 'Por favor, complete todos los campos.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        /* Aquí van tus estilos CSS */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            font-family: 'Open Sans', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
        }

        main {
            flex: 1;
            display: flex;
            padding: 0;
            max-width: 1200px;
            margin: 20px auto;
            margin-left: 20px;
        }

        .menu {
            width: 220px;
            background-color: #2c3e50;
            color: #fff;
            padding: 40px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .menu h3 {
            font-size: 18px;
            margin-bottom: 30px;
            color: #ecf0f1;
            text-align: center;
            border-bottom: 1px solid #ecf0f1;
            padding-bottom: 10px;
        }

        .menu ul {
            list-style-type: none;
            padding: 0;
        }

        .menu ul li {
            margin-bottom: 15px;
        }

        .menu ul li a {
            color: #ecf0f1;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            display: block;
            padding: 10px 15px;
            background-color: #34495e;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .menu ul li a:hover {
            background-color: #1abc9c;
            color: #fff;
        }

        .breadcrumb {
            font-size: 14px;
            margin: 0 0 20px 10px;
            color: #555;
        }

        .breadcrumb a {
            text-decoration: none;
            color: #1abc9c;
            font-weight: bold;
        }

        .breadcrumb span {
            margin: 0 10px;
            color: #999;
        }

        .bread {
            font-size: 20px;
            margin-left: 10px;
            color: #333;
            font-weight: bold;
        }

        .content-container {
            display: flex;
            flex-direction: column;
            flex: 1;
            padding: 0 20px;
        }

        .form-container {
            display: flex;
            flex-direction: column;
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        input[type="password"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 25px;
            width: 100%;
            outline: none;
            transition: border-color 0.3s ease;
        }

        input[type="password"]:focus {
            border-color: #1abc9c;
        }

        input[type="submit"] {
            padding: 10px 15px;
            background-color: #e74c3c;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        input[type="submit"]:hover {
            background-color: #c0392b;
            transform: scale(1.05);
        }

        .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .success-message {
            color: green;
            font-size: 14px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <main>
        <!-- Menú lateral -->
        <div class="menu">
            <h3>Configuraciones</h3>
            <ul>
            <li><a href="ver_mis_datos.php">Mi Cuenta</a></li>
                <?php if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador'):?>
                <li><a href="ver_mis_mascotas.php">Ver Mis Mascotas</a></li>
                <li><a href="agregar_mascota.php">Agregar Mascota</a></li>
                <li><a href="historial_servicios.php">Historial de Servicios</a></li>
                <li><a href="agendar_cita.php">Agendar Cita</a></li>
                <?php endif; ?>
                <?php if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] == 'administrador'):?>
                <li><a href="facturas.php">Facturas y Pagos</a></li>
                <?php endif; ?>
                <li><a href="cambiar_contrasena.php">Cambiar Contraseña</a></li>
                <li><a href="configuracion_notificaciones.php">Configuración de Notificaciones</a></li>
                <li><a href="soporte.php">Soporte o Ayuda</a></li>
            </ul>
        </div>

        <!-- Contenedor de contenido -->
        <div class="content-container">
            <div class="breadcrumb">
                <a href="ver_mis_datos.php">Mi Cuenta</a> <span>➤</span> Cambiar Contraseña
            </div>

            <div class="bread">
                <h2>Cambiar Contraseña</h2>
            </div>

            <!-- Formulario de cambio de contraseña -->
            <div class="form-container">
            <?php if ($mensaje_error): ?>
                <div class="error-message"><?php echo $mensaje_error; ?></div>
            <?php elseif ($mensaje_exito): ?>
                <div class="success-message"><?php echo $mensaje_exito; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <label for="contrasena_actual">Contraseña Actual:</label>
                <input type="password" id="contrasena_actual" name="contrasena_actual" required>

                <label for="contrasena_nueva">Nueva Contraseña:</label>
                <input type="password" id="contrasena_nueva" name="contrasena_nueva" required>

                <label for="contrasena_nueva_confirmada">Confirmar Nueva Contraseña:</label>
                <input type="password" id="contrasena_nueva_confirmada" name="contrasena_nueva_confirmada" required>

                <input type="submit" value="Cambiar Contraseña">
                </form>
            </div>
        </div>
    </main>

    <?php include 'views/footer.php'; ?>
</body>
</html>
