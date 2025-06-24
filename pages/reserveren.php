<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require "includes/header.php";
require "includes/db_connect.php";

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

// Check if user is logged in
$is_logged_in = isset($_SESSION['id']);
$user_id = $is_logged_in ? $_SESSION['id'] : null;

// Get the car ID from the URL parameter
$car_id = $_GET['car_id'] ?? null;
$car = null;
$error_message = '';
$success_message = '';

// Try to fetch car details from database
if ($car_id) {
    try {
        $stmt = $conn->prepare("SELECT * FROM cars WHERE id = :id");
        $stmt->bindParam(':id', $car_id);
        $stmt->execute();
        $car = $stmt->fetch();
        
        // We controleren nu niet meer alleen op de is_available vlag
        // In plaats daarvan checken we of er overlappende boekingen zijn voor de huidige datum
        if ($car) {
            $today = date('Y-m-d');
            $check_stmt = $conn->prepare("
                SELECT COUNT(*) as count FROM bookings 
                WHERE car_id = :car_id 
                AND status IN ('pending', 'confirmed')
                AND start_date <= :today 
                AND end_date >= :today
            ");
            $check_stmt->bindParam(':car_id', $car_id);
            $check_stmt->bindParam(':today', $today);
            $check_stmt->execute();
            $result = $check_stmt->fetch();
            
            if ($result['count'] > 0) {
                $error_message = "Deze auto is momenteel niet beschikbaar voor verhuur.";
                $car['is_available'] = 0; // Markeer als niet beschikbaar voor de view
            } else {
                // Auto is beschikbaar, zorg ervoor dat is_available op 1 staat
                $car['is_available'] = 1;
            }
        }
    } catch(PDOException $e) {
        // If there's an error, handle it gracefully
        $car = null;
        $error_message = "Er is een fout opgetreden bij het ophalen van de voertuiggegevens.";
    }
}

// If no car found, show message
if (!$car) {
    $car = [
        'brand' => 'Unknown',
        'model' => '',
        'price' => '0.00',
        'image_url' => 'assets/images/products/car (0).svg',
        'is_available' => 0
    ];
    if (empty($error_message)) {
        $error_message = "Het geselecteerde voertuig kon niet worden gevonden.";
    }
}

// Full car name
$car_full_name = trim($car['brand'] . ' ' . ($car['model'] ?? ''));

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user is logged in
    if (!$is_logged_in) {
        $_SESSION['redirect_after_login'] = "/reserveren?car_id=" . $car_id;
        header('Location: /login-form');
        exit;
    }
    
    // Get form data
    $pickup_date = $_POST['pickup_date'] ?? '';
    $return_date = $_POST['return_date'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';
    $card_number = $_POST['card_number'] ?? '';
    $expiry_date = $_POST['expiry_date'] ?? '';
    $card_holder = $_POST['card_holder'] ?? '';
    $cvc = $_POST['cvc'] ?? '';
    $marketing_consent = isset($_POST['marketing_consent']);
    $terms_consent = isset($_POST['terms_consent']);
    
    // Validate form data
    $errors = [];
    
    if (empty($pickup_date)) {
        $errors[] = "Ophaaldatum is verplicht.";
    }
    
    if (empty($return_date)) {
        $errors[] = "Retourdatum is verplicht.";
    }
    
    if (!empty($pickup_date) && !empty($return_date)) {
        $pickup = new DateTime($pickup_date);
        $return = new DateTime($return_date);
        $today = new DateTime();
        $today->setTime(0, 0, 0); // Set time to 00:00:00
        
        if ($pickup < $today) {
            $errors[] = "Ophaaldatum kan niet in het verleden liggen.";
        }
        
        if ($return <= $pickup) {
            $errors[] = "Retourdatum moet na de ophaaldatum liggen.";
        }
    }
    
    if (empty($name)) {
        $errors[] = "Naam is verplicht.";
    }
    
    if (empty($email)) {
        $errors[] = "E-mail is verplicht.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Ongeldig e-mailadres.";
    }
    
    if (empty($phone)) {
        $errors[] = "Telefoonnummer is verplicht.";
    }
    
    if (empty($address)) {
        $errors[] = "Adres is verplicht.";
    }
    
    if (empty($payment_method)) {
        $errors[] = "Selecteer een betaalmethode.";
    }
    
    if ($payment_method === 'credit_card') {
        if (empty($card_number)) {
            $errors[] = "Kaartnummer is verplicht.";
        }
        if (empty($expiry_date)) {
            $errors[] = "Vervaldatum is verplicht.";
        }
        if (empty($card_holder)) {
            $errors[] = "Kaarthouder naam is verplicht.";
        }
        if (empty($cvc)) {
            $errors[] = "CVC is verplicht.";
        }
    }
    
    if (!$terms_consent) {
        $errors[] = "U moet akkoord gaan met de algemene voorwaarden.";
    }
    
    // Check if car is already booked for the selected dates
    if (empty($errors)) {
        try {
            $check_stmt = $conn->prepare("
                SELECT COUNT(*) as count FROM bookings 
                WHERE car_id = :car_id 
                AND status IN ('pending', 'confirmed')
                AND (
                    (start_date <= :pickup_date AND end_date >= :pickup_date) OR
                    (start_date <= :return_date AND end_date >= :return_date) OR
                    (start_date >= :pickup_date AND end_date <= :return_date)
                )
            ");
            $check_stmt->bindParam(':car_id', $car_id);
            $check_stmt->bindParam(':pickup_date', $pickup_date);
            $check_stmt->bindParam(':return_date', $return_date);
            $check_stmt->execute();
            $result = $check_stmt->fetch();
            
            if ($result['count'] > 0) {
                $errors[] = "Deze auto is al geboekt voor de geselecteerde periode. Kies een andere periode of een ander voertuig.";
            }
        } catch(PDOException $e) {
            $errors[] = "Er is een fout opgetreden bij het controleren van de beschikbaarheid.";
        }
    }
    
    // If no errors, create booking
    if (empty($errors)) {
        try {
            // Calculate number of days
            $interval = $pickup->diff($return);
            $days = $interval->days;
            if ($days < 1) $days = 1; // Minimum 1 day
            
            // Calculate total price
            $total_price = $car['price'] * $days;
            
            // Begin transaction
            $conn->beginTransaction();
            
            // Insert booking
            $insert_stmt = $conn->prepare("
                INSERT INTO bookings (user_id, car_id, start_date, end_date, total_price, status, payment_method)
                VALUES (:user_id, :car_id, :start_date, :end_date, :total_price, 'confirmed', :payment_method)
            ");
            $insert_stmt->bindParam(':user_id', $user_id);
            $insert_stmt->bindParam(':car_id', $car_id);
            $insert_stmt->bindParam(':start_date', $pickup_date);
            $insert_stmt->bindParam(':end_date', $return_date);
            $insert_stmt->bindParam(':total_price', $total_price);
            $insert_stmt->bindParam(':payment_method', $payment_method);
            $insert_stmt->execute();
            
            $booking_id = $conn->lastInsertId();
            
            // Update the car's availability status to unavailable
            $update_car = $conn->prepare("
                UPDATE cars 
                SET is_available = 0
                WHERE id = :car_id
            ");
            $update_car->bindParam(':car_id', $car_id);
            $update_car->execute();
            
            // Update user information if needed
            $update_user = $conn->prepare("
                UPDATE account 
                SET first_name = :first_name, 
                    email = :email
                WHERE id = :user_id
            ");
            $update_user->bindParam(':first_name', $name);
            $update_user->bindParam(':email', $email);
            $update_user->bindParam(':user_id', $user_id);
            $update_user->execute();
            
            // Check if user has a profile record, create or update it
            $check_profile = $conn->prepare("SELECT id FROM user_profiles WHERE account_id = :account_id");
            $check_profile->bindParam(':account_id', $user_id);
            $check_profile->execute();
            
            if ($check_profile->rowCount() > 0) {
                // Update existing profile
                $update_profile = $conn->prepare("
                    UPDATE user_profiles
                    SET phone = :phone,
                        address = :address
                    WHERE account_id = :account_id
                ");
                $update_profile->bindParam(':phone', $phone);
                $update_profile->bindParam(':address', $address);
                $update_profile->bindParam(':account_id', $user_id);
                $update_profile->execute();
            } else {
                // Create new profile record
                $create_profile = $conn->prepare("
                    INSERT INTO user_profiles (account_id, phone, address)
                    VALUES (:account_id, :phone, :address)
                ");
                $create_profile->bindParam(':account_id', $user_id);
                $create_profile->bindParam(':phone', $phone);
                $create_profile->bindParam(':address', $address);
                $create_profile->execute();
            }
            
            // Commit transaction
            $conn->commit();
            
            // Set success message
            $success_message = "Uw reservering is succesvol verwerkt! Reserveringsnummer: " . $booking_id;
            
            // Redirect to confirmation page
            header("Location: /booking-confirmation?id=" . $booking_id);
            exit;
            
        } catch(PDOException $e) {
            // Rollback transaction on error
            $conn->rollBack();
            $errors[] = "Er is een fout opgetreden bij het verwerken van uw reservering. Probeer het later opnieuw.";
        }
    }
    
    // If there are errors, display them
    if (!empty($errors)) {
        $error_message = implode("<br>", $errors);
    }
}

// Pre-fill form with user data if logged in
$user_data = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'address' => ''
];

if ($is_logged_in) {
    try {
        $user_stmt = $conn->prepare("
            SELECT a.*, p.phone, p.address 
            FROM account a
            LEFT JOIN user_profiles p ON a.id = p.account_id
            WHERE a.id = :id
        ");
        $user_stmt->bindParam(':id', $user_id);
        $user_stmt->execute();
        $user = $user_stmt->fetch();
        
        if ($user) {
            $user_data['name'] = $user['first_name'] . ' ' . ($user['last_name'] ?? '');
            $user_data['email'] = $user['email'];
            $user_data['phone'] = $user['phone'] ?? '';
            $user_data['address'] = $user['address'] ?? '';
        }
    } catch(PDOException $e) {
        // Silent error - just won't pre-fill the form
        error_log("Error fetching user data: " . $e->getMessage());
    }
}

// Voeg deze functie toe na de require statements
// Check if required tables exist and create them if they don't
function ensureTablesExist($conn) {
    try {
        // Check bookings table
        $tableCheck = $conn->query("SHOW TABLES LIKE 'bookings'");
        if ($tableCheck->rowCount() == 0) {
            error_log("Creating bookings table");
            $conn->exec("
                CREATE TABLE IF NOT EXISTS bookings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    car_id INT NOT NULL,
                    start_date DATE NOT NULL,
                    end_date DATE NOT NULL,
                    total_price DECIMAL(10,2) NOT NULL,
                    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
                    payment_method VARCHAR(50) DEFAULT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES account(id) ON DELETE CASCADE,
                    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
                )
            ");
        } else {
            // Check if payment_method column exists, add if not
            $columnCheck = $conn->query("SHOW COLUMNS FROM bookings LIKE 'payment_method'");
            if ($columnCheck->rowCount() == 0) {
                $conn->exec("ALTER TABLE bookings ADD COLUMN payment_method VARCHAR(50) DEFAULT NULL");
            }
        }
        
        // Check user_profiles table
        $tableCheck = $conn->query("SHOW TABLES LIKE 'user_profiles'");
        if ($tableCheck->rowCount() == 0) {
            error_log("Creating user_profiles table");
            $conn->exec("
                CREATE TABLE IF NOT EXISTS `user_profiles` (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `account_id` int NOT NULL,
                  `name` varchar(255) DEFAULT NULL,
                  `phone` varchar(50) DEFAULT NULL,
                  `profile_photo` varchar(255) DEFAULT NULL,
                  `bio` text DEFAULT NULL,
                  `address` varchar(255) DEFAULT NULL,
                  `city` varchar(100) DEFAULT NULL,
                  `postal_code` varchar(20) DEFAULT NULL,
                  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `account_id` (`account_id`),
                  CONSTRAINT `fk_profile_account` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`) ON DELETE CASCADE
                )
            ");
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Error ensuring tables exist: " . $e->getMessage());
        return false;
    }
}

// Call the function to ensure tables exist
ensureTablesExist($conn);
?>

<main class="reservation-page">
    <div class="container">
        <h1>Auto Huren</h1>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?= $error_message ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= $success_message ?>
            </div>
        <?php endif; ?>
        
        <div class="reservation-content">
            <div class="car-summary">
                <h2>Uw geselecteerde voertuig</h2>
                <div class="selected-car">
                    <img src="<?= htmlspecialchars($car['image_url']) ?>" alt="<?= htmlspecialchars($car_full_name) ?>">
                    <div class="car-info">
                        <h3><?= htmlspecialchars($car_full_name) ?></h3>
                        <p class="price">€<?= htmlspecialchars(number_format($car['price'], 2, ',', '.')) ?> / dag</p>
                        <div class="car-features">
                            <?php if (isset($car['transmission'])): ?>
                                <div class="feature">
                                    <i class="fas fa-cog"></i>
                                    <span><?= htmlspecialchars($car['transmission']) ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($car['capacity'])): ?>
                                <div class="feature">
                                    <i class="fas fa-user"></i>
                                    <span><?= htmlspecialchars($car['capacity']) ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($car['fuel_capacity'])): ?>
                                <div class="feature">
                                    <i class="fas fa-gas-pump"></i>
                                    <span><?= htmlspecialchars($car['fuel_capacity']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="availability-status">
                            <?php if ($car['is_available'] == 1): ?>
                                <span class="available"><i class="fas fa-check-circle"></i> Beschikbaar</span>
                            <?php else: ?>
                                <span class="unavailable"><i class="fas fa-times-circle"></i> Niet beschikbaar</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="pricing-calculator">
                    <h3>Prijsberekening</h3>
                    <div class="calculator-content">
                        <div class="calculator-row">
                            <span>Dagprijs:</span>
                            <span>€<?= htmlspecialchars(number_format($car['price'], 2, ',', '.')) ?></span>
                        </div>
                        <div class="calculator-row">
                            <span>Aantal dagen:</span>
                            <span id="days-count">0</span>
                        </div>
                        <div class="calculator-divider"></div>
                        <div class="calculator-row total">
                            <span>Totaalprijs:</span>
                            <span id="total-price">€0,00</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="reservation-form">
                <h2>Reserveringsgegevens</h2>
                
                <?php if (!$is_logged_in): ?>
                    <div class="login-notice">
                        <i class="fas fa-info-circle"></i>
                        <p>U moet <a href="/login-form?redirect=<?= urlencode("/reserveren?car_id=" . $car_id) ?>">inloggen</a> om een reservering te maken. Nog geen account? <a href="/register-form">Registreer hier</a>.</p>
                    </div>
                <?php endif; ?>
                
                <form action="" method="post">
                    <input type="hidden" name="car_id" value="<?= htmlspecialchars($car_id) ?>">
                    
                    <div class="form-group date-inputs">
                        <div class="date-input">
                            <label for="pickup_date">Ophaaldatum</label>
                            <input type="date" id="pickup_date" name="pickup_date" required min="<?= date('Y-m-d') ?>">
                        </div>
                        
                        <div class="date-input">
                            <label for="return_date">Retourdatum</label>
                            <input type="date" id="return_date" name="return_date" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="name">Volledige naam</label>
                        <input type="text" id="name" name="name" required value="<?= htmlspecialchars($user_data['name']) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" required value="<?= htmlspecialchars($user_data['email']) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Telefoonnummer</label>
                        <input type="tel" id="phone" name="phone" required value="<?= htmlspecialchars($user_data['phone']) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Adres</label>
                        <textarea id="address" name="address" required><?= htmlspecialchars($user_data['address']) ?></textarea>
                    </div>
                    
                    <!-- Payment Method Section -->
                    <div class="payment-section">
                        <h3>Betaalmethode</h3>
                        <p class="payment-subtitle">Selecteer uw betaalmethode</p>
                        <div class="step-indicator">Stap 2 van 3</div>
                        
                        <div class="payment-methods">
                            <!-- Credit Card Option -->
                            <div class="payment-option">
                                <label class="payment-method-label">
                                    <input type="radio" name="payment_method" value="credit_card" required>
                                    <span class="payment-method-text">
                                        <i class="fas fa-credit-card"></i>
                                        Credit Card
                                    </span>
                                    <div class="payment-logos">
                                        <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCA0MCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjI0IiByeD0iNCIgZmlsbD0iIzAwNTFBNSIvPgo8cGF0aCBkPSJNMTYuNSA5LjVIMTlWMTQuNUgxNi41VjkuNVoiIGZpbGw9IndoaXRlIi8+CjxwYXRoIGQ9Ik0yMS41IDEwLjVIMjNWMTMuNUgyMS41VjEwLjVaIiBmaWxsPSJ3aGl0ZSIvPgo8L3N2Zz4K" alt="Visa">
                                        <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCA0MCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjI0IiByeD0iNCIgZmlsbD0iI0VCMDAxQiIvPgo8Y2lyY2xlIGN4PSIxNSIgY3k9IjEyIiByPSI1IiBmaWxsPSIjRkY1RjAwIi8+CjxjaXJjbGUgY3g9IjI1IiBjeT0iMTIiIHI9IjUiIGZpbGw9IiNGRkY1RjAiLz4KPC9zdmc+Cg==" alt="Mastercard">
                                    </div>
                                </label>
                                
                                <div class="credit-card-details">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="card_number">Kaartnummer</label>
                                            <input type="text" id="card_number" name="card_number" placeholder="1234 1234 1234 1234" maxlength="19">
                                        </div>
                                        <div class="form-group">
                                            <label for="expiry_date">Vervaldatum</label>
                                            <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/JJ" maxlength="5">
                                        </div>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="card_holder">Kaarthouder</label>
                                            <input type="text" id="card_holder" name="card_holder" placeholder="Kaarthouder">
                                        </div>
                                        <div class="form-group">
                                            <label for="cvc">CVC</label>
                                            <input type="text" id="cvc" name="cvc" placeholder="CVC" maxlength="3">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- PayPal Option -->
                            <div class="payment-option">
                                <label class="payment-method-label">
                                    <input type="radio" name="payment_method" value="paypal">
                                    <span class="payment-method-text">
                                        <i class="fab fa-paypal"></i>
                                        PayPal
                                    </span>
                                    <div class="payment-logos">
                                        <!-- PayPal Logo -->
                                        <table border="0" cellpadding="10" cellspacing="0" align="center"><tbody><tr><td align="center"></td></tr><tr><td align="center"><a href="https://www.paypal.com/nl/webapps/mpp/paypal-popup" title="Hoe PayPal Werkt" onclick="javascript:window.open('https://www.paypal.com/nl/webapps/mpp/paypal-popup','WIPaypal','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1060, height=700'); return false;"><img src="https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_37x23.jpg" border="0" alt="PayPal Logo" /></a></td></tr></tbody></table>
                                        <!-- PayPal Logo -->
                                    </div>
                                </label>
                            </div>
                            
                            <!-- Bitcoin Option -->
                            <div class="payment-option">
                                <label class="payment-method-label">
                                    <input type="radio" name="payment_method" value="bitcoin">
                                    <span class="payment-method-text">
                                        <i class="fab fa-bitcoin"></i>
                                        Bitcoin
                                    </span>
                                    <div class="payment-logos">
                                        <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCA0MCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjI0IiByeD0iNCIgZmlsbD0iI0Y3OTMxQSIvPgo8Y2lyY2xlIGN4PSIyMCIgY3k9IjEyIiByPSI2IiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMTkgOVYxNUgyMVY5SDE5WiIgZmlsbD0iI0Y3OTMxQSIvPgo8L3N2Zz4K" alt="Bitcoin">
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Confirmation Section -->
                    <div class="confirmation-section">
                        <h3>Bevestiging</h3>
                        <p class="confirmation-subtitle">We zijn bijna aan het eind. Nog twee clicks en u bent klaar!</p>
                        <div class="step-indicator">Stap 3 van 3</div>
                        
                        <div class="confirmation-checkboxes">
                            <label class="checkbox-label">
                                <input type="checkbox" name="marketing_consent">
                                <span class="checkmark"></span>
                                Ik ga akkoord met het verzenden van marketing- en nieuwsbriefmails. Geen spam, beloofd!
                            </label>
                            
                            <label class="checkbox-label">
                                <input type="checkbox" name="terms_consent" required>
                                <span class="checkmark"></span>
                                Ik ga akkoord met onze algemene voorwaarden en privacybeleid.
                            </label>
                        </div>
                    </div>
                    
                    <!-- Security Notice -->
                    <div class="security-notice">
                        <i class="fas fa-shield-alt"></i>
                        <div>
                            <h4>Al uw gegevens zijn veilig</h4>
                            <p>We zijn using the most advanced security to provide you the best experience ever.</p>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="button-primary rent-now-btn" <?= (!$car_id) ? 'disabled' : '' ?>>
                            Rent Now
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<style>
.reservation-page {
    padding: 40px 0;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
}

h1 {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 30px;
    color: #333;
}

h2 {
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 20px;
    color: #333;
}

h3 {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 15px;
    color: #333;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 8px;
    display: flex;
    align-items: center;
}

.alert i {
    font-size: 20px;
    margin-right: 10px;
}

.alert-danger {
    background-color: #fff5f5;
    color: #d32f2f;
    border-left: 4px solid #d32f2f;
}

.alert-success {
    background-color: #f0f9f0;
    color: #2e7d32;
    border-left: 4px solid #2e7d32;
}

.reservation-content {
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
    margin-top: 30px;
}

.car-summary, .reservation-form {
    flex: 1;
    min-width: 300px;
}

.selected-car {
    display: flex;
    align-items: flex-start;
    gap: 20px;
    padding: 20px;
    background-color: #f8f9fa;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.selected-car img {
    width: 150px;
    height: auto;
    object-fit: contain;
    border-radius: 8px;
}

.car-info h3 {
    font-size: 20px;
    margin-bottom: 10px;
}

.car-info .price {
    font-size: 18px;
    font-weight: 700;
    color: #3563E9;
    margin-bottom: 15px;
}

.car-features {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 15px;
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

.availability-status {
    margin-top: 10px;
    font-weight: 600;
}

.available {
    color: #2e7d32;
}

.unavailable {
    color: #d32f2f;
}

.pricing-calculator {
    background-color: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.calculator-content {
    margin-top: 15px;
}

.calculator-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 15px;
}

.calculator-row.total {
    font-weight: 700;
    font-size: 18px;
    color: #3563E9;
}

.calculator-divider {
    height: 1px;
    background-color: #ddd;
    margin: 15px 0;
}

.reservation-form {
    background-color: #fff;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}

.login-notice {
    background-color: #e8f4fd;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.login-notice i {
    color: #0277bd;
    font-size: 18px;
    margin-top: 3px;
}

.login-notice p {
    margin: 0;
    font-size: 14px;
    color: #333;
}

.login-notice a {
    color: #3563E9;
    font-weight: 600;
    text-decoration: none;
}

.login-notice a:hover {
    text-decoration: underline;
}

.form-group {
    margin-bottom: 20px;
}

.date-inputs {
    display: flex;
    gap: 20px;
}

.date-input {
    flex: 1;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    font-size: 15px;
    color: #333;
}

.form-group input, .form-group textarea {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-family: inherit;
    font-size: 15px;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-group input:focus, .form-group textarea:focus {
    outline: none;
    border-color: #3563E9;
    box-shadow: 0 0 0 3px rgba(53, 99, 233, 0.15);
}

.form-group textarea {
    height: 100px;
    resize: vertical;
}

/* Payment Section Styles */
.payment-section {
    margin: 30px 0;
    padding: 25px;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    background-color: #fff;
}

.payment-subtitle {
    color: #666;
    font-size: 14px;
    margin-bottom: 10px;
}

.step-indicator {
    color: #999;
    font-size: 14px;
    text-align: right;
    margin-bottom: 20px;
}

.payment-methods {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.payment-option {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.2s;
}

.payment-option:hover {
    border-color: #3563E9;
}

.payment-method-label {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    cursor: pointer;
    transition: background-color 0.2s;
    min-height: 56px;
    box-sizing: border-box;
}

.payment-method-label:hover {
    background-color: #f8f9fa;
}

.payment-method-text {
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 500;
}

.payment-method-text i {
    font-size: 18px;
    color: #3563E9;
}

.payment-logos {
    display: flex;
    gap: 8px;
    align-items: center;
}

.payment-logos img {
    height: 24px;
    width: auto;
}

.payment-logos table {
    height: 24px;
    margin: 0;
    padding: 0;
}
.payment-logos table img {
    height: 24px;
    width: auto;
    display: block;
}
.payment-logos table td {
    padding: 0 !important;
    border: none;
}

input[type="radio"] {
    width: 18px;
    height: 18px;
    margin-right: 12px;
}

.credit-card-details {
    padding: 20px;
    border-top: 1px solid #e0e0e0;
    background-color: #f8f9fa;
    display: none;
}

.payment-option:has(input[type="radio"]:checked) .credit-card-details {
    display: block;
}

.form-row {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
}

.form-row .form-group {
    flex: 1;
    margin-bottom: 0;
}

/* Confirmation Section */
.confirmation-section {
    margin: 30px 0;
    padding: 25px;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    background-color: #fff;
}

.confirmation-subtitle {
    color: #666;
    font-size: 14px;
    margin-bottom: 10px;
}

.confirmation-checkboxes {
    margin-top: 20px;
}

.checkbox-label {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 15px;
    cursor: pointer;
    font-size: 14px;
    line-height: 1.5;
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    margin: 0;
    flex-shrink: 0;
}

.checkmark {
    position: relative;
    margin-top: 2px;
}

/* Security Notice */
.security-notice {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    background-color: #f0f8ff;
    border-radius: 8px;
    margin: 20px 0;
}

.security-notice i {
    font-size: 24px;
    color: #3563E9;
}

.security-notice h4 {
    margin: 0 0 5px 0;
    font-weight: 600;
    color: #333;
}

.security-notice p {
    margin: 0;
    font-size: 14px;
    color: #666;
}

.form-actions {
    margin-top: 30px;
}

.button-primary {
    background-color: #3563E9;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 16px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    transition: background-color 0.2s, transform 0.2s;
}

.rent-now-btn {
    width: 100%;
    justify-content: center;
    padding: 15px 24px;
    font-size: 18px;
}

.button-primary:hover {
    background-color: #2954d4;
    transform: translateY(-2px);
}

.button-primary:disabled {
    background-color: #b0bec5;
    cursor: not-allowed;
    transform: none;
}

@media (max-width: 768px) {
    .date-inputs {
        flex-direction: column;
        gap: 15px;
    }
    
    .selected-car {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .car-features {
        justify-content: center;
    }
    
    .form-row {
        flex-direction: column;
        gap: 15px;
    }
    
    .payment-method-label {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .payment-logos {
        align-self: flex-end;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pickupDateInput = document.getElementById('pickup_date');
    const returnDateInput = document.getElementById('return_date');
    const daysCountElement = document.getElementById('days-count');
    const totalPriceElement = document.getElementById('total-price');
    const dailyPrice = <?= $car['price'] ?>;
    
    // Payment method handling
    const paymentMethodRadios = document.querySelectorAll('input[name="payment_method"]');
    const creditCardDetails = document.querySelector('.credit-card-details');
    const cardInputs = ['card_number', 'expiry_date', 'card_holder', 'cvc'];
    
    // Format card number input
    const cardNumberInput = document.getElementById('card_number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            if (formattedValue !== e.target.value) {
                e.target.value = formattedValue;
            }
        });
    }
    
    // Format expiry date input
    const expiryDateInput = document.getElementById('expiry_date');
    if (expiryDateInput) {
        expiryDateInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });
    }
    
    // CVC input - numbers only
    const cvcInput = document.getElementById('cvc');
    if (cvcInput) {
        cvcInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });
    }
    
    // Handle payment method selection
    paymentMethodRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'credit_card') {
                creditCardDetails.style.display = 'block';
                // Make card inputs required
                cardInputs.forEach(inputName => {
                    const input = document.getElementById(inputName);
                    if (input) input.required = true;
                });
            } else {
                creditCardDetails.style.display = 'none';
                // Remove required from card inputs
                cardInputs.forEach(inputName => {
                    const input = document.getElementById(inputName);
                    if (input) input.required = false;
                });
            }
        });
    });
    
    // Set min dates
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    pickupDateInput.min = today.toISOString().split('T')[0];
    returnDateInput.min = tomorrow.toISOString().split('T')[0];
    
    // Get unavailable dates from the database
    const carId = <?= htmlspecialchars($car_id ?? 'null') ?>;
    if (carId) {
        fetch(`/api/car-availability.php?car_id=${carId}`)
            .then(response => response.json())
            .then(data => {
                if (data.unavailable_dates) {
                    console.log('Unavailable dates:', data.unavailable_dates);
                    // Deze functionaliteit in toekomstige update implementeren met een betere datepicker
                }
            })
            .catch(error => console.error('Error fetching availability:', error));
    }
    
    // Calculate days and total price
    function calculatePrice() {
        const pickupDate = pickupDateInput.value ? new Date(pickupDateInput.value) : null;
        const returnDate = returnDateInput.value ? new Date(returnDateInput.value) : null;
        
        if (pickupDate && returnDate) {
            // Calculate difference in days
            const diffTime = returnDate.getTime() - pickupDate.getTime();
            let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            // Ensure minimum 1 day
            if (diffDays < 1) diffDays = 1;
            
            // Update UI
            daysCountElement.textContent = diffDays;
            const totalPrice = dailyPrice * diffDays;
            totalPriceElement.textContent = '€' + totalPrice.toFixed(2).replace('.', ',');
            
            // Update return date min value
            if (pickupDate) {
                const minReturnDate = new Date(pickupDate);
                minReturnDate.setDate(minReturnDate.getDate() + 1);
                returnDateInput.min = minReturnDate.toISOString().split('T')[0];
                
                // If return date is before new min, update it
                if (returnDate < minReturnDate) {
                    returnDateInput.value = minReturnDate.toISOString().split('T')[0];
                }
            }
        } else {
            daysCountElement.textContent = '0';
            totalPriceElement.textContent = '€0,00';
        }
    }
    
    // Add event listeners
    pickupDateInput.addEventListener('change', calculatePrice);
    returnDateInput.addEventListener('change', calculatePrice);
    
    // Initial calculation
    calculatePrice();
});
</script>

<?php require "includes/footer.php" ?>