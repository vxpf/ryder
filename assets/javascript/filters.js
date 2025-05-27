document.addEventListener('DOMContentLoaded', function() {
    // Price slider functionality
    const priceSlider = document.getElementById('price-range');
    const priceValue = document.getElementById('price-value');
    
    if (priceSlider && priceValue) {
        priceSlider.addEventListener('input', function() {
            priceValue.textContent = `Max. €${this.value},00`;
        });
    }
    
    // Reset filters button
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
    
    // Handle form submission with AJAX for a smoother experience
    const filterForm = document.getElementById('filter-form');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(filterForm);
            
            // Build query string
            const params = new URLSearchParams();
            
            // Handle category checkboxes
            const categoryCheckboxes = document.querySelectorAll('input[name="category[]"]:checked');
            if (categoryCheckboxes.length > 0) {
                categoryCheckboxes.forEach(checkbox => {
                    params.append('category[]', checkbox.value);
                });
            }
            
            // Handle capacity checkboxes
            const capacityCheckboxes = document.querySelectorAll('input[name="capacity[]"]:checked');
            if (capacityCheckboxes.length > 0) {
                capacityCheckboxes.forEach(checkbox => {
                    params.append('capacity[]', checkbox.value);
                });
            }
            
            // Handle price slider
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