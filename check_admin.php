<?php
// Include database connection
require_once 'includes/db_connect.php';

echo "<h1>Admin Account Check</h1>";

// Check account table
echo "<h2>Checking account table:</h2>";
try {
    $stmt = $conn->query("SELECT id, email, password, role FROM account WHERE role = 1 OR email = 'admin@carrental.com'");
    $admins = $stmt->fetchAll();
    
    if (empty($admins)) {
        echo "<p>No admin accounts found in the account table.</p>";
    } else {
        echo "<p>Found " . count($admins) . " admin account(s) in account table:</p>";
        echo "<ul>";
        foreach ($admins as $admin) {
            echo "<li>ID: " . $admin['id'] . ", Email: " . $admin['email'] . ", Role: " . $admin['role'] . "</li>";
            if ($admin['email'] === 'admin@carrental.com') {
                echo "<p>Password hash for admin@carrental.com: " . $admin['password'] . "</p>";
                echo "<p>Verify 'admin123': " . (password_verify('admin123', $admin['password']) ? 'MATCH' : 'NO MATCH') . "</p>";
            }
        }
        echo "</ul>";
    }
} catch(PDOException $e) {
    echo "<p>Error checking account table: " . $e->getMessage() . "</p>";
}

// Check users table
echo "<h2>Checking users table:</h2>";
try {
    $stmt = $conn->query("SELECT id, email, password, is_admin FROM users WHERE is_admin = TRUE OR email = 'admin@carrental.com'");
    $admins = $stmt->fetchAll();
    
    if (empty($admins)) {
        echo "<p>No admin accounts found in the users table.</p>";
    } else {
        echo "<p>Found " . count($admins) . " admin account(s) in users table:</p>";
        echo "<ul>";
        foreach ($admins as $admin) {
            echo "<li>ID: " . $admin['id'] . ", Email: " . $admin['email'] . ", Is Admin: " . ($admin['is_admin'] ? 'Yes' : 'No') . "</li>";
            if ($admin['email'] === 'admin@carrental.com') {
                echo "<p>Password hash for admin@carrental.com: " . $admin['password'] . "</p>";
                echo "<p>Verify 'admin123': " . (password_verify('admin123', $admin['password']) ? 'MATCH' : 'NO MATCH') . "</p>";
            }
        }
        echo "</ul>";
    }
} catch(PDOException $e) {
    echo "<p>Error checking users table: " . $e->getMessage() . "</p>";
}

// Create a guaranteed admin account
echo "<h2>Creating a guaranteed admin account:</h2>";
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
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = 'admin@carrental.com'");
    $stmt->execute();
    $adminExists = $stmt->fetch();
    
    if ($adminExists) {
        echo "<p>Admin account already exists in users table with ID: " . $adminExists['id'] . "</p>";
        echo "<p>Current password hash: " . $adminExists['password'] . "</p>";
        echo "<p>Verify 'admin123': " . (password_verify('admin123', $adminExists['password']) ? 'MATCH' : 'NO MATCH') . "</p>";
        
        // Reset password to admin123
        $newPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password = :password WHERE email = 'admin@carrental.com'");
        $update->bindParam(':password', $newPassword);
        $update->execute();
        
        echo "<p>Password reset to 'admin123'</p>";
        echo "<p>New password hash: " . $newPassword . "</p>";
    } else {
        // Create admin account
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, is_admin) 
                               VALUES ('Admin', 'User', 'admin@carrental.com', :password, TRUE)");
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        
        $newId = $conn->lastInsertId();
        echo "<p>Admin account created successfully with ID: $newId</p>";
        echo "<p>Email: admin@carrental.com</p>";
        echo "<p>Password: admin123</p>";
        echo "<p>Password hash: $password</p>";
    }
    
    // Verify the stored password works
    $verify = $conn->prepare("SELECT password FROM users WHERE email = 'admin@carrental.com'");
    $verify->execute();
    $storedHash = $verify->fetchColumn();
    
    echo "<p>Final verification for 'admin123': " . (password_verify('admin123', $storedHash) ? 'MATCH' : 'NO MATCH') . "</p>";
    
} catch(PDOException $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

// Check login status
echo "<h2>Current Session:</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<p>Admin ID: " . ($_SESSION['admin_id'] ?? 'Not set') . "</p>";
echo "<p>Admin Email: " . ($_SESSION['admin_email'] ?? 'Not set') . "</p>";
echo "<p>Admin Name: " . ($_SESSION['admin_name'] ?? 'Not set') . "</p>";
echo "<p>User ID: " . ($_SESSION['id'] ?? 'Not set') . "</p>";
echo "<p>User Email: " . ($_SESSION['email'] ?? 'Not set') . "</p>";

echo "<h2>Login Form:</h2>";
echo "<form action='/actions/login.php' method='post'>
    <p><label>Email: <input type='email' name='email' value='admin@carrental.com'></label></p>
    <p><label>Password: <input type='password' name='password' value='admin123'></label></p>
    <p><button type='submit'>Login</button></p>
</form>";

// Show debug log if it exists
$log_file = __DIR__ . '/logs/login_debug.log';
if (file_exists($log_file)) {
    echo "<h2>Debug Log:</h2>";
    echo "<pre>" . htmlspecialchars(file_get_contents($log_file)) . "</pre>";
}
?> 