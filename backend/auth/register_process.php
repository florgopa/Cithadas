<?php
session_start();

require_once '../../includes/db.php';
require_once '../../includes/functions.php'; 

// Verificar formulario enviado por POST y el botón de registro
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_btn'])) {

    // Inicializar variables para errores y datos del formulario
    $errors = [];
    $input = []; // Para almacenar los datos válidos y saneados

    // Obtener y limpiar los datos del formulario
    // trim() elimina espacios en blanco al inicio y al final
    $name = trim($_POST['name'] ?? '');
    $lastname = trim($_POST['lastname'] ?? ''); 
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; 
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'cliente';

    // Almacenar los inputs originales (excepto contraseñas) en la sesión para rellenar el formulario en caso de error
    $_SESSION['old_input'] = [
        'name' => $name,
        'lastname' => $lastname, // 
        'email' => $email,
        'role' => $role
    ];


    // Validar Nombre
    if (empty($name)) {
        $errors['name_err'] = "Por favor, ingresa tu nombre.";
    } elseif (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s']+$/u", $name)) { // Permite letras, espacios, tildes y ñ
        $errors['name_err'] = "El nombre solo puede contener letras y espacios.";
    } else {
        $input['name'] = htmlspecialchars($name); // Sanear para HTML
    }

    // Validar Apellido
    if (empty($lastname)) {
        $errors['lastname_err'] = "Por favor, ingresa tu apellido.";
    } elseif (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s']+$/u", $lastname)) {
        $errors['lastname_err'] = "El apellido solo puede contener letras y espacios.";
    } else {
        $input['lastname'] = htmlspecialchars($lastname); // Sanear para HTML
    }
  
    // Validar Email
    if (empty($email)) {
        $errors['email_err'] = "Por favor, ingresa tu correo electrónico.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email_err'] = "El formato del correo electrónico no es válido.";
    } else {
     
        $sanitized_email = mysqli_real_escape_string($conn, $email);
        $input['email'] = $sanitized_email;

        // Verificar si el email ya está en uso en la base de datos
        $sql = "SELECT id FROM usuario WHERE email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $sanitized_email);
            $stmt->execute();
            $stmt->store_result(); 
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

    // Validar Rol 
    $allowed_roles = ['cliente', 'negocio']; // para el registro público
    if (!in_array($role, $allowed_roles)) {
        $errors['role_err'] = "Rol no válido.";
    } else {
        $input['role'] = htmlspecialchars($role); // 
    }

    // Procesar si no hay errores
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

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
    exit(); 
}
?>