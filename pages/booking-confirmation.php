<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require "includes/header.php";
require "includes/db_connect.php";

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: /login-form');
    exit;
}

$user_id = $_SESSION['id'];
$booking_id = $_GET['id'] ?? null;
$booking = null;
$car = null;
$error = false;

// Get booking details
if ($booking_id) {
    try {
        // Get booking with car details
        $stmt = $conn->prepare("
            SELECT b.*, c.brand, c.model, c.image_url, c.price, c.transmission, c.capacity, c.fuel_capacity, c.category
            FROM bookings b
            JOIN cars c ON b.car_id = c.id
            WHERE b.id = :booking_id AND b.user_id = :user_id
        ");
        $stmt->bindParam(':booking_id', $booking_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $booking = $stmt->fetch();
        
        if (!$booking) {
            $error = "Deze reservering kon niet worden gevonden of u heeft geen toegang tot deze reservering.";
        }
    } catch(PDOException $e) {
        $error = "Er is een fout opgetreden bij het ophalen van de reserveringsgegevens.";
    }
} else {
    $error = "Geen reserveringsnummer opgegeven.";
}

// Calculate rental duration
$rental_days = 0;
if ($booking) {
    $start_date = new DateTime($booking['start_date']);
    $end_date = new DateTime($booking['end_date']);
    $interval = $start_date->diff($end_date);
    $rental_days = $interval->days;
    if ($rental_days < 1) $rental_days = 1; // Minimum 1 day
}

// Get user details
$user = null;
if ($booking) {
    try {
        $user_stmt = $conn->prepare("
            SELECT a.*, p.phone, p.address 
            FROM account a
            LEFT JOIN user_profiles p ON a.id = p.account_id
            WHERE a.id = :user_id
        ");
        $user_stmt->bindParam(':user_id', $user_id);
        $user_stmt->execute();
        $user = $user_stmt->fetch();
        
        if (!$user) {
            error_log("User not found for ID: " . $user_id);
        }
    } catch(PDOException $e) {
        // Silent error - we'll just show less user details
        error_log("Error fetching user details: " . $e->getMessage());
    }
}

// Format car name
$car_name = $booking ? trim($booking['brand'] . ' ' . ($booking['model'] ?? '')) : '';

// Format status
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

$status_label = $booking ? ($status_labels[$booking['status']] ?? $booking['status']) : '';
$status_color = $booking ? ($status_colors[$booking['status']] ?? '#666') : '#666';
?>

<main class="confirmation-page">
    <div class="container">
        <?php if ($error): ?>
            <div class="error-container">
                <i class="fas fa-exclamation-circle"></i>
                <h2>Fout</h2>
                <p><?= htmlspecialchars($error) ?></p>
                <a href="/" class="button-primary">Terug naar home</a>
            </div>
        <?php else: ?>
            <div class="confirmation-header">
                <div class="confirmation-status" style="--status-color: <?= $status_color ?>">
                    <div class="status-icon">
                        <?php if ($booking['status'] === 'confirmed'): ?>
                            <i class="fas fa-check"></i>
                        <?php elseif ($booking['status'] === 'pending'): ?>
                            <i class="fas fa-clock"></i>
                        <?php elseif ($booking['status'] === 'cancelled'): ?>
                            <i class="fas fa-times"></i>
                        <?php else: ?>
                            <i class="fas fa-flag-checkered"></i>
                        <?php endif; ?>
                    </div>
                    <div class="status-text">
                        <span>Status: <?= htmlspecialchars($status_label) ?></span>
                    </div>
                </div>
                <h1>Reservering bevestigd</h1>
                <p class="confirmation-subtitle">Bedankt voor uw reservering bij Rydr! Hieronder vindt u de details van uw boeking.</p>
                <div class="booking-reference">
                    <span>Reserveringsnummer:</span>
                    <strong><?= htmlspecialchars($booking_id) ?></strong>
                </div>
            </div>
            
            <div class="confirmation-content">
                <div class="booking-details">
                    <div class="car-summary">
                        <div class="car-image">
                            <img src="<?= htmlspecialchars($booking['image_url']) ?>" alt="<?= htmlspecialchars($car_name) ?>">
                        </div>
                        <div class="car-info">
                            <h2><?= htmlspecialchars($car_name) ?></h2>
                            <div class="car-category"><?= htmlspecialchars($booking['category']) ?></div>
                            <div class="car-features">
                                <?php if (isset($booking['transmission'])): ?>
                                    <div class="feature">
                                        <i class="fas fa-cog"></i>
                                        <span><?= htmlspecialchars($booking['transmission']) ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (isset($booking['capacity'])): ?>
                                    <div class="feature">
                                        <i class="fas fa-user"></i>
                                        <span><?= htmlspecialchars($booking['capacity']) ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (isset($booking['fuel_capacity'])): ?>
                                    <div class="feature">
                                        <i class="fas fa-gas-pump"></i>
                                        <span><?= htmlspecialchars($booking['fuel_capacity']) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="booking-info-container">
                        <div class="booking-info-section">
                            <h3><i class="fas fa-calendar-alt"></i> Reserveringsdetails</h3>
                            <div class="booking-info-content">
                                <div class="info-row">
                                    <div class="info-label">Ophaaldatum:</div>
                                    <div class="info-value"><?= date('d-m-Y', strtotime($booking['start_date'])) ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Retourdatum:</div>
                                    <div class="info-value"><?= date('d-m-Y', strtotime($booking['end_date'])) ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Aantal dagen:</div>
                                    <div class="info-value"><?= $rental_days ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Dagprijs:</div>
                                    <div class="info-value">€<?= number_format($booking['price'], 2, ',', '.') ?></div>
                                </div>
                                <div class="info-row total">
                                    <div class="info-label">Totaalprijs:</div>
                                    <div class="info-value">€<?= number_format($booking['total_price'], 2, ',', '.') ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($user): ?>
                        <div class="booking-info-section">
                            <h3><i class="fas fa-user"></i> Klantgegevens</h3>
                            <div class="booking-info-content">
                                <div class="info-row">
                                    <div class="info-label">Naam:</div>
                                    <div class="info-value"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">E-mail:</div>
                                    <div class="info-value"><?= htmlspecialchars($user['email']) ?></div>
                                </div>
                                <?php if (!empty($user['phone'])): ?>
                                <div class="info-row">
                                    <div class="info-label">Telefoonnummer:</div>
                                    <div class="info-value"><?= htmlspecialchars($user['phone']) ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($user['address'])): ?>
                                <div class="info-row">
                                    <div class="info-label">Adres:</div>
                                    <div class="info-value"><?= nl2br(htmlspecialchars($user['address'])) ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="booking-actions">
                    <a href="/" class="button-secondary">
                        <i class="fas fa-home"></i> Terug naar home
                    </a>
                    <a href="/my-bookings" class="button-secondary">
                        <i class="fas fa-calendar"></i> Mijn reserveringen
                    </a>
                    <a href="#" class="button-primary" onclick="window.print(); return false;">
                        <i class="fas fa-print"></i> Bevestiging afdrukken
                    </a>
                    <?php if ($booking['status'] === 'confirmed' && strtotime($booking['start_date']) > time()): ?>
                        <a href="#" class="button-cancel" onclick="confirmCancel(<?= $booking['id'] ?>); return false;">
                            <i class="fas fa-times"></i> Reservering annuleren
                        </a>
                    <?php endif; ?>
                </div>
                
                <div class="booking-notes">
                    <h3><i class="fas fa-info-circle"></i> Belangrijke informatie</h3>
                    <ul>
                        <li>Neem uw rijbewijs en identiteitsbewijs mee bij het ophalen van de auto.</li>
                        <li>De auto moet worden opgehaald en ingeleverd bij ons hoofdkantoor op de afgesproken data.</li>
                        <li>De auto wordt met een volle tank afgeleverd en moet met een volle tank worden ingeleverd.</li>
                        <li>Voor vragen of wijzigingen kunt u contact opnemen met onze klantenservice op 010-123456.</li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
function confirmCancel(bookingId) {
    if (confirm('Weet u zeker dat u deze reservering wilt annuleren?')) {
        window.location.href = '/actions/cancel-booking.php?id=' + bookingId;
    }
}
</script>

<style>
.confirmation-page {
    padding: 40px 0;
    font-family: 'Plus Jakarta Sans', sans-serif;
    color: #333;
}

.container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 15px;
}

.error-container {
    text-align: center;
    padding: 60px 20px;
    background-color: #fff;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

.error-container i {
    font-size: 60px;
    color: #f44336;
    margin-bottom: 20px;
}

.error-container h2 {
    font-size: 28px;
    margin-bottom: 15px;
}

.error-container p {
    font-size: 16px;
    color: #666;
    margin-bottom: 30px;
}

.confirmation-header {
    text-align: center;
    margin-bottom: 40px;
    position: relative;
}

.confirmation-status {
    display: inline-flex;
    align-items: center;
    background-color: rgba(var(--status-color-rgb), 0.1);
    padding: 8px 16px;
    border-radius: 30px;
    margin-bottom: 20px;
    color: var(--status-color);
}

.status-icon {
    width: 24px;
    height: 24px;
    background-color: var(--status-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
    color: white;
    font-size: 12px;
}

.status-text {
    font-weight: 600;
    font-size: 14px;
}

h1 {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 15px;
}

.confirmation-subtitle {
    font-size: 16px;
    color: #666;
    margin-bottom: 20px;
}

.booking-reference {
    display: inline-flex;
    align-items: center;
    background-color: #f5f5f5;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 15px;
}

.booking-reference strong {
    font-weight: 700;
    margin-left: 8px;
    color: #3563E9;
}

.confirmation-content {
    background-color: #fff;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    overflow: hidden;
}

.booking-details {
    padding: 30px;
}

.car-summary {
    display: flex;
    align-items: flex-start;
    gap: 25px;
    padding-bottom: 25px;
    border-bottom: 1px solid #eee;
    margin-bottom: 25px;
}

.car-image {
    width: 180px;
    flex-shrink: 0;
}

.car-image img {
    width: 100%;
    height: auto;
    border-radius: 8px;
    object-fit: cover;
}

.car-info h2 {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 8px;
}

.car-category {
    display: inline-block;
    background-color: #3563E9;
    color: white;
    font-size: 12px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 20px;
    margin-bottom: 15px;
}

.car-features {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.feature {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #666;
}

.feature i {
    color: #3563E9;
}

.booking-info-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
}

.booking-info-section {
    background-color: #f8f9fa;
    border-radius: 12px;
    overflow: hidden;
}

.booking-info-section h3 {
    background-color: #eef1f6;
    padding: 15px 20px;
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.booking-info-section h3 i {
    color: #3563E9;
}

.booking-info-content {
    padding: 20px;
}

.info-row {
    display: flex;
    margin-bottom: 12px;
}

.info-row:last-child {
    margin-bottom: 0;
}

.info-label {
    flex: 1;
    font-weight: 600;
    color: #666;
}

.info-value {
    flex: 1;
}

.info-row.total {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #ddd;
    font-weight: 700;
    color: #3563E9;
}

.booking-actions {
    display: flex;
    justify-content: space-between;
    padding: 25px 30px;
    background-color: #f8f9fa;
    border-top: 1px solid #eee;
}

.button-primary, .button-secondary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 15px;
    text-decoration: none;
    transition: all 0.2s;
}

.button-primary {
    background-color: #3563E9;
    color: white;
}

.button-primary:hover {
    background-color: #2954d4;
    transform: translateY(-2px);
}

.button-secondary {
    background-color: #fff;
    color: #3563E9;
    border: 1px solid #3563E9;
}

.button-secondary:hover {
    background-color: #f0f4ff;
    transform: translateY(-2px);
}

.button-cancel {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background-color: #f44336;
    color: white;
    font-weight: 600;
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.2s;
}

.button-cancel:hover {
    background-color: #d32f2f;
    transform: translateY(-2px);
}

.booking-notes {
    padding: 25px 30px;
    border-top: 1px solid #eee;
}

.booking-notes h3 {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.booking-notes h3 i {
    color: #3563E9;
}

.booking-notes ul {
    padding-left: 20px;
    margin: 0;
}

.booking-notes li {
    margin-bottom: 8px;
    color: #666;
}

@media (max-width: 768px) {
    .booking-info-container {
        grid-template-columns: 1fr;
    }
    
    .car-summary {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .car-image {
        width: 100%;
        max-width: 250px;
    }
    
    .car-features {
        justify-content: center;
    }
    
    .booking-actions {
        flex-direction: column;
        gap: 15px;
    }
    
    .button-primary, .button-secondary {
        width: 100%;
        justify-content: center;
    }
}

@media print {
    .topbar, .footer, .booking-actions {
        display: none !important;
    }
    
    .confirmation-page {
        padding: 0;
    }
    
    .confirmation-content {
        box-shadow: none;
        border: 1px solid #ddd;
    }
    
    .booking-notes {
        page-break-inside: avoid;
    }
}
</style>

<?php require "includes/footer.php" ?> 