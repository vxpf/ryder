<?php require "includes/header.php" ?>
<?php require_once "includes/db_connect.php";

// Function to update car availability status based on active bookings
function updateCarAvailabilityStatus($conn) {
    try {
        // Get current date
        $today = date('Y-m-d');
        
        // First, set all cars to available
        $reset_stmt = $conn->prepare("UPDATE cars SET is_available = 1");
        $reset_stmt->execute();
        
        // Then, set cars with active bookings to unavailable
        $update_stmt = $conn->prepare("
            UPDATE cars c
            SET c.is_available = 0
            WHERE EXISTS (
                SELECT 1 FROM bookings b
                WHERE b.car_id = c.id
                AND b.status IN ('pending', 'confirmed')
                AND :today BETWEEN b.start_date AND b.end_date
            )
        ");
        $update_stmt->bindParam(':today', $today);
        $update_stmt->execute();
        
        return true;
    } catch(PDOException $e) {
        error_log("Error updating car availability: " . $e->getMessage());
        return false;
    }
}

// Update car availability status
updateCarAvailabilityStatus($conn);

// Get filter parameters from URL
$category_filter = isset($_GET['category']) ? (is_array($_GET['category']) ? $_GET['category'] : explode(',', $_GET['category'])) : [];
$capacity_filter = isset($_GET['capacity']) ? (is_array($_GET['capacity']) ? $_GET['capacity'] : explode(',', $_GET['capacity'])) : [];
$price_filter = isset($_GET['price']) ? intval($_GET['price']) : 150;
$availability_filter = isset($_GET['available']) ? ($_GET['available'] === '1') : false;
$vehicle_type = isset($_GET['type']) ? $_GET['type'] : ''; // 'business' of leeg voor alle typen

// Count cars by category
$category_counts = [];
$stmt = $conn->query("SELECT category, COUNT(*) as count FROM cars GROUP BY category");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $category_counts[$row['category']] = $row['count'];
}

// Count cars by capacity
$capacity_counts = [];
$stmt = $conn->query("SELECT 
    CASE 
        WHEN capacity LIKE '%2%' THEN '2'
        WHEN capacity LIKE '%3%' THEN '3'
        WHEN capacity LIKE '%4%' THEN '4'
        WHEN capacity LIKE '%6%' THEN '6'
        WHEN capacity LIKE '%8%' THEN '8'
        ELSE 'other'
    END as capacity_group,
    COUNT(*) as count
    FROM cars
    GROUP BY capacity_group");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $capacity_counts[$row['capacity_group']] = $row['count'];
}

// Build SQL query based on filters
$sql = "SELECT * FROM cars WHERE 1=1";
$params = [];

// Apply vehicle type filter
if ($vehicle_type === 'business') {
    $sql .= " AND type = 'business'";
} else if ($vehicle_type === 'regular') {
    $sql .= " AND (type = 'regular' OR type IS NULL OR type = '')";
}

// Apply category filter
if (!empty($category_filter)) {
    $placeholders = implode(',', array_fill(0, count($category_filter), '?'));
    $sql .= " AND category IN ($placeholders)";
    $params = array_merge($params, $category_filter);
}

// Apply capacity filter
if (!empty($capacity_filter)) {
    $capacity_conditions = [];
    foreach ($capacity_filter as $capacity) {
        $capacity_conditions[] = "capacity LIKE ?";
        $params[] = "%$capacity%";
    }
    $sql .= " AND (" . implode(" OR ", $capacity_conditions) . ")";
}

// Apply price filter
$sql .= " AND price <= ?";
$params[] = $price_filter;

// Apply availability filter
if ($availability_filter) {
    $sql .= " AND is_available = 1";
}

// Add ordering
$sql .= " ORDER BY type, brand";

// Execute query
try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Error fetching cars: " . $e->getMessage());
    $vehicles = []; // Empty array if there's an error
}

// Get min and max prices for slider
try {
    $price_stmt = $conn->query("SELECT MIN(price) as min_price, MAX(price) as max_price FROM cars");
    $price_range = $price_stmt->fetch(PDO::FETCH_ASSOC);
    $min_price = floor($price_range['min_price']);
    $max_price = ceil($price_range['max_price']);
} catch(PDOException $e) {
    $min_price = 0;
    $max_price = 150;
}
?>

