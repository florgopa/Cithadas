<?php

// Define las constantes de conexión a la base de datos
define('DB_SERVER', 'localhost'); // La dirección del servidor de la base de datos (generalmente 'localhost' para XAMPP)
define('DB_USERNAME', 'root');    // El nombre de usuario de tu base de datos (por defecto 'root' en XAMPP)
define('DB_PASSWORD', '');        // La contraseña de tu base de datos (por defecto vacía en XAMPP)
define('DB_NAME', 'cithadas_db'); // El nombre de la base de datos que creamos

// Intenta establecer la conexión a la base de datos
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verifica si la conexión fue exitosa
if ($conn->connect_error) {
    // Si hay un error, lo mostramos y terminamos la ejecución
    die("ERROR: Could not connect. " . $conn->connect_error);
}

// Establece el conjunto de caracteres a UTF-8 para evitar problemas con tildes y caracteres especiales
$conn->set_charset("utf8mb4");

?>