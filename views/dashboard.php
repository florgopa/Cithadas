<?php
// views/dashboard.php

// Asegurarse de que solo usuarios logueados puedan acceder a este dashboard
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.php?page=login");
    exit;
}

$user_name = htmlspecialchars($_SESSION['user_name'] ?? 'Usuario');
$user_role = htmlspecialchars($_SESSION['user_role'] ?? 'rol desconocido');

?>

<div class="dashboard-container">
    <h2>Bienvenido/a, <?php echo $user_name; ?>!</h2>
    <p>Tu rol actual es: <?php echo $user_role; ?></p>

    <h3>Opciones de Administración</h3>
    <ul>
        <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <li><a href="index.php?page=manage_users">Gestionar Usuarios</a></li>
            <li><a href="index.php?page=manage_business">Gestionar Negocios</a></li>
            <li><a /* href="index.php?page=manage_services" */>Gestionar Servicios</a></li>
            <li><a /* href="index.php?page=reports" */>Ver Reportes</a></li>
      <?php elseif ($_SESSION['user_role'] === 'negocio'): ?>
    <h3>Panel de Negocio</h3>
    <ul>
        <li><a href="index.php?page=register_business">Registrar/Editar mi Negocio</a></li>
        <li><a href="index.php?page=manage_services">Gestionar Mis Servicios</a></li>
        <li><a /* href="index.php?page=my_business" */>Mi Negocio</a></li>
        <li><a /* href="index.php?page=my_appointments" */>Mis Citas</a></li>
    </ul>
        <?php else: // Rol cliente o desconocido ?>
            <li><p>No tienes opciones de administración disponibles para tu rol.</p></li>
            <li><a href="index.php?page=home">Volver al Inicio</a></li>
        <?php endif; ?>
    </ul>

    <p>Desde aquí podrás gestionar las diferentes secciones de Cithadas.</p>
</div>
