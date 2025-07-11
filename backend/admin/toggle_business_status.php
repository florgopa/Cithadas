<?php
session_start();

// Asegurarse de que solo usuarios logueados y con rol 'admin' puedan acceder
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'admin') {
    header("location: ../../index.php?page=home"); // Redirigir a home o a una página de acceso denegado
    exit;
}

// Incluir la conexión a la base de datos
require_once '../../includes/db.php'; // La ruta es correcta (dos niveles arriba desde backend/admin)

// Verificar si la solicitud es POST y si se ha enviado una acción de botón
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action_btn'])) {

    $business_id = $_POST['business_id'] ?? null;
    $current_status = $_POST['current_status'] ?? null;
    $action = $_POST['action_btn'] ?? null; // 'aprobar', 'rechazar', 'activar', 'inactivar'

    if ($business_id && $current_status && $action) {
        $new_status = '';

        // Determinar el nuevo estado basado en la acción y el estado actual
        switch ($action) {
            case 'aprobar':
                if ($current_status === 'pendiente') {
                    $new_status = 'activo';
                }
                break;
            case 'rechazar':
                if ($current_status === 'pendiente') {
                    $new_status = 'inactivo'; // O 'rechazado' si quieres un estado específico
                }
                break;
            case 'activar':
                if ($current_status === 'inactivo' || $current_status === 'rechazado') {
                    $new_status = 'activo';
                }
                break;
            case 'inactivar':
                if ($current_status === 'activo') {
                    $new_status = 'inactivo';
                }
                break;
        }

        if (!empty($new_status)) {
            // Preparar la consulta SQL para actualizar el estado del negocio
            $sql = "UPDATE negocio SET estado = ? WHERE id = ?";

            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("si", $new_status, $business_id); // "s" para string (estado), "i" para integer (id)

                if ($stmt->execute()) {
                    // Éxito al actualizar
                    $_SESSION['status_message'] = "El estado del negocio ha sido actualizado a '" . $new_status . "'.";
                    $_SESSION['status_type'] = 'success';
                } else {
                    // Error al ejecutar la actualización
                    $_SESSION['status_message'] = "Error al actualizar el estado del negocio: " . $stmt->error;
                    $_SESSION['status_type'] = 'error';
                }
                $stmt->close();
            } else {
                // Error al preparar la consulta
                $_SESSION['status_message'] = "Error interno al preparar la actualización: " . $conn->error;
                $_SESSION['status_type'] = 'error';
            }
        } else {
            $_SESSION['status_message'] = "Acción o estado inválido para el negocio.";
            $_SESSION['status_type'] = 'error';
        }

    } else {
        // Faltan parámetros
        $_SESSION['status_message'] = "Parámetros insuficientes para actualizar el estado del negocio.";
        $_SESSION['status_type'] = 'error';
    }

    $conn->close(); // Cerrar la conexión a la base de datos

    // Redirigir de vuelta a la página de gestión de negocios
    header("location: ../../index.php?page=manage_business");
    exit();

} else {
    // Si se accede directamente sin POST o sin la acción del botón
    header("location: ../../index.php?page=manage_business"); // Redirigir de vuelta a la página de gestión
    exit();
}
?>