document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('toggle-button');
    const hiddenCars = document.querySelector('.hidden-cars') || document.getElementById('hidden-cars');
    
    if (toggleButton && hiddenCars) {
        toggleButton.addEventListener('click', function(e) {
            e.preventDefault();
            
         
            const isHidden = hiddenCars.style.display === 'none';
            hiddenCars.style.display = isHidden ? (hiddenCars.classList.contains('hidden-cars') ? 'contents' : 'block') : 'none';
            toggleButton.textContent = isHidden ? 'Toon minder' : 'Toon alle';
            
            
            if (!isHidden) {
                document.getElementById('recommended-cars').scrollIntoView({ behavior: 'smooth' });
            }
        });
    }
}); 