<main class="aanbod-page">
    <div class="aanbod-container">
        <form id="filter-form" class="filters-sidebar" method="GET" action="/ons-aanbod">
            <div class="filter-header">
                <h2>Filters</h2>
                <button type="button" id="reset-filters" class="reset-button">Reset filters</button>
            </div>
            
            <div class="filter-section">
                <h3>CATEGORIE</h3>
                <div class="filter-options">
                    <?php foreach ($category_counts as $category => $count): ?>
                    <div class="filter-option">
                        <input type="checkbox" id="category-<?= strtolower($category) ?>" name="category[]" value="<?= $category ?>" 
                            <?= in_array($category, $category_filter) ? 'checked' : '' ?>>
                        <label for="category-<?= strtolower($category) ?>"><?= $category ?> <span class="count">(<?= $count ?>)</span></label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="filter-section">
                <h3>CAPACITEIT</h3>
                <div class="filter-options">
                    <?php 
                    $capacity_labels = [
                        '2' => '2 Personen',
                        '3' => '3 Personen',
                        '4' => '4 Personen',
                        '6' => '6 Personen',
                        '8' => '8 Personen'
                    ];
                    
                    foreach ($capacity_labels as $capacity_value => $capacity_label): 
                        if (isset($capacity_counts[$capacity_value])):
                    ?>
                    <div class="filter-option">
                        <input type="checkbox" id="capacity-<?= $capacity_value ?>" name="capacity[]" value="<?= $capacity_value ?>"
                            <?= in_array($capacity_value, $capacity_filter) ? 'checked' : '' ?>>
                        <label for="capacity-<?= $capacity_value ?>"><?= $capacity_label ?> <span class="count">(<?= $capacity_counts[$capacity_value] ?>)</span></label>
                    </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
            </div>

            <div class="filter-section">
                <h3>PRIJS</h3>
                <div class="price-slider">
                    <input type="range" min="<?= $min_price ?>" max="<?= $max_price ?>" value="<?= $price_filter ?>" class="slider" id="price-range" name="price">
                    <div class="price-range-labels">
                        <span>€<?= $min_price ?></span>
                        <span id="price-value">Max. €<?= $price_filter ?>,00</span>
                    </div>
                </div>
            </div>
            
            <div class="filter-section">
                <h3>BESCHIKBAARHEID</h3>
                <div class="filter-options">
                    <div class="filter-option">
                        <input type="checkbox" id="available" name="available" value="1" <?= $availability_filter ? 'checked' : '' ?>>
                        <label for="available">Alleen beschikbare auto's</label>
                    </div>
                </div>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="apply-filters-btn">Filters toepassen</button>
            </div>
        </form>

        <div class="car-listings">
            <div class="listings-header">
                <h2>Ons aanbod</h2>
                <p class="results-count"><?= count($vehicles) ?> auto's gevonden</p>
            </div>
            
            <div class="car-grid">
                <?php
                if (empty($vehicles)): ?>
                    <div class="no-results">
                        <i class="fas fa-car-slash"></i>
                        <h3>Geen auto's gevonden</h3>
                        <p>Probeer andere filtercriteria of reset de filters om alle auto's te zien.</p>
                        <a href="/ons-aanbod" class="btn-primary">Alle auto's bekijken</a>
                    </div>
                <?php else:
                foreach ($vehicles as $vehicle) :
                    // Use the image_url from the database for business vehicles
                    if ($vehicle['type'] === 'business') {
                        // First get the brand from the image_url
                        preg_match('/business\/([^\/]+)\.webp/', $vehicle['image_url'], $matches);
                        if (isset($matches[1])) {
                            $brand = $matches[1];
                            // Special cases for business vehicles
                            switch($brand) {
                                case 'Peugeot-Expert':
                                    $brand = 'Peugot-Expert';
                                    break;
                                case 'Volkswagen-Transporter':
                                    $brand = 'Volkswagen-Transporter';
                                    break;
                                case 'Ford-Transit-Custom':
                                    $brand = 'Ford-Transit-Custom';
                                    break;
                                case 'Iveco-Daily':
                                    $brand = 'Iveco-Daily';
                                    break;
                                    case 'Mercedes-Benz-Sprinter':
                                        $imgPath = "assets/images/business/Mercedes-Benz Sprinter.webp";
                                        break;
                                case 'Opel-Vivaro':
                                    $brand = 'Opel-Vivaro';
                                    break;
                                case 'Renault-Trafic':
                                    $brand = 'Renault-Trafic';
                                    break;
                            }
                            $imgPath = "assets/images/business/{$brand}.webp";
                        } else {
                            $imgPath = $vehicle['image_url'];
                        }
                        // Replace 'company' with 'business' if needed
                        $imgPath = str_replace('company/', 'business/', $imgPath);
                        // Ensure the extension is .webp
                        $imgPath = str_replace('.jpg', '.webp', $imgPath);
                    } else {
                        $imgPath = $vehicle['image_url'];
                    }
                    $detailUrl = ($vehicle['type'] === 'regular') ? "car-detail?id=" . $vehicle['id'] : "bedrijfswagen-detail?id=" . $vehicle['id'];
                    $reserveUrl = "/reserveren?car_id=" . $vehicle['id'];
                    
                    // Format price with comma as decimal separator
                    $price = number_format(floatval($vehicle['price']), 2, ',', '.');
                    
                    // Extract specs for display
                    $fuel = $vehicle['fuel_capacity'] ?? "70L";
                    $transmission = $vehicle['transmission'] ?? "Manual";
                    $capacity = $vehicle['capacity'] ?? "2 People";
                ?>
                <div class="car-card" data-category="<?= $vehicle['category'] ?>" data-price="<?= $vehicle['price'] ?>">
                    <div class="car-image">
                        <img src="<?= $imgPath ?>" alt="<?= $vehicle['brand'] ?>">
                    </div>
                    <div class="car-info">
                        <div class="car-titles">
                            <h3><?= $vehicle['brand'] ?> <?= $vehicle['model'] ?></h3>
                            <span class="car-type"><?= $vehicle['category'] ?></span>
                        </div>
                        <div class="car-specs">
                            <div class="spec">
                                <i class="fa fa-users"></i>
                                <span><?= $capacity ?></span>
                            </div>
                            <div class="spec">
                                <i class="fa fa-tachometer"></i>
                                <span><?= $transmission ?></span>
                            </div>
                            <div class="spec">
                                <i class="fa fa-gas-pump"></i>
                                <span><?= $fuel ?></span>
                            </div>
                        </div>
                        <div class="car-price-actions">
                            <div class="price-wrapper">
                                <span class="price">€<?= $price ?></span>
                                <span class="price-label">/ dag</span>
                            </div>
                            <div class="availability-status">
                                <?php if ($vehicle['is_available'] == 1): ?>
                                    <span class="available"><i class="fas fa-check-circle"></i> Beschikbaar</span>
                                <?php else: ?>
                                    <span class="unavailable"><i class="fas fa-times-circle"></i> Niet beschikbaar</span>
                                <?php endif; ?>
                            </div>
                            <div class="action-buttons">
                                <a href="<?= $detailUrl ?>" class="btn-details">Details</a>
                                <?php if ($vehicle['is_available'] == 1): ?>
                                    <a href="<?= $reserveUrl ?>" class="btn-rent">Huur nu</a>
                                <?php else: ?>
                                    <button class="btn-rent disabled" disabled>Niet beschikbaar</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; 
                endif; ?>
            </div>
        </div>
    </div>
