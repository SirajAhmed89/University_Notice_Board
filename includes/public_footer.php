        </main>
        <footer class="footer">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>University Notice Board</h3>
                    <p>Stay connected with all university updates and announcements</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="<?php echo BASE_URL; ?>">Home</a></li>
                        <li><a href="<?php echo BASE_URL; ?>?category=Academic">Academic</a></li>
                        <li><a href="<?php echo BASE_URL; ?>?category=Events">Events</a></li>
                        <li><a href="<?php echo BASE_URL; ?>?category=Examination">Examination</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <ul>
                        <li><i class="fas fa-envelope"></i> contact@university.edu</li>
                        <li><i class="fas fa-phone"></i> +1 234 567 890</li>
                        <li><i class="fas fa-map-marker-alt"></i> University Campus, City</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> University Notice Board. All rights reserved.</p>
            </div>
        </footer>

        <script>
            // Add smooth scrolling
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });
        </script>
    </body>
</html>