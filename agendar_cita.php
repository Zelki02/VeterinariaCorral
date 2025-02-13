<?php 
include 'views/header.php';
include 'includes/db.php';

// Comprobar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Consulta para obtener las mascotas del usuario
$sql_mascotas = "SELECT id_mascota, nombre_mascota FROM mascotas WHERE id_usuario = ?";
$stmt_mascotas = $db->prepare($sql_mascotas);
$stmt_mascotas->bind_param("i", $usuario_id);
$stmt_mascotas->execute();
$resultado_mascotas = $stmt_mascotas->get_result();

// Nueva consulta para obtener los servicios
$sql_servicios = "SELECT id_servicio, nombre_servicio FROM servicios";
$result_servicios = $db->query($sql_servicios);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Cita</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
/* ==== Estilo general ==== */
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
    flex-wrap: wrap; /* Permite que los elementos se acomoden en varias líneas */
}

/* ==== Menú lateral ==== */
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

/* ==== Breadcrumbs ==== */
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

/* ==== Contenedor principal ==== */
.content-container {
    display: flex;
    flex-direction: column;
    flex: 1;
    padding: 0 20px;
}

/* ==== Formulario ==== */
.form-container {
    width: 100%; /* Cambiado a 100% para que ocupe todo el espacio en pantallas pequeñas */
    background-color: #ffffff;
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    margin-top: 20px;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.form-container h2 {
    font-size: 24px;
    margin-bottom: 20px;
    color: #333;
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

/* ==== Botones de acción ==== */
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

/* ==== Media Queries para Responsividad ==== */

/* Pantallas pequeñas (móviles) */
@media (max-width: 768px) {
    main {
        flex-direction: column;
        margin: 10px;
    }

    .menu {
        width: 100%; /* El menú ocupará el 100% del ancho */
        padding: 20px;
    }

    .form-container {
        width: 100%; /* Asegura que el formulario ocupe todo el ancho disponible */
        padding: 15px;
    }

    .menu ul li a {
        font-size: 12px; /* Ajusta el tamaño de fuente para pantallas pequeñas */
    }

    .breadcrumb {
        font-size: 12px; /* Reduce el tamaño de la fuente */
    }

    .boton-editar {
        font-size: 14px; /* Ajusta el tamaño del botón */
        padding: 8px 15px;
    }
}

/* Pantallas medianas (tabletas) */
@media (max-width: 1024px) {
    .menu {
        width: 200px; /* Reduce el tamaño del menú en pantallas más pequeñas */
        padding: 30px 20px;
    }

    .form-container {
        width: 90%; /* Ajusta el formulario para ocupar más espacio */
    }

    .boton-editar {
        font-size: 15px; /* Ajusta el tamaño del botón */
        padding: 10px 18px;
    }
}

/* Pantallas grandes (escritorios) */
@media (min-width: 1200px) {
    .menu {
        width: 220px;
    }

    .form-container {
        max-width: 700px;
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

    <!-- Contenedor de contenido -->
    <div class="content-container">
        <div class="breadcrumb">
            <a href="ver_mis_datos.php">Mi Cuenta</a> <span>➤</span> <a href="agendar_cita.php">Agendar Cita</a>
        </div>

        <!-- Formulario para agendar la cita -->
        <div class="form-container">
            <h2>Agendar una Cita</h2>
            <form method="POST" action="procesar_cita.php">
                <label for="fecha_cita">Fecha y Hora:</label>
                <input type="datetime-local" name="fecha_cita" required>

                <label for="servicio">Seleccionar Servicio:</label>
    <select name="servicio" id="servicio" required>
        <option value="">Seleccionar servicio</option>
        <?php
        // Verificar si hay servicios y mostrarlos en el select
        if ($result_servicios->num_rows > 0) {
            while ($row = $result_servicios->fetch_assoc()) {
                echo '<option value="' . $row['id_servicio'] . '">' . $row['nombre_servicio'] . '</option>';
            }
        } else {
            echo '<option value="">No hay servicios disponibles</option>';
        }
        ?>
    </select>

                <label for="mascota">Selecciona Mascota:</label>
                <select name="mascota" required>
                    <?php while ($mascota = $resultado_mascotas->fetch_assoc()): ?>
                        <option value="<?php echo $mascota['id_mascota']; ?>"><?php echo $mascota['nombre_mascota']; ?></option>
                    <?php endwhile; ?>
                </select>

                <button type="submit" class="boton-editar">Agendar cita</button>
            </form>
        </div>
    </div>
</main>

<?php
$stmt_mascotas->close();
$db->close();
include 'views/footer.php';
?>
</body>
</html>
