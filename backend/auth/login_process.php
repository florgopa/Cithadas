<?php
// backend/auth/login_process.php

// HABILITAR ERRORES (Mantenemos esto para cualquier otro error general, puedes quitarlo en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar la sesión al principio
session_start();

// Incluir la conexión a la base de datos
require_once '../../includes/db.php'; // Ajusta la ruta si es necesario

// Verificar si el formulario ha sido enviado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_btn'])) {

    // 1. Inicializar variables para errores y datos del formulario
    $errors = [];
    $input_email = '';

    // 2. Obtener y limpiar los datos del formulario
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Almacenar el email en la sesión para rellenar el formulario en caso de error
    $_SESSION['old_input'] = ['email' => $email];

    // 3. Validar los datos
    if (empty($email)) {
        $errors['email_err'] = "Por favor, ingresa tu correo electrónico.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email_err'] = "El formato del correo electrónico no es válido.";
    } else {
        $input_email = mysqli_real_escape_string($conn, $email); // Sanear para la consulta SQL
    }

    if (empty($password)) {
        $errors['password_err'] = "Por favor, ingresa tu contraseña.";
    }

    // 4. Si no hay errores de validación de formato, intentar verificar credenciales
    if (empty($errors)) {
        // MODIFICACIÓN AQUÍ: AÑADIMOS 'estado' A LA CONSULTA SELECT
        $sql = "SELECT id, nombre, email, contraseña, rol, estado FROM usuario WHERE email = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $input_email); // "s" para string (email)
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                // MODIFICACIÓN AQUÍ: AÑADIMOS $estado A bind_result
                $stmt->bind_result($id, $name, $email_db, $hashed_password, $role, $estado);
                $stmt->fetch();

                // Verificar la contraseña usando password_verify
                if (password_verify($password, $hashed_password)) {
                    // **NUEVA VERIFICACIÓN: AHORA CHEQUEAMOS EL ESTADO DEL USUARIO**
                    if ($estado === 'activo') { // Solo permite login si el estado es 'activo'
                        // Contraseña correcta y usuario activo, iniciar sesión
                        session_regenerate_id(true); // Regenera el ID de sesión para seguridad (evita Session Fixation)

                        $_SESSION['loggedin'] = true;
                        $_SESSION['user_id'] = $id;
                        $_SESSION['user_name'] = $name;
                        $_SESSION['user_email'] = $email_db;
                        $_SESSION['user_role'] = $role;

                        // Redirigir al usuario según su rol o a la página de inicio
                        if ($role == 'admin' || $role == 'negocio') {
                            header("location: ../../index.php?page=dashboard"); // Redirigir a un dashboard para admins/negocios
                        } else {
                            header("location: ../../index.php?page=home"); // Redirigir al home para clientes
                        }
                        exit(); // Es crucial terminar el script después de una redirección
                    } else {
                        // Usuario no activo (pendiente, inactivo, etc.)
                        $errors['general_err'] = "Tu cuenta no está activa. Contacta al administrador.";
                    }
                } else {
                    // Contraseña incorrecta
                    $errors['general_err'] = "Credenciales incorrectas. Por favor, verifica tu correo y contraseña.";
                }
            } else {
                // Email no encontrado
                $errors['general_err'] = "Credenciales incorrectas. Por favor, verifica tu correo y contraseña.";
            }
            $stmt->close();
        } else {
            $errors['general_err'] = "Error interno del sistema al preparar la consulta. (" . $conn->error . ")";
        }
    }

    // Si hay errores (ya sea de validación o de credenciales), guardarlos en la sesión
    if (!empty($errors)) {
        $_SESSION['login_errors'] = $errors;
        header("location: ../../index.php?page=login"); // Redirigir de vuelta al formulario de login
        exit();
    }

    // Cerrar la conexión a la base de datos
    $conn->close();

} else {
    // Si se intenta acceder a este script directamente sin enviar el formulario POST
    header("location: ../../index.php?page=login");
    exit();
}
?>