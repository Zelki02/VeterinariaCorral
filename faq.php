<?php
include 'views/header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preguntas Frecuentes (FAQ)</title>
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

        .faq-container {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            max-width: 800px;
        }

        .faq-container h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 20px;
        }

        .faq-item {
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        .faq-item h3 {
            font-size: 18px;
            color: #34495e;
            cursor: pointer;
        }

        .faq-item p {
            display: none;
            font-size: 16px;
            color: #666;
            margin-top: 10px;
        }

        .faq-item.active p {
            display: block;
        }
    </style>
</head>
<body>
    <main>
        <div class="menu">
            <h3>Ayuda</h3>
            <ul>
                <li><a href="faq.php">Preguntas Frecuentes</a></li>
                <li><a href="soporte.php">Soporte</a></li>
                <li><a href="contacto.php">Contacto</a></li>
            </ul>
        </div>

        <div class="content-container">
            <div class="faq-container">
                <h2>Preguntas Frecuentes</h2>

                <div class="faq-item">
                    <h3 onclick="toggleFAQ(this)">¿Cómo agendar una cita?</h3>
                    <p>Para agendar una cita, dirígete a la sección de 'Agendar Cita', selecciona el servicio y elige la fecha y hora deseadas.</p>
                </div>

                <div class="faq-item">
                    <h3 onclick="toggleFAQ(this)">¿Cómo puedo ver el historial de servicios de mi mascota?</h3>
                    <p>Puedes ver el historial de servicios de tu mascota en la sección 'Historial de Servicios' dentro de tu perfil.</p>
                </div>

                <div class="faq-item">
                    <h3 onclick="toggleFAQ(this)">¿Cómo recuperar mi contraseña?</h3>
                    <p>Si olvidaste tu contraseña, haz clic en '¿Olvidaste tu contraseña?' en la página de inicio de sesión y sigue las instrucciones.</p>
                </div>

                <div class="faq-item">
                    <h3 onclick="toggleFAQ(this)">¿Dónde puedo ver mis facturas?</h3>
                    <p>Tus facturas están disponibles en la sección 'Facturas y Pagos' dentro de tu cuenta.</p>
                </div>

            </div>
        </div>
    </main>

    <script>
        function toggleFAQ(element) {
            const faqItem = element.parentElement;
            faqItem.classList.toggle('active');
        }
    </script>
</body>
</html>
