<?php
// backend/business/process_business_registration.php

session_start();

// Asegurarse de que solo usuarios logueados con rol 'negocio' puedan procesar esto
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'negocio') {
    $_SESSION['status_message'] = "Acceso no autorizado.";
    $_SESSION['status_type'] = "error";
    header("location: ../../index.php?page=home");
    exit;
}

require_once '../../includes/db.php'; // Ajusta la ruta a tu archivo db.php

$user_id = $_SESSION['user_id'];
$business_id = $_POST['business_id'] ?? null; // Si existe, es una edición

// Habilitar errores para depuración - REMOVER EN PRODUCCIÓN
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recolectar y sanear los datos del formulario
    $nombre_negocio = trim($_POST['nombre_negocio'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $provincia = trim($_POST['provincia'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email_negocio = trim($_POST['email_negocio'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $horario_apertura = trim($_POST['horario_apertura'] ?? '');
    $horario_cierre = trim($_POST['horario_cierre'] ?? '');

    // Validaciones básicas (puedes añadir más si es necesario)
    if (empty($nombre_negocio) || empty($direccion)) {
        $_SESSION['status_message'] = "El nombre del negocio y la dirección son obligatorios.";
        $_SESSION['status_type'] = "error";
        header("location: ../../index.php?page=register_business");
        exit;
    }

    if (!filter_var($email_negocio, FILTER_VALIDATE_EMAIL) && !empty($email_negocio)) {
        $_SESSION['status_message'] = "El formato del email del negocio no es válido.";
        $_SESSION['status_type'] = "error";
        header("location: ../../index.php?page=register_business");
        exit;
    }

    // Preparar la consulta SQL
    if ($business_id) {
        // ACTUALIZAR NEGOCIO EXISTENTE
        $sql = "UPDATE negocio SET
                    nombre_negocio = ?,
                    descripcion = ?,
                    direccion = ?,
                    ciudad = ?,
                    provincia = ?,
                    telefono = ?,
                    email_negocio = ?,
                    website = ?,
                    horario_apertura = ?,
                    horario_cierre = ?
                WHERE id = ? AND usuario_id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssssssssii",
                $nombre_negocio, $descripcion, $direccion, $ciudad, $provincia,
                $telefono, $email_negocio, $website, $horario_apertura, $horario_cierre,
                $business_id, $user_id
            );

            if ($stmt->execute()) {
                $_SESSION['status_message'] = "Negocio actualizado correctamente.";
                $_SESSION['status_type'] = "success";
            } else {
                $_SESSION['status_message'] = "Error al actualizar el negocio: " . $stmt->error;
                $_SESSION['status_type'] = "error";
            }
            $stmt->close();
        } else {
            $_SESSION['status_message'] = "Error de preparación de la consulta al actualizar: " . $conn->error;
            $_SESSION['status_type'] = "error";
        }
    } else {
        // INSERTAR NUEVO NEGOCIO
        // Primero, verificar si el usuario ya tiene un negocio para evitar duplicados
        $sql_check_existing = "SELECT id FROM negocio WHERE usuario_id = ?";
        if ($stmt_check = $conn->prepare($sql_check_existing)) {
            $stmt_check->bind_param("i", $user_id);
            $stmt_check->execute();
            $stmt_check->store_result();
            if ($stmt_check->num_rows > 0) {
                $_SESSION['status_message'] = "Ya tienes un negocio registrado. Por favor, edítalo en lugar de crear uno nuevo.";
                $_SESSION['status_type'] = "error";
                $stmt_check->close();
                $conn->close();
                header("location: ../../index.php?page=register_business");
                exit;
            }
            $stmt_check->close();
        } else {
            $_SESSION['status_message'] = "Error al verificar negocios existentes: " . $conn->error;
            $_SESSION['status_type'] = "error";
            $conn->close();
            header("location: ../../index.php?page=register_business");
            exit;
        }


        $sql = "INSERT INTO negocio (
                    usuario_id, nombre_negocio, descripcion, direccion, ciudad, provincia,
                    telefono, email_negocio, website, horario_apertura, horario_cierre, estado, fecha_registro
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'activo', NOW())"; // Estado inicial 'activo'

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("issssssssss",
                $user_id, $nombre_negocio, $descripcion, $direccion, $ciudad, $provincia,
                $telefono, $email_negocio, $website, $horario_apertura, $horario_cierre
            );

            if ($stmt->execute()) {
                $_SESSION['status_message'] = "Negocio registrado correctamente.";
                $_SESSION['status_type'] = "success";
            } else {
                $_SESSION['status_message'] = "Error al registrar el negocio: " . $stmt->error;
                $_SESSION['status_type'] = "error";
            }
            $stmt->close();
        } else {
            $_SESSION['status_message'] = "Error de preparación de la consulta al insertar: " . $conn->error;
            $_SESSION['status_type'] = "error";
        }
    }

    $conn->close();
    header("location: ../../index.php?page=register_business"); // Redirigir de vuelta al formulario
    exit;

} else {
    // Si se accede directamente sin POST
    header("location: ../../index.php?page=register_business");
    exit;
}
?>