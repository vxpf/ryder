document.addEventListener('DOMContentLoaded', function() {
   
    const priceSlider = document.getElementById('price-range');
    const priceValue = document.getElementById('price-value');
    
    if (priceSlider && priceValue) {
        priceSlider.addEventListener('input', function() {
            priceValue.textContent = `Max. €${this.value},00`;
        });
    }
    
    
    const resetButton = document.getElementById('reset-filters');
    if (resetButton) {
        resetButton.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = '/ons-aanbod';
        });
    }
    
    // Mobile filter toggles
    const filterSections = document.querySelectorAll('.filter-section');
    if (window.innerWidth <= 768) {
        filterSections.forEach(section => {
            const heading = section.querySelector('h3');
            if (heading) {
                heading.addEventListener('click', function() {
                    section.classList.toggle('active');
                });
            }
        });
    }
    
    
    const filterForm = document.getElementById('filter-form');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
           
            const formData = new FormData(filterForm);
            
           
            const params = new URLSearchParams();
            
           
            const categoryCheckboxes = document.querySelectorAll('input[name="category[]"]:checked');
            if (categoryCheckboxes.length > 0) {
                categoryCheckboxes.forEach(checkbox => {
                    params.append('category[]', checkbox.value);
                });
            }
            
          
            const capacityCheckboxes = document.querySelectorAll('input[name="capacity[]"]:checked');
            if (capacityCheckboxes.length > 0) {
                capacityCheckboxes.forEach(checkbox => {
                    params.append('capacity[]', checkbox.value);
                });
            }
            
            
            if (priceSlider) {
                params.append('price', priceSlider.value);
            }
            
            // Handle availability checkbox
            const availableCheckbox = document.getElementById('available');
            if (availableCheckbox && availableCheckbox.checked) {
                params.append('available', '1');
            }
            
            // Update URL and reload page
            window.location.href = '/ons-aanbod?' + params.toString();
        });
    }
}); 