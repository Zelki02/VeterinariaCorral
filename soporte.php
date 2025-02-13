<?php
include 'views/header.php';
include 'includes/db.php'; // Conexión a la base de datos

// Verificar si el usuario tiene permisos
if (!isset($_SESSION['usuario_id'])) {
    die("Acceso no autorizado");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soporte y Ayuda</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
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

        .content-container {
            display: flex;
            flex-direction: column;
            flex: 1;
            padding: 0 20px;
        }

        .form-container {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            max-width: 600px;
        }

        .boton-editar {
            display: inline-block;
            padding: 10px 20px;
            background-color: #1abc9c;
            color: white;
            text-decoration: none;
            font-size: 16px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            text-align: center;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .boton-editar:hover {
            background-color: #16a085;
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }

        .boton-editar:active {
            background-color: #148f77;
            transform: translateY(2px);
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

    </style>
</head>
<body>
    <main>
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

        <div class="content-container">
        <div class="breadcrumb">
                <a href="ver_mis_datos.php">Mi Cuenta</a> <span>➤</span> Soporte o Ayuda
            </div>

            <div class="form-container">
                <h2>Centro de Soporte</h2>
                <p>Si tienes alguna pregunta o necesitas ayuda con el sistema, revisa las siguientes opciones:</p>
                
                <ul>
                    <li><strong>Preguntas Frecuentes (FAQ):</strong> Encuentra respuestas a las dudas más comunes.</li>
                    <li><strong>Guías de Uso:</strong> Aprende cómo utilizar las diferentes funciones del sistema.</li>
                    <li><strong>Contacto de Soporte:</strong> Si necesitas ayuda personalizada, contáctanos a través de nuestro correo soporte@veterinaria.com.</li>
                </ul>
                
                <a href="faq.php" class="boton-editar">Ver Preguntas Frecuentes</a>
                <a href="contacto_soporte.php" class="boton-editar">Contactar Soporte</a>
            </div>
        </div>
    </main>
    <?php
    include 'views/footer.php';
    ?>
</body>
</html>
