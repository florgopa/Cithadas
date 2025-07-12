<?php
session_start();
require_once 'includes/db.php';

// Asegurarse de que el usuario esté logueado como cliente
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'cliente') {
    $_SESSION['status_message'] = "Debes iniciar sesión como cliente para reservar un servicio.";
    $_SESSION['status_type'] = "warning";
    header("location: index.php?page=login");
    exit;
}

$page_title = 'Explorar Servicios Disponibles';
$status_message = $_SESSION['status_message'] ?? '';
$status_type = $_SESSION['status_type'] ?? '';
unset($_SESSION['status_message'], $_SESSION['status_type']);

$sql = "SELECT s.id, s.nombre_servicio, s.descripcion, s.precio, s.duracion_estimada,
               n.id AS negocio_id, n.nombre_negocio
        FROM servicio s
        JOIN negocio n ON s.negocio_id = n.id
        WHERE s.estado = 'activo' AND n.estado = 'activo'
        ORDER BY n.nombre_negocio, s.nombre_servicio";

$result = $conn->query($sql);
include 'includes/header.php';
?>

<div class="container py-4">
    <h2 class="text-center-heading">Explorar Servicios Disponibles</h2>

    <?php if ($status_message): ?>
        <div class="alert <?php echo $status_type; ?>">
            <?php echo htmlspecialchars($status_message); ?>
        </div>
    <?php endif; ?>

    <?php if ($result && $result->num_rows > 0): ?>
        <div class="row">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['nombre_servicio']); ?></h5>
                            <h6 class="card-subtitle mb-2 text-muted"><?php echo htmlspecialchars($row['nombre_negocio']); ?></h6>
                            <p class="card-text"><?php echo htmlspecialchars($row['descripcion']); ?></p>
                            <p><strong>Duración:</strong> <?php echo htmlspecialchars($row['duracion_estimada']); ?></p>
                            <p><strong>Precio:</strong> $<?php echo number_format($row['precio'], 2, ',', '.'); ?></p>
                        </div>
                        <div class="card-footer text-center">
                            <a href="index.php?page=book_appointment&service_id=<?php echo $row['id']; ?>&business_id=<?php echo $row['negocio_id']; ?>" class="btn btn-primary">Reservar Ahora</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>No hay servicios disponibles por el momento.</p>
    <?php endif; ?>
</div>

<?php
$conn->close();
include 'includes/footer.php';
?>
