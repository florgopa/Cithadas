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

<div class="manage-business-container">
    <h2>Gestionar Negocios</h2>
    <p>Desde aquí podrás ver, aprobar/rechazar, y editar la información de los negocios registrados.</p>

    <?php
    // Mostrar mensajes de estado (éxito/error)
    if (isset($_SESSION['status_message'])) {
        $message_class = ($_SESSION['status_type'] === 'success') ? 'success-message' : 'error-message';
        echo '<div class="alert ' . $message_class . '">' . htmlspecialchars($_SESSION['status_message']) . '</div>';
        unset($_SESSION['status_message']); // Limpiar el mensaje después de mostrarlo
        unset($_SESSION['status_type']);     // Limpiar el tipo de mensaje
    }
    ?>

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
                            <td><?php echo htmlspecialchars($business['estado']); ?></td>
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

<style>
    .manage-business-container {
        max-width: 1200px;
        margin: 50px auto;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        background-color: #fff;
    }
    .manage-business-container h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #333;
    }
    .manage-business-container p {
        text-align: center;
        margin-bottom: 30px;
        color: #666;
    }
    .business-list table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        font-size: 0.9em;
    }
    .business-list th, .business-list td {
        border: 1px solid #eee;
        padding: 8px;
        text-align: left;
        vertical-align: middle;
    }
    .business-list th {
        background-color: #f2f2f2;
        font-weight: bold;
        color: #555;
    }
    .business-list tr:nth-child(even) {
        background-color: #f9f9f9;
    }