<?php 
session_start();
include 'includes/db.php'; // Conexión a la base de datos

$query_inventario = "SELECT alarma_activada FROM configuracion WHERE id = 1";
$result_inventario = $db->query($query_inventario);
$row_inventario = $result_inventario->fetch_assoc();
$alarmaInventario = $row_inventario['alarma_activada'];

$query_caducidad = "SELECT alarma_activada FROM configuracion WHERE id = 2";
$result_caducidad = $db->query($query_caducidad);
$row_caducidad = $result_caducidad->fetch_assoc();
$alarmaCaducidad = $row_caducidad['alarma_activada'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veterinaria El Corral</title>
    <link rel="stylesheet" href="css/estilos.css">
    <script>
        function toggleDropdown() {
            const dropdownContent = document.querySelector('.dropdown-content');
            dropdownContent.style.display = dropdownContent.style.display === 'block' ? 'none' : 'block';
        }

        let alarmaInventario = <?php echo $alarmaInventario; ?>; // Se imprime el valor de PHP en JS
        let alarmaCaducidad = <?php echo $alarmaCaducidad; ?>; // Se imprime el valor de PHP en JS

    function verificarInventario() {
        if (alarmaInventario === 1) { // Se compara correctamente en JS
            fetch('verificar_inventario.php')
                .then(response => response.json())
                .then(data => {
                    const inventarioIcono = document.getElementById('inventarioIcono');
                    if (data.productos && data.productos.length > 0) {
                        inventarioIcono.classList.add('notificacion-activa-inventario');
                    } else {
                        inventarioIcono.classList.remove('notificacion-activa-inventario');
                    }
                })
                .catch(error => console.error('Error al verificar el inventario:', error));
        }
    }

    function verificarCaducidades() {
        if (alarmaCaducidad === 1) {
        fetch('verificar_caducidades.php')
            .then(response => response.json())
            .then(data => {
                const caducidadIcono = document.getElementById('caducidadIcono');
                if (data.caducados && data.productos.length > 0) {
                    caducidadIcono.classList.add('notificacion-activa-caducidad');
                } else {
                    caducidadIcono.classList.remove('notificacion-activa-caducidad');
                }
            })
            .catch(error => console.error('Error al verificar caducidades:', error));
    }
}
    function verificarNotificaciones() {
        fetch('verificar_notificaciones.php')
            .then(response => response.json())
            .then(data => {
                const notificacionIcono = document.getElementById('notificacionIcono');
                if (data.nuevas) {
                    notificacionIcono.classList.add('notificacion-activa');
                } else {
                    notificacionIcono.classList.remove('notificacion-activa');
                }
            })
            .catch(error => console.error('Error al verificar notificaciones:', error));
    }

    // Ejecutar la verificación cada 10 segundos solo si la alarma está activada
    if (alarmaInventario === 1) {
        setInterval(verificarInventario, 10000);
    }
    
    setInterval(verificarNotificaciones, 10000);

    if (alarmaCaducidad === 1) {
    setInterval(verificarCaducidades, 10000);
    }

        // Cerrar el menú desplegable si se hace clic fuera de él
        window.onclick = function(event) {
            if (!event.target.matches('.dropdown img')) {
                const dropdowns = document.getElementsByClassName("dropdown-content");
                for (let i = 0; i < dropdowns.length; i++) {
                    const openDropdown = dropdowns[i];
                    if (openDropdown.style.display === 'block') {
                        openDropdown.style.display = 'none';
                    }
                }
            }
        }
    </script>
</head>
<body>
    <header>
        <a href="index.php" style="text-decoration: none;">
            <div class="logo">
                <img src="img/logo.png" alt="Logo de la Veterinaria">
            </div>
        </a>

        <div class="dropdown">
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <img src="img/User.png" alt="Menu" onclick="toggleDropdown()"> <!-- Icono de usuario -->
                <div class="dropdown-content">
                    <a href="ver_mis_datos.php">Mi Cuenta</a>
                    <?php if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador'):?>
                    <a href="ver_mis_mascotas.php">Ver Mis Mascotas</a>
                    <a href="agregar_mascota.php">Agregar Mascota</a>
                    <a href="historial_servicios.php">Historial de Servicios</a>
                    <a href="agendar_cita.php">Agendar Cita</a>
                    <?php endif; ?>
                    <a href="facturas.php">Facturas y Pagos</a>
                    <a href="cambiar_contrasena.php">Cambiar Contraseña</a>
                    <a href="configuracion_notificaciones.php">Configuración de Notificaciones</a>
                    <a href="soporte.php">Soporte o Ayuda</a>
                    <a href="cerrar.php">Cerrar Sesión</a>
                </div>
            <?php endif; ?>
        </div>
        
        <nav>
            <ul>
                <li><a href="index.php">Inicio</a></li>
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador'): ?>
                        <li><a href="punto_venta.php">Caja de Venta</a></li>
                        <li><a href="administrar_citas.php">Citas</a></li>
                        <li><a href="register.php">Registrar Miembros</a></li>
                        <li><a href="ver_ventas.php">Verificar mis Ventas</a></li>
                        <li><a href="servicios.php">Añadir Servicios</a></li>
                        <li><a href="inventario.php">Inventario</a></li>
                    <?php endif; ?>
                <?php else: ?>
                    <li><a href="login.php">Iniciar Sesión</a></li>
                    <li><a href="register.php">Registrarse</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        
        <?php if (isset($_SESSION['usuario_id']) && ($_SESSION['rol'] === 'administrador' || $_SESSION['rol'] === 'vendedor')): ?>
            <!-- Icono de notificación general -->
            <div id="notificacionIcono" class="notificacion-icono">
                <a href="ver_notificaciones.php">
                    <img src="img/image.png" alt="Notificación">
                </a>
            </div>

            <!-- Icono de notificación de inventario -->
            <div id="inventarioIcono" class="notificacion-icono-inventario">
                <a href="stock_bajo.php">
                    <img src="img/2897785.png" alt="Inventario Bajo">
                </a>
            </div>

            <!-- Icono de notificación de caducidades -->
            <div id="caducidadIcono" class="notificacion-icono-caducidad">
                <a href="productos_caducidad.php">
                    <img src="img/6534474.png" alt="Productos Próximos a Caducar">
                </a>
            </div>

        <?php endif; ?>
    </header>
</body>
</html>