<?php
session_start();

// solo usuarios logueados y rol 'admin' pueden acceder
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'admin') {
    header("location: ../../index.php?page=home"); // Redirigir a home o a una página de acceso denegado
    exit;
}

require_once '../../includes/db.php'; 

// Verificar solicitud POST 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action_btn'])) {

    $business_id = $_POST['business_id'] ?? null;
    $current_status = $_POST['current_status'] ?? null;
    $action = $_POST['action_btn'] ?? null; 

    if ($business_id && $current_status && $action) {
        $new_status = '';

        // Determinar el nuevo estado 
        switch ($action) {
            case 'aprobar':
                if ($current_status === 'pendiente') {
                    $new_status = 'activo';
                }
                break;
            case 'rechazar':
                if ($current_status === 'pendiente') {
                    $new_status = 'inactivo'; 
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
                $stmt->bind_param("si", $new_status, $business_id);

                if ($stmt->execute()) {
                   
                    $_SESSION['status_message'] = "El estado del negocio ha sido actualizado a '" . $new_status . "'.";
                    $_SESSION['status_type'] = 'success';
                } else {
                    
                    $_SESSION['status_message'] = "Error al actualizar el estado del negocio: " . $stmt->error;
                    $_SESSION['status_type'] = 'error';
                }
                $stmt->close();
            } else {
                
                $_SESSION['status_message'] = "Error interno al preparar la actualización: " . $conn->error;
                $_SESSION['status_type'] = 'error';
            }
        } else {
            $_SESSION['status_message'] = "Acción o estado inválido para el negocio.";
            $_SESSION['status_type'] = 'error';
        }

    } else {
        
        $_SESSION['status_message'] = "Parámetros insuficientes para actualizar el estado del negocio.";
        $_SESSION['status_type'] = 'error';
    }

    $conn->close(); 

    header("location: ../../index.php?page=manage_business");
    exit();

} else {
    header("location: ../../index.php?page=manage_business"); 
    exit();
}
?>