<?php

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

  <form action="backend/business/process_appointment.php" method="post" id="formReserva">

  <!-- Campos ocultos obligatorios -->
  <input type="hidden" name="service_id" value="<?php echo htmlspecialchars($service_id); ?>">
  <input type="hidden" name="business_id" value="<?php echo htmlspecialchars($business_id); ?>">

  <div class="mb-3">
    <label for="fecha_turno" class="form-label">Fecha del Turno *</label>
    <input type="date" id="fecha_turno" name="fecha_turno" class="form-control" required>
  </div>

  <div class="mb-3">
    <label for="hora_turno" class="form-label">Hora *</label>
    <select id="hora_turno" name="hora_turno" class="form-control" required disabled>
      <option value="">-- Selecciona una hora --</option>
      <option value="10:00">10:00</option>
      <option value="14:00">14:00</option>
      <option value="18:00">18:00</option>
    </select>
  </div>

  <div class="mb-3">
    <label for="id_profesional" class="form-label">Profesional (opcional)</label>
    <select name="id_profesional" class="form-control">
      <option value="">Cualquiera</option>
      <?php foreach ($professional_options as $id => $nombre): ?>
        <option value="<?= htmlspecialchars($id) ?>"><?= htmlspecialchars($nombre) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="mb-3">
    <label for="comentarios" class="form-label">Comentarios</label>
    <textarea name="comentarios" class="form-control" placeholder="Ej: preferencias, alergias..."></textarea>
  </div>

  <div class="text-center mt-4">
    <button type="submit" class="btn btn-success" id="btnConfirmar" disabled>Confirmar Reserva</button>
    <a href="index.php?page=book_a_service" class="btn btn-secondary ms-2">Cancelar</a>
  </div>
</form>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const fechaInput = document.getElementById("fecha_turno");
  const horaSelect = document.getElementById("hora_turno");
  const btnConfirmar = document.getElementById("btnConfirmar");

  // Setear fecha mínima a hoy
  const hoy = new Date().toISOString().split("T")[0];
  fechaInput.setAttribute("min", hoy);

  fechaInput.addEventListener("change", () => {
    const fecha = new Date(fechaInput.value);
    const esDomingo = fecha.getDay() === 0;

    if (esDomingo) {
      alert("No se pueden reservar turnos los domingos.");
      fechaInput.value = "";
      horaSelect.disabled = true;
      btnConfirmar.disabled = true;
    } else {
      horaSelect.disabled = false;
    }
  });

  horaSelect.addEventListener("change", () => {
    btnConfirmar.disabled = !(fechaInput.value && horaSelect.value);
  });
});
</script>
