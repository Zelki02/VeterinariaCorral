<?php
$host = "localhost";  // El host donde corre MySQL (XAMPP usa localhost)
$user = "root";       // Usuario por defecto en XAMPP
$password = "";       // Contraseña por defecto en XAMPP (vacía)
$dbname = "veterinaria";  // Asegúrate de que esta sea tu base de datos en XAMPP

// Conexión a la base de datos usando MySQLi
$db = new mysqli($host, $user, $password, $dbname);

// Verificar si la conexión tuvo éxito
if ($db->connect_error) {
    die("Conexión fallida: " . $db->connect_error);
}
?>
