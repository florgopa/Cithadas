<?php
// Iniciar la sesión al principio
session_start();

// Incluir la conexión a la base de datos y funciones generales
// Asegúrate de que la ruta sea correcta (dos niveles arriba desde backend/auth)
require_once '../../includes/db.php';
require_once '../../includes/functions.php'; // Para funciones de validación adicionales si las creamos aquí o en functions.php

// Verificar si el formulario ha sido enviado por método POST y si el botón de registro fue presionado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_btn'])) {

    // 1. Inicializar variables para errores y datos del formulario
    $errors = [];
    $input = []; // Para almacenar los datos válidos y saneados

    // 2. Obtener y limpiar los datos del formulario
    // trim() elimina espacios en blanco al inicio y al final
    $name = trim($_POST['name'] ?? '');
    $lastname = trim($_POST['lastname'] ?? ''); // NUEVO: Obtener y trimear el apellido
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; // La contraseña no se trimea para permitir espacios si se desea
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'cliente'; // Por defecto 'cliente'

    // Almacenar los inputs originales (excepto contraseñas) en la sesión para rellenar el formulario en caso de error
    $_SESSION['old_input'] = [
        'name' => $name,
        'lastname' => $lastname, // NUEVO: Guardar el apellido en old_input
        'email' => $email,
        'role' => $role
    ];

    // 3. Validar los datos

    // Validar Nombre
    if (empty($name)) {
        $errors['name_err'] = "Por favor, ingresa tu nombre.";
    } elseif (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s']+$/u", $name)) { // Permite letras, espacios, tildes y ñ
        $errors['name_err'] = "El nombre solo puede contener letras y espacios.";
    } else {
        $input['name'] = htmlspecialchars($name); // Sanear para HTML
    }

    // NUEVO: Validar Apellido
    if (empty($lastname)) {
        $errors['lastname_err'] = "Por favor, ingresa tu apellido.";
    } elseif (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s']+$/u", $lastname)) {
        $errors['lastname_err'] = "El apellido solo puede contener letras y espacios.";
    } else {
        $input['lastname'] = htmlspecialchars($lastname); // Sanear para HTML
    }
    // FIN NUEVO

    // Validar Email
    if (empty($email)) {
        $errors['email_err'] = "Por favor, ingresa tu correo electrónico.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email_err'] = "El formato del correo electrónico no es válido.";
    } else {
        // Sanear el email antes de la consulta
        $sanitized_email = mysqli_real_escape_string($conn, $email);
        $input['email'] = $sanitized_email;

        // Verificar si el email ya está en uso en la base de datos
        $sql = "SELECT id FROM usuario WHERE email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $sanitized_email);
            $stmt->execute();
            $stmt->store_result(); // Almacena el resultado para poder usar num_rows
            if ($stmt->num_rows > 0) {
                $errors['email_err'] = "Este correo electrónico ya está registrado.";
            }
            $stmt->close(); // Cerrar el statement
        } else {
            $errors['email_err'] = "Error interno al verificar el email. Por favor, inténtalo de nuevo. (" . $conn->error . ")"; // Para depuración
        }
    }

    // Validar Contraseña
    if (empty($password)) {
        $errors['password_err'] = "Por favor, ingresa una contraseña.";
    } elseif (strlen($password) < 6) {
        $errors['password_err'] = "La contraseña debe tener al menos 6 caracteres.";
    }

    // Validar Confirmación de Contraseña
    if (empty($confirm_password)) {
        $errors['confirm_password_err'] = "Por favor, confirma la contraseña.";
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password_err'] = "Las contraseñas no coinciden.";
    }

    // Validar Rol (asegúrate de que solo se envíen los roles permitidos por el select)
    $allowed_roles = ['cliente', 'negocio']; // Roles permitidos para el registro público
    if (!in_array($role, $allowed_roles)) {
        $errors['role_err'] = "Rol no válido.";
    } else {
        $input['role'] = htmlspecialchars($role); // Sanear para HTML
    }

    // 4. Procesar si no hay errores
    if (empty($errors)) {
        // Hash de la contraseña antes de guardarla en la base de datos
        // password_hash() es la forma segura y recomendada
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Concatenamos nombre y apellido para guardar en el campo 'nombre' de la tabla 'usuario'
        // (ya que la tabla 'usuario' actual no tiene un campo 'apellido' separado)
        $full_name_to_db = $input['name'] . ' ' . $input['lastname'];

        // Preparar la consulta SQL para insertar el nuevo usuario
        $sql_insert = "INSERT INTO usuario (nombre, email, contraseña, rol) VALUES (?, ?, ?, ?)";

        if ($stmt_insert = $conn->prepare($sql_insert)) {
            // "ssss" indica que los 4 parámetros son strings
            $stmt_insert->bind_param("ssss", $full_name_to_db, $input['email'], $hashed_password, $input['role']);

            if ($stmt_insert->execute()) {
                // Registro exitoso, redirigir a la página de login o home con mensaje de éxito
                $_SESSION['register_success'] = "¡Registro exitoso! Ya puedes iniciar sesión con tu correo y contraseña.";
                header("location: ../../index.php?page=login"); // Redirigir al login
                exit(); // Terminar el script después de la redirección
            } else {
                // Error al insertar en la DB
                $errors['general_err'] = "Error al registrar el usuario. Por favor, inténtalo de nuevo. (" . $stmt_insert->error . ")"; // Para depuración
            }
            $stmt_insert->close(); // Cerrar el statement de inserción
        } else {
            $errors['general_err'] = "Error interno al preparar la inserción. Por favor, inténtalo de nuevo. (" . $conn->error . ")"; // Para depuración
        }
    }

    // Si hay errores, guardarlos en la sesión y redirigir de vuelta al formulario de registro
    if (!empty($errors)) {
        $_SESSION['register_errors'] = $errors;
        // La variable $_SESSION['old_input'] ya se guardó al inicio, no es necesario volver a hacerlo aquí
        header("location: ../../index.php?page=register");
        exit(); // Terminar el script después de la redirección
    }

    // Cerrar la conexión a la base de datos al final del script de procesamiento
    $conn->close();

} else {
    // Si se intenta acceder a este script directamente sin enviar el formulario POST
    header("location: ../../index.php?page=register"); // Redirigir de nuevo a la página de registro
    exit(); // Terminar el script
}
?>