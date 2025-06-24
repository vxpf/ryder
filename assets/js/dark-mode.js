document.addEventListener('DOMContentLoaded', function() {
    const darkModeToggle = document.querySelector('.dark-mode-toggle');
    

    const isDarkMode = localStorage.getItem('darkMode') === 'true';
    
    
    if (isDarkMode) {
        document.body.classList.add('dark-mode');
    }
    
    
    darkModeToggle.addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');
        
        // Save preference to localStorage
        const currentDarkMode = document.body.classList.contains('dark-mode');
        localStorage.setItem('darkMode', currentDarkMode);
    });
}); 