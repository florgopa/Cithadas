<?php
// views/dashboard.php

// Asegurarse de que solo usuarios logueados puedan acceder a este dashboard
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.php?page=login");
    exit;
}

$user_name = htmlspecialchars($_SESSION['user_name'] ?? 'Usuario');
$user_role = htmlspecialchars($_SESSION['user_role'] ?? 'rol desconocido');

// Mapeo de roles para mostrar un texto más amigable
$friendly_role = [
    'admin' => 'Administrador',
    'negocio' => 'Dueño de Negocio',
    'cliente' => 'Cliente',
][$user_role] ?? 'Rol Desconocido'; // Fallback por si el rol no está mapeado

?>

<div class="dashboard-container">
    <h2 class="dashboard-heading">Bienvenido/a, <span class="user-name-highlight"><?php echo $user_name; ?></span>!</h2>
    <p class="dashboard-intro">Tu rol actual es: <span class="user-role-badge role-<?php echo $user_role; ?>"><?php echo $friendly_role; ?></span></p>

    <div class="options-section">
        <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <h3 class="section-title">Panel de Administración</h3>
            <ul class="dashboard-options-list">
                <li><a href="index.php?page=manage_users" class="dashboard-option-link">Gestionar Usuarios</a></li>
                <li><a href="index.php?page=manage_business" class="dashboard-option-link">Gestionar Negocios</a></li>
                <li><a href="#" class="dashboard-option-link disabled-link">Gestionar Servicios (Próximamente)</a></li>
                <li><a href="#" class="dashboard-option-link disabled-link">Ver Reportes (Próximamente)</a></li>
            </ul>
        <?php elseif ($_SESSION['user_role'] === 'negocio'): ?>
            <h3 class="section-title">Panel de Negocio</h3>
            <ul class="dashboard-options-list">
                <li><a href="index.php?page=register_business" class="dashboard-option-link">Registrar/Editar mi Negocio</a></li>
                <li><a href="index.php?page=manage_services" class="dashboard-option-link">Gestionar Mis Servicios</a></li>
                <li><a href="#" class="dashboard-option-link disabled-link">Mi Negocio (Próximamente)</a></li>
                <li><a href="#" class="dashboard-option-link disabled-link">Mis Citas (Próximamente)</a></li>
            </ul>
        <?php else: // Rol cliente ?>
            <h3 class="section-title">Opciones de Cliente</h3>
            <ul class="dashboard-options-list">
                <li><a href="index.php?page=home" class="dashboard-option-link">Volver al Inicio</a></li>
                <li><a href="index.php?page=appointments" class="dashboard-option-link">Mis Turnos</a></li>
                <li><a href="index.php?page=client_profile" class="dashboard-option-link">Mi Perfil</a></li>
            </ul>


<?php endif; ?>
    </div>

    <p class="dashboard-footer-text">Desde aquí podrás gestionar las diferentes secciones de Cithadas.</p>
</div>

