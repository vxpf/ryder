<?php require "includes/header.php" ?>

<main class="aanbod-page">
    <div class="aanbod-container">
        <div class="filters-sidebar">
            <div class="filter-section">
                <h3>TYPE</h3>
                <div class="filter-options">
                    <div class="filter-option">
                        <input type="checkbox" id="sport" name="type" value="sport" checked>
                        <label for="sport">Sport (10)</label>
                    </div>
                    <div class="filter-option">
                        <input type="checkbox" id="suv" name="type" value="suv" checked>
                        <label for="suv">SUV (12)</label>
                    </div>
                    <div class="filter-option">
                        <input type="checkbox" id="transport" name="type" value="transport" checked>
                        <label for="transport">Transport (5)</label>
                    </div>
                    <div class="filter-option">
                        <input type="checkbox" id="mpv" name="type" value="mpv">
                        <label for="mpv">MPV (16)</label>
                    </div>
                    <div class="filter-option">
                        <input type="checkbox" id="sedan" name="type" value="sedan">
                        <label for="sedan">Sedan (20)</label>
                    </div>
                    <div class="filter-option">
                        <input type="checkbox" id="coupe" name="type" value="coupe">
                        <label for="coupe">Coupe (14)</label>
                    </div>
                    <div class="filter-option">
                        <input type="checkbox" id="hatchback" name="type" value="hatchback">
                        <label for="hatchback">Hatchback (14)</label>
                    </div>
                </div>
            </div>

            <div class="filter-section">
                <h3>CAPACITEIT</h3>
                <div class="filter-options">
                    <div class="filter-option">
                        <input type="checkbox" id="2-person" name="capacity" value="2" checked>
                        <label for="2-person">2 Personen (6)</label>
                    </div>
                    <div class="filter-option">
                        <input type="checkbox" id="3-person" name="capacity" value="3" checked>
                        <label for="3-person">3 Personen (2)</label>
                    </div>
                    <div class="filter-option">
                        <input type="checkbox" id="4-person" name="capacity" value="4">
                        <label for="4-person">4 Personen (3)</label>
                    </div>
                    <div class="filter-option">
                        <input type="checkbox" id="8-person" name="capacity" value="8" checked>
                        <label for="8-person">8 Personen (1)</label>
                    </div>
                </div>
            </div>

            <div class="filter-section">
                <h3>PRIJS</h3>
                <div class="price-slider">
                    <input type="range" min="0" max="150" value="100" class="slider" id="price-range">
                    <div class="price-range-labels">
                        <span>€0</span>
                        <span>Max. €100,00</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="car-listings">
            <div class="car-grid">
                <?php
                $vehicles = [
                    // Regular cars
                    [
                        'brand' => 'Koenigsegg',
                        'type' => 'Sport',
                        'price' => '99,00',
                        'favorite' => true,
                        'image' => 'products/car (0).svg',
                        'vehicle_type' => 'regular',
                        'id' => 1
                    ],
                    [
                        'brand' => 'Nissan GT - R',
                        'type' => 'Sport',
                        'price' => '80,00',
                        'favorite' => false,
                        'image' => 'products/car (1).svg',
                        'vehicle_type' => 'regular',
                        'id' => 2
                    ],
                    [
                        'brand' => 'Rolls-Royce',
                        'type' => 'Sport',
                        'price' => '96,00',
                        'favorite' => false,
                        'image' => 'products/Car (2).svg',
                        'vehicle_type' => 'regular',
                        'id' => 3
                    ],
                    [
                        'brand' => 'All New Rush',
                        'type' => 'SUV',
                        'price' => '72,00',
                        'favorite' => false,
                        'image' => 'products/Car (3).svg',
                        'vehicle_type' => 'regular',
                        'id' => 4
                    ],
                    [
                        'brand' => 'CR - V',
                        'type' => 'SUV',
                        'price' => '80,00',
                        'favorite' => true,
                        'image' => 'products/Car (4).svg',
                        'vehicle_type' => 'regular',
                        'id' => 5
                    ],
                    [
                        'brand' => 'All New Terios',
                        'type' => 'SUV',
                        'price' => '74,00',
                        'favorite' => false,
                        'image' => 'products/Car (5).svg',
                        'vehicle_type' => 'regular',
                        'id' => 6
                    ],
                    [
                        'brand' => 'MG ZX Exclusive',
                        'type' => 'SUV',
                        'price' => '76,00',
                        'favorite' => true,
                        'image' => 'products/Car (6).svg',
                        'vehicle_type' => 'regular',
                        'id' => 7
                    ],
                    [
                        'brand' => 'New MG ZS',
                        'type' => 'SUV',
                        'price' => '80,00',
                        'favorite' => false,
                        'image' => 'products/Car (7).svg',
                        'vehicle_type' => 'regular',
                        'id' => 8
                    ],
                    [
                        'brand' => 'MG ZX Excite',
                        'type' => 'SUV',
                        'price' => '74,00',
                        'favorite' => true,
                        'image' => 'products/Car (8).svg',
                        'vehicle_type' => 'regular',
                        'id' => 9
                    ],
                    // Business vehicles
                    [
                        'brand' => 'Volkswagen',
                        'type' => 'Transport',
                        'price' => '95,00',
                        'favorite' => true,
                        'image' => 'bedrijfswagen2.png',
                        'vehicle_type' => 'business',
                        'id' => 'volkswagen',
                        'specs' => [
                            'fuel' => '70L',
                            'transmission' => 'Manual',
                            'capacity' => '8 People'
                        ]
                    ],
                    [
                        'brand' => 'Citroën',
                        'type' => 'Transport',
                        'price' => '89,00',
                        'favorite' => false,
                        'image' => 'citroene.avif',
                        'vehicle_type' => 'business',
                        'id' => 'citroen',
                        'specs' => [
                            'fuel' => '50L',
                            'transmission' => 'Manual',
                            'capacity' => '3 People'
                        ]
                    ],
                    [
                        'brand' => 'Bedrijfswagen',
                        'type' => 'Transport',
                        'price' => '85,00',
                        'favorite' => false,
                        'image' => 'bedrijfswagen1.png',
                        'vehicle_type' => 'business',
                        'id' => 'bedrijfswagen1',
                        'specs' => [
                            'fuel' => '80L',
                            'transmission' => 'Manual',
                            'capacity' => '3 People'
                        ]
                    ]
                ];

                foreach ($vehicles as $index => $vehicle) :
                    $imgBasePath = "assets/images/";
                    $imgPath = $imgBasePath . $vehicle['image'];
                    $detailUrl = ($vehicle['vehicle_type'] === 'regular') ? "car-detail?id=" . $vehicle['id'] : "bedrijfswagen-detail?id=" . $vehicle['id'];
                    
                    // Extract specs for display
                    $fuel = $vehicle['specs']['fuel'] ?? "70L";
                    $transmission = $vehicle['specs']['transmission'] ?? "Manual";
                    $capacity = $vehicle['specs']['capacity'] ?? "2 People";
                ?>
                <div class="car-card">
                    <div class="favorite-icon <?= $vehicle['favorite'] ? 'active' : '' ?>" data-car-id="<?= $vehicle['id'] ?>">
                        <i class="fa <?= $vehicle['favorite'] ? 'fa-heart' : 'fa-heart-o' ?>"></i>
                    </div>
                    <div class="car-header">
                        <div class="car-info">
                            <h3><?= $vehicle['brand'] ?></h3>
                            <span class="car-type"><?= $vehicle['type'] ?></span>
                        </div>
                    </div>
                    <div class="car-image">
                        <img src="<?= $imgPath ?>" alt="<?= $vehicle['brand'] ?>">
                    </div>
                    <div class="car-specs">
                        <div class="spec-item">
                            <img src="assets/images/icons/gas-station.svg" alt="Fuel">
                            <span><?= $fuel ?></span>
                        </div>
                        <div class="spec-item">
                            <img src="assets/images/icons/car.svg" alt="Manual">
                            <span><?= $transmission ?></span>
                        </div>
                        <div class="spec-item">
                            <img src="assets/images/icons/profile-2user.svg" alt="People">
                            <span><?= $capacity ?></span>
                        </div>
                    </div>
                    <div class="car-footer">
                        <div class="price">
                            <span class="amount">€<?= $vehicle['price'] ?></span>
                            <span class="period">/dag</span>
                        </div>
                        <a href="<?= $detailUrl ?>" class="rent-now-btn">Rent Now</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="pagination">
                <button class="show-more-btn">Show more car's</button>
                <div class="page-indicator">12/12</div>
            </div>
        </div>
    </div>
</main>

<style>
.car-image img {
    height: 200px;
    object-fit: contain;
}

.car-card {
    position: relative;
}

.favorite-icon {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 10;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

.favorite-icon:hover {
    transform: scale(1.1);
}

.favorite-icon.active i {
    color: #ff3b58;
}

.favorite-icon i {
    font-size: 18px;
    color: #ccc;
}
</style>

<script src="assets/javascript/filters.js"></script>
<script src="/assets/js/favorites.js"></script>
<script>
// Initialize favorite icons to work with the favorites.js functionality
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.favorite-icon').forEach(icon => {
        icon.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const isLoggedIn = document.body.classList.contains('logged-in');
            
            if (!isLoggedIn) {
                // If there's a showLoginPrompt function available from favorites.js
                if (typeof showLoginPrompt === 'function') {
                    showLoginPrompt();
                } else {
                    window.location.href = '/login-form';
                }
                return;
            }
            
            const carId = this.getAttribute('data-car-id');
            if (typeof toggleFavorite === 'function') {
                toggleFavorite(carId, this);
            }
        });
    });
});
</script>

<?php require "includes/footer.php" ?>
