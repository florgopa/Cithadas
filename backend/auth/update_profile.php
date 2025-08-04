<?php
require_once '../../includes/db.php';
session_start();

// Verificar sesión activa y que sea cliente
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'cliente') {
    header('Location: ../../index.php?page=login');
    exit;
}

// Sanitizar los datos recibidos
$id = $_SESSION['usuario_id'];
$nombre = mysqli_real_escape_string($conn, trim($_POST['nombre']));
$email = mysqli_real_escape_string($conn, trim($_POST['email']));

// Actualizar en la base de datos
$sql = "UPDATE usuario SET nombre = ?, email = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $nombre, $email, $id);

if ($stmt->execute()) {
    header("Location: ../../index.php?page=client_profile&success=1");
    exit;
} else {
    echo "Error al actualizar los datos. Por favor intentá de nuevo.";
}
