    </div>
</main>

<footer class="footer mt-auto py-3 bg-light text-center">
    <div class="container">
        <span class="text-muted">Sistem Inventaris by Yoanda Nofriza</span>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-hide Bootstrap alerts after 5 seconds (5000 ms)
document.addEventListener('DOMContentLoaded', function () {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(a => {
        // give a small delay before starting timer so user sees it render
        setTimeout(() => {
            // Use Bootstrap's Alert dispose if available, else remove
            try {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(a);
                bsAlert.close();
            } catch (e) {
                if (a.parentNode) a.parentNode.removeChild(a);
            }
        }, 5000);
    });
});
</script>
</body>
</html>
