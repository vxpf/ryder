document.addEventListener('DOMContentLoaded', function() {
    const darkModeToggle = document.querySelector('.dark-mode-toggle');
    
    // Check for saved dark mode preference
    const isDarkMode = localStorage.getItem('darkMode') === 'true';
    
    // Apply dark mode if saved preference exists
    if (isDarkMode) {
        document.body.classList.add('dark-mode');
    }
    
    // Toggle dark mode on click
    darkModeToggle.addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');
        
        // Save preference to localStorage
        const currentDarkMode = document.body.classList.contains('dark-mode');
        localStorage.setItem('darkMode', currentDarkMode);
    });
}); 