</main>

<style>
.car-image {
    background-color: #f8f9fa;
    padding: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 200px;
}

.car-image img {
    max-height: 160px;
    width: auto;
    object-fit: contain;
    transition: transform 0.3s ease;
}

.car-card:hover .car-image img {
    transform: scale(1.05);
}

.car-card {
    position: relative;
    background-color: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    z-index: 1;
}

.car-card:hover {
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    transform: translateY(-5px);
}

/* Styling voor de actieknoppen */
.action-buttons {
    display: flex;
    gap: 10px;
    margin-top: 15px;
    width: 100%;
}

.btn-details, .btn-rent {
    padding: 12px 0;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    transition: all 0.3s ease;
    flex: 1;
}

.btn-details {
    background-color: #f1f5f9;
    color: #333;
    border: none;
}

.btn-details:hover {
    background-color: #e2e8f0;
    transform: translateY(-2px);
}

.btn-rent {
    background-color: #3563e9;
    color: white;
    border: none;
    box-shadow: 0 4px 10px rgba(53, 99, 233, 0.3);
}

.btn-rent:hover {
    background-color: #2954d4;
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(53, 99, 233, 0.4);
}

/* Extra styling voor de car card */
.car-info {
    padding: 15px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.car-titles {
    margin-bottom: 10px;
}

.car-titles h3 {
    margin: 0 0 5px 0;
    font-size: 18px;
}

.car-type {
    background-color: #3563E9;
    color: white;
    font-size: 12px;
    padding: 3px 8px;
    border-radius: 4px;
    display: inline-block;
}

.car-specs {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
}

.spec {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #666;
    font-size: 14px;
}

.spec i {
    color: #3563E9;
}

.price-wrapper {
    display: flex;
    align-items: baseline;
}

.price {
    color: #3563E9;
    font-weight: 700;
    font-size: 18px;
}

.price-label {
    color: #666;
    font-size: 14px;
    margin-left: 4px;
}

/* Availability status styling */
.availability-status {
    margin: 10px 0;
    text-align: center;
    font-size: 14px;
    font-weight: 500;
}

.available {
    color: #28a745;
}

.unavailable {
    color: #dc3545;
}

.availability-status i {
    margin-right: 5px;
}

/* Disabled button styling */
.btn-rent.disabled {
    background-color: #6c757d;
    cursor: not-allowed;
    opacity: 0.7;
    box-shadow: none;
}

.btn-rent.disabled:hover {
    background-color: #6c757d;
    transform: none;
    box-shadow: none;
}

.car-price-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: auto;
    flex-direction: column;
}

