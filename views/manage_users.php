<?php

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'admin') {
    header("location: index.php?page=home"); // Redirigir a home o a una página de acceso denegado
    exit;
}

require_once 'includes/db.php'; 
$users = [];
$error_message = '';

$status_message = '';
$status_type = '';
if (isset($_SESSION['status_message'])) {
    $status_message = $_SESSION['status_message'];
    $status_type = $_SESSION['status_type'];
    unset($_SESSION['status_message']);
    unset($_SESSION['status_type']);
}

$sql = "SELECT id, nombre, email, rol, estado FROM usuario ORDER BY id ASC";
if ($stmt = $conn->prepare($sql)) {
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    } else {
        $error_message = "No hay usuarios registrados en la base de datos.";
    }
    $stmt->close();
} else {
    $error_message = "Error al preparar la consulta para obtener usuarios: " . $conn->error;
}

$conn->close(); 
?>

<div class="manage-users-container container">
    <h2 class="text-center-heading">Gestionar Usuarios</h2>
    <p class="text-center">Desde aquí podrás ver, buscar, y gestionar el estado (activar/inactivar) de los usuarios.</p>

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

    <div class="user-list">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['rol']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo htmlspecialchars($user['estado']); ?>">
                                    <?php echo htmlspecialchars(ucfirst($user['estado'])); ?>
                                </span>
                            </td>
                            <td>
                                <!-- Importante: La acción del formulario debe ir a un script de backend -->
                                <form action="backend/admin/toggle_user_status.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                                    <input type="hidden" name="current_status" value="<?php echo htmlspecialchars($user['estado']); ?>">
                                    <?php if ($user['estado'] === 'activo'): ?>
                                        <button type="submit" name="toggle_status_btn" class="btn btn-inactivate">Desactivar</button>
                                    <?php else: ?>
                                        <button type="submit" name="toggle_status_btn" class="btn btn-activate">Activar</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No se encontraron usuarios.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


