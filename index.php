<?php 
include 'views/header.php';
include 'includes/db.php'; // Conexión a la base de datos
?>

<div class="informacion" style="padding-left: 75px">
    <h1>Veterinaria El Corral</h1>

    <?php
    // Obtener la hora actual
    date_default_timezone_set('America/Mexico_City'); // Establecer la zona horaria
    $hora_actual = date("H:i");
    $horario_cierre = "20:00"; // Horario de cierre
    $horario_apertura = "09:00"; // Horario de apertura

    // Función para verificar si está abierto
    function estaAbierto($hora_actual, $horario_apertura, $horario_cierre) {
        return $hora_actual >= $horario_apertura && $hora_actual <= $horario_cierre;
    }

    $estado_abierto = estaAbierto($hora_actual, $horario_apertura, $horario_cierre);
    ?>

    <div class="estado">
        <?php if ($estado_abierto): ?>
            <p><span style="color: green;">Abierto</span> • Cierra a las 20:00</p>
        <?php else: ?>
            <p><span style="color: red;">Cerrado</span> • Abre a las 09:00</p>
        <?php endif; ?>
    </div>
</div>

<div class="horarios-container" style="display: flex; align-items: flex-start; justify-content: space-between; padding-left: 150px; padding-right: 150px;">
    <div class="imagen-horarios" style="margin-right: 20px;">
        <img src="img/Captura de pantalla 2024-11-06 122120.png" alt="Imagen Veterinaria" style="max-width: 500px; max-height: 500px; border-radius: 5px;">
    </div>
    <div class="horarios" style="margin-left: auto; margin-right: 10px;">
        <h2>Horarios</h2>
        <p>Lunes:       9:00 AM - 8:00 PM</p>
        <p>Martes:      9:00 AM - 8:00 PM</p>
        <p>Miércoles:   9:00 AM - 8:00 PM</p>
        <p>Jueves:      9:00 AM - 8:00 PM</p>
        <p>Viernes:     9:00 AM - 8:00 PM</p>
        <p>Sábado:      9:00 AM - 8:00 PM</p>
        <p>Domingo:     9:00 AM - 2:00 PM</p>
    </div>
</div>

<div class="servicios-container" style="margin-top: 30px; padding-left: 75px; padding-right: 75px;">
    <h2 style="text-align: center; margin-bottom: 20px; color: #a67c52;">Servicios Disponibles</h2>
    <div style="overflow-x: auto; display: flex; justify-content: center;">
        <table style="width: 70%; max-width: 800px; border-collapse: collapse; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin: auto;">
            <thead>
                <tr style="background-color: #d9a066; color: white;">
                    <th style="padding: 10px; text-align: left; border-bottom: 2px solid #ddd;">Nombre</th>
                    <th style="padding: 10px; text-align: left; border-bottom: 2px solid #ddd; width: 50%;">Descripción</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT id_servicio, nombre_servicio, descripcion FROM servicios";
                $resultado = $db->query($sql);

                if ($resultado && $resultado->num_rows > 0):
                    $contador = 0;
                    while ($row = $resultado->fetch_assoc()):
                        $fondo = ($contador % 2 === 0) ? '#f9f9f9' : '#ffffff';
                        ?>
                        <tr style="background-color: <?php echo $fondo; ?>;">
                            <td style="padding: 10px; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($row['nombre_servicio']); ?></td>
                            <td style="padding: 10px; border-bottom: 1px solid #ddd; width: 50%;"><?php echo htmlspecialchars($row['descripcion']); ?></td>
                        </tr>
                        <?php 
                        $contador++;
                    endwhile;
                else:
                ?>
                    <tr>
                        <td colspan="3" style="padding: 10px; text-align: center; color: #999;">No hay servicios disponibles.</td>
                    </tr>
                <?php 
                endif;
                $resultado->free();
                ?>
            </tbody>
        </table>
    </div>
</div>


<div class="map-container" style="margin-top: 20px; padding-left: 75px;">
    <h2>Ubicación</h2>
    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4435.551738705615!2d-99.9946404910802!3d21.928227810041903!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x85d50dbf72a579ef%3A0x5814a91366235efc!2sVeterinaria%20el%20corral!5e0!3m2!1ses-419!2smx!4v1727819248810!5m2!1ses-419!2smx" width="400" height="250" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    <p>Bravo esquina con Morelos, Rioverde, S.L.P</p>
</div>

<?php
$db->close();
include 'views/footer.php';
?>
