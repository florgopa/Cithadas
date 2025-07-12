<?php
// backend/auth/login_process.php
session_start();

// Incluir la conexión a la base de datos
require_once '../../includes/db.php'; // Asegúrate de que esta ruta sea correcta

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"] ?? '');
    $password = trim($_POST["password"] ?? '');

    // Validación básica de campos vacíos
    if (empty($email) || empty( $password)) {
        $_SESSION['status_message'] = "Por favor, ingresa tu correo electrónico y contraseña.";
        $_SESSION['status_type'] = "error";
        header("location: ../../index.php?page=login"); // Redirige de vuelta al login
        exit;
    }

    // Preparar la consulta SQL para obtener el usuario por email
    $sql = "SELECT id, nombre, email, contraseña, rol FROM usuario WHERE email = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            // Verificar la contraseña hasheada
            if (password_verify($password, $user['contraseña'])) {
                // Contraseña correcta, iniciar sesión
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nombre'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['rol'];

                // Redirigir SIEMPRE al dashboard genérico después del login exitoso
                header("location: ../../index.php?page=dashboard");
                exit; // Es crucial llamar a exit() después de header()
            } else {
                // Contraseña incorrecta
                $_SESSION['status_message'] = "Contraseña incorrecta. Intenta de nuevo.";
                $_SESSION['status_type'] = "error";
            }
        } else {
            // Usuario no encontrado
            $_SESSION['status_message'] = "No se encontró una cuenta con ese correo electrónico.";
            $_SESSION['status_type'] = "error";
        }
        $stmt->close();
    } else {
        $_SESSION['status_message'] = "Error al preparar la consulta de login: " . $conn->error;
        $_SESSION['status_type'] = "error";
    }

    $conn->close(); // Cerrar la conexión a la base de datos
    header("location: ../../index.php?page=login"); // Redirige de vuelta al login en caso de error
    exit;
} else {
    // Si se intenta acceder directamente sin POST
    header("location: ../../index.php?page=login");
    exit;
}

// Función de ejemplo para obtener el ID del negocio si el usuario es un negocio
// Necesitarías implementar esto si aún no lo tienes
/*
function obtenerBusinessIdDelUsuario($userId, $conn) {
    $businessId = null;
    $sql = "SELECT id FROM negocio WHERE usuario_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($b_id);
        if ($stmt->fetch()) {
            $businessId = $b_id;
        }
        $stmt->close();
    }
    return $businessId;
}
*/
?>
