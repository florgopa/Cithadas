<?php
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

    <section class="how-it-works-section py-5 bg-light">
        <div class="container">
            <h2 class="text-center-heading">¿Cómo Funciona?</h2>
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-icon">1</div>
                    <h4>Explora</h4>
                    <p>Descubre una amplia variedad de servicios y negocios en tu zona.</p>
                </div>
                <div class="step-card">
                    <div class="step-icon">2</div>
                    <h4>Reserva</h4>
                    <p>Elige tu servicio, horario y profesional preferido al instante.</p>
                </div>
                <div class="step-card">
                    <div class="step-icon">3</div>
                    <h4>Disfruta</h4>
                    <p>Relájate y prepárate para una experiencia de bienestar.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="featured-section py-5">
        <div class="container">
            <h2 class="text-center-heading">Categorías Populares</h2>
            <div class="category-grid">
                <div class="category-card">
                    <img src="public/images/icon_peluqueria.png" alt="Peluquería" class="category-icon">
                    <h3>Peluquería</h3>
                    <p>Cortes, tintes, peinados y tratamientos capilares.</p>
                    <a href="index.php?page=book_a_service&category=Peluquería" class="btn btn-secondary">Ver Servicios</a>
                </div>
                <div class="category-card">
                    <img src="public/images/icon_estetica.png" alt="Estética Facial" class="category-icon">
                    <h3>Estética Facial</h3>
                    <p>Limpiezas, hydrafacial, anti-edad y más.</p>
                    <a href="index.php?page=book_a_service&category=Estética Facial" class="btn btn-secondary">Ver Servicios</a>
                </div>
                <div class="category-card">
                    <img src="public/images/icon_masajes.png" alt="Masajes" class="category-icon">
                    <h3>Masajes</h3>
                    <p>Relajantes, descontracturantes, deportivos.</p>
                    <a href="index.php?page=book_a_service&category=Masajes" class="btn btn-secondary">Ver Servicios</a>
                </div>
                <div class="category-card">
                    <img src="public/images/icon_unas.png" alt="Uñas" class="category-icon">
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

</div>
