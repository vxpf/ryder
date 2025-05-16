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
                        <label for="2-person">2 Person (10)</label>
                    </div>
                    <div class="filter-option">
                        <input type="checkbox" id="4-person" name="capacity" value="4">
                        <label for="4-person">4 Person (14)</label>
                    </div>
                    <div class="filter-option">
                        <input type="checkbox" id="6-person" name="capacity" value="6">
                        <label for="6-person">6 Person (12)</label>
                    </div>
                </div>
            </div>

            <div class="filter-section">
                <h3>PRIJS</h3>
                <div class="price-slider">
                    <input type="range" min="0" max="500" value="300" class="slider" id="price-range">
                    <div class="price-range-labels">
                        <span>€0</span>
                        <span>Max. €300,00</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="car-listings">
            <div class="listings-header">
                <div class="listing-tabs">
                    <div class="tab active">
                        <span>Pick-Up</span>
                        <div class="tab-details">
                            <div class="location-field">
                                <label>Locatie</label>
                                <select>
                                    <option>Amsterdam</option>
                                </select>
                            </div>
                            <div class="date-field">
                                <label>Datum</label>
                                <select>
                                    <option>7 Juli 2023</option>
                                </select>
                            </div>
                            <div class="time-field">
                                <label>Tijd</label>
                                <select>
                                    <option>10:00</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="tab">
                        <span>Drop-Off</span>
                        <div class="tab-details">
                            <div class="location-field">
                                <label>Locatie</label>
                                <select>
                                    <option>Amsterdam</option>
                                </select>
                            </div>
                            <div class="date-field">
                                <label>Datum</label>
                                <select>
                                    <option>10 Juli 2023</option>
                                </select>
                            </div>
                            <div class="time-field">
                                <label>Tijd</label>
                                <select>
                                    <option>12:00</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="car-grid">
                <?php
                $cars = [
                    [
                        'brand' => 'Koenigsegg',
                        'type' => 'Sport',
                        'price' => '99,00',
                        'favorite' => true,
                        'image' => 'car (0).svg'
                    ],
                    [
                        'brand' => 'Nissan GT - R',
                        'type' => 'Sport',
                        'price' => '80,00',
                        'favorite' => false,
                        'image' => 'car (1).svg'
                    ],
                    [
                        'brand' => 'Rolls-Royce',
                        'type' => 'Sport',
                        'price' => '96,00',
                        'favorite' => false,
                        'image' => 'Car (2).svg'
                    ],
                    [
                        'brand' => 'All New Rush',
                        'type' => 'SUV',
                        'price' => '72,00',
                        'favorite' => false,
                        'image' => 'Car (3).svg'
                    ],
                    [
                        'brand' => 'CR - V',
                        'type' => 'SUV',
                        'price' => '80,00',
                        'favorite' => true,
                        'image' => 'Car (4).svg'
                    ],
                    [
                        'brand' => 'All New Terios',
                        'type' => 'SUV',
                        'price' => '74,00',
                        'favorite' => false,
                        'image' => 'Car (5).svg'
                    ],
                    [
                        'brand' => 'MG ZX Exclusive',
                        'type' => 'SUV',
                        'price' => '76,00',
                        'favorite' => true,
                        'image' => 'Car (6).svg'
                    ],
                    [
                        'brand' => 'New MG ZS',
                        'type' => 'SUV',
                        'price' => '80,00',
                        'favorite' => false,
                        'image' => 'Car (7).svg'
                    ],
                    [
                        'brand' => 'MG ZX Excite',
                        'type' => 'SUV',
                        'price' => '74,00',
                        'favorite' => true,
                        'image' => 'Car (8).svg'
                    ]
                ];

                foreach ($cars as $index => $car) :
                ?>
                <div class="car-card">
                    <div class="car-header">
                        <div class="car-info">
                            <h3><?= $car['brand'] ?></h3>
                            <span class="car-type"><?= $car['type'] ?></span>
                        </div>
                        <div class="favorite-icon <?= $car['favorite'] ? 'active' : '' ?>">
                            <i class="fa <?= $car['favorite'] ? 'fa-heart' : 'fa-heart-o' ?>"></i>
                        </div>
                    </div>
                    <div class="car-image">
                        <img src="assets/images/products/<?= $car['image'] ?>" alt="<?= $car['brand'] ?>">
                    </div>
                    <div class="car-specs">
                        <div class="spec-item">
                            <img src="assets/images/icons/gas-station.svg" alt="Fuel">
                            <span>70L</span>
                        </div>
                        <div class="spec-item">
                            <img src="assets/images/icons/car.svg" alt="Manual">
                            <span>Manual</span>
                        </div>
                        <div class="spec-item">
                            <img src="assets/images/icons/profile-2user.svg" alt="People">
                            <span>2 People</span>
                        </div>
                    </div>
                    <div class="car-footer">
                        <div class="price">
                            <span class="amount">€<?= $car['price'] ?></span>
                            <span class="period">/dag</span>
                        </div>
                        <a href="car-detail.php" class="rent-now-btn">Rent Now</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="pagination">
                <button class="show-more-btn">Show more cars</button>
                <div class="page-indicator">10/10</div>
            </div>
        </div>
    </div>
</main>

<?php require "includes/footer.php" ?>
