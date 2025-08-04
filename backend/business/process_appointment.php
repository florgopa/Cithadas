<?php
session_start();
require_once '../../includes/db.php';

// Verificar que esté logueado como cliente
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'cliente') {
    $_SESSION['status_message'] = "Debes iniciar sesión como cliente para reservar un turno.";
    $_SESSION['status_type'] = "error";
    header("Location: ../../index.php?page=login");
    exit;
}

$usuario_id = $_SESSION['user_id'] ?? null;

// Validar datos del formulario
$service_id = $_POST['service_id'] ?? null;
$business_id = $_POST['business_id'] ?? null;
$fecha_turno = $_POST['fecha_turno'] ?? null;
$hora_turno = $_POST['hora_turno'] ?? null;
$id_profesional = !empty($_POST['id_profesional']) ? $_POST['id_profesional'] : null;
$comentarios = $_POST['comentarios'] ?? null;

// Validación básica
if (!$usuario_id || !$service_id || !$fecha_turno || !$hora_turno) {
    $_SESSION['status_message'] = "Faltan datos obligatorios para reservar.";
    $_SESSION['status_type'] = "error";
    header("Location: ../../index.php?page=book_appointment&service_id=$service_id&business_id=$business_id");
    exit;
}

// Insertar turno
$sql = "INSERT INTO turno (usuario_id, servicio_id, profesional_id, fecha_turno, hora_turno, estado)
        VALUES (?, ?, ?, ?, ?, 'pendiente')";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiss", $usuario_id, $service_id, $id_profesional, $fecha_turno, $hora_turno);

if ($stmt->execute()) {
    $_SESSION['status_message'] = "¡Turno reservado exitosamente!";
    $_SESSION['status_type'] = "success";
    header("Location: ../../index.php?page=appointments");
} else {
    $_SESSION['status_message'] = "Error al reservar el turno.";
    $_SESSION['status_type'] = "error";
    header("Location: ../../index.php?page=book_appointment&service_id=$service_id&business_id=$business_id");
}

$stmt->close();
$conn->close();
exit;
