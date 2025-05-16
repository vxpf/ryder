<?php require "includes/header.php" ?>
<?php require "includes/db_connect.php" ?>

<?php
// Get the car ID from the URL parameter
$car_id = $_GET['id'] ?? null;

// Initialize car data
$car = null;

// Try to fetch car from database
if ($car_id) {
    try {
        $stmt = $conn->prepare("SELECT * FROM cars WHERE id = :id AND type = 'regular'");
        $stmt->bindParam(':id', $car_id);
        $stmt->execute();
        $car = $stmt->fetch();
        
        // If no car found with ID, try to get any regular car as fallback
        if (!$car) {
            $stmt = $conn->prepare("SELECT * FROM cars WHERE type = 'regular' LIMIT 1");
            $stmt->execute();
            $car = $stmt->fetch();
        }
    } catch(PDOException $e) {
        // If there's an error, fallback to static data
        $car = null;
    }
}

// If no car found in database, use fallback data
if (!$car) {
    // Fallback data for display
    $car = [
        'brand' => 'Koenigsegg',
        'model' => '',
        'category' => 'Sport',
        'image_url' => 'assets/images/products/car (0).svg',
        'description' => 'De Koenigsegg is een Zweedse hypercar met adembenemende prestaties. Met zijn lichtgewicht constructie en krachtige motor biedt deze auto een ongeëvenaarde rijervaring voor liefhebbers van pure snelheid.',
        'capacity' => '2 People',
        'transmission' => 'Manual',
        'fuel_capacity' => '90l',
        'price' => '99.00',
        'original_price' => null
    ];
}

// Ensure we have a full car name
$car_full_name = trim($car['brand'] . ' ' . ($car['model'] ?? ''));
?>
<main class="car-detail">
    <div class="grid">
        <div class="row">
            <div class="advertorial">
                <h2><?= htmlspecialchars($car_full_name) ?> - Ultieme rijervaring</h2>
                <p>Ervaar de kracht en het raffinement van deze geweldige auto</p>
                <img src="<?= htmlspecialchars($car['image_url']) ?>" alt="<?= htmlspecialchars($car_full_name) ?>">
                <img src="assets/images/header-circle-background.svg" alt="" class="background-header-element">
            </div>
        </div>
        <div class="row white-background">
            <h2><?= htmlspecialchars($car_full_name) ?></h2>
            <div class="rating">
                <span class="stars stars-4"></span>
                <span>440+ reviewers</span>
            </div>
            <p><?= htmlspecialchars($car['description'] ?? 'Geen beschrijving beschikbaar.') ?></p>
            <div class="car-type">
                <div class="grid">
                    <div class="row"><span class="accent-color">Type Car</span><span><?= htmlspecialchars($car['category']) ?></span></div>
                    <div class="row"><span class="accent-color">Capacity</span><span><?= htmlspecialchars($car['capacity']) ?></span></div>
                </div>
                <div class="grid">
                    <div class="row"><span class="accent-color">Steering</span><span><?= htmlspecialchars($car['transmission']) ?></span></div>
                    <div class="row"><span class="accent-color">Gasoline</span><span><?= htmlspecialchars($car['fuel_capacity']) ?></span></div>
                </div>
                <div class="call-to-action">
                    <div class="row price-display">
                        <?php if (!empty($car['original_price'])): ?>
                            <span class="original-price">€<?= htmlspecialchars($car['original_price']) ?></span>
                        <?php endif; ?>
                        <span class="font-weight-bold">€<?= htmlspecialchars($car['price']) ?></span> / day
                    </div>
                    <div class="row"><a href="reserveren.php?car_id=<?= htmlspecialchars($car['id'] ?? 1) ?>" class="button-primary">Rent Now</a></div>
                </div>
            </div>
        </div>
        
        <div class="row white-background">
            <h3>Specifications</h3>
            <ul class="car-specs">
                <li><strong>Brand:</strong> <?= htmlspecialchars($car['brand']) ?></li>
                <?php if (!empty($car['model'])): ?>
                    <li><strong>Model:</strong> <?= htmlspecialchars($car['model']) ?></li>
                <?php endif; ?>
                <li><strong>Category:</strong> <?= htmlspecialchars($car['category']) ?></li>
                <li><strong>Transmission:</strong> <?= htmlspecialchars($car['transmission']) ?></li>
                <li><strong>Capacity:</strong> <?= htmlspecialchars($car['capacity']) ?></li>
                <li><strong>Fuel capacity:</strong> <?= htmlspecialchars($car['fuel_capacity']) ?></li>
                <li><strong>Daily price:</strong> €<?= htmlspecialchars($car['price']) ?>/day</li>
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
