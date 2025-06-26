<?php
// User must be logged in to access this page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id'])) {
    header('Location: /login-form');
    exit;
}

// Include database connection
require_once __DIR__ . '/../includes/db_connect.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // Get user's favorite cars using a simplified query
    $user_id = $_SESSION['id'];
    
    // First check if the user has any favorites at all
    $check_favorites = $conn->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = :user_id");
    $check_favorites->bindParam(":user_id", $user_id);
    $check_favorites->execute();
    $has_favorites = $check_favorites->fetchColumn() > 0;
    
    $favorites = [];
    
    if ($has_favorites) {
        // If user has favorites, get the details
        $stmt = $conn->prepare("
            SELECT c.*, f.created_at as favorite_date 
            FROM favorites f
            LEFT JOIN cars c ON f.car_id = c.id
            WHERE f.user_id = :user_id
            ORDER BY f.created_at DESC
        ");
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        $favorites = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    
    error_log("Error in my-favorites.php: " . $e->getMessage());
    $favorites = [];
}

$pageTitle = "Mijn Favorieten";
include_once __DIR__ . '/../includes/header.php';
?>


<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<style>
    .favorites-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .page-title {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 10px;
        color: #333;
    }
    
    .page-subtitle {
        font-size: 16px;
        color: #666;
        margin-bottom: 30px;
    }
    
    .favorites-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 25px;
        margin-bottom: 40px;
    }
    
    .car-card {
        background-color: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        position: relative;
    }
    
    .car-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.12);
    }
    
    .car-image-container {
        height: 180px;
        overflow: hidden;
        position: relative;
    }
    
    .car-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .car-card:hover .car-image {
        transform: scale(1.05);
    }
    
    .favorite-button {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 10;
        background-color: rgba(255, 255, 255, 0.8);
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 18px;
        color: #ff3b58;
        transition: all 0.2s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    
    .favorite-button:hover {
        transform: scale(1.1);
    }
    
    .car-details {
        padding: 20px;
    }
    
    .car-brand {
        font-weight: 700;
        font-size: 20px;
        margin-bottom: 5px;
        color: #333;
    }
    
    .car-info {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
        font-size: 14px;
        color: #666;
    }
    
    .car-features {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 15px;
    }
    
    .car-feature {
        font-size: 12px;
        background-color: #f5f5f5;
        padding: 5px 10px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        gap: 5px;
        color: #555;
    }
    
    .car-feature i {
        color: #ff3b58;
    }
    
    .car-price {
        font-size: 18px;
        font-weight: 700;
        color: #ff3b58;
        margin-bottom: 15px;
    }
    
    .car-actions {
        display: flex;
        justify-content: flex-end;
    }
    
    .button-primary {
        background-color: #ff3b58;
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.2s ease;
    }
    
    .button-primary:hover {
        background-color: #e52d4a;
        transform: translateY(-2px);
    }
    
    .empty-favorites {
        text-align: center;
        padding: 60px 20px;
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    
    .empty-favorites i {
        font-size: 70px;
        color: #ddd;
        margin-bottom: 20px;
        display: block;
    }
    
    .empty-favorites h3 {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 10px;
        color: #333;
    }
    
    .empty-favorites p {
        font-size: 16px;
        color: #666;
        margin-bottom: 25px;
        max-width: 400px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .notification {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background-color: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        z-index: 1000;
        display: none;
        max-width: 300px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    @media (max-width: 768px) {
        .favorites-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
        }
        
        .page-title {
            font-size: 24px;
        }
        
        .car-image-container {
            height: 160px;
        }
    }
</style>

<main class="favorites-container">
    <div class="page-header">
        <h1 class="page-title">Mijn Favoriete Auto's</h1>
        <p class="page-subtitle">Bekijk en beheer de auto's die u hebt opgeslagen als favoriet</p>
    </div>
    
    <?php if (count($favorites) > 0): ?>
        <div class="favorites-grid">
            <?php foreach ($favorites as $car): ?>
                <div class="car-card" id="car-<?= $car['id'] ?>">
                    <button class="favorite-button active" data-car-id="<?= $car['id'] ?>">
                        <i class="fas fa-heart"></i>
                    </button>
                    <div class="car-image-container">
                        <img src="<?= htmlspecialchars($car['image_url'] ?? 'assets/images/cars/default.jpg') ?>" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>" class="car-image">
                    </div>
                    <div class="car-details">
                        <h3 class="car-brand"><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h3>
                        
                        <div class="car-info">
                            <?php if (!empty($car['year'])): ?>
                                <span><?= htmlspecialchars($car['year']) ?></span>
                            <?php endif; ?>
                            
                            <?php if (!empty($car['fuel_type'])): ?>
                                <span><?= htmlspecialchars($car['fuel_type']) ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="car-features">
                            <?php if (!empty($car['transmission'])): ?>
                                <span class="car-feature"><i class="fas fa-cog"></i> <?= htmlspecialchars($car['transmission']) ?></span>
                            <?php endif; ?>
                            
                            <?php if (!empty($car['capacity'])): ?>
                                <span class="car-feature"><i class="fas fa-user"></i> <?= htmlspecialchars($car['capacity']) ?></span>
                            <?php endif; ?>
                            
                            <?php if (!empty($car['category'])): ?>
                                <span class="car-feature"><i class="fas fa-car"></i> <?= htmlspecialchars($car['category']) ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="car-price">€<?= htmlspecialchars(number_format($car['price'] ?? 0, 2, ',', '.')) ?> / dag</div>
                        
                        <div class="car-actions">
                            <a href="/car-detail?id=<?= $car['id'] ?>" class="button-primary">
                                Details <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-favorites">
            <i class="far fa-heart"></i>
            <h3>Geen favoriete auto's gevonden</h3>
            <p>U heeft nog geen auto's aan uw favorieten toegevoegd. Bekijk ons aanbod en voeg de auto's toe die u interessant vindt.</p>
            <a href="/ons-aanbod" class="button-primary">Bekijk ons aanbod <i class="fas fa-car"></i></a>
        </div>
    <?php endif; ?>
</main>

<div class="notification" id="notification">
    Auto verwijderd uit favorieten
</div>

<script>
    // Handle favorite buttons
    document.querySelectorAll('.favorite-button').forEach(button => {
        button.addEventListener('click', function() {
            const carId = this.getAttribute('data-car-id');
            const carCard = document.getElementById('car-' + carId);
            
            // Send AJAX request to toggle favorite
            const formData = new FormData();
            formData.append('car_id', carId);
            
            fetch('/toggle-favorite', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (!data.is_favorite) {
                        // Remove from view with animation
                        carCard.style.opacity = '0';
                        carCard.style.transform = 'scale(0.8)';
                        carCard.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                        
                        // Show notification
                        const notification = document.getElementById('notification');
                        notification.textContent = 'Auto verwijderd uit favorieten';
                        notification.style.display = 'block';
                        
                        // Hide notification after 3 seconds
                        setTimeout(() => {
                            notification.style.display = 'none';
                        }, 3000);
                        
                        // Remove the element after animation completes
                        setTimeout(() => {
                            carCard.remove();
                            
                            // Check if there are no more favorites
                            if (document.querySelectorAll('.car-card').length === 0) {
                                document.querySelector('.favorites-grid').innerHTML = `
                                    <div class="empty-favorites">
                                        <i class="far fa-heart"></i>
                                        <h3>Geen favoriete auto's gevonden</h3>
                                        <p>U heeft nog geen auto's aan uw favorieten toegevoegd. Bekijk ons aanbod en voeg de auto's toe die u interessant vindt.</p>
                                        <a href="/ons-aanbod" class="button-primary">Bekijk ons aanbod <i class="fas fa-car"></i></a>
                                    </div>
                                `;
                            }
                        }, 300);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?> 