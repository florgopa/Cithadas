<?php
$page_title = "Contacto - Cithadas";

?>

<div class="container contact-page-content">
    <div class="mission-section">
        <h2>Nuestra Misión</h2>
        <p>En Cithadas, conectamos la belleza con la conveniencia. Nuestra misión es empoderar a profesionales y negocios de estética y cuidado personal, ofreciéndoles una plataforma intuitiva para gestionar sus citas y expandir su alcance.</p>
        <p>Al mismo tiempo, brindamos a nuestros usuarios una forma sencilla y rápida de descubrir, reservar y disfrutar de los mejores servicios de belleza y bienestar cerca de ellos.</p>
        <p>Creemos en la importancia de cuidarte y dedicarte tiempo. Por eso, nos esforzamos en crear una experiencia fluida que transforme la búsqueda de tu próximo servicio en un momento de anticipación y alegría. Tu bienestar es nuestra inspiración.</p>
    </div>

    <div class="contact-form-section">
        <h2>Contáctanos</h2>
        <p>¿Tienes alguna pregunta, sugerencia o necesitas soporte? ¡Estamos aquí para ayudarte! Completa el siguiente formulario y nos pondremos en contacto contigo a la brevedad.</p>
        <form action="backend/process_contact.php" method="POST" class="contact-form">
            <div class="form-group">
                <label for="name">Nombre Completo:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Correo Electrónico:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="subject">Asunto:</label>
                <input type="text" id="subject" name="subject" required>
            </div>
            <div class="form-group">
                <label for="message">Tu Mensaje:</label>
                <textarea id="message" name="message" rows="6" required></textarea>
            </div>
            <button type="submit" class="btn-submit-contact">Enviar Mensaje</button>
        </form>
    </div>
</div>

<?php
?>