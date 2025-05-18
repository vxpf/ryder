document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('toggle-button');
    const hiddenCars = document.querySelector('.hidden-cars') || document.getElementById('hidden-cars');
    
    if (toggleButton && hiddenCars) {
        toggleButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Toggle visibility of hidden cars
            const isHidden = hiddenCars.style.display === 'none';
            hiddenCars.style.display = isHidden ? (hiddenCars.classList.contains('hidden-cars') ? 'contents' : 'block') : 'none';
            toggleButton.textContent = isHidden ? 'Toon minder' : 'Toon alle';
            
            // Scroll back to recommended cars section if hiding
            if (!isHidden) {
                document.getElementById('recommended-cars').scrollIntoView({ behavior: 'smooth' });
            }
        });
    }
}); 