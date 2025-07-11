<?php
// backend/business/process_appointment.php

session_start();

// Habilitar errores para depuración - ¡REMOVER EN PRODUCCIÓN!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Asegurarse de que el usuario esté logueado como cliente
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'cliente') {
    $_SESSION['status_message'] = "Acceso no autorizado. Debes ser un cliente logueado para reservar.";
    $_SESSION['status_type'] = "error";
    header("location: ../../index.php?page=login"); // Ajusta la ruta si es necesario
    exit;
}

require_once '../../includes/db.php'; // Ajusta la ruta a tu archivo db.php

// Obtener ID del cliente logueado
$cliente_id = $_SESSION['user_id'];

// Obtener datos del formulario
$service_id = $_POST['service_id'] ?? null;
$business_id = $_POST['business_id'] ?? null;
$fecha_turno = $_POST['fecha_turno'] ?? '';
$hora_turno = $_POST['hora_turno'] ?? '';
$id_profesional = $_POST['id_profesional'] ?? null; // Puede ser NULL si se eligió "Sin preferencia"
$comentarios = trim($_POST['comentarios'] ?? '');
$duracion_estimada_text = $_POST['duracion_estimada_text'] ?? ''; // La etiqueta de texto de duración

// --- Validaciones iniciales ---
if (!$service_id || !$business_id || empty($fecha_turno) || empty($hora_turno)) {
    $_SESSION['status_message'] = "Todos los campos obligatorios deben ser completados (Servicio, Negocio, Fecha, Hora).";
    $_SESSION['status_type'] = "error";
    header("location: ../../index.php?page=book_appointment&service_id=" . urlencode($service_id) . "&business_id=" . urlencode($business_id));
    exit;
}

// Convertir la fecha y hora a objetos DateTime para validaciones
$appointment_datetime_str = $fecha_turno . ' ' . $hora_turno;
$appointment_datetime = DateTime::createFromFormat('Y-m-d H:i', $appointment_datetime_str);
$current_datetime = new DateTime();

if (!$appointment_datetime || $appointment_datetime < $current_datetime) {
    $_SESSION['status_message'] = "La fecha y hora del turno no son válidas o ya han pasado.";
    $_SESSION['status_type'] = "error";
    header("location: ../../index.php?page=book_appointment&service_id=" . urlencode($service_id) . "&business_id=" . urlencode($business_id));
    exit;
}

// --- Mapeo de duracion_estimada (texto a minutos) para calcular hora_fin ---
$duracion_map = [
    '15-30min'      => 30,  // Tomamos el valor máximo del rango
    '30min-1h'      => 60,
    '2h'            => 120,
    'entre 2h y 3h' => 180,
    'entre 3h y 5h' => 300,
    '+5h'           => 480, // Asumimos un máximo de 8 horas (480 minutos) para '+5h'
];

$duracion_en_minutos = $duracion_map[$duracion_estimada_text] ?? null;

if (is_null($duracion_en_minutos)) {
    $_SESSION['status_message'] = "Error interno: No se pudo determinar la duración del servicio.";
    $_SESSION['status_type'] = "error";
    header("location: ../../index.php?page=book_appointment&service_id=" . urlencode($service_id) . "&business_id=" . urlencode($business_id));
    exit;
}

// Calcular hora_fin
$hora_fin_datetime = clone $appointment_datetime;
$hora_fin_datetime->modify('+' . $duracion_en_minutos . ' minutes');
$hora_fin_str = $hora_fin_datetime->format('H:i:s'); // Formato para la columna TIME de la DB


// --- Verificación de Solapamiento (BÁSICA) ---
$can_book = true;

