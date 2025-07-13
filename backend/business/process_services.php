<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'negocio') {
    $_SESSION['status_message'] = "Acceso no autorizado.";
    $_SESSION['status_type'] = "error";
    header("location: ../../index.php?page=home");
    exit;
}

require_once '../../includes/db.php';

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$service_id = $_POST['service_id'] ?? $_GET['service_id'] ?? null;

// Obtener el negocio_id del usuario
$business_id = null;
$sql_get_business_id = "SELECT id FROM negocio WHERE usuario_id = ?";
if ($stmt = $conn->prepare($sql_get_business_id)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($b_id);
    if ($stmt->fetch()) {
        $business_id = $b_id;
    }
    $stmt->close();
}

if (!$business_id) {
    $_SESSION['status_message'] = "Error: No se encontró un negocio asociado a tu cuenta.";
    $_SESSION['status_type'] = "error";
    header("location: ../../index.php?page=dashboard");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_servicio = trim($_POST['nombre_servicio'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $duracion_estimada = trim($_POST['duracion_estimada'] ?? '');
    $categoria = trim($_POST['categoria'] ?? 'Otros');
    $estado = $_POST['estado'] ?? 'activo';

    if (empty($nombre_servicio) || $precio <= 0 || empty($duracion_estimada)) {
        $_SESSION['status_message'] = "El nombre, precio y duración son obligatorios y el precio debe ser mayor que cero.";
        $_SESSION['status_type'] = "error";
        header("location: ../../index.php?page=manage_services" . ($action == 'edit_service' ? "&action=edit&service_id=" . $service_id : ""));
        exit;
    }

    if ($action == 'add_service') {
        $sql = "INSERT INTO servicio (negocio_id, nombre_servicio, descripcion, precio, duracion_estimada, categoria, estado, fecha_creacion) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("issdsss", $business_id, $nombre_servicio, $descripcion, $precio, $duracion_estimada, $categoria, $estado);
            if ($stmt->execute()) {
                $_SESSION['status_message'] = "Servicio añadido correctamente.";
                $_SESSION['status_type'] = "success";
            } else {
                $_SESSION['status_message'] = "Error al añadir el servicio: " . $stmt->error;
                $_SESSION['status_type'] = "error";
            }
            $stmt->close();
        } else {
            $_SESSION['status_message'] = "Error de preparación de la consulta al añadir: " . $conn->error;
            $_SESSION['status_type'] = "error";
        }

        header("location: ../../index.php?page=manage_services");
        exit;
    }

    if ($action == 'edit_service') {
        if (!$service_id) {
            $_SESSION['status_message'] = "ID de servicio no proporcionado para edición.";
            $_SESSION['status_type'] = "error";
            header("location: ../../index.php?page=manage_services");
            exit;
        }

        $sql_check_owner = "SELECT id FROM servicio WHERE id = ? AND negocio_id = ?";
        if ($stmt = $conn->prepare($sql_check_owner)) {
            $stmt->bind_param("ii", $service_id, $business_id);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows === 0) {
                $_SESSION['status_message'] = "No tienes permiso para editar este servicio.";
                $_SESSION['status_type'] = "error";
                $stmt->close();
                header("location: ../../index.php?page=manage_services");
                exit;
            }
            $stmt->close();
        }

        $sql = "UPDATE servicio 
                SET nombre_servicio = ?, descripcion = ?, precio = ?, duracion_estimada = ?, categoria = ?, estado = ?, fecha_actualizacion = NOW() 
                WHERE id = ? AND negocio_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssdssii", $nombre_servicio, $descripcion, $precio, $duracion_estimada, $categoria, $estado, $service_id, $business_id);
            if ($stmt->execute()) {
                $_SESSION['status_message'] = "Servicio actualizado correctamente.";
                $_SESSION['status_type'] = "success";
            } else {
                $_SESSION['status_message'] = "Error al actualizar el servicio: " . $stmt->error;
                $_SESSION['status_type'] = "error";
            }
            $stmt->close();
        }

        header("location: ../../index.php?page=manage_services");
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && $action == 'delete') {
    if (!$service_id) {
        $_SESSION['status_message'] = "ID de servicio no proporcionado para eliminación.";
        $_SESSION['status_type'] = "error";
        header("location: ../../index.php?page=manage_services");
        exit;
    }

    $sql_check_owner = "SELECT id FROM servicio WHERE id = ? AND negocio_id = ?";
    if ($stmt = $conn->prepare($sql_check_owner)) {
        $stmt->bind_param("ii", $service_id, $business_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) {
            $_SESSION['status_message'] = "No tienes permiso para eliminar este servicio.";
            $_SESSION['status_type'] = "error";
            $stmt->close();
            header("location: ../../index.php?page=manage_services");
            exit;
        }
        $stmt->close();
    }

    $sql = "DELETE FROM servicio WHERE id = ? AND negocio_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $service_id, $business_id);
        if ($stmt->execute()) {
            $_SESSION['status_message'] = "Servicio eliminado correctamente.";
            $_SESSION['status_type'] = "success";
        } else {
            $_SESSION['status_message'] = "Error al eliminar el servicio: " . $stmt->error;
            $_SESSION['status_type'] = "error";
        }
        $stmt->close();
    }
}

$conn->close();
header("location: ../../index.php?page=manage_services");
exit;
?>
