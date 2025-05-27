<?php require "includes/header.php"; ?>
<?php require "includes/db_connect.php"; ?>

<?php
// Check if user is logged in
$is_logged_in = isset($_SESSION['id']);
$is_favorite = false;

// Check if car is in user's favorites
if ($is_logged_in && isset($_GET['id'])) {
    $car_id = $_GET['id'];
    $user_id = $_SESSION['id'];
    
    try {
        $favorite_check = $conn->prepare("SELECT id FROM favorites WHERE user_id = :user_id AND car_id = :car_id");
        $favorite_check->bindParam(':user_id', $user_id);
        $favorite_check->bindParam(':car_id', $car_id);
        $favorite_check->execute();
        
        $is_favorite = $favorite_check->rowCount() > 0;
    } catch (PDOException $e) {
        // Silently fail
    }
}

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
        'id' => 1, // Add default ID for fallback data
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

<!-- Add Font Awesome for icons -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<main class="car-detail">
    <div class="grid">
        <div class="row">
            <div class="advertorial">
                <button class="favorite-button <?= $is_favorite ? 'active' : '' ?>" data-car-id="<?= htmlspecialchars($car['id'] ?? 1) ?>">
                    <i class="fas fa-heart"></i>
                </button>
                <h2><?= htmlspecialchars($car_full_name) ?> - Ultieme rijervaring</h2>
                <p>Ervaar de kracht en het raffinement van deze geweldige auto</p>
                <div class="car-image-container">
                    <img src="<?= htmlspecialchars($car['image_url']) ?>" alt="<?= htmlspecialchars($car_full_name) ?>">
                </div>
                <img src="assets/images/header-circle-background.svg" alt="" class="background-header-element">
            </div>
        </div>
        <div class="row white-background">
            <h2><?= htmlspecialchars($car_full_name) ?></h2>
            <div class="rating">
                <span class="stars stars-4"></span>
                <span>440+ beoordelingen</span>
            </div>
            <p><?= htmlspecialchars($car['description'] ?? 'Geen beschrijving beschikbaar.') ?></p>
            <div class="car-type">
                <div class="grid">
                    <div class="row"><span class="accent-color">Type Auto</span><span><?= htmlspecialchars($car['category']) ?></span></div>
                    <div class="row"><span class="accent-color">Capaciteit</span><span><?= htmlspecialchars($car['capacity']) ?></span></div>
                </div>
                <div class="grid">
                    <div class="row"><span class="accent-color">Besturing</span><span><?= htmlspecialchars($car['transmission']) ?></span></div>
                    <div class="row"><span class="accent-color">Brandstof</span><span><?= htmlspecialchars($car['fuel_capacity']) ?></span></div>
                </div>
                <div class="call-to-action">
                    <div class="row price-display">
                        <?php if (!empty($car['original_price'])): ?>
                            <span class="original-price">€<?= htmlspecialchars($car['original_price']) ?></span>
                        <?php endif; ?>
                        <span class="font-weight-bold">€<?= htmlspecialchars($car['price']) ?></span> / dag
                    </div>
                    <div class="row">
                        <a href="/reserveren?car_id=<?= htmlspecialchars($car['id'] ?? 1) ?>" class="button-primary">Huur nu</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row white-background">
            <h3>Specificaties</h3>
            <ul class="car-specs">
                <li><strong>Merk:</strong> <?= htmlspecialchars($car['brand']) ?></li>
                <?php if (!empty($car['model'])): ?>
                    <li><strong>Model:</strong> <?= htmlspecialchars($car['model']) ?></li>
                <?php endif; ?>
                <li><strong>Categorie:</strong> <?= htmlspecialchars($car['category']) ?></li>
                <li><strong>Transmissie:</strong> <?= htmlspecialchars($car['transmission']) ?></li>
                <li><strong>Capaciteit:</strong> <?= htmlspecialchars($car['capacity']) ?></li>
                <li><strong>Brandstof capaciteit:</strong> <?= htmlspecialchars($car['fuel_capacity']) ?></li>
                <li><strong>Dagprijs:</strong> €<?= htmlspecialchars($car['price']) ?>/dag</li>
            </ul>
            
            <p class="car-note">
                * Alle prijzen zijn inclusief btw. Brandstof en verzekering zijn inbegrepen in de huurprijs.
                Bekijk onze <a href="/terms-and-conditions">algemene voorwaarden</a> voor meer informatie.
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
.advertorial {
    position: relative;
}
.favorite-button {
    position: absolute;
    top: 15px;
    right: 15px;
    z-index: 100;
    background-color: rgba(255, 255, 255, 0.8);
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 20px;
    color: #ccc;
    transition: all 0.2s ease;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}
.favorite-button.active {
    color: #ff3b58;
}
.favorite-button:hover {
    transform: scale(1.1);
}
.button-primary {
    background-color: #3563E9;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 16px;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    transition: all 0.3s ease;
    width: 100%;
    box-shadow: 0 4px 10px rgba(53, 99, 233, 0.3);
}
.button-primary:hover {
    background-color: #2954d4;
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(53, 99, 233, 0.4);
}
.button-secondary {
    background-color: #6c757d;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 16px;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    transition: all 0.3s ease;
    width: 100%;
    box-shadow: 0 4px 10px rgba(108, 117, 125, 0.3);
}
.button-secondary:hover {
    background-color: #5a6268;
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(90, 98, 104, 0.4);
}
.call-to-action {
    margin-top: 20px;
}
.call-to-action .row {
    margin-bottom: 15px;
}
</style>

<!-- Add favorites JavaScript -->
<script src="/assets/js/favorites.js"></script>

<!-- Mark the body as logged in if user is logged in -->
<?php if ($is_logged_in): ?>
<script>
    console.log("User is logged in with ID: <?= $_SESSION['id'] ?>");
    document.body.classList.add('logged-in');
</script>
<?php else: ?>
<script>
    console.log("User is NOT logged in");
</script>
<?php endif; ?>

<!-- Debug script to help identify the issue -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add debugging for favorite button clicks
    document.querySelectorAll('.favorite-button').forEach(button => {
        button.addEventListener('click', function() {
            console.log('Favorite button clicked');
            console.log('Car ID:', this.getAttribute('data-car-id'));
            console.log('Is logged in:', document.body.classList.contains('logged-in'));
        });
    });
    
    // Override fetch for debugging
    const originalFetch = window.fetch;
    window.fetch = function(url, options) {
        console.log('Fetch request to:', url);
        console.log('Fetch options:', options);
        
        return originalFetch(url, options)
            .then(response => {
                console.log('Fetch response status:', response.status);
                console.log('Fetch response OK:', response.ok);
                // Clone the response so we can log it and still use it
                const clone = response.clone();
                clone.text().then(text => {
                    try {
                        const json = JSON.parse(text);
                        console.log('Response JSON:', json);
                    } catch (e) {
                        console.log('Response text:', text);
                    }
                });
                return response;
            })
            .catch(error => {
                console.error('Fetch error:', error);
                throw error;
            });
    };
});
</script>

<?php require "includes/footer.php" ?>
