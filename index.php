<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Página solicitada por parámetro
$page = $_GET['page'] ?? 'home';

$page_title = 'Cithadas';
switch ($page) {
    case 'home':
        $page_title .= ' - Beauty & Personal Care Appointments';
        break;
    case 'appointments':
        $page_title .= ' - Mis Citas';
        break;
    case 'dashboard':
        $page_title .= ' - Dashboard';
        break;
    case 'register_business':
        $page_title .= ' - Registra tu Negocio';
        break;
    case 'register':
        $page_title .= ' - Registrarse';
        break;
    case 'login':
        $page_title .= ' - Iniciar Sesión';
        break;
    case 'contact':
        $page_title .= ' - Contacto';
        break;
    case 'book_a_service':
        $page_title .= ' - Reservar Servicio';
        break;
    case 'book_appointment':
        $page_title .= ' - Confirmar Reserva';
        break;
    case 'search_results':
        $page_title .= ' - Resultados de Búsqueda';
        break;
    default:
    case 'client_profile':
    $page_title .= ' - Mi Perfil';
    break;
        $page = '404'; // 
        $page_title .= ' - Página no encontrada';
        break;
}

include 'includes/header.php';


$view_file = 'views/' . $page . '.php';

if (file_exists($view_file)) {
    echo "<!-- DEBUG: Incluyendo vista $view_file -->";
    include $view_file;
} else {
    include 'views/404.php'; 
}

include 'includes/footer.php';