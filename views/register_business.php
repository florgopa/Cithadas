<?php
// views/register_business.php

// Asegurarse de que solo usuarios logueados con rol 'negocio' puedan acceder
// Y que el negocio no tenga ya un salón registrado (un usuario solo puede tener un salón)
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'negocio') {
    header("location: index.php?page=home"); // Redirigir si no es negocio o no está logueado
    exit;
}

require_once 'includes/db.php';

$user_id = $_SESSION['user_id'];
$business_exists = false;
$business_data = [];
$message = '';
$message_type = '';

// Verificar si el usuario ya tiene un negocio registrado
// Ahora las columnas 'ciudad', 'provincia', 'website', 'horario_apertura', 'horario_cierre' existen
$sql_check = "SELECT id, nombre_negocio, descripcion, direccion, ciudad, provincia, telefono, email_negocio, website, horario_apertura, horario_cierre, estado FROM negocio WHERE usuario_id = ?";
if ($stmt_check = $conn->prepare($sql_check)) {
    $stmt_check->bind_param("i", $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows > 0) {
        $business_exists = true;
        $business_data = $result_check->fetch_assoc();
        $message = "Ya tienes un negocio registrado. Puedes editarlo a continuación.";
        $message_type = "info"; // Un tipo de mensaje para informar
    }
    $stmt_check->close();
} else {
    $message = "Error al verificar el negocio existente: " . $conn->error;
    $message_type = "error";
}

$conn->close();

// Mostrar mensajes de estado (éxito/error)
if (isset($_SESSION['status_message'])) {
    $message = $_SESSION['status_message'];
    $message_type = $_SESSION['status_type'];
    unset($_SESSION['status_message']);
    unset($_SESSION['status_type']);
}
?>

<div class="register-business-container container">
    <h2 class="text-center-heading"><?php echo $business_exists ? 'Editar tu Negocio' : 'Registrar tu Negocio'; ?></h2>
    <p class="text-center"><?php echo $business_exists ? 'Actualiza la información de tu salón.' : 'Completa los datos de tu salón o emprendimiento para que los clientes puedan encontrarte.'; ?></p>

    <?php if (!empty($message)): ?>
        <div class="alert <?php echo $message_type === 'success' ? 'success-message' : ($message_type === 'error' ? 'error-message' : 'info-message'); ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form action="backend/business/process_business_registration.php" method="POST" class="business-form">
        <?php if ($business_exists): ?>
            <input type="hidden" name="business_id" value="<?php echo htmlspecialchars($business_data['id']); ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="nombre_negocio">Nombre del Negocio:</label>
            <input type="text" id="nombre_negocio" name="nombre_negocio" value="<?php echo htmlspecialchars($business_exists ? $business_data['nombre_negocio'] : ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción:</label>
            <textarea id="descripcion" name="descripcion" rows="4"><?php echo htmlspecialchars($business_exists ? $business_data['descripcion'] : ''); ?></textarea>
        </div>

        <div class="form-group">
            <label for="direccion">Dirección:</label>
            <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($business_exists ? $business_data['direccion'] : ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="ciudad">Ciudad:</label>
            <input type="text" id="ciudad" name="ciudad" value="<?php echo htmlspecialchars($business_exists ? $business_data['ciudad'] : ''); ?>">
        </div>

        <div class="form-group">
            <label for="provincia">Provincia:</label>
            <input type="text" id="provincia" name="provincia" value="<?php echo htmlspecialchars($business_exists ? $business_data['provincia'] : ''); ?>">
        </div>

        <div class="form-group">
            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($business_exists ? $business_data['telefono'] : ''); ?>">
        </div>

        <div class="form-group">
            <label for="email_negocio">Email del Negocio:</label>
            <input type="email" id="email_negocio" name="email_negocio" value="<?php echo htmlspecialchars($business_exists ? $business_data['email_negocio'] : ''); ?>">
        </div>

        <div class="form-group">
            <label for="website">Sitio Web (URL):</label>
            <input type="url" id="website" name="website" value="<?php echo htmlspecialchars($business_exists ? $business_data['website'] : ''); ?>">
        </div>

        <div class="form-group">
            <label for="horario_apertura">Horario de Apertura:</label>
            <input type="time" id="horario_apertura" name="horario_apertura" value="<?php echo htmlspecialchars($business_exists ? $business_data['horario_apertura'] : ''); ?>">
        </div>

        <div class="form-group">
            <label for="horario_cierre">Horario de Cierre:</label>
            <input type="time" id="horario_cierre" name="horario_cierre" value="<?php echo htmlspecialchars($business_exists ? $business_data['horario_cierre'] : ''); ?>">
        </div>

        <button type="submit" class="btn-submit"><?php echo $business_exists ? 'Actualizar Negocio' : 'Registrar Negocio'; ?></button>
    </form>
</div>


