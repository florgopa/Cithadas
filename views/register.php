<?php
// views/register.php

// Inicializar variables para mensajes y valores de formulario (por si hay errores)
$name = $lastname = $email = $password = $confirm_password = $role = '';
$name_err = $lastname_err = $email_err = $password_err = $confirm_password_err = $role_err = '';
$success_msg = '';

// Si hay algún mensaje de error o éxito que viene del backend, lo mostramos
if (isset($_SESSION['register_errors'])) {
    $errors = $_SESSION['register_errors'];
    $name_err = $errors['name_err'] ?? '';
    $lastname_err = $errors['lastname_err'] ?? ''; // Nueva variable para el apellido
    $email_err = $errors['email_err'] ?? '';
    $password_err = $errors['password_err'] ?? '';
    $confirm_password_err = $errors['confirm_password_err'] ?? '';
    $role_err = $errors['role_err'] ?? '';
    // Recuperar valores antiguos para que el usuario no tenga que reescribir todo
    $name = $_SESSION['old_input']['name'] ?? '';
    $lastname = $_SESSION['old_input']['lastname'] ?? ''; // Recuperar apellido
    $email = $_SESSION['old_input']['email'] ?? '';
    $role = $_SESSION['old_input']['role'] ?? 'cliente'; // Default a cliente si no hay valor
    unset($_SESSION['register_errors']); // Limpiar los errores después de mostrarlos
    unset($_SESSION['old_input']); // Limpiar los inputs antiguos
}

if (isset($_SESSION['register_success'])) {
    $success_msg = $_SESSION['register_success'];
    unset($_SESSION['register_success']); // Limpiar el mensaje de éxito
}

?>

<div class="register-container">
    <h2>Registrate</h2>

    <?php if (!empty($success_msg)): ?>
        <div class="alert success-message"><?php echo htmlspecialchars($success_msg); ?></div>
    <?php endif; ?>

    <form action="backend/auth/register_process.php" method="POST">
        <div class="form-group">
            <label for="name">Nombre:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
            <span class="error-message"><?php echo $name_err; ?></span>
        </div>
        <div class="form-group">
            <label for="lastname">Apellido:</label>
            <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($lastname); ?>" required>
            <span class="error-message"><?php echo $lastname_err; ?></span>
        </div>
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

        <div class="form-group">
            <label for="confirm_password">Confirmar Contraseña:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            <span class="error-message"><?php echo $confirm_password_err; ?></span>
        </div>

        <div class="form-group">
            <label for="role">Registrarse como:</label> <select id="role" name="role">
                <option value="cliente" <?php echo ($role == 'cliente') ? 'selected' : ''; ?>>Cliente</option>
                <option value="negocio" <?php echo ($role == 'negocio') ? 'selected' : ''; ?>>Profesional/Negocio</option>
            </select>
            <span class="error-message"><?php echo $role_err; ?></span>
        </div>

        <button type="submit" name="register_btn">Registrarme</button> </form>
</div>