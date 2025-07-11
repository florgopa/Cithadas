<?php
// views/book_a_service.php

session_start(); // Asegúrate de iniciar la sesión

require_once 'includes/db.php'; // Ajusta la ruta a tu archivo db.php

// Manejar mensajes de estado (si vienen de otras páginas)
$status_message = '';
$status_type = '';
if (isset($_SESSION['status_message'])) {
    $status_message = $_SESSION['status_message'];
    $status_type = $_SESSION['status_type'];
    unset($_SESSION['status_message']);
    unset($_SESSION['status_type']);
}

// Obtener todos los servicios activos con su negocio asociado
$sql_services = "SELECT s.id AS service_id, s.nombre_servicio AS service_name, s.descripcion AS service_description, 
                        s.precio, s.duracion_estimada, s.categoria, 
                        n.id AS business_id, n.nombre_negocio AS business_name, n.direccion AS business_address, 
                        n.ciudad AS business_city, n.provincia AS business_province
                 FROM servicio s
                 JOIN negocio n ON s.negocio_id = n.id    /* ¡CORREGIDO AQUÍ: s.negocio_id! */
                 WHERE s.estado = 'activo' AND n.estado = 'activo'
                 ORDER BY n.nombre_negocio, s.nombre_servicio";

$result_services = $conn->query($sql_services);

$services = [];
if ($result_services && $result_services->num_rows > 0) {
    while ($row = $result_services->fetch_assoc()) {
        $services[] = $row;
    }
}
$conn->close();
?>

<div class="container py-4">
    <h2 class="text-center-heading">Explorar Servicios y Reservar</h2>

    <?php if ($status_message): ?>
        <div class="alert <?php echo ($status_type === 'success') ? 'success-message' : (($status_type === 'error') ? 'error-message' : 'info-message'); ?>">
            <?php echo $status_message; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($services)): ?>
        <p class="text-center">Actualmente no hay servicios disponibles para reservar. ¡Vuelve más tarde!</p>
    <?php else: ?>
        <div class="service-list">
            <?php foreach ($services as $service): ?>
                <div class="service-card card mb-4 shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title"><?php echo htmlspecialchars($service['service_name']); ?></h4>
                        <h5 class="card-subtitle mb-2 text-muted">
                            <?php echo htmlspecialchars($service['business_name']); ?> 
                            <small>(<?php echo htmlspecialchars($service['business_city'] . ', ' . $service['business_province']); ?>)</small>
                        </h5>
                        <p class="card-text description-text"><?php echo htmlspecialchars($service['service_description']); ?></p>
                        <ul class="list-unstyled service-details-list">
                            <li><strong>Categoría:</strong> <?php echo htmlspecialchars($service['categoria'] ?? 'N/A'); ?></li>
                            <li><strong>Precio:</strong> $<?php echo htmlspecialchars(number_format($service['precio'], 2, ',', '.')); ?></li>
                            <li><strong>Duración Estimada:</strong> <?php echo htmlspecialchars($service['duracion_estimada']); ?></li>
                        </ul>
                        <a href="index.php?page=book_appointment&service_id=<?php echo htmlspecialchars($service['service_id']); ?>&business_id=<?php echo htmlspecialchars($service['business_id']); ?>" 
                           class="btn btn-primary btn-block">
                            Reservar Ahora
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>