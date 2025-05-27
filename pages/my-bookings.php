<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: /login-form');
    exit;
}

// Include database connection
require_once __DIR__ . '/../includes/db_connect.php';

// Check if bookings table exists, if not create it
try {
    $tableCheck = $conn->query("SHOW TABLES LIKE 'bookings'");
    if ($tableCheck->rowCount() == 0) {
        error_log("Bookings table does not exist. Creating it now...");
        
        $createTable = $conn->prepare("
            CREATE TABLE IF NOT EXISTS bookings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                car_id INT NOT NULL,
                start_date DATE NOT NULL,
                end_date DATE NOT NULL,
                total_price DECIMAL(10,2) NOT NULL,
                status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES account(id) ON DELETE CASCADE,
                FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
            )
        ");
        $createTable->execute();
        error_log("Bookings table created successfully.");
    } else {
        error_log("Bookings table already exists.");
    }
} catch (PDOException $e) {
    error_log("Error checking/creating bookings table: " . $e->getMessage());
}

$user_id = $_SESSION['id'];
$bookings = [];
$error = null;
$success = null;

// Check for success/error messages from session
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Check for message from redirect
if (isset($_SESSION['message'])) {
    if (isset($_SESSION['error']) && $_SESSION['error']) {
        $error = $_SESSION['message'];
    } else {
        $success = $_SESSION['message'];
    }
    unset($_SESSION['message']);
    if (isset($_SESSION['error'])) {
        unset($_SESSION['error']);
    }
}

