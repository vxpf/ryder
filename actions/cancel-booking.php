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

// Function to handle error and redirect
function redirectWithError($message) {
    $_SESSION['error'] = true;
    $_SESSION['message'] = $message;
    header('Location: /my-bookings');
    exit;
}

// Function to handle success and redirect
function redirectWithSuccess($message) {
    $_SESSION['success'] = $message;
    header('Location: /my-bookings');
    exit;
}

try {
    $user_id = $_SESSION['id'];
    $booking_id = $_GET['id'] ?? null;
    
    if (!$booking_id) {
        redirectWithError("Geen reservering opgegeven.");
    }
    
    // Check if booking exists and belongs to the user
    $check_stmt = $conn->prepare("
        SELECT b.*, c.id as car_id
        FROM bookings b
        JOIN cars c ON b.car_id = c.id
        WHERE b.id = :booking_id AND b.user_id = :user_id
    ");
    $check_stmt->bindParam(':booking_id', $booking_id);
    $check_stmt->bindParam(':user_id', $user_id);
    $check_stmt->execute();
    $booking = $check_stmt->fetch();
    
    if (!$booking) {
        redirectWithError("Deze reservering kon niet worden gevonden of u heeft geen toegang tot deze reservering.");
    }
    
    // Check if booking can be cancelled (is confirmed and start date is in the future)
    if ($booking['status'] !== 'confirmed') {
        redirectWithError("Alleen bevestigde reserveringen kunnen worden geannuleerd.");
    }
    
    $start_date = new DateTime($booking['start_date']);
    $today = new DateTime();
    $today->setTime(0, 0, 0); // Set time to 00:00:00
    
    if ($start_date <= $today) {
        redirectWithError("Reserveringen kunnen alleen geannuleerd worden vóór de ophaaldatum.");
    }
    
    // Begin transaction
    $conn->beginTransaction();
    
    // Update booking status
    $update_stmt = $conn->prepare("
        UPDATE bookings
        SET status = 'cancelled'
        WHERE id = :booking_id
    ");
    $update_stmt->bindParam(':booking_id', $booking_id);
    $update_stmt->execute();
    
    // Make car available again
    $update_car = $conn->prepare("
        UPDATE cars
        SET is_available = 1
        WHERE id = :car_id
    ");
    $update_car->bindParam(':car_id', $booking['car_id']);
    $update_car->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Redirect with success message
    redirectWithSuccess("Uw reservering is succesvol geannuleerd.");
    
} catch(PDOException $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    // Log the error
    error_log("Cancel booking error: " . $e->getMessage());
    redirectWithError("Er is een fout opgetreden bij het annuleren van uw reservering. Probeer het later opnieuw.");
}
?> 