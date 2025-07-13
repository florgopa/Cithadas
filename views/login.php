<?php

$page_title = "Iniciar Sesión - Cithadas"; // Título de la página

// --- Lógica para manejar y mostrar mensajes de estado de la sesión ---
$status_message = '';
$status_type = '';


if (isset($_SESSION['status_message'])) {
    $status_message = $_SESSION['status_message'];
    $status_type = $_SESSION['status_type'];
    // Limpiar los mensajes de la sesión para que no se muestren de nuevo
    unset($_SESSION['status_message']);
    unset($_SESSION['status_type']);
}
// --- Fin de la lógica de mensajes ---
?>

<div class="login-container">
    <h2 class="text-center-heading">Iniciar Sesión</h2>

    <?php
    // --- Sección para mostrar los mensajes de estado ---
    if ($status_message):
        // Clases CSS para los diferentes tipos de mensajes (éxito, error, info)
        $alert_class = '';
        if ($status_type === 'success') {
            $alert_class = 'success-message';
        } elseif ($status_type === 'error') {
            $alert_class = 'error-message';
        } else {
            $alert_class = 'info-message';
        }
    ?>
        <div class="alert <?php echo $alert_class; ?>">
            <?php echo htmlspecialchars($status_message); ?>
        </div>
    <?php endif; ?>
    <!-- --- Fin de la sección de mensajes --- -->

    <form action="backend/auth/login_process.php" method="POST" class="login-form">
        <div class="form-group">
            <label for="email">Correo Electrónico:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn-submit">Iniciar Sesión</button>
    </form>
    <p class="register-link">¿No tienes una cuenta? <a href="index.php?page=register">Regístrate aquí</a></p>
</div>

<?php

?>
