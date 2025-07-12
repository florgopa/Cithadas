<?php
// views/search_results.php
session_start();

$page = 'book_a_service'; // Esto hará que se aplique el CSS de book_a_service.css
$page_title = 'Resultados de Búsqueda';

require_once 'includes/db.php';

// Obtener el término de búsqueda
$query = $_GET['query'] ?? '';
$services = [];

$status_message = '';
$status_type = '';

// Si hay algo para buscar
if (!empty($query)) {
    $search_term = "%" . $query . "%";

    $sql = "SELECT s.id AS service_id, s.nombre_servicio AS service_name, s.descripcion AS service_description,
                   s.precio, s.duracion_estimada, s.categoria,
                   n.id AS business_id, n.nombre_negocio AS business_name, n.direccion AS business_address,
                   n.ciudad AS business_city, n.provincia AS business_province
            FROM servicio s
            JOIN negocio n ON s.negocio_id = n.id
            WHERE s.estado = 'activo' AND n.estado = 'activo'
              AND (
                  s.nombre_servicio LIKE ? OR
                  n.nombre_negocio LIKE ? OR
                  n.ciudad LIKE ? OR
                  n.provincia LIKE ? OR
                  s.categoria LIKE ?
              )
            ORDER BY n.nombre_negocio, s.nombre_servicio";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssss", $search_term, $search_term, $search_term, $search_term, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $services[] = $row;
        }

        $stmt->close();
    } else {
        $status_message = "Error al preparar la consulta: " . $conn->error;
        $status_type = "error";
    }
}

$conn->close();

?>

<div class="book-service-container container">
    <h2 class="text-center-heading">Resultados de búsqueda para: "<?php echo htmlspecialchars($query); ?>"</h2>

    <?php if ($status_message): ?>
        <div class="alert <?php echo ($status_type === 'success') ? 'success-message' : (($status_type === 'error') ? 'error-message' : 'info-message'); ?>">
            <?php echo htmlspecialchars($status_message); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($services)): ?>
        <p class="no-services-message">No se encontraron servicios que coincidan con tu búsqueda. Intenta con otro término.</p>
    <?php else: ?>
        <div class="service-list-grid">
            <?php foreach ($services as $service): ?>
                <div class="service-card">
                    <div class="card-content">
                        <h4 class="card-title"><?php echo htmlspecialchars($service['service_name']); ?></h4>
                        <h5 class="card-subtitle">
                            <?php echo htmlspecialchars($service['business_name']); ?>
                            <small>(<?php echo htmlspecialchars($service['business_city'] . ', ' . $service['business_province']); ?>)</small>
                        </h5>
                        <p class="card-text description-text"><?php echo htmlspecialchars($service['service_description']); ?></p>
                        <ul class="service-details-list">
                            <li><strong>Categoría:</strong> <?php echo htmlspecialchars($service['categoria'] ?? 'N/A'); ?></li>
                            <li><strong>Precio:</strong> $<?php echo htmlspecialchars(number_format($service['precio'], 2, ',', '.')); ?></li>
                            <li><strong>Duración Estimada:</strong> <?php echo htmlspecialchars($service['duracion_estimada']); ?></li>
                        </ul>
                    </div>
                    <div class="card-actions">
                        <a href="index.php?page=book_appointment&service_id=<?php echo htmlspecialchars($service['service_id']); ?>&business_id=<?php echo htmlspecialchars($service['business_id']); ?>"
                           class="btn-book-service">
                            Reservar Ahora
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
