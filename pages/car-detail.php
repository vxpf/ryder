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

<!-- REVIEWS SECTION START -->
<div class="reviews-container">
    <div class="reviews-card">
        <div class="reviews-header">
            <div class="reviews-title">
                <span class="reviews-label">Reviews</span>
                <span class="reviews-count"><?php
                    $review_count = 0;
                    try {
                        $stmt = $conn->prepare("SELECT COUNT(*) FROM reviews WHERE car_id = :car_id");
                        $stmt->bindParam(':car_id', $car['id']);
                        $stmt->execute();
                        $review_count = $stmt->fetchColumn();
                    } catch (Exception $e) {}
                    echo $review_count;
                ?></span>
            </div>
            <a href="#" class="view-all-btn">View All</a>
        </div>
        
        <div class="reviews-list">
            <?php
            try {
                $stmt = $conn->prepare("SELECT r.*, u.name, u.profile_photo, u.subtitle FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.car_id = :car_id ORDER BY r.created_at DESC LIMIT 2");
                $stmt->bindParam(':car_id', $car['id']);
                $stmt->execute();
                $reviews = $stmt->fetchAll();
            } catch (Exception $e) { $reviews = []; }
            
            if (empty($reviews)) {
                // Show sample reviews for demonstration
                $reviews = [
                    [
                        'name' => 'Alex Stanton',
                        'subtitle' => 'CEO at Bukalapak',
                        'profile_photo' => '/assets/images/Profil.png',
                        'rating' => 4,
                        'comment' => 'We are very happy with the service from the MORENT App. Morent has a low price and also a large variety of cars with good and comfortable facilities. In addition, the service provided by the officers is also very friendly and very polite.',
                        'created_at' => '2024-06-15 10:30:00'
                    ],
                    [
                        'name' => 'Skylar Dias',
                        'subtitle' => 'CEO at Amazon',
                        'profile_photo' => '/assets/images/Profil.png',
                        'rating' => 4,
                        'comment' => 'We are greatly helped by the services of the MORENT Application. Morent has low prices and also a wide variety of cars with good and comfortable facilities. In addition, the service provided by the officers is also very friendly and very polite.',
                        'created_at' => '2024-06-14 15:45:00'
                    ]
                ];
            }
            
            foreach ($reviews as $review):
                $avatar = !empty($review['profile_photo']) ? htmlspecialchars($review['profile_photo']) : '/assets/images/Profil.png';
                $name = htmlspecialchars($review['name'] ?? 'Gebruiker');
                $subtitle = htmlspecialchars($review['subtitle'] ?? '');
                $date = date('d M Y', strtotime($review['created_at']));
                $rating = (int)$review['rating'];
                $comment = nl2br(htmlspecialchars($review['comment']));
            ?>
                <div class="review-item">
                    <div class="review-left">
                        <img class="review-avatar" src="<?= $avatar ?>" alt="Profile picture">
                        <div class="review-info">
                            <div class="review-name"><?= $name ?></div>
                            <div class="review-subtitle"><?= $subtitle ?></div>
                            <div class="review-date"><?= $date ?></div>
                        </div>
                    </div>
                    <div class="review-right">
                        <div class="review-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fa fa-star<?= $i > $rating ? '-o' : '' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <div class="review-text"><?= $comment ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<!-- REVIEWS SECTION END -->

<style>
/* ... keep existing code (existing styles) */
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

/* Updated Reviews Section - Matching the design exactly */
.reviews-container {
    max-width: 1200px;
    margin: 32px auto;
    padding: 0 20px;
}

.reviews-card {
    background: #FFFFFF;
    border-radius: 10px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.reviews-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 32px;
}

.reviews-title {
    display: flex;
    align-items: center;
    gap: 8px;
}

.reviews-label {
    background: #3563E9;
    color: #FFFFFF;
    padding: 8px 20px;
    border-radius: 4px;
    font-weight: 600;
    font-size: 16px;
    line-height: 24px;
}

.reviews-count {
    background: #F6F7F9;
    color: #3563E9;
    padding: 8px 12px;
    border-radius: 4px;
    font-weight: 700;
    font-size: 16px;
    line-height: 24px;
}

.view-all-btn {
    color: #3563E9;
    font-weight: 600;
    font-size: 16px;
    line-height: 24px;
    text-decoration: none;
    transition: opacity 0.2s ease;
}

.view-all-btn:hover {
    opacity: 0.8;
}

.reviews-list {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.review-item {
    display: flex;
    gap: 16px;
    align-items: flex-start;
}

.review-left {
    display: flex;
    gap: 16px;
    align-items: flex-start;
    min-width: 200px;
}

.review-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}

.review-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.review-name {
    font-weight: 700;
    font-size: 20px;
    line-height: 30px;
    color: #1A202C;
}

.review-subtitle {
    font-weight: 500;
    font-size: 14px;
    line-height: 21px;
    color: #90A3BF;
}

.review-date {
    font-weight: 500;
    font-size: 14px;
    line-height: 21px;
    color: #90A3BF;
}

.review-right {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.review-rating {
    display: flex;
    gap: 4px;
    color: #FBAD39;
    font-size: 16px;
}

.review-rating .fa-star-o {
    color: #E0E4E7;
}

.review-text {
    font-weight: 400;
    font-size: 14px;
    line-height: 28px;
    color: #596780;
}

/* Responsive design */
@media (max-width: 768px) {
    .reviews-container {
        padding: 0 16px;
    }
    
    .reviews-card {
        padding: 16px;
    }
    
    .review-item {
        flex-direction: column;
        gap: 12px;
    }
    
    .review-left {
        min-width: auto;
    }
    
    .reviews-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }
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
