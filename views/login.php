<?php
// views/login.php

// Es CRUCIAL que session_start() se llame una única vez al principio de index.php
// (o en un archivo de configuración que index.php incluya muy al principio).
// Si ya lo tienes globalmente, NO lo añadas aquí para evitar el "Ignoring session_start()" notice.

$page_title = "Iniciar Sesión - Cithadas"; // Título de la página

// --- Lógica para manejar y mostrar mensajes de estado de la sesión ---
$status_message = '';
$status_type = '';

// --- DEBUG: Imprime el contenido de $_SESSION al llegar a login.php ---
echo "<!-- DEBUG (views/login.php): Contenido de SESSION al cargar: -->";
echo "<!-- " . print_r($_SESSION, true) . " -->";
echo "<!-- FIN DEBUG -->";

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
// Asegúrate de que los estilos CSS para .alert, .success-message, .error-message, .info-message estén definidos.
// Si no los tienes, puedes usar el ejemplo de CSS que te dejé comentado en la inmersiva anterior.
?>
