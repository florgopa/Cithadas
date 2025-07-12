<?php

error_reporting(E_ALL); ini_set('display_errors', 1)

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'cliente') {
    $_SESSION['status_message'] = "Debes iniciar sesión como cliente para reservar un servicio.";
    $_SESSION['status_type'] = "warning";
    header("location: index.php?page=login");
    exit;
}

require_once 'includes/db.php';

$service_id = $_GET['service_id'] ?? null;
$business_id = $_GET['business_id'] ?? null;

$service_details = null;
$business_details = null;
$professional_options = [];

if ($service_id && $business_id) {
    $sql_service = "SELECT s.id, s.nombre_servicio AS nombre, s.descripcion, s.precio, s.duracion_estimada, 
                    n.id AS negocio_id, n.nombre_negocio AS negocio_nombre, n.descripcion AS negocio_descripcion
                    FROM servicio s
                    JOIN negocio n ON s.negocio_id = n.id
                    WHERE s.id = ? AND n.id = ? AND s.estado = 'activo' AND n.estado = 'activo'";

    if ($stmt_service = $conn->prepare($sql_service)) {
        $stmt_service->bind_param("ii", $service_id, $business_id);
        $stmt_service->execute();
        $result_service = $stmt_service->get_result();
        if ($result_service->num_rows === 1) {
            $service_details = $result_service->fetch_assoc();
            $business_details = [
                'id' => $service_details['negocio_id'],
                'nombre' => $service_details['negocio_nombre'],
                'descripcion' => $service_details['negocio_descripcion']
            ];
        }
        $stmt_service->close();
    }

    if ($business_details) {
        $sql_professionals = "SELECT p.id, p.nombre FROM profesional p WHERE p.negocio_id = ? ORDER BY p.nombre ASC";
        if ($stmt_professionals = $conn->prepare($sql_professionals)) {
            $stmt_professionals->bind_param("i", $business_details['id']);
            $stmt_professionals->execute();
            $result_professionals = $stmt_professionals->get_result();
            while ($row = $result_professionals->fetch_assoc()) {
                $professional_options[$row['id']] = $row['nombre'];
            }
            $stmt_professionals->close();
        }
    }
}

if (!$service_details) {
    $_SESSION['status_message'] = "Servicio o negocio no válido.";
    $_SESSION['status_type'] = "error";
    header("location: index.php?page=book_a_service");
    exit;
}

$conn->close();

$status_message = $_SESSION['status_message'] ?? '';
$status_type = $_SESSION['status_type'] ?? '';
unset($_SESSION['status_message'], $_SESSION['status_type']);

$today = date('Y-m-d');
?>

<div class="container py-4">
    <h2 class="text-center-heading">Reservar Servicio</h2>

    <?php if ($status_message): ?>
        <div class="alert <?php echo $status_type; ?>">
            <?php echo htmlspecialchars($status_message); ?>
        </div>
    <?php endif; ?>

    <div class="booking-summary mb-4 p-3 rounded shadow-sm">
        <h4>Detalles de tu Reserva:</h4>
        <p><strong>Negocio:</strong> <?php echo htmlspecialchars($business_details['nombre']); ?></p>
        <p><strong>Servicio:</strong> <?php echo htmlspecialchars($service_details['nombre']); ?></p>
        <p><strong>Descripción:</strong> <?php echo htmlspecialchars($service_details['descripcion']); ?></p>
        <p><strong>Precio:</strong> $<?php echo number_format($service_details['precio'], 2, ',', '.'); ?></p>
        <p><strong>Duración Estimada:</strong> <?php echo htmlspecialchars($service_details['duracion_estimada']); ?></p>
    </div>

    <form action="backend/business/process_appointment.php" method="POST">
        <input type="hidden" name="service_id" value="<?php echo htmlspecialchars($service_details['id']); ?>">
        <input type="hidden" name="business_id" value="<?php echo htmlspecialchars($business_details['id']); ?>">
        <input type="hidden" name="duracion_estimada_text" value="<?php echo htmlspecialchars($service_details['duracion_estimada']); ?>">

        <div class="form-group">
            <label for="fecha_turno">Fecha del Turno *</label>
            <input type="date" name="fecha_turno" id="fecha_turno" required min="<?php echo $today; ?>">
        </div>

        <div class="form-group">
            <label for="hora_turno">Hora *</label>
            <input type="time" name="hora_turno" id="hora_turno" required>
        </div>

        <div class="form-group">
            <label for="id_profesional">Profesional (opcional)</label>
            <select name="id_profesional" id="id_profesional">
                <option value="">Cualquiera</option>
                <?php foreach ($professional_options as $id => $name): ?>
                    <option value="<?php echo htmlspecialchars($id); ?>"><?php echo htmlspecialchars($name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="comentarios">Comentarios</label>
            <textarea name="comentarios" id="comentarios" rows="3" placeholder="Ej: preferencias, alergias..."></textarea>
        </div>

        <button type="submit" class="btn btn-success">Confirmar Reserva</button>
        <a href="index.php?page=book_a_service" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
