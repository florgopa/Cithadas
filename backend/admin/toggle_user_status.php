<?php
session_start();

// Asegurarse de que solo usuarios logueados y con rol 'admin' puedan acceder
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'admin') {
    header("location: ../../index.php?page=home"); // Redirigir a home o a una página de acceso denegado
    exit;
}

// Incluir la conexión a la base de datos
require_once '../../includes/db.php'; // La ruta es correcta (dos niveles arriba desde backend/admin)

// Verificar si la solicitud es POST y si se ha enviado el botón de toggle
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['toggle_status_btn'])) {

    $user_id = $_POST['user_id'] ?? null;
    $current_status = $_POST['current_status'] ?? null;

    if ($user_id && $current_status) {
        // Determinar el nuevo estado
        $new_status = ($current_status === 'activo') ? 'inactivo' : 'activo';

        // Preparar la consulta SQL para actualizar el estado
        $sql = "UPDATE usuario SET estado = ? WHERE id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("si", $new_status, $user_id); // "s" para string (estado), "i" para integer (id)

            if ($stmt->execute()) {
                // Éxito al actualizar
                $_SESSION['status_message'] = "El estado del usuario ha sido actualizado a '" . $new_status . "'.";
                $_SESSION['status_type'] = 'success';
            } else {
                // Error al ejecutar la actualización
                $_SESSION['status_message'] = "Error al actualizar el estado del usuario: " . $stmt->error;
                $_SESSION['status_type'] = 'error';
            }
            $stmt->close();
        } else {
            // Error al preparar la consulta
            $_SESSION['status_message'] = "Error interno al preparar la actualización: " . $conn->error;
            $_SESSION['status_type'] = 'error';
        }
    } else {
        // Faltan parámetros
        $_SESSION['status_message'] = "Parámetros insuficientes para actualizar el estado del usuario.";
        $_SESSION['status_type'] = 'error';
    }

    $conn->close(); // Cerrar la conexión a la base de datos

    // Redirigir de vuelta a la página de gestión de usuarios
    header("location: ../../index.php?page=manage_users");
    exit();

} else {
    // Si se accede directamente sin POST o sin el botón de toggle
    header("location: ../../index.php?page=manage_users"); // Redirigir de vuelta a la página de gestión
    exit();
}
?>