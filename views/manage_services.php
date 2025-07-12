<?php
// views/manage_services.php

// Asegurarse de que solo usuarios con rol 'negocio' puedan acceder a esta vista
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'negocio') {
    header("location: index.php?page=home");
    exit;
}

require_once 'includes/db.php'; // Incluye la conexión a la base de datos

$user_id = $_SESSION['user_id'];
$business_id = null;
$current_services = []; // Para almacenar los servicios existentes del negocio

// --- Lógica para obtener el ID del negocio del usuario logueado ---
$sql_get_business_id = "SELECT id FROM negocio WHERE usuario_id = ?";
if ($stmt_get_business_id = $conn->prepare($sql_get_business_id)) {
    $stmt_get_business_id->bind_param("i", $user_id);
    $stmt_get_business_id->execute();
    $stmt_get_business_id->bind_result($b_id);
    if ($stmt_get_business_id->fetch()) {
        $business_id = $b_id;
    }
    $stmt_get_business_id->close();
}

if (!$business_id) {
    // Si el usuario no tiene un negocio registrado, redirigir o mostrar un mensaje
    $_SESSION['status_message'] = "Primero debes registrar la información de tu negocio.";
    $_SESSION['status_type'] = "warning";
    header("location: index.php?page=register_business");
    exit;
}

// --- Lógica para obtener los servicios existentes del negocio ---
$sql_get_services = "SELECT id, nombre_servicio, descripcion, precio, duracion_estimada, categoria, estado FROM servicio WHERE negocio_id = ?";
if ($stmt_get_services = $conn->prepare($sql_get_services)) {
    $stmt_get_services->bind_param("i", $business_id);
    $stmt_get_services->execute();
    $result_services = $stmt_get_services->get_result();
    while ($row = $result_services->fetch_assoc()) {
        $current_services[] = $row;
    }
    $stmt_get_services->close();
}

// Inicializar variables para el formulario de adición/edición de servicio
$service_id_to_edit = '';
$nombre_servicio_edit = '';
$descripcion_edit = '';
$precio_edit = '';
$duracion_estimada_edit = '';
$categoria_edit = '';
$estado_edit = 'activo';
$form_action = 'add_service'; // Default action for the form

// Lógica para precargar datos si se está editando un servicio
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['service_id'])) {
    $service_id_to_edit = intval($_GET['service_id']);
    $sql_get_single_service = "SELECT id, nombre_servicio, descripcion, precio, duracion_estimada, categoria, estado FROM servicio WHERE id = ? AND negocio_id = ?";
    if ($stmt_single = $conn->prepare($sql_get_single_service)) {
        $stmt_single->bind_param("ii", $service_id_to_edit, $business_id);
        $stmt_single->execute();
        $stmt_single->bind_result($s_id, $s_nombre, $s_descripcion, $s_precio, $s_duracion, $s_categoria, $s_estado);
        if ($stmt_single->fetch()) {
            $nombre_servicio_edit = htmlspecialchars($s_nombre);
            $descripcion_edit = htmlspecialchars($s_descripcion);
            $precio_edit = htmlspecialchars($s_precio);
            $duracion_estimada_edit = htmlspecialchars($s_duracion);
            $categoria_edit = htmlspecialchars($s_categoria);
            $estado_edit = htmlspecialchars($s_estado);
            $form_action = 'edit_service';
        } else {
            $_SESSION['status_message'] = "Servicio no encontrado o no tienes permiso para editarlo.";
            $_SESSION['status_type'] = "error";
            header("location: index.php?page=manage_services");
            exit;
        }
        $stmt_single->close();
    }
}

// Manejar mensajes de estado
$status_message = '';
$status_type = '';
if (isset($_SESSION['status_message'])) {
    $status_message = $_SESSION['status_message'];
    $status_type = $_SESSION['status_type'];
    unset($_SESSION['status_message']);
    unset($_SESSION['status_type']);
}

// Opciones para el selector de duración
$duracion_options = [
    ''            => 'Selecciona una duración (Opcional)',
    '15-30min'    => '15-30 minutos',
    '30min-1h'    => '30 minutos - 1 hora',
    '2h'          => '2 horas',
    'entre 2h y 3h' => 'Entre 2 y 3 horas',
    'entre 3h y 5h' => 'Entre 3 y 5 horas',
    '+5h'         => 'Más de 5 horas',
];

