<?php
// Connect to the database
require_once "../../includes/db_connect.php";

// Admin account details
$email = 'admin@rydr.nl';
$password = 'admin123';
$first_name = 'Admin';
$last_name = 'Rydr';

try {
    // Check if admin already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    
    if ($admin) {
        $message = "Admin account bestaat al met e-mail: " . $email;
        $success = true;
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Create the admin account
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, is_admin) VALUES (?, ?, ?, ?, TRUE)");
        $stmt->execute([$first_name, $last_name, $email, $hashed_password]);
        
        $message = "Admin account succesvol aangemaakt!";
        $details = "E-mail: " . $email . "<br>Wachtwoord: " . $password;
        $success = true;
    }
} catch(PDOException $e) {
    if ($e->getCode() == '42S02') {
        // Table doesn't exist, create it first
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            address TEXT,
            is_admin BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $conn->exec($sql);
        
        // Now try inserting the admin again
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, is_admin) VALUES (?, ?, ?, ?, TRUE)");
        $stmt->execute([$first_name, $last_name, $email, $hashed_password]);
        
        $message = "Users tabel aangemaakt en admin account succesvol aangemaakt!";
        $details = "E-mail: " . $email . "<br>Wachtwoord: " . $password;
        $success = true;
    } else {
        // Some other error
        $message = "Error: " . $e->getMessage();
        $success = false;
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Setup - Rydr</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="icon" type="image/png" href="/assets/images/Ricon.png" sizes="32x32">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            text-align: center;
        }
        
        .setup-header {
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
        
        .message-box {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        
        .success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }
        
        .error {
            background-color: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }
        
        .message-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .details-box {
            background-color: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
        }
        
        .button-container {
            margin-top: 30px;
        }
        
        .button {
            background-color: #3563e9;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 20px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.2s;
            display: inline-block;
        }
        
        .button:hover {
            background-color: #2954d4;
        }
        
        .home-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #fff;
            color: #3563e9;
            border: 2px solid #3563e9;
            border-radius: 30px;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .home-button:hover {
            background-color: #3563e9;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(53, 99, 233, 0.2);
        }
        
        @media (max-width: 768px) {
            .home-button {
                top: 15px;
                right: 15px;
                padding: 6px 12px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <a href="/" class="home-button">
        <i class="fas fa-home"></i> Terug naar homepage
    </a>
    
    <div class="setup-container">
        <div class="setup-header">
            <div class="logo">Rydr<span class="dot">.</span></div>
            <div class="setup-title">Admin Setup</div>
        </div>
        
        <div class="message-box <?= $success ? 'success' : 'error' ?>">
            <div class="message-title"><?= $success ? 'Succes!' : 'Fout!' ?></div>
            <p><?= $message ?></p>
        </div>
        
        <?php if (isset($details)): ?>
        <div class="details-box">
            <strong>Account details:</strong><br>
            <?= $details ?>
        </div>
        <?php endif; ?>
        
        <div class="button-container">
            <a href="/admin/login" class="button">Ga naar inlogpagina</a>
        </div>
    </div>
</body>
</html> 