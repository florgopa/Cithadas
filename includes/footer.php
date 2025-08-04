    </main>
    <footer>
        <div class="footer-content">
            <div class="footer-section company-info">
                <h4>Cithadas ©</h4>
                <ul>
                    <li><a href="#">Nosotros</a></li>
                    <li><a href="#">Términos & Condiciones</a></li>
                    <li><a href="#">Política de Privacidad</a></li>
                </ul>
            </div>
            <div class="footer-section salons-info">
                <h4>Negocios</h4>
                <ul>
                    <li><a href="#">Publicá tu Negocio</a></li>
                    <li><a href="index.php?page=contact">Contacto</a></li>
                </ul>
            </div>
            <div class="footer-section social-media">
                <h4>Seguinos</h4>
                <a href="#"><img src="img/facebook.png" alt="Facebook"></a>
                <a href="#"><img src="img/instagram.png" alt="Instagram"></a>
                <a href="#"><img src="img/linkedin.png" alt="LinkedIn"></a>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; <?php echo date('Y'); ?> Cithadas. All rights reserved.</p>
            <p>Flor Gomez Pacheco | Programación Web II</p>
        </div>
    </footer>

    <!-- Script responsive -->
    <script>
        const toggle = document.querySelector('.menu-toggle');
        const navRight = document.querySelector('.nav-right');

        if (toggle && navRight) {
            toggle.addEventListener('click', () => {
                navRight.classList.toggle('active');
            });
        }
    </script>
</body>
</html>
