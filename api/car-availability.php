<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set content type to JSON
header('Content-Type: application/json');

// Include database connection
require_once __DIR__ . '/../includes/db_connect.php';

// Get car ID from query parameters
$car_id = isset($_GET['car_id']) ? intval($_GET['car_id']) : 0;

// Check if car ID is provided
if ($car_id <= 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Geen geldig voertuig ID opgegeven.'
    ]);
    exit;
}

try {
    // Query to find all reservations for this car
    $stmt = $conn->prepare("
        SELECT start_date, end_date 
        FROM bookings 
        WHERE car_id = :car_id 
        AND status IN ('pending', 'confirmed')
        ORDER BY start_date ASC
    ");
    $stmt->bindParam(':car_id', $car_id);
    $stmt->execute();
    
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $unavailable_dates = [];
    
    // Generate array of all unavailable dates
    foreach ($reservations as $reservation) {
        $start = new DateTime($reservation['start_date']);
        $end = new DateTime($reservation['end_date']);
        $end->modify('+1 day'); // Include end date
        
        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($start, $interval, $end);
        
        foreach ($period as $date) {
            $unavailable_dates[] = $date->format('Y-m-d');
        }
    }
    
    // Remove duplicates
    $unavailable_dates = array_unique($unavailable_dates);
    
    // Sort dates
    sort($unavailable_dates);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'car_id' => $car_id,
        'unavailable_dates' => $unavailable_dates
    ]);
    
} catch (PDOException $e) {
    error_log("Error retrieving car availability: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'error' => 'Er is een fout opgetreden bij het ophalen van de beschikbaarheid.'
    ]);
}
?> 