// Solo verificamos solapamiento si se seleccionó un profesional
if (!empty($id_profesional)) {
    $sql_overlap = "SELECT COUNT(*) FROM turno 
                    WHERE fecha_turno = ? 
                    AND id_profesional = ?
                    AND (
                        (hora_turno < ? AND ADDTIME(hora_turno, SEC_TO_TIME(
                            (SELECT CASE s.duracion_estimada
                                WHEN '15-30min' THEN 30
                                WHEN '30min-1h' THEN 60
                                WHEN '2h' THEN 120
                                WHEN 'entre 2h y 3h' THEN 180
                                WHEN 'entre 3h y 5h' THEN 300
                                WHEN '+5h' THEN 480
                                ELSE 0 END FROM servicio sx WHERE sx.id = turno.id_servicio) * 60
                        )) > ?)
                        OR
                        (? < ADDTIME(hora_turno, SEC_TO_TIME(
                            (SELECT CASE s.duracion_estimada
                                WHEN '15-30min' THEN 30
                                WHEN '30min-1h' THEN 60
                                WHEN '2h' THEN 120
                                WHEN 'entre 2h y 3h' THEN 180
                                WHEN 'entre 3h y 5h' THEN 300
                                WHEN '+5h' THEN 480
                                ELSE 0 END FROM servicio sy WHERE sy.id = turno.id_servicio) * 60
                        )) AND ADDTIME(?, SEC_TO_TIME(? * 60)) > hora_turno)
                    )";

    if ($stmt_overlap = $conn->prepare($sql_overlap)) {
        // Asegúrate de que $id_profesional no sea una cadena vacía si viene de un select option
        $prof_id_for_bind = ($id_profesional === '') ? null : $id_profesional;

        // "sissssis" s=string(fecha_turno), i=int(id_profesional), s=string(hora_fin_str), s=string(hora_turno), s=string(hora_turno), s=string(hora_fin_str), s=string(hora_turno), i=int(duracion_en_minutos)
        $stmt_overlap->bind_param("sissssis", 
            $fecha_turno, 
            $prof_id_for_bind, 
            $hora_fin_str, 
            $hora_turno,   
            $hora_turno,   
            $hora_fin_str, 
            $hora_turno,   
            $duracion_en_minutos 
        );
        $stmt_overlap->execute();
        $result_overlap = $stmt_overlap->get_result();
        $overlap_count = $result_overlap->fetch_row()[0];
        if ($overlap_count > 0) {
            $can_book = false;
        }
        $stmt_overlap->close();
    } else {
        $_SESSION['status_message'] = "Error interno al verificar disponibilidad: " . $conn->error;
        $_SESSION['status_type'] = "error";
        header("location: ../../index.php?page=book_appointment&service_id=" . urlencode($service_id) . "&business_id=" . urlencode($business_id));
        exit;
    }
}
// NOTA: Si no se elige un profesional (id_profesional es NULL), la lógica actual NO verifica solapamientos.
// Esto asume que el negocio puede tener múltiples turnos en paralelo si no se asigna un profesional específico.
// Si tu negocio requiere que incluso los turnos "sin preferencia" no se solapen, necesitarías una lógica de capacidad aquí.


if (!$can_book) {
    $_SESSION['status_message'] = "El turno solicitado se solapa con otro turno ya existente para ese profesional y fecha. Por favor, elige otra hora o profesional.";
    $_SESSION['status_type'] = "error";
    header("location: ../../index.php?page=book_appointment&service_id=" . urlencode($service_id) . "&business_id=" . urlencode($business_id));
    exit;
}


// --- Insertar el turno en la base de datos ---
$estado = 'pendiente'; // Estado inicial del turno
$fecha_creacion = date('Y-m-d H:i:s'); // Fecha y hora actual del sistema

$sql_insert_turno = "INSERT INTO turno (id_id_usuario, id_servicio, id_profesional, fecha_turno, hora_turno, hora_fin, estado, comentarios, fecha_creacion) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

if ($stmt_insert = $conn->prepare($sql_insert_turno)) {
    // Si id_profesional es una cadena vacía (del option value=""), lo convertimos a NULL de PHP para la DB
    $prof_id_param = ($id_profesional === '') ? NULL : $id_profesional;

    // 'iiissssss' -> i para int, s para string. id_profesional es INT, se mapea correctamente con NULL.
    $stmt_insert->bind_param("iiissssss", 
        $cliente_id,
        $service_id,
        $prof_id_param, 
        $fecha_turno,
        $hora_turno,
        $hora_fin_str,
        $estado,
        $comentarios,
        $fecha_creacion
    );

    if ($stmt_insert->execute()) {
        $_SESSION['status_message'] = "¡Turno reservado con éxito! Está pendiente de confirmación.";
        $_SESSION['status_type'] = "success";
        // Redirigir a una página donde el cliente pueda ver sus turnos
        header("location: ../../index.php?page=my_appointments"); 
        exit;
    } else {
        $_SESSION['status_message'] = "Error al reservar el turno: " . $stmt_insert->error;
        $_SESSION['status_type'] = "error";
    }
    $stmt_insert->close();
} else {
    $_SESSION['status_message'] = "Error interno al preparar la reserva: " . $conn->error;
    $_SESSION['status_type'] = "error";
}

$conn->close();

// Si hubo un error y no se redirigió antes, redirigir a la página de reserva original
header("location: ../../index.php?page=book_appointment&service_id=" . urlencode($service_id) . "&business_id=" . urlencode($business_id));
exit;
?>