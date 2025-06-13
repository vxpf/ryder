<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Connect to the database
require_once "../../includes/db_connect.php";

// Check if already logged in as admin
if (isset($_SESSION['admin_id'])) {
    header('Location: /pages/admin/dashboard.php');
    exit;
}

$message = '';
$details = '';
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    
    if (empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
        $message = "Alle velden zijn verplicht.";
    } else {
        try {
            // Check which table exists
            $userTableExists = false;
            $accountTableExists = false;
            
            try {
                $check = $conn->query("SELECT 1 FROM users LIMIT 1");
                $userTableExists = true;
            } catch(PDOException $e) {
                // Table doesn't exist
            }
            
            try {
                $check = $conn->query("SELECT 1 FROM account LIMIT 1");
                $accountTableExists = true;
            } catch(PDOException $e) {
                // Table doesn't exist
            }
            
            if (!$userTableExists && !$accountTableExists) {
                // Create users table if neither table exists
                $sql = "CREATE TABLE IF NOT EXISTS users (
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
                )";
                
                $conn->exec($sql);
                $userTableExists = true;
                $message = "Gebruikerstabel succesvol aangemaakt.";
            }
            
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            if ($userTableExists) {
                // Check if admin already exists in users table
                $stmt = $conn->prepare("SELECT * FROM users WHERE is_admin = TRUE LIMIT 1");
                $stmt->execute();
                $admin = $stmt->fetch();
                
                if ($admin) {
                    $message = "Er bestaat al een admin account met e-mail: " . $admin['email'];
                } else {
                    // Create the admin account in users table
                    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, is_admin) VALUES (?, ?, ?, ?, TRUE)");
                    $stmt->execute([$first_name, $last_name, $email, $hashed_password]);
                    
                    $message = "Admin account succesvol aangemaakt!";
                    $details = "E-mail: " . $email;
                    $success = true;
                }
            } elseif ($accountTableExists) {
                // Check if account table has role column
                $hasRoleColumn = false;
                $result = $conn->query("SHOW COLUMNS FROM account LIKE 'role'");
                if ($result && $result->rowCount() > 0) {
                    $hasRoleColumn = true;
                } else {
                    // Add role column if it doesn't exist
                    $conn->exec("ALTER TABLE account ADD COLUMN role INT DEFAULT NULL");
                    $hasRoleColumn = true;
                }
                
                // Check if first_name and last_name columns exist
                $hasNameColumns = false;
                $result = $conn->query("SHOW COLUMNS FROM account LIKE 'first_name'");
                if ($result && $result->rowCount() > 0) {
                    $hasNameColumns = true;
                } else {
                    // Add name columns if they don't exist
                    $conn->exec("ALTER TABLE account ADD COLUMN first_name VARCHAR(100) AFTER password, ADD COLUMN last_name VARCHAR(100) AFTER first_name");
                    $hasNameColumns = true;
                }
                
                // Check if admin already exists in account table
                $stmt = $conn->prepare("SELECT * FROM account WHERE role = 1 LIMIT 1");
                $stmt->execute();
                $admin = $stmt->fetch();
                
                if ($admin) {
                    $message = "Er bestaat al een admin account met e-mail: " . $admin['email'];
                } else {
                    // Create the admin account in account table
                    if ($hasNameColumns) {
                        $stmt = $conn->prepare("INSERT INTO account (email, password, first_name, last_name, role) VALUES (?, ?, ?, ?, 1)");
                        $stmt->execute([$email, $hashed_password, $first_name, $last_name]);
                    } else {
                        $stmt = $conn->prepare("INSERT INTO account (email, password, role) VALUES (?, ?, 1)");
                        $stmt->execute([$email, $hashed_password]);
                    }
                    
                    $message = "Admin account succesvol aangemaakt!";
                    $details = "E-mail: " . $email;
                    $success = true;
                }
            }
        } catch(PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}

// Check if admin exists
$adminExists = false;
$adminEmail = '';

try {
    // Check users table
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE is_admin = TRUE LIMIT 1");
        $stmt->execute();
        $admin = $stmt->fetch();
        
        if ($admin) {
            $adminExists = true;
            $adminEmail = $admin['email'];
        }
    } catch(PDOException $e) {
        // Table might not exist, ignore
    }
    
    // Check account table if no admin found in users
    if (!$adminExists) {
        try {
            $stmt = $conn->prepare("SELECT * FROM account WHERE role = 1 LIMIT 1");
            $stmt->execute();
            $admin = $stmt->fetch();
            
            if ($admin) {
                $adminExists = true;
                $adminEmail = $admin['email'];
            }
        } catch(PDOException $e) {
            // Table might not exist, ignore
        }
    }
} catch(PDOException $e) {
    $message = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Setup - Rydr</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="icon" type="image/png" href="/assets/images/favicon.ico" sizes="32x32">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f6f7f9;
            font-family: 'Plus Jakarta Sans', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        
        .setup-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
            padding: 40px;
        }
        
        .setup-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo {
            font-size: 32px;
            font-weight: 800;
            color: #333;
            margin-bottom: 5px;
            display: inline-block;
        }
        
        .logo .dot {
            color: #ff3b58;
        }
        
        .setup-title {
            font-size: 20px;
            color: #666;
            margin-top: 5px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: inherit;
            font-size: 16px;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #3563e9;
            box-shadow: 0 0 0 2px rgba(53, 99, 233, 0.2);
        }
        
        .submit-btn {
            background-color: #3563e9;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 20px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .submit-btn:hover {
            background-color: #2954d4;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }
        
        .message.success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .message.error {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .message.info {
            background-color: #e3f2fd;
            color: #1565c0;
        }
        
        .details {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
            font-size: 14px;
        }
        
        .back-link:hover {
            color: #3563e9;
        }
        
        .login-btn {
            display: inline-block;
            background-color: #4caf50;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 15px;
            transition: background-color 0.2s;
        }
        
        .login-btn:hover {
            background-color: #43a047;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <div class="logo">Rydr<span class="dot">.</span></div>
            <div class="setup-title">Admin Setup</div>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $success ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            
            <?php if (!empty($details)): ?>
                <div class="details">
                    <?php echo $details; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <?php if ($adminExists): ?>
            <div class="message info">
                Er is al een admin account aanwezig met e-mail: <?php echo htmlspecialchars($adminEmail); ?>
            </div>
            <div style="text-align: center;">
                <a href="/pages/admin/login.php" class="login-btn">Login als Admin</a>
            </div>
        <?php else: ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="first_name">Voornaam</label>
                    <input type="text" id="first_name" name="first_name" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="last_name">Achternaam</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">E-mailadres</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Wachtwoord</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="submit-btn">Admin Account Aanmaken</button>
            </form>
        <?php endif; ?>
        
        <a href="/" class="back-link">Terug naar website</a>
    </div>
</body>
</html> 