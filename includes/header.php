<?php
$page_title = $page_title ?? 'Cithadas - Beauty & Personal Care Appointments';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($page_title); ?></title>

    <!-- Tipografía -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap" rel="stylesheet" />

    <!-- Estilos -->
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/header.css" />
    <link rel="stylesheet" href="css/footer.css" />
    <?php
    if (isset($page)) {
        $page_specific_css = 'css/' . $page . '.css';
        if (file_exists($page_specific_css)) {
            echo '<link rel="stylesheet" href="' . htmlspecialchars($page_specific_css) . '">';
        }
    }
    ?>
    <link rel="icon" type="image/svg+xml" href="img/C-logo.svg" />
</head>
<body>
    <header class="main-header">
        <nav class="main-nav">
            <div class="nav-container">
                <!-- Buscador -->
                <div class="nav-left">
                    <form action="index.php" method="GET" class="header-search-form">
                        <input type="hidden" name="page" value="search_results" />
                        <input type="text" name="query" placeholder="Buscar servicio, ubicación..." value="<?php echo htmlspecialchars($_GET['query'] ?? ''); ?>" />
                        <button type="submit" class="btn-search-header">Buscar</button>
                    </form>
                </div>

                <!-- Logo centrado -->
                <div class="nav-center">
                    <a href="index.php?page=home">
                        <img src="img/Cithadas.svg" alt="Logo de Cithadas" class="site-logo" />
                    </a>
                </div>

                <!-- Botón hamburguesa para mobile -->
                <button class="menu-toggle" aria-label="Abrir menú">&#9776;</button>

                <!-- Enlaces de navegación -->
                <div class="nav-right">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="index.php?page=dashboard" class="dashboard-link">Mi Panel</a>
                        <a href="index.php?page=appointments">Mis Citas</a>
                        <a href="backend/auth/logout.php" class="logout-btn">
                        Cerrar Sesión (<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>)
                     </a>
            <?php else: ?>
                    <a href="index.php?page=register">Registrarse</a>
                    <a href="index.php?page=login" class="login-btn">Iniciar Sesión</a>
                <?php endif; ?>
                </div>

        </nav>
    </header>
    <main>
