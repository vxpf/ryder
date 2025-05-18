document.addEventListener('DOMContentLoaded', function() {
    // Get all filter elements
    const typeFilters = document.querySelectorAll('input[name="type"]');
    const capacityFilters = document.querySelectorAll('input[name="capacity"]');
    const priceSlider = document.getElementById('price-range');
    const priceLabel = document.querySelector('.price-range-labels span:last-child');
    const carCards = document.querySelectorAll('.car-card');
    const showMoreBtn = document.querySelector('.show-more-btn');
    
    // Set initial display state - only show first 8 cars
    let showingAll = false;
    limitInitialDisplay();
    
    // Show more/less button functionality
    showMoreBtn.addEventListener('click', function() {
        showingAll = !showingAll;
        
        if (showingAll) {
            // Show all cars (that match current filters)
            applyFilters();
            showMoreBtn.textContent = 'Toon minder auto\'s';
        } else {
            // Show only first 8 cars that match current filters
            limitInitialDisplay();
            showMoreBtn.textContent = 'Toon alle auto\'s';
        }
    });
    
    // Function to limit initial display to 8 cars
    function limitInitialDisplay() {
        // First apply filters
        applyFilters(true);
        
        // Then limit to 8 visible cars
        let visibleCount = 0;
        carCards.forEach(card => {
            if (card.style.display !== 'none') {
                visibleCount++;
                if (visibleCount > 8) {
                    card.style.display = 'none';
                }
            }
        });
        
        // Update count
        updateVehicleCount(true);
    }
    
    // Initialize max price value display
    updatePriceLabel(priceSlider.value);
    
    // Add event listeners to all filter inputs
    typeFilters.forEach(filter => {
        filter.addEventListener('change', function() {
            applyFilters();
            if (!showingAll) {
                limitInitialDisplay();
            }
        });
    });
    
    capacityFilters.forEach(filter => {
        filter.addEventListener('change', function() {
            applyFilters();
            if (!showingAll) {
                limitInitialDisplay();
            }
        });
    });
    
    priceSlider.addEventListener('input', function() {
        updatePriceLabel(this.value);
        applyFilters();
        if (!showingAll) {
            limitInitialDisplay();
        }
    });
    
    // Function to update price label
    function updatePriceLabel(value) {
        priceLabel.textContent = `Max. €${value},00`;
    }
    
    // Main filter function
    function applyFilters(skipLimit = false) {
        // Get selected filters
        const selectedTypes = Array.from(typeFilters)
            .filter(input => input.checked)
            .map(input => input.value);
            
        const selectedCapacities = Array.from(capacityFilters)
            .filter(input => input.checked)
            .map(input => input.value);
            
        const maxPrice = parseInt(priceSlider.value);
        
        // Loop through each car card and check if it matches the filters
        carCards.forEach(card => {
            const carType = card.querySelector('.car-type').textContent.trim().toLowerCase();
            const capacityText = card.querySelector('.spec-item:nth-child(3) span').textContent;
            const capacity = parseInt(capacityText.match(/\d+/)[0]); // Extract number from "X People"
            const priceText = card.querySelector('.amount').textContent.replace('€', '').replace(',', '.').trim();
            const price = parseFloat(priceText);
            
            // Check if car matches all selected filters
            const matchesType = selectedTypes.length === 0 || selectedTypes.some(type => carType.includes(type.toLowerCase()));
            const matchesCapacity = selectedCapacities.length === 0 || selectedCapacities.includes(capacity.toString());
            const matchesPrice = price <= maxPrice;
            
            // Show or hide the car based on filter matches
            if (matchesType && matchesCapacity && matchesPrice) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
        
        // Update vehicle count in pagination
        updateVehicleCount(skipLimit);
    }
    
    // Update vehicle count shown/total in pagination
    function updateVehicleCount(skipLimit = false) {
        let visibleCount = Array.from(carCards).filter(card => card.style.display !== 'none').length;
        const totalMatchingCount = visibleCount;
        
        // If we're limiting display and not showing all, adjust the count
        if (!skipLimit && !showingAll && visibleCount > 8) {
            visibleCount = 8;
        }
        
        const totalCount = carCards.length;
        document.querySelector('.page-indicator').textContent = `${visibleCount}/${totalCount}`;
    }
    
    // Initial filter application
    limitInitialDisplay();
}); 