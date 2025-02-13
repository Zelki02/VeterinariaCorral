<?php
include 'views/header.php';
include 'includes/db.php'; // Conexión a la base de datos

// Comprobar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Procesar el formulario de agregar mascota
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre_mascota'];
    $especie = $_POST['especie'];
    $raza = $_POST['raza'];
    $edad = $_POST['edad'];
    $peso = $_POST ['peso'];
    $sexo = $_POST['sexo'];

    // Insertar mascota en la base de datos
    $sql = "INSERT INTO mascotas (nombre_mascota, especie, raza, edad, peso, sexo, id_usuario) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("sssisss", $nombre, $especie, $raza, $edad, $peso, $sexo, $usuario_id);
    
    $stmt->execute();
    $stmt->close();
    header("Location: ver_mis_mascotas.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Mascota</title>
    <link rel="stylesheet" href="css/estilos.css"> <!-- Asegúrate de que la ruta es correcta -->
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

        /* Estilos de la tabla */
        table {
            width: 150%; /* O puedes usar un valor en píxeles o porcentaje, como '800px' */
            max-width: 1200px; /* Controla el ancho máximo de la tabla */
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 15px;
            text-align: left;
        }

        th {
            background-color: #34495e;
            color: #fff;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #ddd;
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

        /* Estilos del formulario */
        .form-container {
            width: 150%; /* O puedes usar un valor en píxeles o porcentaje, como '800px' */
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            padding-right:40px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            max-width: 600px; /* Ajusta este valor si deseas un ancho específico */
        }

        .form-container h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .form-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #34495e;
        }

        .form-container input[type="text"],
        .form-container input[type="number"],
        .form-container select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .form-container input[type="submit"] {
            background-color: #1abc9c;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease, transform 0.2s ease;
            width: 100%;
        }

        .form-container input[type="submit"]:hover {
            background-color: #16a085;
            transform: scale(1.05);
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
                <a href="ver_mis_datos.php">Mi Cuenta</a> <span>➤</span> Agregar Mascota
            </div>

            <div class="bread">
                <h2>Agregar Nueva Mascota</h2>
            </div>

            <!-- Formulario para agregar mascota -->
            <div class="form-container">
                <form action="agregar_mascota.php" method="POST">
                    <label for="nombre_mascota">Nombre:</label>
                    <input type="text" id="nombre_mascota" name="nombre_mascota" required>

                    <label for="especie">Especie:</label>
                    <input type="text" id="especie" name="especie" required>

                    <label for="raza">Raza:</label>
                    <input type="text" id="raza" name="raza" required>

                    <label for="edad">Edad (años):</label>
                    <input type="number" id="edad" name="edad" required>

                    <label for="peso">Peso (Kg.):</label>
                    <input type="number" id="peso" name="peso" required>

                    <label for="sexo">Sexo:</label>
                    <select id="sexo" name="sexo" required>
                        <option value="Macho">Macho</option>
                        <option value="Hembra">Hembra</option>
                    </select>

                    <input type="submit" value="Agregar Mascota">
                </form>
            </div>
        </div>
    </main>

    <?php
    include 'views/footer.php';
    ?>
</body>
</html>