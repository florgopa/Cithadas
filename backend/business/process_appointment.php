<?php
require_once '../../includes/db.php';

// Verificar login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'cliente') {
    $_SESSION['status_message'] = "Debes iniciar sesión como cliente para reservar.";
    $_SESSION['status_type'] = "error";
    header("Location: ../../index.php?page=login");
    exit;
}

$id_cliente = $_SESSION['user_id'] ?? null;
$service_id = $_POST['service_id'] ?? null;
$fecha_turno = $_POST['fecha_turno'] ?? null;
$hora_turno = $_POST['hora_turno'] ?? null;
$id_profesional = !empty($_POST['id_profesional']) ? $_POST['id_profesional'] : null;

if (!$id_cliente || !$service_id || !$fecha_turno || !$hora_turno) {
    $_SESSION['status_message'] = "Faltan datos obligatorios para completar la reserva.";
    $_SESSION['status_type'] = "error";
    header("Location: ../../index.php?page=book_a_service");
    exit;
}

// Validar domingo
$dia_semana = date('w', strtotime($fecha_turno));
if ($dia_semana == 0) {
    $_SESSION['status_message'] = "No se pueden realizar reservas los domingos.";
    $_SESSION['status_type'] = "error";
    header("Location: ../../index.php?page=book_a_service");
    exit;
}

// Validar horario permitido
$hora_entera = intval(substr($hora_turno, 0, 2));
if ($hora_entera < 9 || $hora_entera > 18) {
    $_SESSION['status_message'] = "El horario seleccionado no es válido. Debe ser entre las 09:00 y 18:00.";
    $_SESSION['status_type'] = "error";
    header("Location: ../../index.php?page=book_a_service");
    exit;
}

// 🚫 Validar que no exista un turno duplicado para este cliente en el mismo día y hora
$check_sql = "SELECT id FROM turno 
              WHERE usuario_id = ? AND fecha_turno = ? AND hora_turno = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("iss", $id_cliente, $fecha_turno, $hora_turno);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows > 0) {
    $_SESSION['status_message'] = "Ya tienes un turno reservado en ese horario.";
    $_SESSION['status_type'] = "warning";
    header("Location: ../../index.php?page=book_a_service");
    $check_stmt->close();
    $conn->close();
    exit;
}
$check_stmt->close();

// ✔ Insertar el turno
$sql = "INSERT INTO turno (usuario_id, servicio_id, profesional_id, fecha_turno, hora_turno, estado, fecha_creacion)
        VALUES (?, ?, ?, ?, ?, 'pendiente', NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiss", $id_cliente, $service_id, $id_profesional, $fecha_turno, $hora_turno);

if ($stmt->execute()) {
    $_SESSION['status_message'] = "¡Tu reserva se realizó con éxito!";
    $_SESSION['status_type'] = "success";
    header("Location: ../../index.php?page=appointments");
} else {
    $_SESSION['status_message'] = "Error al guardar el turno: " . $conn->error;
    $_SESSION['status_type'] = "error";
    header("Location: ../../index.php?page=book_a_service");
}

$stmt->close();
$conn->close();
exit;
