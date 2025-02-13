<?php
include 'views/header.php';
include 'includes/db.php'; // Conexión a la base de datos

// Comprobar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Consultar los datos del usuario incluyendo apellidos y domicilio
$sql = "SELECT u.nombre, u.apellidos, u.email, u.telefono, d.codigo_postal, d.calle, d.numero_exterior, d.numero_interior, d.colonia, d.ciudad, d.estado, d.pais
        FROM usuarios u
        LEFT JOIN domicilios d ON u.id_usuario = d.id_usuario
        WHERE u.id_usuario = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Cuenta</title>
    <link rel="stylesheet" href="css/estilos.css"> <!-- Asegúrate de que la ruta es correcta -->
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
    width: 100%; /* Ajusta a 100% en pantallas pequeñas */
    background-color: #ffffff;
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    margin-top: 20px;
    max-width: 600px;
}

.form-container h2 {
    font-size: 24px;
    margin-bottom: 20px;
    color: #333;
}

.form-container p {
    margin-bottom: 10px;
    font-size: 16px;
}

.form-container p strong {
    color: #1abc9c;
}

input[type="submit"] {
    padding: 8px 15px;
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

.boton-editar {
    display: inline-block;
    padding: 10px 20px; /* Tamaño del botón */
    background-color: #1abc9c; /* Color de fondo */
    color: white; /* Color del texto */
    text-decoration: none; /* Quitar subrayado */
    font-size: 16px; /* Tamaño del texto */
    border-radius: 5px; /* Bordes redondeados */
    transition: background-color 0.3s ease; /* Transición suave */
    text-align: center; /* Centrar el texto */
    font-weight: bold; /* Texto en negrita */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Sombra */
}

.boton-editar:hover {
    background-color: #16a085; /* Color cuando pasas el mouse */
    box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15); /* Sombra más intensa al hacer hover */
}

.boton-editar:active {
    background-color: #148f77; /* Color al hacer clic */
    transform: translateY(2px); /* Efecto de presionar el botón */
}

/* Media Queries para pantallas pequeñas */

@media (max-width: 768px) {
    main {
        flex-direction: column;
        margin-left: 0;
    }

    .menu {
        width: 100%;
        padding: 20px;
    }

    .menu h3 {
        font-size: 16px;
    }

    .content-container {
        padding: 0 10px;
    }

    .form-container {
        width: 100%;
        padding: 20px;
    }

    .form-container h2 {
        font-size: 20px;
    }

    input[type="submit"] {
        padding: 10px 20px;
    }

    .boton-editar {
        padding: 8px 15px;
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    .menu {
        padding: 15px;
    }

    .menu h3 {
        font-size: 14px;
    }

    .form-container {
        padding: 15px;
    }

    .form-container h2 {
        font-size: 18px;
    }

    input[type="submit"] {
        padding: 10px 15px;
    }

    .boton-editar {
        padding: 6px 12px;
        font-size: 12px;
    }
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

        <!-- Contenedor de contenido (breadcrumb + datos de usuario) -->
        <div class="content-container">
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <a href="ver_mis_datos.php">Mi Cuenta</a>
            </div>

            <div class="bread">
                <h2>Información de Mi Cuenta</h2>
            </div>

            <!-- Cuadro con información del usuario -->
            <div class="form-container">
                <h2>Mis Datos</h2>
                <p><strong>Nombre:</strong> <?php echo $usuario['nombre']; ?></p>
                <p><strong>Apellidos:</strong> <?php echo $usuario['apellidos']; ?></p>
                <p><strong>Email:</strong> <?php echo $usuario['email']; ?></p>
                <p><strong>Teléfono:</strong> <?php echo $usuario['telefono']; ?></p>
                <h3>Dirección</h3>
                <p><strong>Código Postal:</strong> <?php echo $usuario['codigo_postal']; ?></p>
                <p><strong>Calle:</strong> <?php echo $usuario['calle']; ?></p>
                <p><strong>Número Exterior:</strong> <?php echo $usuario['numero_exterior']; ?></p>
                <p><strong>Número Interior:</strong> <?php echo $usuario['numero_interior']; ?></p>
                <p><strong>Colonia:</strong> <?php echo $usuario['colonia']; ?></p>
                <p><strong>Ciudad:</strong> <?php echo $usuario['ciudad']; ?></p>
                <p><strong>Estado:</strong> <?php echo $usuario['estado']; ?></p>
                <p><strong>País:</strong> <?php echo $usuario['pais']; ?></p>
                <a href="editar_mis_datos.php" class="boton-editar">Editar mis datos</a>
                </form>
            </div>
        </div>
    </main>

    <?php
    $stmt->close();
    $db->close();
    include 'views/footer.php';
    ?>
</body>
</html>