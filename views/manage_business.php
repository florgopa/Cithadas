<?php
// views/manage_business.php

// Asegurarse de que solo usuarios logueados y con rol 'admin' puedan acceder
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'admin') {
    header("location: index.php?page=home"); // Redirigir a home o a una página de acceso denegado
    exit;
}

// Incluir la conexión a la base de datos
require_once 'includes/db.php';

$businesses = [];
$error_message = '';

// --- Manejo de mensajes de estado de la sesión (ej. después de aprobar/rechazar) ---
$status_message = '';
$status_type = '';
if (isset($_SESSION['status_message'])) {
    $status_message = $_SESSION['status_message'];
    $status_type = $_SESSION['status_type'];
    unset($_SESSION['status_message']);
    unset($_SESSION['status_type']);
}
// --- Fin manejo de mensajes ---

// Obtener todos los negocios de la base de datos
// También obtenemos el nombre del usuario_id para saber quién es el dueño
$sql = "SELECT n.id, n.nombre_negocio, n.direccion, n.telefono, n.email_negocio, n.estado, u.nombre AS nombre_usuario, u.email AS email_usuario
            FROM negocio n
            JOIN usuario u ON n.usuario_id = u.id
            ORDER BY n.fecha_registro DESC";

if ($stmt = $conn->prepare($sql)) {
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $businesses[] = $row;
        }
    } else {
        $error_message = "No hay negocios registrados en la base de datos.";
    }
    $stmt->close();
} else {
    $error_message = "Error al preparar la consulta para obtener negocios: " . $conn->error;
}

$conn->close(); // Cerrar la conexión a la base de datos
?>

<div class="manage-business-container container">
    <h2 class="text-center-heading">Gestionar Negocios</h2>
    <p class="text-center">Desde aquí podrás ver, aprobar/rechazar, y editar la información de los negocios registrados.</p>

    <?php if ($status_message):
        $alert_class = ($status_type === 'success') ? 'success-message' : (($status_type === 'error') ? 'error-message' : 'info-message');
    ?>
        <div class="alert <?php echo $alert_class; ?>">
            <?php echo htmlspecialchars($status_message); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="alert error-message"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <div class="business-list">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre del Negocio</th>
                    <th>Dueño (Usuario)</th>
                    <th>Email Negocio</th>
                    <th>Dirección</th>
                    <th>Teléfono</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($businesses)): ?>
                    <?php foreach ($businesses as $business): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($business['id']); ?></td>
                            <td><?php echo htmlspecialchars($business['nombre_negocio']); ?></td>
                            <td><?php echo htmlspecialchars($business['nombre_usuario']); ?> (<?php echo htmlspecialchars($business['email_usuario']); ?>)</td>
                            <td><?php echo htmlspecialchars($business['email_negocio']); ?></td>
                            <td><?php echo htmlspecialchars($business['direccion']); ?></td>
                            <td><?php echo htmlspecialchars($business['telefono']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo htmlspecialchars($business['estado']); ?>">
                                    <?php echo htmlspecialchars(ucfirst($business['estado'])); ?>
                                </span>
                            </td>
                            <td>
                                <form action="backend/admin/toggle_business_status.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="business_id" value="<?php echo htmlspecialchars($business['id']); ?>">
                                    <input type="hidden" name="current_status" value="<?php echo htmlspecialchars($business['estado']); ?>">
                                    <?php if ($business['estado'] === 'pendiente'): ?>
                                        <button type="submit" name="action_btn" value="aprobar" class="btn-approve">Aprobar</button>
                                        <button type="submit" name="action_btn" value="rechazar" class="btn-reject">Rechazar</button>
                                    <?php elseif ($business['estado'] === 'activo'): ?>
                                        <button type="submit" name="action_btn" value="inactivar" class="btn-inactivate">Inactivar</button>
                                    <?php elseif ($business['estado'] === 'inactivo' || $business['estado'] === 'rechazado'): ?>
                                        <button type="submit" name="action_btn" value="activar" class="btn-activate">Activar</button>
                                    <?php endif; ?>
                                </form>
                                <a href="index.php?page=edit_business&id=<?php echo htmlspecialchars($business['id']); ?>" class="btn-edit">Editar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">No se encontraron negocios.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
