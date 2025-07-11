<?php
// views/book_appointment.php

// Asegurarse de que el usuario esté logueado como cliente para poder reservar
// Asumo que tu login establece $_SESSION['loggedin'] y $_SESSION['user_role']
session_start(); // Asegúrate de iniciar la sesión si no lo haces en el index.php antes de incluir vistas
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'cliente') {
    $_SESSION['status_message'] = "Debes iniciar sesión como cliente para reservar un servicio.";
    $_SESSION['status_type'] = "warning";
    header("location: index.php?page=login"); // Redirigir al login
    exit;
}

require_once 'includes/db.php'; // Incluye la conexión a la base de datos

$service_id = $_GET['service_id'] ?? null;
$business_id = $_GET['business_id'] ?? null;

$service_details = null;
$business_details = null;
$professional_options = []; // Para el select de profesionales

// --- Validar y obtener detalles del servicio y negocio ---
if ($service_id && $business_id) {
    // Obtener detalles del servicio
    $sql_service = "SELECT s.id, s.nombre, s.descripcion, s.precio, s.duracion_estimada, n.id AS negocio_id, n.nombre AS negocio_nombre, n.descripcion AS negocio_descripcion
                    FROM servicio s
                    JOIN negocio n ON s.id_negocio = n.id
                    WHERE s.id = ? AND n.id = ? AND s.estado = 'activo' AND n.estado = 'activo'";
    if ($stmt_service = $conn->prepare($sql_service)) {
        $stmt_service->bind_param("ii", $service_id, $business_id);
        $stmt_service->execute();
        $result_service = $stmt_service->get_result();
        if ($result_service->num_rows == 1) {
            $service_details = $result_service->fetch_assoc();
            $business_details = [
                'id' => $service_details['negocio_id'],
                'nombre' => $service_details['negocio_nombre'],
                'descripcion' => $service_details['negocio_descripcion']
            ];
        }
        $stmt_service->close();
    }

    // --- Obtener profesionales asociados a este negocio de la tabla 'profesional' ---
    if ($business_details) {
        $sql_professionals = "SELECT p.id, p.nombre_profesional
                              FROM profesional p
                              WHERE p.negocio_id = ? AND p.estado = 'activo'
                              ORDER BY p.nombre_profesional ASC";
        if ($stmt_professionals = $conn->prepare($sql_professionals)) {
            $stmt_professionals->bind_param("i", $business_details['id']);
            $stmt_professionals->execute();
            $result_professionals = $stmt_professionals->get_result();
            while ($prof_row = $result_professionals->fetch_assoc()) {
                $professional_options[$prof_row['id']] = $prof_row['nombre_profesional'];
            }
            $stmt_professionals->close();
        }
    }

}

// Si no se encontró el servicio o el negocio, redirigir
if (!$service_details) {
    $_SESSION['status_message'] = "Servicio o negocio no válido para la reserva.";
    $_SESSION['status_type'] = "error";
    header("location: index.php?page=book_a_service"); // Redirigir de vuelta a la exploración
    exit;
}

$conn->close();

// Manejar mensajes de estado (si vienen de process_appointment.php)
$status_message = '';
$status_type = '';
if (isset($_SESSION['status_message'])) {
    $status_message = $_SESSION['status_message'];
    $status_type = $_SESSION['status_type'];
    unset($_SESSION['status_message']);
    unset($_SESSION['status_type']);
}

// Obtener la fecha actual para el atributo min del input date (no permite elegir fechas pasadas)
$today = date('Y-m-d');
?>

<div class="container py-4">
    <h2 class="text-center-heading">Reservar Servicio</h2>

    <?php if ($status_message): ?>
        <div class="alert <?php echo ($status_type === 'success') ? 'success-message' : (($status_type === 'error') ? 'error-message' : 'info-message'); ?>">
            <?php echo $status_message; ?>
        </div>
    <?php endif; ?>

    <div class="booking-summary mb-4 p-3 rounded shadow-sm">
        <h4>Detalles de tu Reserva:</h4>
        <p><strong>Negocio:</strong> <?php echo htmlspecialchars($business_details['nombre']); ?></p>
        <p><strong>Servicio:</strong> <?php echo htmlspecialchars($service_details['nombre']); ?></p>
        <p><strong>Descripción:</strong> <?php echo htmlspecialchars($service_details['descripcion']); ?></p>
        <p><strong>Precio:</strong> $<?php echo htmlspecialchars(number_format($service_details['precio'], 2, ',', '.')); ?></p>
        <p><strong>Duración Estimada:</strong> <?php echo htmlspecialchars($service_details['duracion_estimada']); ?></p>
    </div>

    <form action="backend/business/process_appointment.php" method="POST" class="booking-form">
        <input type="hidden" name="service_id" value="<?php echo htmlspecialchars($service_details['id']); ?>">
        <input type="hidden" name="business_id" value="<?php echo htmlspecialchars($business_details['id']); ?>">
        <input type="hidden" name="duracion_estimada_text" value="<?php echo htmlspecialchars($service_details['duracion_estimada']); ?>">

        <div class="form-group">
            <label for="fecha_turno">Fecha del Turno <span class="required-field">*</span></label>
            <input type="date" id="fecha_turno" name="fecha_turno" class="form-control" required min="<?php echo $today; ?>">
            <small class="form-hint">Selecciona una fecha en el futuro.</small>
        </div>

        <div class="form-group">
            <label for="hora_turno">Hora de Inicio del Turno <span class="required-field">*</span></label>
            <input type="time" id="hora_turno" name="hora_turno" class="form-control" required>
            <small class="form-hint">Ingresa la hora en formato HH:MM (ej. 09:30, 14:00).</small>
        </div>

        <div class="form-group">
            <label for="id_profesional">Seleccionar Profesional (Opcional)</label>
            <select id="id_profesional" name="id_profesional" class="form-control">
                <option value="">Sin preferencia / Cualquiera</option>
                <?php foreach ($professional_options as $id => $name): ?>
                    <option value="<?php echo htmlspecialchars($id); ?>">
                        <?php echo htmlspecialchars($name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="form-hint">Puedes elegir un profesional específico o dejarlo sin asignar.</small>
        </div>

        <div class="form-group">
            <label for="comentarios">Comentarios Adicionales</label>
            <textarea id="comentarios" name="comentarios" rows="3" class="form-control" placeholder="Ej: alergias, preferencias, etc."></textarea>
        </div>

        <button type="submit" class="btn btn-success btn-block">Confirmar Reserva</button>
        <a href="index.php?page=book_a_service" class="btn btn-secondary btn-block">Volver a Explorar Servicios</a>
    </form>
</div>

