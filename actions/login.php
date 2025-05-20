<?php
session_start();

// Include database connection
$connectionPath = $_SERVER['DOCUMENT_ROOT'] . '/database/connection.php';
if (!file_exists($connectionPath)) {
    die("Error: Database connection file not found at: $connectionPath");
}
require_once $connectionPath;

// Check if email and password are provided
if (empty($_POST['email']) || empty($_POST['password'])) {
    $_SESSION['error'] = 'Vul alle velden in.';
    header('Location: /login-form');
    exit;
}

try {
    // Prepare and execute the query
    $select_user = $conn->prepare("SELECT * FROM account WHERE email = :email");
    $select_user->bindParam(":email", $_POST['email']);
    $select_user->execute();
    $user = $select_user->fetch();
    
    // Verify password and create session
    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        
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
