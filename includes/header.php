<?php
$page_title = $page_title ?? 'Cithadas - Beauty & Personal Care Appointments';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>

   <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">

    <?php
    if (isset($page)) {
        $page_specific_css = 'css/' . $page . '.css';
        if (file_exists($page_specific_css)) {
            echo '<link rel="stylesheet" href="' . htmlspecialchars($page_specific_css) . '">';
        }
    }
    ?>

    <link rel="icon" type="image/svg+xml" href="img/C-logo.svg">
</head>
<body>
    <header>
        <nav class="main-nav">
            <div class="logo">
                <a href="index.php?page=home">
                    <img src="img/Cithadas.svg" alt="Logo de Cithadas" class="site-logo">
                </a>
            </div>
            <div class="search-bar">
                <form action="index.php" method="GET">
                    <input type="hidden" name="page" value="search_results">
                    <input type="text" name="query" placeholder="Buscar servicio, ubicación...">
                    <button type="submit">Buscar</button>
                </form>
            </div>
            <div class="nav-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="index.php?page=appointments">Mis Citas</a>
                    <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] == 'negocio' || $_SESSION['user_role'] == 'admin')): ?>
                        <a href="index.php?page=dashboard">Dashboard</a>
                    <?php endif; ?>
                    <a href="backend/auth/logout.php">Cerrar Sesión (<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>)</a>
                <?php else: ?>
                    <a href="index.php?page=register">Registrarse</a>
                    <a href="index.php?page=login">Iniciar Sesión</a>
                <?php endif; ?>
                <a href="index.php?page=contact">Contacto</a>
            </div>
        </nav>
    </header>
    <main></main>