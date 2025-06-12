<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once __DIR__ . '/../includes/db_connect.php';

// Check if email and password are provided
if (empty($_POST['email']) || empty($_POST['password'])) {
    $_SESSION['error'] = 'Vul alle velden in.';
    header('Location: /login-form');
    exit;
}

try {
    // Prepare and execute the query to get account and profile data
    $select_user = $conn->prepare("
        SELECT a.*, p.profile_photo 
        FROM account a
        LEFT JOIN user_profiles p ON a.id = p.account_id
        WHERE a.email = :email
    ");
    $select_user->bindParam(":email", $_POST['email']);
    $select_user->execute();
    $user = $select_user->fetch();
    
    // Verify password and create session
    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        
        // Store profile photo in session if available
        if (!empty($user['profile_photo'])) {
            $_SESSION['profile_photo'] = $user['profile_photo'];
        }
        
        // Redirect to homepage
        header('Location: /');
        exit;
    } else {
        // Invalid credentials
        $_SESSION['error'] = 'Ongeldige e-mail of wachtwoord.';
        header('Location: /login-form');
        exit;
    }
} catch (PDOException $e) {
    // Log the error (in a production environment)
    error_log("Login error: " . $e->getMessage());
    
    // Redirect with a generic error message
    $_SESSION['error'] = 'Er is een systeemfout opgetreden. Probeer het later opnieuw.';
    header('Location: /login-form');
    exit;
}