// Get all bookings for the user
try {
    $stmt = $conn->prepare("
        SELECT b.*, c.brand, c.model, c.image_url, c.category
        FROM bookings b
        JOIN cars c ON b.car_id = c.id
        WHERE b.user_id = :user_id
        ORDER BY b.created_at DESC
    ");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    // Debug info
    error_log("Executing query: SELECT b.*, c.brand, c.model, c.image_url, c.category FROM bookings b JOIN cars c ON b.car_id = c.id WHERE b.user_id = " . $user_id);
    
    $bookings = $stmt->fetchAll();
    
    // Debug info
    error_log("User ID: " . $user_id . ", Number of bookings found: " . count($bookings));
    
    if (empty($bookings)) {
        error_log("No bookings found for user ID: " . $user_id);
        
        // Check if any bookings exist at all
        $all_bookings = $conn->query("SELECT COUNT(*) as count FROM bookings");
        $result = $all_bookings->fetch();
        error_log("Total bookings in database: " . $result['count']);
        
        // Check if this user has any booking records with incorrect table reference
        $check_users = $conn->query("SHOW TABLES LIKE 'users'");
        if ($check_users->rowCount() > 0) {
            $old_bookings = $conn->prepare("
                SELECT COUNT(*) as count FROM bookings 
                WHERE user_id IN (SELECT id FROM users WHERE email = (SELECT email FROM account WHERE id = :user_id))
            ");
            $old_bookings->bindParam(':user_id', $user_id);
            $old_bookings->execute();
            $old_result = $old_bookings->fetch();
            error_log("Bookings with old user_id reference: " . $old_result['count']);
        }
    } else {
        error_log("Found " . count($bookings) . " bookings for user ID: " . $user_id);
    }
} catch(PDOException $e) {
    $error = "Er is een fout opgetreden bij het ophalen van uw reserveringen.";
    error_log("Error fetching bookings: " . $e->getMessage());
}

// Format status labels
$status_labels = [
    'pending' => 'In afwachting',
    'confirmed' => 'Bevestigd',
    'cancelled' => 'Geannuleerd',
    'completed' => 'Voltooid'
];

$status_colors = [
    'pending' => '#ff9800',
    'confirmed' => '#4caf50',
    'cancelled' => '#f44336',
    'completed' => '#2196f3'
];

$pageTitle = "Mijn Reserveringen";
require "includes/header.php";
?>

<main class="my-bookings-page">
    <div class="container">
        <div class="page-header">
            <h1>Mijn Reserveringen</h1>
            <p class="subtitle">Bekijk en beheer uw autoreserveringen</p>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($bookings)): ?>
            <div class="no-bookings">
                <i class="fas fa-calendar-times"></i>
                <h2>Geen reserveringen gevonden</h2>
                <p>U heeft nog geen auto's gereserveerd.</p>
                <a href="/ons-aanbod" class="button-primary">Bekijk beschikbare auto's</a>
            </div>
        <?php else: ?>
            <div class="bookings-list">
                <?php foreach ($bookings as $booking): ?>
                    <?php 
                        $car_name = trim($booking['brand'] . ' ' . ($booking['model'] ?? ''));
                        $status_label = $status_labels[$booking['status']] ?? $booking['status'];
                        $status_color = $status_colors[$booking['status']] ?? '#666';
                        
                        // Calculate days
                        $start_date = new DateTime($booking['start_date']);
                        $end_date = new DateTime($booking['end_date']);
                        $interval = $start_date->diff($end_date);
                        $days = $interval->days;
                        if ($days < 1) $days = 1;
                        
                        // Always allow deletion of any booking
                        $canDelete = true;
                    ?>
                    <div class="booking-card">
                        <div class="booking-status" style="--status-color: <?= $status_color ?>">
                            <i class="status-indicator"></i>
                            <span><?= htmlspecialchars($status_label) ?></span>
                        </div>
                        
                        <div class="booking-content">
                            <div class="booking-car">
                                <img src="<?= htmlspecialchars($booking['image_url']) ?>" alt="<?= htmlspecialchars($car_name) ?>">
                                <div class="car-info">
                                    <h3><?= htmlspecialchars($car_name) ?></h3>
                                    <p class="car-category"><?= htmlspecialchars($booking['category']) ?></p>
                                </div>
                            </div>
                            
                            <div class="booking-details">
                                <div class="booking-dates">
                                    <div class="date-item">
                                        <span class="date-label">Ophalen</span>
                                        <span class="date-value"><?= date('d-m-Y', strtotime($booking['start_date'])) ?></span>
                                    </div>
                                    <div class="date-arrow">
                                        <i class="fas fa-arrow-right"></i>
                                    </div>
                                    <div class="date-item">
                                        <span class="date-label">Retour</span>
                                        <span class="date-value"><?= date('d-m-Y', strtotime($booking['end_date'])) ?></span>
                                    </div>
                                </div>
                                
                                <div class="booking-info">
                                    <div class="info-item">
                                        <span class="info-label">Aantal dagen</span>
                                        <span class="info-value"><?= $days ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Reserveringsnummer</span>
                                        <span class="info-value">#<?= $booking['id'] ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Totaalbedrag</span>
                                        <span class="info-value price">€<?= number_format($booking['total_price'], 2, ',', '.') ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="booking-actions">
                            <a href="/booking-confirmation?id=<?= $booking['id'] ?>" class="action-button view">
                                <i class="fas fa-eye"></i> Bekijken
                            </a>
                            
                            <?php if ($booking['status'] === 'confirmed' && strtotime($booking['start_date']) > time()): ?>
                                <a href="#" class="action-button cancel" onclick="confirmCancel(<?= $booking['id'] ?>); return false;">
                                    <i class="fas fa-times"></i> Annuleren
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($canDelete): ?>
                                <a href="#" class="action-button delete" onclick="confirmDelete(<?= $booking['id'] ?>); return false;">
                                    <i class="fas fa-trash"></i> Verwijderen
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
.my-bookings-page {
    padding: 40px 0;
    font-family: 'Plus Jakarta Sans', sans-serif;
    color: #333;
    background-color: #f8f9fa;
}

.container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 15px;
}

.page-header {
    margin-bottom: 30px;
    text-align: center;
}

h1 {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 8px;
}

.subtitle {
    font-size: 16px;
    color: #666;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    background-color: #fff5f5;
    color: #d32f2f;
    border-left: 4px solid #d32f2f;
}

.alert-success {
    background-color: #f0f9f0;
    color: #2e7d32;
    border-left: 4px solid #2e7d32;
}

.alert i {
    font-size: 20px;
    margin-right: 10px;
}