.car-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 40px;
    margin-bottom: 30px;
}

@media (max-width: 768px) {
    .car-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 25px;
    }
}

/* New filter styles */
.aanbod-container {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 30px;
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px 20px;
}

@media (max-width: 992px) {
    .aanbod-container {
        grid-template-columns: 1fr;
    }
}

.filters-sidebar {
    background-color: white;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    padding: 24px;
    height: fit-content;
    position: sticky;
    top: 100px;
}

.filter-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    border-bottom: 1px solid #eee;
    padding-bottom: 15px;
}

.filter-header h2 {
    margin: 0;
    font-size: 22px;
    font-weight: 600;
}

.reset-button {
    background: none;
    border: none;
    color: #3563E9;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    padding: 0;
}

.reset-button:hover {
    text-decoration: underline;
}

.filter-section {
    margin-bottom: 24px;
    border-bottom: 1px solid #f5f5f5;
    padding-bottom: 20px;
}

.filter-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.filter-section h3 {
    font-size: 16px;
    font-weight: 600;
    margin: 0 0 15px 0;
    color: #333;
}

.filter-options {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.filter-option {
    display: flex;
    align-items: center;
}

.filter-option input[type="checkbox"] {
    margin-right: 10px;
    width: 18px;
    height: 18px;
    accent-color: #3563E9;
}

.filter-option label {
    font-size: 15px;
    display: flex;
    justify-content: space-between;
    width: 100%;
    cursor: pointer;
}

.count {
    color: #999;
    font-size: 14px;
}

.price-slider {
    padding: 10px 0;
}

.slider {
    -webkit-appearance: none;
    width: 100%;
    height: 6px;
    border-radius: 5px;
    background: #e0e0e0;
    outline: none;
    margin-bottom: 15px;
}

.slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #3563E9;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(53, 99, 233, 0.3);
}

.slider::-moz-range-thumb {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #3563E9;
    cursor: pointer;
    border: none;
    box-shadow: 0 2px 5px rgba(53, 99, 233, 0.3);
}

.price-range-labels {
    display: flex;
    justify-content: space-between;
    font-size: 14px;
    color: #666;
}

.filter-actions {
    margin-top: 30px;
}

.apply-filters-btn {
    width: 100%;
    background-color: #3563E9;
    color: white;
    border: none;
    padding: 12px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.apply-filters-btn:hover {
    background-color: #2954d4;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(53, 99, 233, 0.3);
}

.listings-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.listings-header h2 {
    font-size: 24px;
    margin: 0;
}

.results-count {
    color: #666;
    margin: 0;
}

.no-results {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    background-color: #f9f9f9;
    border-radius: 10px;
}

.no-results i {
    font-size: 48px;
    color: #ccc;
    margin-bottom: 20px;
}

.no-results h3 {
    font-size: 22px;
    margin: 0 0 15px 0;
}

.no-results p {
    color: #666;
    margin-bottom: 20px;
}

.btn-primary {
    background-color: #3563E9;
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    display: inline-block;
    transition: all 0.2s ease;
}

.btn-primary:hover {
    background-color: #2954d4;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .filters-sidebar {
        position: static;
        margin-bottom: 20px;
    }
    
    .filter-section h3 {
        cursor: pointer;
        position: relative;
    }
    
    .filter-section h3::after {
        content: '+';
        position: absolute;
        right: 0;
        top: 0;
        font-size: 20px;
        color: #999;
    }
    
    .filter-section.active h3::after {
        content: '-';
    }
    
    .filter-options {
        display: none;
    }
    
    .filter-section.active .filter-options {
        display: flex;
    }
}
</style>

<script>
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
            heading.addEventListener('click', function() {
                section.classList.toggle('active');
            });
        });
    }
});
</script>

<?php require "includes/footer.php" ?>
