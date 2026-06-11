// resources/js/app.js
document.addEventListener('DOMContentLoaded', function() {
    // Theme toggle
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('cn_theme', newTheme);
        });
    }
    
    // File input validation
    const fileInputs = document.querySelectorAll('input[type="file"][multiple]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            let totalSize = 0;
            files.forEach(file => { totalSize += file.size; });
            if (totalSize > 20 * 1024 * 1024) {
                alert('Total file size exceeds 20MB limit');
                this.value = '';
            }
        });
    });
});