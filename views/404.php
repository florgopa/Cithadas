<?php
// views/404.php
// Esta vista se muestra cuando la página solicitada no se encuentra

// Opcional: Establece el código de estado HTTP 404 Not Found
http_response_code(404);
?>

<section class="not-found-section text-center">
    <div class="container">
        <h1>404</h1>
        <h2>Página no encontrada</h2>
        <p>Lo sentimos, la página que estás buscando no existe o se ha movido.</p>
        <a href="index.php?page=home" class="btn-primary">Volver al Inicio</a>
    </div>
</section>