.no-bookings {
    text-align: center;
    background-color: white;
    border-radius: 12px;
    padding: 50px 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.no-bookings i {
    font-size: 60px;
    color: #ccc;
    margin-bottom: 20px;
}

.no-bookings h2 {
    font-size: 24px;
    margin-bottom: 10px;
}

.no-bookings p {
    color: #666;
    margin-bottom: 25px;
}

.button-primary {
    display: inline-block;
    background-color: #3563E9;
    color: white;
    font-weight: 600;
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.2s;
}

.button-primary:hover {
    background-color: #2954d4;
    transform: translateY(-2px);
}

.bookings-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.booking-card {
    background-color: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    position: relative;
}

.booking-status {
    position: absolute;
    top: 15px;
    right: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
    background-color: white;
    padding: 5px 12px;
    border-radius: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    font-size: 14px;
    font-weight: 600;
    color: var(--status-color);
}

.status-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: var(--status-color);
}

.booking-content {
    padding: 25px;
}

.booking-car {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 20px;
}

.booking-car img {
    width: 100px;
    height: 70px;
    object-fit: cover;
    border-radius: 8px;
}

.car-info h3 {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 5px;
}

.car-category {
    display: inline-block;
    background-color: #3563E9;
    color: white;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.booking-details {
    display: flex;
    flex-wrap: wrap;
    gap: 25px;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

.booking-dates {
    display: flex;
    align-items: center;
    gap: 15px;
}

.date-item {
    display: flex;
    flex-direction: column;
}

.date-label {
    font-size: 14px;
    color: #666;
    margin-bottom: 5px;
}

.date-value {
    font-weight: 600;
}

.date-arrow {
    color: #ccc;
}

.booking-info {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.info-item {
    display: flex;
    flex-direction: column;
}

.info-label {
    font-size: 14px;
    color: #666;
    margin-bottom: 5px;
}

.info-value {
    font-weight: 600;
}

.info-value.price {
    color: #3563E9;
}

.booking-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 15px 25px;
    background-color: #f8f9fa;
    border-top: 1px solid #eee;
}

.action-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.2s;
}

.action-button.view {
    background-color: #e8f0fe;
    color: #3563E9;
}

.action-button.view:hover {
    background-color: #d0e1fd;
}

.action-button.cancel {
    background-color: #fff2f2;
    color: #f44336;
}

.action-button.cancel:hover {
    background-color: #ffe0e0;
}

.action-button.delete {
    background-color: #f44336;
    color: white;
}

.action-button.delete:hover {
    background-color: #d32f2f;
}

@media (max-width: 768px) {
    .booking-car {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .booking-car img {
        width: 180px;
        height: 120px;
    }
    
    .booking-details {
        flex-direction: column;
        gap: 20px;
    }
    
    .booking-dates {
        justify-content: center;
    }
    
    .booking-info {
        justify-content: space-around;
    }
    
    .booking-actions {
        justify-content: center;
    }
}
</style>

<script>
function confirmCancel(bookingId) {
    if (confirm('Weet u zeker dat u deze reservering wilt annuleren?')) {
        window.location.href = '/actions/cancel-booking.php?id=' + bookingId;
    }
}

function confirmDelete(bookingId) {
    if (confirm('Weet u zeker dat u deze reservering wilt verwijderen?')) {
        window.location.href = '/actions/delete-booking.php?id=' + bookingId;
    }
}

// Automatisch verdwijnen van meldingen na 5 seconden
document.addEventListener('DOMContentLoaded', function() {
    // Selecteer alle alert elementen
    const alerts = document.querySelectorAll('.alert');
    
    if (alerts.length > 0) {
        // Voor elk alert element, start een timer om het te verwijderen
        alerts.forEach(function(alert) {
            setTimeout(function() {
                // Fade out effect
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                
                // Verwijder het element na de fade-out
                setTimeout(function() {
                    alert.remove();
                }, 500);
            }, 5000); // 5 seconden wachten voor de fade-out
        });
    }
});
</script>

<?php require "includes/footer.php" ?> 