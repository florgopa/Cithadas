<?php
session_start();
require_once '../../includes/db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"] ?? '');
    $password = trim($_POST["password"] ?? '');

    if (empty($email) || empty($password)) {
        $_SESSION['status_message'] = "Por favor, ingresa tu correo electrónico y contraseña.";
        $_SESSION['status_type'] = "error";
        header("location: ../../index.php?page=login");
        exit;
    }

    // Agregamos el campo 'estado' en la consulta
    $sql = "SELECT id, nombre, email, contraseña, rol, estado FROM usuario WHERE email = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verificar estado
            if ($user['estado'] !== 'activo') {
                $_SESSION['status_message'] = "Tu cuenta ha sido desactivada. Contactá al administrador.";
                $_SESSION['status_type'] = "error";
                header("location: ../../index.php?page=login");
                exit;
            }

            // Verificar la contraseña hasheada
            if (password_verify($password, $user['contraseña'])) {
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nombre'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['rol'];

                header("location: ../../index.php?page=dashboard");
                exit;
            } else {
                $_SESSION['status_message'] = "Contraseña incorrecta. Intenta de nuevo.";
                $_SESSION['status_type'] = "error";
            }
        } else {
            $_SESSION['status_message'] = "No se encontró una cuenta con ese correo electrónico.";
            $_SESSION['status_type'] = "error";
        }
        $stmt->close();
    } else {
        $_SESSION['status_message'] = "Error al preparar la consulta: " . $conn->error;
        $_SESSION['status_type'] = "error";
    }

    $conn->close();
    header("location: ../../index.php?page=login");
    exit;
} else {
    header("location: ../../index.php?page=login");
    exit;
}
?>
