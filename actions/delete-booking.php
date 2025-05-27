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

// Get user ID and booking ID
$user_id = $_SESSION['id'];
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check if booking ID is provided
if ($booking_id <= 0) {
    $_SESSION['error'] = "Ongeldige reserverings-ID.";
    header('Location: /my-bookings');
    exit;
}

try {
    // First check if the booking exists and belongs to the current user
    $check_stmt = $conn->prepare("
        SELECT * FROM bookings 
        WHERE id = :booking_id AND user_id = :user_id
    ");
    $check_stmt->bindParam(':booking_id', $booking_id);
    $check_stmt->bindParam(':user_id', $user_id);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() === 0) {
        // Booking doesn't exist or doesn't belong to this user
        $_SESSION['error'] = "Deze reservering werd niet gevonden of u bent niet gemachtigd om deze te verwijderen.";
        header('Location: /my-bookings');
        exit;
    }
    
    $booking = $check_stmt->fetch();
    
    // Begin transaction
    $conn->beginTransaction();
    
    // Delete the booking
    $delete_stmt = $conn->prepare("DELETE FROM bookings WHERE id = :booking_id AND user_id = :user_id");
    $delete_stmt->bindParam(':booking_id', $booking_id);
    $delete_stmt->bindParam(':user_id', $user_id);
    $delete_stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Set success message
    $_SESSION['success'] = "De reservering is succesvol verwijderd.";
    
} catch(PDOException $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    // Log the error
    error_log("Error deleting booking: " . $e->getMessage());
    
    // Set error message
    $_SESSION['error'] = "Er is een fout opgetreden bij het verwijderen van de reservering. Probeer het later opnieuw.";
}

// Redirect back to my bookings page
header('Location: /my-bookings');
exit;
?> 