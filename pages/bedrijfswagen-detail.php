<?php require "includes/header.php" ?>
<?php require "includes/db_connect.php" ?>

<?php
// Get the business vehicle ID from the URL parameter
$vehicle_id = $_GET['id'] ?? null;

// Initialize vehicle data
$vehicle = null;

// Try to fetch business vehicle from database
if ($vehicle_id) {
    try {
        $stmt = $conn->prepare("SELECT * FROM cars WHERE id = :id AND type = 'business'");
        $stmt->bindParam(':id', $vehicle_id);
        $stmt->execute();
        $vehicle = $stmt->fetch();
        
        // If no business vehicle found with ID, try to get any business vehicle as fallback
        if (!$vehicle) {
            $stmt = $conn->prepare("SELECT * FROM cars WHERE type = 'business' LIMIT 1");
            $stmt->execute();
            $vehicle = $stmt->fetch();
        }
    } catch(PDOException $e) {
        // If there's an error, fallback to static data
        $vehicle = null;
    }
}

// If no vehicle found in database or if ID is 'citroen', use fallback data
if (!$vehicle || $vehicle_id === 'citroen') {
    // Fallback data for display
    $vehicle = [
        'brand' => 'Citroën',
        'model' => 'Berlingo',
        'category' => 'Transport',
        'image_url' => 'assets/images/citroene.avif',
        'description' => 'De Citroën Berlingo combineert functionaliteit met comfort. Deze compacte bedrijfswagen biedt een verrassend ruim laadvolume van 3,3m³ en heeft innovatieve schuifdeuren aan beide zijden voor gemakkelijke toegang. Perfect voor stedelijk gebruik.',
        'capacity' => '3 People',
        'transmission' => 'Manual',
        'fuel_capacity' => '50l',
        'price' => '89.00'
    ];
}

// Ensure we have a full vehicle name
$vehicle_full_name = trim($vehicle['brand'] . ' ' . ($vehicle['model'] ?? ''));
?>
<main class="car-detail">
    <div class="grid">
        <div class="row">
            <div class="advertorial">
                <h2><?= htmlspecialchars($vehicle_full_name) ?> - Reliable and practical</h2>
                <p>The ideal business vehicle for your professional needs</p>
                <img src="<?= htmlspecialchars($vehicle['image_url']) ?>" alt="<?= htmlspecialchars($vehicle_full_name) ?>" style="height: 240px; object-fit: contain;">
                <img src="assets/images/header-circle-background.svg" alt="" class="background-header-element">
            </div>
        </div>
        <div class="row white-background">
            <h2><?= htmlspecialchars($vehicle_full_name) ?></h2>
            <div class="rating">
                <span class="stars stars-4"></span>
                <span>320+ reviewers</span>
            </div>
            <p><?= htmlspecialchars($vehicle['description'] ?? 'No description available.') ?></p>
            <div class="car-type">
                <div class="grid">
                    <div class="row"><span class="accent-color">Type Vehicle</span><span><?= htmlspecialchars($vehicle['category']) ?></span></div>
                    <div class="row"><span class="accent-color">Capacity</span><span><?= htmlspecialchars($vehicle['capacity']) ?></span></div>
                </div>
                <div class="grid">
                    <div class="row"><span class="accent-color">Steering</span><span><?= htmlspecialchars($vehicle['transmission']) ?></span></div>
                    <div class="row"><span class="accent-color">Gasoline</span><span><?= htmlspecialchars($vehicle['fuel_capacity']) ?></span></div>
                </div>
                <div class="call-to-action">
                    <div class="row price-display">
                        <?php if (!empty($vehicle['original_price'])): ?>
                            <span class="original-price">€<?= htmlspecialchars($vehicle['original_price']) ?></span>
                        <?php endif; ?>
                        <span class="font-weight-bold">€<?= htmlspecialchars($vehicle['price']) ?></span> / day
                    </div>
                    <div class="row"><a href="reserveren.php?car_id=<?= htmlspecialchars($vehicle['id'] ?? 13) ?>" class="button-primary">Rent Now</a></div>
                </div>
            </div>
        </div>
        
        <div class="row white-background">
            <h3>Specifications</h3>
            <ul class="car-specs">
                <li><strong>Brand:</strong> <?= htmlspecialchars($vehicle['brand']) ?></li>
                <?php if (!empty($vehicle['model'])): ?>
                    <li><strong>Model:</strong> <?= htmlspecialchars($vehicle['model']) ?></li>
                <?php endif; ?>
                <li><strong>Category:</strong> <?= htmlspecialchars($vehicle['category']) ?></li>
                <li><strong>Transmission:</strong> <?= htmlspecialchars($vehicle['transmission']) ?></li>
                <li><strong>Capacity:</strong> <?= htmlspecialchars($vehicle['capacity']) ?></li>
                <li><strong>Fuel capacity:</strong> <?= htmlspecialchars($vehicle['fuel_capacity']) ?></li>
                <li><strong>Daily price:</strong> €<?= htmlspecialchars($vehicle['price']) ?>/day</li>
            </ul>
            
            <h3>Business Benefits</h3>
            <ul class="benefits">
                <li>No deposit required for business contracts</li>
                <li>24/7 roadside assistance included</li>
                <li>Flexible rental periods, from 1 day to several months</li>
                <li>Possibility of vehicle branding (by arrangement)</li>
                <li>VAT invoice available for business customers</li>
            </ul>
            
            <p class="car-note">
                * All prices include taxes. Fuel and insurance are included in the rental price.
                View our <a href="#">terms and conditions</a> for more information.
            </p>
        </div>
    </div>
</main>

<style>
.price-display {
    display: flex;
    align-items: center;
}
.original-price {
    text-decoration: line-through;
    color: #999;
    margin-right: 10px;
    font-size: 0.9em;
}
</style>

<?php require "includes/footer.php" ?> 