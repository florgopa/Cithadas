<?php
// backend/auth/logout.php

// Iniciar la sesión si no está iniciada (necesario para acceder a $_SESSION)
session_start();

// Destruir todas las variables de sesión
$_SESSION = array();

// Si se desea destruir la cookie de sesión, también es necesario eliminarla.
// Nota: Esto destruirá la sesión, y no solo los datos de sesión.
// Esto es generalmente lo que se quiere al hacer logout.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir la sesión.
session_destroy();

// Redirigir al usuario a la página de inicio (home) después de cerrar sesión
header("location: ../../index.php?page=home");
exit(); // Terminar el script para asegurar la redirección
?>