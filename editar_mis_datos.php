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

// Procesar el formulario de edición
// Procesar el formulario de edición
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];
    $codigo_postal = $_POST['codigo_postal'];
    $calle = $_POST['calle'];
    $numero_exterior = $_POST['numero_exterior'];
    $numero_interior = $_POST['numero_interior'];
    $colonia = $_POST['colonia'];
    $ciudad = $_POST['ciudad'];
    $estado = $_POST['estado'];
    $pais = $_POST['pais'];

    // Actualizar los datos del usuario
    $update_user_sql = "UPDATE usuarios SET nombre = ?, apellidos = ?, email = ?, telefono = ? WHERE id_usuario = ?";
    $stmt = $db->prepare($update_user_sql);
    $stmt->bind_param("ssssi", $nombre, $apellidos, $email, $telefono, $usuario_id);
    $stmt->execute();

    // Verificar si existe un domicilio asociado al usuario
    $check_address_sql = "SELECT id_usuario FROM domicilios WHERE id_usuario = ?";
    $stmt = $db->prepare($check_address_sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Actualizar domicilio existente
        $update_address_sql = "UPDATE domicilios SET codigo_postal = ?, calle = ?, numero_exterior = ?, numero_interior = ?, colonia = ?, ciudad = ?, estado = ?, pais = ? WHERE id_usuario = ?";
        $stmt = $db->prepare($update_address_sql);
        $stmt->bind_param("isssssssi", $codigo_postal, $calle, $numero_exterior, $numero_interior, $colonia, $ciudad, $estado, $pais, $usuario_id);
    } else {
        // Insertar nuevo domicilio
        $insert_address_sql = "INSERT INTO domicilios (id_usuario, codigo_postal, calle, numero_exterior, numero_interior, colonia, ciudad, estado, pais) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($insert_address_sql);
        $stmt->bind_param("iisssssss", $usuario_id, $codigo_postal, $calle, $numero_exterior, $numero_interior, $colonia, $ciudad, $estado, $pais);
    }
    $stmt->execute();

    // Redireccionar después de guardar los cambios
    echo "<script type='text/javascript'>
                window.location.href = 'ver_mis_datos.php';
              </script>";
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Mis Datos</title>
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

        /* Ajustes generales */
        main {
            flex: 1;
            display: flex;
            padding: 0;
            max-width: 1200px;
            margin: 20px auto;
            margin-left: 20px;
        }

        /* Menú lateral */
        .menu {
            width: 220px;
            background-color: #2c3e50;
            color: #fff;
            padding: 40px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .content-container {
            display: flex;
            flex-direction: column;
            flex: 1;
            padding: 0 20px;
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

        .form-container {
            width: 150%; /* O puedes usar un valor en píxeles o porcentaje, como '800px' */
            background-color: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            max-width: 600px;
        }

        .wrapper {
            display: flex;
            justify-content: space-between; /* Para que las columnas estén separadas */
        }

        .info-column, .address-column {
            width: 45%; /* Ancho de las columnas */
        }

        h3 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #333;
        }

        p {
            font-size: 16px;
            margin-bottom: 10px;
        }

        .boton-editar {
            padding: 10px 20px;
            background-color: #1abc9c;
            color: #fff;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s ease;
            margin-top: 20px;
        }

        .boton-editar:hover {
            background-color: #16a085;
        }

        input[type="text"], input[type="email"], input[type="tel"], input[type="number"], textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
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

    </style>
</head>
<body>
    <main>
        <!-- Menú lateral -->
        <div class="menu">
            <h3>Configuraciones</h3>
            <ul>
                <li><a href="ver_mis_datos.php">Mi Cuenta</a></li>
                <li><a href="ver_mis_mascotas.php">Ver Mis Mascotas</a></li>
                <li><a href="agregar_mascota.php">Agregar Mascota</a></li>
                <li><a href="historial_servicios.php">Historial de Servicios</a></li>
                <li><a href="agendar_cita.php">Agendar Cita</a></li>
                <li><a href="facturas.php">Facturas y Pagos</a></li>
                <li><a href="cambiar_contrasena.php">Cambiar Contraseña</a></li>
                <li><a href="configuracion_notificaciones.php">Configuración de Notificaciones</a></li>
                <li><a href="soporte.php">Soporte o Ayuda</a></li>
            </ul>
        </div>

        <!-- Contenedor de contenido (breadcrumb + datos de usuario) -->
        <div class="content-container">
        <div class="breadcrumb">
                <a href="ver_mis_datos.php">Mi Cuenta</a> <span>➤</span> Editar mis Datos
            </div>

            <div class="bread">
                <h2>Editar mis Datos</h2>
            </div>

        <!-- Cuadro con información del usuario y domicilio -->
        <div class="form-container">
            
            <form method="POST" action="">
                <div class="wrapper">
                    <!-- Primera columna (Información del usuario) -->
                    <div class="info-column">
                        <h3>Información del Usuario</h3>
                        <p><strong>Nombre:</strong><br><input type="text" name="nombre" value="<?php echo $usuario['nombre']; ?>"></p>
                        <p><strong>Apellidos:</strong><br><input type="text" name="apellidos" value="<?php echo $usuario['apellidos']; ?>"></p>
                        <p><strong>Email:</strong><br><input type="email" name="email" value="<?php echo $usuario['email']; ?>" readonly></p> <!-- Campo deshabilitado -->
                        <p><strong>Teléfono:</strong><br><input type="tel" name="telefono" value="<?php echo $usuario['telefono']; ?>"></p>
                    </div>

                    <!-- Segunda columna (Información del domicilio) -->
                    <div class="address-column">
                        <h3>Información del Domicilio</h3>
                        <p><strong>Código Postal:</strong><br><input type="number" name="codigo_postal" value="<?php echo $usuario['codigo_postal']; ?>"></p>
                        <p><strong>Calle:</strong><br><input type="text" name="calle" value="<?php echo $usuario['calle']; ?>"></p>
                        <p><strong>Número Exterior:</strong><br><input type="text" name="numero_exterior" value="<?php echo $usuario['numero_exterior']; ?>"></p>
                        <p><strong>Número Interior:</strong><br><input type="text" name="numero_interior" value="<?php echo $usuario['numero_interior']; ?>"></p>
                        <p><strong>Colonia:</strong><br><input type="text" name="colonia" value="<?php echo $usuario['colonia']; ?>"></p>
                        <p><strong>Ciudad:</strong><br><input type="text" name="ciudad" value="<?php echo $usuario['ciudad']; ?>"></p>
                        <p><strong>Estado:</strong><br><input type="text" name="estado" value="<?php echo $usuario['estado']; ?>"></p>
                        <p><strong>País:</strong><br><input type="text" name="pais" value="<?php echo $usuario['pais']; ?>"></p>
                    </div>
                </div>

                <input type="submit" value="Guardar Cambios">
            </form>
        </div>
    </main>

    <?php include 'views/footer.php'; ?>
</body>
</html>