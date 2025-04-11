        <!-- End of page content -->
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get the toggle checkbox
            const darkModeToggle = document.getElementById('darkmode-toggle');
            
            // Check for saved theme preference
            const savedTheme = localStorage.getItem('darkMode');
            
            // Apply dark mode if saved
            if (savedTheme === 'true') {
                document.body.classList.add('dark-mode');
                darkModeToggle.checked = true;
            }
            
            // Toggle dark mode on checkbox change
            darkModeToggle.addEventListener('change', function() {
                if (this.checked) {
                    document.body.classList.add('dark-mode');
                    localStorage.setItem('darkMode', 'true');
                } else {
                    document.body.classList.remove('dark-mode');
                    localStorage.setItem('darkMode', 'false');
                }
            });
        });
    </script>
</body>
</html> 