<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../../includes/db.php';

// Verificar que el usuario esté logueado como cliente
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'cliente') {
    header("Location: ../../index.php?page=login");
    exit;
}

$user_id = $_SESSION['user_id'];
$turno_id = $_POST['turno_id'] ?? null;

if ($turno_id) {
    // Confirmar que el turno le pertenece al usuario
    $check_sql = "SELECT id FROM turno WHERE id = ? AND usuario_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    
    if ($check_stmt) {
        $check_stmt->bind_param("ii", $turno_id, $user_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result && $result->num_rows > 0) {
            // Cancelar el turno
            $sql = "UPDATE turno SET estado = 'cancelado' WHERE id = ?";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("i", $turno_id);
                if ($stmt->execute()) {
                    $_SESSION['status_message'] = 'El turno fue cancelado exitosamente.';
                    $_SESSION['status_type'] = 'success';
                } else {
                    $_SESSION['status_message'] = 'Error al cancelar el turno.';
                    $_SESSION['status_type'] = 'error';
                }
                $stmt->close();
            } else {
                $_SESSION['status_message'] = 'Error preparando la consulta de cancelación.';
                $_SESSION['status_type'] = 'error';
            }
        } else {
            $_SESSION['status_message'] = 'Turno no válido o no te pertenece.';
            $_SESSION['status_type'] = 'error';
        }

        $check_stmt->close();
    } else {
        $_SESSION['status_message'] = 'Error preparando la verificación del turno.';
        $_SESSION['status_type'] = 'error';
    }
} else {
    $_SESSION['status_message'] = 'Turno inválido.';
    $_SESSION['status_type'] = 'error';
}

$conn->close();
header("Location: ../../index.php?page=appointments");
exit;
