<?php
$password = "password123"; // Reemplaza con la contraseña que quieras para tu admin
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
echo "El hash de tu contraseña es: " . $hashed_password;
?>