<?php
// views/home.php
// Aquí iría el header de tu sitio, o cualquier lógica inicial que ya tengas
// Por ejemplo, si tienes un session_start() o includes de configuración al inicio de index.php

$page_title = "Bienvenido a Cithadas"; // Título de la página

// Manejar mensajes de estado para el formulario de contacto
$status_message = '';
$status_type = '';
if (isset($_SESSION['status_message'])) {
    $status_message = $_SESSION['status_message'];
    $status_type = $_SESSION['status_type'];
    unset($_SESSION['status_message']);
    unset($_SESSION['status_type']);
}

// Recuperar datos de contacto simulados si existen
$contact_data_display = null;
if (isset($_SESSION['contact_data'])) {
    $contact_data_display = $_SESSION['contact_data'];
    unset($_SESSION['contact_data']); // Limpiar después de mostrar
}
?>

<div class="home-container">

    <section class="hero-section">
        <div class="hero-overlay"></div>
        <div class="hero-header">
            </div>
        <div class="hero-content">
            <h1>Descubre los mejores salones, spas y profesionales cerca de ti.</h1>

            <form action="index.php" method="GET" class="search-bar">
                <input type="hidden" name="page" value="book_a_service">
                <input type="text" name="search_query" placeholder="Buscar por servicio, negocio o categoría..." class="search-input">
                <button type="submit" class="search-button">Buscar</button>
            </form>
        </div>
    </section>

    <section class="mission-section container">
        <h2 class="text-center-heading">Nuestra Misión</h2>
        <div class="mission-text">
            <p>En Cithadas, conectamos la belleza con la conveniencia. Nuestra misión es empoderar a profesionales y negocios de estética y cuidado personal, ofreciéndoles una plataforma intuitiva para gestionar sus citas y expandir su alcance.</p>
            <p>Al mismo tiempo, brindamos a nuestros usuarios una forma sencilla y rápida de descubrir, reservar y disfrutar de los mejores servicios de belleza y bienestar cerca de ellos.</p>
            <p>Creemos en la importancia de cuidarte y dedicarte tiempo. Por eso, nos esforzamos en crear una experiencia fluida que transforme la búsqueda de tu próximo servicio en un momento de anticipación y alegría. Tu bienestar es nuestra inspiración.</p>
        </div>
    </section>

    <section class="featured-section py-5">
        <div class="container">
            <h2 class="text-center-heading">Categorías Populares</h2>
            <div class="category-grid">
                <div class="category-card">
                    <img src="img/peluqueria.jpg" alt="Peluquería" class="category-icon">
                    <h3>Peluquería</h3>
                    <p>Cortes, tintes, peinados y tratamientos capilares.</p>
                    <a href="index.php?page=book_a_service&category=Peluquería" class="btn btn-secondary">Ver Servicios</a>
                </div>
                <div class="category-card">
                    <img src="img/cosmeto.jpg" alt="Estética Facial" class="category-icon">
                    <h3>Estética Facial</h3>
                    <p>Limpiezas, hydrafacial, antiage y más.</p>
                    <a href="index.php?page=book_a_service&category=Estética Facial" class="btn btn-secondary">Ver Servicios</a>
                </div>
                <div class="category-card">
                    <img src="img/masajes.jpg" alt="Masajes" class="category-icon">
                    <h3>Masajes</h3>
                    <p>Relajantes, descontracturantes, deportivos.</p>
                    <a href="index.php?page=book_a_service&category=Masajes" class="btn btn-secondary">Ver Servicios</a>
                </div>
                <div class="category-card">
                    <img src="img/uñas.jpg" alt="Uñas" class="category-icon">
                    <h3>Uñas</h3>
                    <p>Manicura, pedicura, esmaltado semipermanente.</p>
                    <a href="index.php?page=book_a_service&category=Uñas" class="btn btn-secondary">Ver Servicios</a>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-business-section py-5 text-center">
        <div class="container">
            <h2 class="text-center-heading">¿Eres dueño de un negocio?</h2>
            <p>Únete a nuestra plataforma y llega a más clientes. ¡Es fácil y rápido!</p>
            <a href="index.php?page=register_business" class="btn btn-cta">Registra tu Negocio Hoy</a>
        </div>
    </section>

    <section id="contact-section" class="contact-form-section container">
        <h2 class="text-center-heading">Contáctanos</h2>
        <p class="text-center">¿Tienes alguna pregunta, sugerencia o necesitas soporte? ¡Estamos aquí para ayudarte! Completa el siguiente formulario y nos pondremos en contacto contigo a la brevedad.</p>

        <?php if ($status_message): ?>
            <div class="alert <?php echo ($status_type === 'success') ? 'success-message' : (($status_type === 'error') ? 'error-message' : 'info-message'); ?>">
                <?php echo $status_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($contact_data_display): ?>
            <div class="sent-message-display success-message">
                <h3>Mensaje Enviado Exitosamente (Simulación)</h3>
                <p><strong>Nombre:</strong> <?php echo $contact_data_display['name']; ?></p>
                <p><strong>Email:</strong> <?php echo $contact_data_display['email']; ?></p>
                <p><strong>Asunto:</strong> <?php echo $contact_data_display['subject']; ?></p>
                <p><strong>Mensaje:</strong></p>
                <p class="message-content"><?php echo nl2br($contact_data_display['message']); ?></p>
                <small>Este es un mensaje de confirmación para cumplir con el requisito del proyecto. No se ha enviado un correo electrónico real.</small>
            </div>
        <?php endif; ?>

        <form action="backend/process_contact.php" method="POST" class="contact-form">
            <div class="form-group">
                <label for="name">Nombre Completo:</label>
                <input type="text" id="name" name="name" value="<?php echo isset($contact_data_display['name']) ? $contact_data_display['name'] : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Correo Electrónico:</label>
                <input type="email" id="email" name="email" value="<?php echo isset($contact_data_display['email']) ? $contact_data_display['email'] : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="subject">Asunto:</label>
                <input type="text" id="subject" name="subject" value="<?php echo isset($contact_data_display['subject']) ? $contact_data_display['subject'] : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="message">Tu Mensaje:</label>
                <textarea id="message" name="message" rows="6" required><?php echo isset($contact_data_display['message']) ? $contact_data_display['message'] : ''; ?></textarea>
            </div>
            <button type="submit" class="btn-submit-contact">Enviar Mensaje</button>
        </form>
    </section>

</div>

<?php
// Aquí iría el footer de tu sitio, o cualquier lógica final
?>