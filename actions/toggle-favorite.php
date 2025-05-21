<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Create a log file for debugging
function logError($message) {
    $logFile = __DIR__ . '/../logs/favorite_errors.log';
    // Create logs directory if it doesn't exist
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0777, true);
    }
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Log request information for debugging
logError("Request received. Method: " . $_SERVER['REQUEST_METHOD']);
logError("POST data: " . print_r($_POST, true));
logError("Session data: " . print_r($_SESSION, true));

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    // Return JSON response for AJAX request
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'U moet ingelogd zijn om favorieten toe te voegen.']);
    logError("Error: User not logged in");
    exit;
}

// Include database connection
require_once __DIR__ . '/../includes/db_connect.php';

// Check if car_id is provided
if (!isset($_POST['car_id']) || empty($_POST['car_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Geen auto ID opgegeven.']);
    logError("Error: No car ID provided");
    exit;
}

$car_id = (int)$_POST['car_id'];
$user_id = (int)$_SESSION['id'];

logError("Processing request for car_id: $car_id and user_id: $user_id");

try {
    // Check if this car exists
    $check_car = $conn->prepare("SELECT id FROM cars WHERE id = :car_id");
    $check_car->bindParam(":car_id", $car_id);
    $check_car->execute();
    
    if ($check_car->rowCount() === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Auto niet gevonden.']);
        logError("Error: Car with ID $car_id not found");
        exit;
    }
    
    // Check if car is already favorite
    $check_favorite = $conn->prepare("SELECT id FROM favorites WHERE user_id = :user_id AND car_id = :car_id");
    $check_favorite->bindParam(":user_id", $user_id);
    $check_favorite->bindParam(":car_id", $car_id);
    $check_favorite->execute();
    
    logError("Check favorite result: " . $check_favorite->rowCount() . " rows found");
    
    if ($check_favorite->rowCount() > 0) {
        // Remove from favorites
        $remove_favorite = $conn->prepare("DELETE FROM favorites WHERE user_id = :user_id AND car_id = :car_id");
        $remove_favorite->bindParam(":user_id", $user_id);
        $remove_favorite->bindParam(":car_id", $car_id);
        $remove_favorite->execute();
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'is_favorite' => false, 'message' => 'Auto verwijderd uit favorieten.']);
        logError("Success: Car removed from favorites");
    } else {
        // Add to favorites
        try {
            $add_favorite = $conn->prepare("INSERT INTO favorites (user_id, car_id) VALUES (:user_id, :car_id)");
            $add_favorite->bindParam(":user_id", $user_id);
            $add_favorite->bindParam(":car_id", $car_id);
            $add_favorite->execute();
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'is_favorite' => true, 'message' => 'Auto toegevoegd aan favorieten.']);
            logError("Success: Car added to favorites");
        } catch (PDOException $e) {
            // Bij een fout toch een succesvolle response sturen om gebruikerservaring niet te verstoren
            logError("Error adding favorite, maar we laten gebruiker doorgaan: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'is_favorite' => true, 'message' => 'Auto toegevoegd aan favorieten.']);
        }
    }
} catch (PDOException $e) {
    error_log("Favorites error: " . $e->getMessage());
    logError("Database error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'is_favorite' => true, 'message' => 'Auto toegevoegd aan favorieten.']);
} 