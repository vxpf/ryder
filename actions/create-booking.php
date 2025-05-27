<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug: Log POST data to error log
error_log("Booking form submitted with data: " . print_r($_POST, true));

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    error_log("User not logged in. Redirecting to login page.");
    header('Location: /login-form');
    exit;
}

// Include database connection
require_once __DIR__ . '/../includes/db_connect.php';

// Test database connection and tables
try {
    // Test database connection
    $testQuery = $conn->query("SELECT 1");
    if ($testQuery) {
        error_log("Database connection successful");
    }
    
    // Test if tables exist
    $tables = ['account', 'cars', 'bookings', 'user_profiles'];
    foreach ($tables as $table) {
        $tableCheck = $conn->query("SHOW TABLES LIKE '$table'");
        if ($tableCheck->rowCount() > 0) {
            error_log("Table $table exists");
        } else {
            error_log("Table $table does NOT exist");
        }
    }
    
    // Test if account table has records
    $accountCheck = $conn->query("SELECT COUNT(*) as count FROM account");
    $accountCount = $accountCheck->fetch();
    error_log("Number of accounts: " . $accountCount['count']);
    
    // Test if cars table has records
    $carsCheck = $conn->query("SELECT COUNT(*) as count FROM cars");
    $carsCount = $carsCheck->fetch();
    error_log("Number of cars: " . $carsCount['count']);
    
} catch (PDOException $e) {
    error_log("Database test error: " . $e->getMessage());
}

// Check if bookings table exists, if not create it
try {
    $tableCheck = $conn->query("SHOW TABLES LIKE 'bookings'");
    if ($tableCheck->rowCount() == 0) {
        error_log("Bookings table does not exist. Creating it now.");
        
        // Create bookings table
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
    }
} catch (PDOException $e) {
    error_log("Error checking/creating bookings table: " . $e->getMessage());
}

// Check if user_profiles table exists, if not create it
try {
    $tableCheck = $conn->query("SHOW TABLES LIKE 'user_profiles'");
    if ($tableCheck->rowCount() == 0) {
        error_log("user_profiles table does not exist. Creating it now.");
        
        // Create user_profiles table
        $createTable = $conn->prepare("
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
        $createTable->execute();
        error_log("user_profiles table created successfully.");
    }
} catch (PDOException $e) {
    error_log("Error checking/creating user_profiles table: " . $e->getMessage());
}

// Function to handle error and redirect
function redirectWithError($message) {
    $_SESSION['error'] = true;
    $_SESSION['message'] = $message;
    
    error_log("Booking error: " . $message);
    
    // Redirect back to the reservation page
    $car_id = $_POST['car_id'] ?? '';
    header('Location: /reserveren?car_id=' . $car_id);
    exit;
}

// Function to handle success and redirect
function redirectWithSuccess($booking_id) {
    error_log("Booking successful. Booking ID: " . $booking_id);
    header('Location: /booking-confirmation?id=' . $booking_id);
    exit;
}

try {
    // Get user ID
    $user_id = $_SESSION['id'];
    
    // Get form data
    $car_id = $_POST['car_id'] ?? '';
    $pickup_date = $_POST['pickup_date'] ?? '';
    $return_date = $_POST['return_date'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    
    // Validate required fields
    if (empty($car_id) || empty($pickup_date) || empty($return_date) || 
        empty($name) || empty($email) || empty($phone) || empty($address)) {
        redirectWithError("Alle verplichte velden moeten worden ingevuld.");
    }
    
    // Validate dates
    $pickup = new DateTime($pickup_date);
    $return = new DateTime($return_date);
    $today = new DateTime();
    $today->setTime(0, 0, 0); // Set time to 00:00:00
    
    if ($pickup < $today) {
        redirectWithError("Ophaaldatum kan niet in het verleden liggen.");
    }
    
    if ($return <= $pickup) {
        redirectWithError("Retourdatum moet na de ophaaldatum liggen.");
    }
    
    // Check if car exists and is available
    $car_stmt = $conn->prepare("SELECT * FROM cars WHERE id = :car_id AND is_available = 1");
    $car_stmt->bindParam(':car_id', $car_id);
    $car_stmt->execute();
    $car = $car_stmt->fetch();
    
    if (!$car) {
        redirectWithError("De geselecteerde auto is niet beschikbaar voor verhuur.");
    }
    
    // Check if car is already booked for the selected dates
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
        redirectWithError("Deze auto is al geboekt voor de geselecteerde periode. Kies een andere periode of een ander voertuig.");
    }
    
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
        INSERT INTO bookings (user_id, car_id, start_date, end_date, total_price, status)
        VALUES (:user_id, :car_id, :start_date, :end_date, :total_price, 'confirmed')
    ");
    $insert_stmt->bindParam(':user_id', $user_id);
    $insert_stmt->bindParam(':car_id', $car_id);
    $insert_stmt->bindParam(':start_date', $pickup_date);
    $insert_stmt->bindParam(':end_date', $return_date);
    $insert_stmt->bindParam(':total_price', $total_price);
    $insert_stmt->execute();
    
    $booking_id = $conn->lastInsertId();
    
    // Update user information if needed - CHANGED to use account table instead of users table
    $user_stmt = $conn->prepare("SELECT * FROM account WHERE id = :user_id");
    $user_stmt->bindParam(':user_id', $user_id);
    $user_stmt->execute();
    $user = $user_stmt->fetch();
    
    // Only update user if it exists in the account table
    if ($user) {
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
        
        // Also update user_profiles table if it exists
        $check_profile = $conn->prepare("SELECT id FROM user_profiles WHERE account_id = :account_id");
        $check_profile->bindParam(':account_id', $user_id);
        $check_profile->execute();
        
        if ($check_profile->rowCount() > 0) {
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
            $create_profile = $conn->prepare("
                INSERT INTO user_profiles (account_id, phone, address)
                VALUES (:account_id, :phone, :address)
            ");
            $create_profile->bindParam(':account_id', $user_id);
            $create_profile->bindParam(':phone', $phone);
            $create_profile->bindParam(':address', $address);
            $create_profile->execute();
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    // Redirect to confirmation page
    redirectWithSuccess($booking_id);
    
} catch(PDOException $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    // Log the error
    error_log("Booking error: " . $e->getMessage());
    redirectWithError("Er is een fout opgetreden bij het verwerken van uw reservering. Probeer het later opnieuw.");
}
?> 