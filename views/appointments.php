<?php
// views/appointments.php

session_start();

require_once 'includes/db.php';

// Verificar si el usuario está logueado y es cliente
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'cliente') {
    header("Location: index.php?page=login");
    exit;
}

$user_id = $_SESSION['user_id'];
$page = 'appointments';
$page_title = 'Mis Turnos';

// Traer los turnos del usuario
$sql = "SELECT t.fecha_turno, t.hora_turno, t.estado, 
               s.nombre_servicio, n.nombre_negocio, n.ciudad, n.provincia
        FROM turno t
        JOIN servicio s ON t.servicio_id = s.id
        JOIN negocio n ON s.negocio_id = n.id
        WHERE t.usuario_id = ?
        ORDER BY t.fecha_turno DESC, t.hora_turno DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}

$stmt->close();
$conn->close();

// Incluir encabezado
require_once 'includes/header.php';
?>

<div class="appointments-container container">
    <h2 class="text-center-heading">Mis Turnos Reservados</h2>

    <?php if (empty($appointments)): ?>
        <p class="no-appointments-message">Todavía no realizaste ninguna reserva.</p>
    <?php else: ?>
        <div class="appointments-list">
            <?php foreach ($appointments as $appt): ?>
                <div class="appointment-card">
                    <h4><?php echo htmlspecialchars($appt['nombre_servicio']); ?></h4>
                    <p><strong>Negocio:</strong> <?php echo htmlspecialchars($appt['nombre_negocio']); ?> (<?php echo htmlspecialchars($appt['ciudad'] . ', ' . $appt['provincia']); ?>)</p>
                    <p><strong>Fecha:</strong> <?php echo htmlspecialchars(date('d/m/Y', strtotime($appt['fecha_turno']))); ?></p>
                    <p><strong>Hora:</strong> <?php echo htmlspecialchars($appt['hora_turno']); ?></p>
                    <p><strong>Estado:</strong> <?php echo htmlspecialchars(ucfirst($appt['estado'])); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
