  <footer>
    © <?php echo date('Y'); ?> <?php echo SITE_NAME; ?> • Privacy Policy • Terms of Use • Contact Us
  </footer>
</main>

<!-- Mobile Menu Toggle -->
<button class="mobile-menu-toggle" onclick="toggleMobileMenu()">☰</button>
<div class="mobile-menu-overlay" onclick="toggleMobileMenu()"></div>

<?php if (isset($additional_js)) echo $additional_js; ?>

<script>
// Mobile Menu Toggle
function toggleMobileMenu() {
    const aside = document.querySelector('aside');
    const overlay = document.querySelector('.mobile-menu-overlay');
    
    if (aside && overlay) {
        aside.classList.toggle('mobile-open');
        if (aside.classList.contains('mobile-open')) {
            overlay.style.display = 'block';
        } else {
            overlay.style.display = 'none';
        }
    }
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});
</script>

</body>
</html>