// Opciones para el selector de categoría (deben coincidir con tu ENUM de DB)
$categoria_options = [
    '' => 'Selecciona una categoría',
    'Estética Corporal' => 'Estética Corporal',
    'Estética Facial' => 'Estética Facial',
    'Masajes' => 'Masajes',
    'Depilación' => 'Depilación',
    'Peluquería' => 'Peluquería',
    'Uñas' => 'Uñas',
    'Spa' => 'Spa',
    'Fitness' => 'Fitness',
    'Otros' => 'Otros'
];
?>

<div class="manage-services-container container">
    <h2 class="text-center-heading">Gestionar Mis Servicios</h2>

    <?php if ($status_message): ?>
        <div class="alert <?php echo ($status_type === 'success') ? 'success-message' : (($status_type === 'error') ? 'error-message' : 'info-message'); ?>">
            <?php echo htmlspecialchars($status_message); ?>
        </div>
    <?php endif; ?>

    <h3 class="form-section-heading"><?php echo ($form_action == 'add_service') ? 'Añadir Nuevo Servicio' : 'Editar Servicio'; ?></h3>
    <form action="backend/business/process_services.php" method="POST" class="service-form business-form">
        <input type="hidden" name="action" value="<?php echo $form_action; ?>">
        <?php if ($form_action == 'edit_service'): ?>
            <input type="hidden" name="service_id" value="<?php echo $service_id_to_edit; ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="nombre_servicio">Nombre del Servicio <span class="required-field">*</span></label>
            <input type="text" id="nombre_servicio" name="nombre_servicio" value="<?php echo $nombre_servicio_edit; ?>" required>
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" rows="3"><?php echo $descripcion_edit; ?></textarea>
        </div>

        <div class="form-group">
            <label for="precio">Precio <span class="required-field">*</span></label>
            <input type="number" step="0.01" id="precio" name="precio" value="<?php echo $precio_edit; ?>" required>
        </div>

        <div class="form-group">
            <label for="categoria">Categoría <span class="required-field">*</span></label>
            <select id="categoria" name="categoria" required>
                <?php foreach ($categoria_options as $value => $label): ?>
                    <option value="<?php echo htmlspecialchars($value); ?>" <?php echo ($categoria_edit === $value) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="duracion_estimada">Duración Estimada</label>
            <select id="duracion_estimada" name="duracion_estimada">
                <?php foreach ($duracion_options as $value => $label): ?>
                    <option value="<?php echo htmlspecialchars($value); ?>" <?php echo ($duracion_estimada_edit === $value) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="form-hint">Selecciona un rango de duración para el servicio. Puedes dejarlo vacío si no aplica.</small>
        </div>

        <div class="form-group">
            <label for="estado">Estado</label>
            <select id="estado" name="estado">
                <option value="activo" <?php echo ($estado_edit == 'activo') ? 'selected' : ''; ?>>Activo</option>
                <option value="inactivo" <?php echo ($estado_edit == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
            </select>
        </div>

        <button type="submit" class="btn-submit">
            <?php echo ($form_action == 'add_service') ? 'Añadir Servicio' : 'Actualizar Servicio'; ?>
        </button>
        <?php if ($form_action == 'edit_service'): ?>
            <a href="index.php?page=manage_services" class="btn-cancel">Cancelar Edición</a>
        <?php endif; ?>
    </form>

    <hr class="section-divider">

    <h3 class="list-section-heading">Mis Servicios Registrados</h3>
    <?php if (empty($current_services)): ?>
        <p class="no-services-message">Aún no tienes servicios registrados. ¡Usa el formulario de arriba para añadir uno!</p>
    <?php else: ?>
        <div class="table-container">
            <table class="service-list-table common-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Duración</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($current_services as $service): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($service['nombre_servicio']); ?></td>
                            <td><?php echo htmlspecialchars($service['categoria'] ?? 'N/A'); ?></td>
                            <td>$<?php echo htmlspecialchars(number_format($service['precio'], 2, ',', '.')); ?></td>
                            <td>
                                <?php
                                    // Muestra la etiqueta o "N/A" si es NULL/vacío
                                    echo !empty($service['duracion_estimada']) ? htmlspecialchars($duracion_options[$service['duracion_estimada']] ?? $service['duracion_estimada']) : 'N/A';
                                ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo htmlspecialchars($service['estado']); ?>">
                                    <?php echo htmlspecialchars(ucfirst($service['estado'])); ?>
                                </span>
                            </td>
                            <td>
                                <a href="index.php?page=manage_services&action=edit&service_id=<?php echo $service['id']; ?>" class="btn-edit">Editar</a>
                                <a href="backend/business/process_services.php?action=delete&service_id=<?php echo $service['id']; ?>" class="btn-delete" onclick="return confirm('¿Estás seguro de que quieres eliminar este servicio?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

