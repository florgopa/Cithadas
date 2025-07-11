<?php
// views/login.php

// Inicializar variables para mensajes y valores de formulario (por si hay errores)
$email = $password = '';
$email_err = $password_err = '';
$general_err = '';
$success_msg = '';

// Si hay algún mensaje de error o éxito que viene del backend, lo mostramos
if (isset($_SESSION['login_errors'])) {
    $errors = $_SESSION['login_errors'];
    $email_err = $errors['email_err'] ?? '';
    $password_err = $errors['password_err'] ?? '';
    $general_err = $errors['general_err'] ?? ''; // Para errores generales (ej. credenciales incorrectas)
    // Recuperar valores antiguos para que el usuario no tenga que reescribir el email
    $email = $_SESSION['old_input']['email'] ?? '';
    unset($_SESSION['login_errors']); // Limpiar los errores después de mostrarlos
    unset($_SESSION['old_input']); // Limpiar los inputs antiguos
}

// Mensaje de éxito si viene desde el registro
if (isset($_SESSION['register_success'])) {
    $success_msg = $_SESSION['register_success'];
    unset($_SESSION['register_success']); // Limpiar el mensaje de éxito después de mostrarlo
}

?>

<div class="login-container">
    <h2>Iniciar Sesión</h2>

    <?php if (!empty($success_msg)): ?>
        <div class="alert success-message"><?php echo htmlspecialchars($success_msg); ?></div>
    <?php endif; ?>

    <?php if (!empty($general_err)): ?>
        <div class="alert error-message"><?php echo htmlspecialchars($general_err); ?></div>
    <?php endif; ?>

    <form action="backend/auth/login_process.php" method="POST">
        <div class="form-group">
            <label for="email">Correo electrónico:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            <span class="error-message"><?php echo $email_err; ?></span>
        </div>

        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
            <span class="error-message"><?php echo $password_err; ?></span>
        </div>

        <button type="submit" name="login_btn">Ingresar</button>
    </form>
    <p class="text-center mt-3">¿No tienes una cuenta? <a href="index.php?page=register">Regístrate aquí</a></p>
</div>

