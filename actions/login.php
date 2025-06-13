<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once __DIR__ . '/../includes/db_connect.php';

// Debugging log file
$log_file = __DIR__ . '/../logs/login_debug.log';
file_put_contents($log_file, "Login attempt at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

// Check if email and password are provided
if (empty($_POST['email']) || empty($_POST['password'])) {
    $_SESSION['error'] = 'Vul alle velden in.';
    file_put_contents($log_file, "Error: Empty fields\n", FILE_APPEND);
    header('Location: /login-form');
    exit;
}

$email = $_POST['email'];
$password = $_POST['password'];
$isAdmin = false;

file_put_contents($log_file, "Login attempt with email: $email\n", FILE_APPEND);

try {
    // First check if user exists in the account table
    $select_user = $conn->prepare("
        SELECT a.*, p.profile_photo 
        FROM account a
        LEFT JOIN user_profiles p ON a.id = p.account_id
        WHERE a.email = :email
    ");
    $select_user->bindParam(":email", $email);
    $select_user->execute();
    $user = $select_user->fetch();
    
    file_put_contents($log_file, "Account table check: " . ($user ? "User found" : "User not found") . "\n", FILE_APPEND);
    
    // If user exists in account table, check password
    if ($user && password_verify($password, $user['password'])) {
        file_put_contents($log_file, "Password verified for account table user\n", FILE_APPEND);
        
        // Check if user is admin (role = 1)
        if (isset($user['role']) && $user['role'] == 1) {
            $isAdmin = true;
            file_put_contents($log_file, "Admin user found in account table (role=1)\n", FILE_APPEND);
            
            // Set admin session variables
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_email'] = $user['email'];
            
            // Check if first_name and last_name exist in the account table
            if (isset($user['first_name']) && isset($user['last_name'])) {
                $_SESSION['admin_name'] = $user['first_name'] . ' ' . $user['last_name'];
            } else {
                $_SESSION['admin_name'] = 'Administrator';
            }
            
            $_SESSION['admin_table'] = 'account';
            
            // Also set regular user session variables to ensure compatibility
            $_SESSION['id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            
            // Set login_verified flag to indicate an explicit login action
            $_SESSION['login_verified'] = true;
            
            // Redirect to admin dashboard
            file_put_contents($log_file, "Redirecting to admin dashboard\n", FILE_APPEND);
            header('Location: /pages/admin/dashboard.php');
            exit;
        } else {
            // Set regular user session
            $_SESSION['id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            
            // Store profile photo in session if available
            if (!empty($user['profile_photo'])) {
                $_SESSION['profile_photo'] = $user['profile_photo'];
            }
            
            // Set login_verified flag to indicate an explicit login action
            $_SESSION['login_verified'] = true;
            
            // Redirect to homepage
            file_put_contents($log_file, "Redirecting regular user to homepage\n", FILE_APPEND);
            header('Location: /');
            exit;
        }
    } else {
        file_put_contents($log_file, "Checking users table\n", FILE_APPEND);
        
        // If not found in account table, check users table
        try {
            $select_admin = $conn->prepare("SELECT * FROM users WHERE email = :email");
            $select_admin->bindParam(":email", $email);
            $select_admin->execute();
            $admin = $select_admin->fetch();
            
            file_put_contents($log_file, "Users table check: " . ($admin ? "User found" : "User not found") . "\n", FILE_APPEND);
            
            if ($admin && password_verify($password, $admin['password'])) {
                file_put_contents($log_file, "Password verified for users table user\n", FILE_APPEND);
                
                // Check if user is admin
                if (isset($admin['is_admin']) && $admin['is_admin'] == true) {
                    $isAdmin = true;
                    file_put_contents($log_file, "Admin user found in users table (is_admin=true)\n", FILE_APPEND);
                    
                    // Set admin session variables
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_email'] = $admin['email'];
                    $_SESSION['admin_name'] = $admin['first_name'] . ' ' . $admin['last_name'];
                    $_SESSION['admin_table'] = 'users';
                    
                    // Also set regular user session variables to ensure compatibility
                    $_SESSION['id'] = $admin['id'];
                    $_SESSION['email'] = $admin['email'];
                    
                    // Set login_verified flag to indicate an explicit login action
                    $_SESSION['login_verified'] = true;
                    
                    // Redirect to admin dashboard
                    file_put_contents($log_file, "Redirecting to admin dashboard\n", FILE_APPEND);
                    header('Location: /pages/admin/dashboard.php');
                    exit;
                } else {
                    // Set regular user session from users table
                    $_SESSION['id'] = $admin['id'];
                    $_SESSION['email'] = $admin['email'];
                    
                    // Set login_verified flag to indicate an explicit login action
                    $_SESSION['login_verified'] = true;
                    
                    // Redirect to homepage
                    file_put_contents($log_file, "Redirecting regular user to homepage\n", FILE_APPEND);
                    header('Location: /');
                    exit;
                }
            } else if ($admin) {
                file_put_contents($log_file, "User found in users table but password incorrect\n", FILE_APPEND);
            }
        } catch (PDOException $e) {
            // Table might not exist, ignore error
            file_put_contents($log_file, "Error checking users table: " . $e->getMessage() . "\n", FILE_APPEND);
        }
        
        // Special case for admin@carrental.com
        if ($email === 'admin@carrental.com') {
            file_put_contents($log_file, "Special case for admin@carrental.com\n", FILE_APPEND);
            
            try {
                // Create users table if it doesn't exist
                $conn->exec("CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    first_name VARCHAR(50) NOT NULL,
                    last_name VARCHAR(50) NOT NULL,
                    email VARCHAR(100) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    phone VARCHAR(20),
                    address TEXT,
                    is_admin BOOLEAN DEFAULT TRUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");
                
                // Check if admin already exists
                $check = $conn->prepare("SELECT * FROM users WHERE email = 'admin@carrental.com'");
                $check->execute();
                $adminExists = $check->fetch();
                
                if (!$adminExists) {
                    // Create default admin account
                    $defaultPassword = 'admin123';
                    $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);
                    
                    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, is_admin) 
                                         VALUES ('Admin', 'User', 'admin@carrental.com', :password, TRUE)");
                    $stmt->bindParam(':password', $hashedPassword);
                    $stmt->execute();
                    
                    file_put_contents($log_file, "Created default admin account\n", FILE_APPEND);
                    
                    // Check if password matches the default
                    if ($password === $defaultPassword) {
                        // Set admin session
                        $_SESSION['admin_id'] = $conn->lastInsertId();
                        $_SESSION['admin_email'] = 'admin@carrental.com';
                        $_SESSION['admin_name'] = 'Admin User';
                        $_SESSION['admin_table'] = 'users';
                        
                        // Also set regular user session
                        $_SESSION['id'] = $_SESSION['admin_id'];
                        $_SESSION['email'] = $_SESSION['admin_email'];
                        
                        // Set login_verified flag to indicate an explicit login action
                        $_SESSION['login_verified'] = true;
                        
                        // Redirect to admin dashboard
                        file_put_contents($log_file, "Redirecting to admin dashboard with newly created account\n", FILE_APPEND);
                        header('Location: /pages/admin/dashboard.php');
                        exit;
                    }
                }
            } catch (PDOException $e) {
                file_put_contents($log_file, "Error creating admin account: " . $e->getMessage() . "\n", FILE_APPEND);
            }
        }
        
        // Invalid credentials
        file_put_contents($log_file, "Invalid credentials, redirecting to login form\n", FILE_APPEND);
        $_SESSION['error'] = 'Ongeldige e-mail of wachtwoord.';
        header('Location: /login-form');
        exit;
    }
} catch (PDOException $e) {
    // Log the error (in a production environment)
    file_put_contents($log_file, "Database error: " . $e->getMessage() . "\n", FILE_APPEND);
    error_log("Login error: " . $e->getMessage());
    
    // Redirect with a generic error message
    $_SESSION['error'] = 'Er is een systeemfout opgetreden. Probeer het later opnieuw.';
    header('Location: /login-form');
    exit;
}
?>
