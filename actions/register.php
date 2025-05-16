<?php
session_start();

// Include database connection
$connectionPath = $_SERVER['DOCUMENT_ROOT'] . '/database/connection.php';
if (!file_exists($connectionPath)) {
    die("Error: Database connection file not found at: $connectionPath");
}
require_once $connectionPath;

// Sanitize and validate input
$email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
$password = $_POST["password"];
$confirm_password = $_POST["confirm_password"];

// Check if all fields are filled
if (empty($email) || empty($password) || empty($confirm_password)) {
    header("Location: ../register.php?error=empty");
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../register.php?error=invalid_email");
    exit();
}

try {
    // Check if passwords match
    if ($password === $confirm_password) {
        // Check if email already exists
        $check_account = $conn->prepare("SELECT * FROM account WHERE email = :email");
        $check_account->bindParam(":email", $email);
        $check_account->execute();

        if ($check_account->rowCount() === 0) {
            // Hash password with high security cost
            $options = ['cost' => 12]; // 12 is a good balance between security and performance
            $encrypted_password = password_hash($password, PASSWORD_DEFAULT, $options);

            // Insert new account
            $create_account = $conn->prepare("INSERT INTO account (email, password) VALUES (:email, :password)");
            $create_account->bindParam(":email", $email);
            $create_account->bindParam(":password", $encrypted_password);
            $create_account->execute();

            // Set success message and redirect to login
            $_SESSION["success"] = "Registration successful, please log in:";
            header("Location: ../login.php");
            exit();
        } else {
            // Email already exists
            $_SESSION["message"] = "This email is already in use.";
            $_SESSION["email"] = htmlspecialchars($email);
            header("Location: ../register.php?error=email_exists");
            exit();
        }
    } else {
        // Passwords don't match
        $_SESSION["message"] = "Passwords do not match.";
        $_SESSION["email"] = htmlspecialchars($email);
        header("Location: ../register.php?error=password_mismatch");
        exit();
    }
} catch (PDOException $e) {
    // Log the error (in a production environment)
    error_log("Registration error: " . $e->getMessage());
    
    // Redirect with a generic error message
    header("Location: ../register.php?error=system");
    exit();
}
