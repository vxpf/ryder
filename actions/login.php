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
    header('Location: ../login.php?error=empty');
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
        header('Location: ../login.php?error=invalid');
        exit;
    }
} catch (PDOException $e) {
    // Log the error (in a production environment)
    error_log("Login error: " . $e->getMessage());
    
    // Redirect with a generic error message
    header('Location: ../login.php?error=system');
    exit;
}
