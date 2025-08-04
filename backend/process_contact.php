<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibir y sanitizar los datos
    $name = trim($_POST['name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validaciones
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $_SESSION['status_message'] = "Por favor, completa todos los campos del formulario de contacto.";
        $_SESSION['status_type'] = "error";
        header("Location: ../index.php?page=home#contact-section"); // Redirigir de vuelta al home
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['status_message'] = "El formato del correo electrónico no es válido.";
        $_SESSION['status_type'] = "error";
        header("Location: ../index.php?page=home#contact-section");
        exit();
    }

    // Guardar los datos simulados en la sesión para mostrarlos
    $_SESSION['contact_data'] = [
        'name' => htmlspecialchars($name),
        'email' => htmlspecialchars($email),
        'subject' => htmlspecialchars($subject),
        'message' => htmlspecialchars($message),
    ];

    $_SESSION['status_message'] = "¡Mensaje enviado con éxito! Nos pondremos en contacto contigo a la brevedad.";
    $_SESSION['status_type'] = "success";

    // Redirigir de nuevo al home, anclando a la sección de contacto
    header("Location: ../index.php?page=home#contact-section");
    exit();

} else {
    // Si alguien intenta acceder directamente a este script sin POST
    header("Location: ../index.php?page=home");
    exit();
}
?>