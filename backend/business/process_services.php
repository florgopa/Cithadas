<?php
// backend/business/process_services.php

session_start();

// Habilitar errores para depuración - ¡REMOVER EN PRODUCCIÓN!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Asegurarse de que solo usuarios logueados con rol 'negocio' puedan procesar esto
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'negocio') {
    $_SESSION['status_message'] = "Acceso no autorizado.";
    $_SESSION['status_type'] = "error";
    header("location: ../../index.php?page=home");
    exit;
}

require_once '../../includes/db.php'; // Ajusta la ruta a tu archivo db.php

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? ''; // Determina la acción (add, edit, delete)
$service_id = $_POST['service_id'] ?? $_GET['service_id'] ?? null; // ID del servicio si es edición/eliminación

// Obtener el negocio_id del usuario logueado
$business_id = null;
$sql_get_business_id = "SELECT id FROM negocio WHERE usuario_id = ?";
if ($stmt_get_business_id = $conn->prepare($sql_get_business_id)) {
    $stmt_get_business_id->bind_param("i", $user_id);
    $stmt_get_business_id->execute();
    $stmt_get_business_id->bind_result($b_id);
    if ($stmt_get_business_id->fetch()) {
        $business_id = $b_id;
    }
    $stmt_get_business_id->close();
}

if (!$business_id) {
    $_SESSION['status_message'] = "Error: No se encontró un negocio asociado a tu cuenta.";
    $_SESSION['status_type'] = "error";
    header("location: ../../index.php?page=dashboard"); // O redirigir a register_business
    exit;
}

// Lógica para añadir o editar servicio (POST request)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_servicio = trim($_POST['nombre_servicio'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0); // Convertir a float
    $duracion_estimada = trim($_POST['duracion_estimada'] ?? '');
    $estado = $_POST['estado'] ?? 'activo';

    // Validaciones básicas
    if (empty($nombre_servicio) || $precio <= 0 || empty($duracion_estimada)) {
        $_SESSION['status_message'] = "El nombre, precio y duración son obligatorios y el precio debe ser mayor que cero.";
        $_SESSION['status_type'] = "error";
        header("location: ../../index.php?page=manage_services" . ($action == 'edit_service' ? "&action=edit&service_id=" . $service_id : ""));
        exit;
    }

    if ($action == 'add_service') {
        // Añadir nuevo servicio
        $sql = "INSERT INTO servicio (negocio_id, nombre_servicio, descripcion, precio, duracion_estimada, estado, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("isssss", $business_id, $nombre_servicio, $descripcion, $precio, $duracion_estimada, $estado);
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
    } elseif ($action == 'edit_service') {
        // Editar servicio existente
        if (!$service_id) {
            $_SESSION['status_message'] = "ID de servicio no proporcionado para edición.";
            $_SESSION['status_type'] = "error";
            header("location: ../../index.php?page=manage_services");
            exit;
        }

        // Asegurarse de que el servicio pertenezca a este negocio antes de editar
        $sql_check_owner = "SELECT id FROM servicio WHERE id = ? AND negocio_id = ?";
        if ($stmt_check = $conn->prepare($sql_check_owner)) {
            $stmt_check->bind_param("ii", $service_id, $business_id);
            $stmt_check->execute();
            $stmt_check->store_result();
            if ($stmt_check->num_rows === 0) {
                $_SESSION['status_message'] = "No tienes permiso para editar este servicio.";
                $_SESSION['status_type'] = "error";
                $stmt_check->close();
                header("location: ../../index.php?page=manage_services");
                exit;
            }
            $stmt_check->close();
        } else {
            $_SESSION['status_message'] = "Error al verificar la propiedad del servicio: " . $conn->error;
            $_SESSION['status_type'] = "error";
            header("location: ../../index.php?page=manage_services");
            exit;
        }


        $sql = "UPDATE servicio SET nombre_servicio = ?, descripcion = ?, precio = ?, duracion_estimada = ?, estado = ?, fecha_actualizacion = NOW() WHERE id = ? AND negocio_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssssii", $nombre_servicio, $descripcion, $precio, $duracion_estimada, $estado, $service_id, $business_id);
            if ($stmt->execute()) {
                $_SESSION['status_message'] = "Servicio actualizado correctamente.";
                $_SESSION['status_type'] = "success";
            } else {
                $_SESSION['status_message'] = "Error al actualizar el servicio: " . $stmt->error;
                $_SESSION['status_type'] = "error";
            }
            $stmt->close();
        } else {
            $_SESSION['status_message'] = "Error de preparación de la consulta al actualizar: " . $conn->error;
            $_SESSION['status_type'] = "error";
        }
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "GET" && $action == 'delete') {
    // Lógica para eliminar servicio (GET request desde el enlace "Eliminar")
    if (!$service_id) {
        $_SESSION['status_message'] = "ID de servicio no proporcionado para eliminación.";
        $_SESSION['status_type'] = "error";
        header("location: ../../index.php?page=manage_services");
        exit;
    }

    // Asegurarse de que el servicio pertenezca a este negocio antes de eliminar
    $sql_check_owner = "SELECT id FROM servicio WHERE id = ? AND negocio_id = ?";
    if ($stmt_check = $conn->prepare($sql_check_owner)) {
        $stmt_check->bind_param("ii", $service_id, $business_id);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows === 0) {
            $_SESSION['status_message'] = "No tienes permiso para eliminar este servicio.";
            $_SESSION['status_type'] = "error";
            $stmt_check->close();
            header("location: ../../index.php?page=manage_services");
            exit;
        }
        $stmt_check->close();
    } else {
        $_SESSION['status_message'] = "Error al verificar la propiedad del servicio para eliminar: " . $conn->error;
        $_SESSION['status_type'] = "error";
        header("location: ../../index.php?page=manage_services");
        exit;
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
    } else {
        $_SESSION['status_message'] = "Error de preparación de la consulta al eliminar: " . $conn->error;
        $_SESSION['status_type'] = "error";
    }
}

$conn->close();
header("location: ../../index.php?page=manage_services"); // Redirigir siempre de vuelta al formulario de gestión de servicios
exit;
?>