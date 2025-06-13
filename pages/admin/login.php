<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: /pages/admin/dashboard.php');
    exit;
}

// Process login form
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once "../../includes/db_connect.php";
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        $admin = null;
        $adminFound = false;
        
        // First check users table
        try {
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND is_admin = TRUE");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password'])) {
                // Set admin session
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_name'] = $admin['first_name'] . ' ' . $admin['last_name'];
                $_SESSION['admin_table'] = 'users';
                
                // Redirect to dashboard
                header('Location: /pages/admin/dashboard.php');
                exit;
            } elseif ($admin) {
                $adminFound = true; // Admin exists but password is wrong
            }
        } catch(PDOException $e) {
            // Table might not exist, continue to check account table
        }
        
        // If not found in users table, check account table
        if (!$adminFound) {
            try {
                $stmt = $conn->prepare("SELECT * FROM account WHERE email = ? AND role = 1");
                $stmt->execute([$email]);
                $admin = $stmt->fetch();
                
                if ($admin && password_verify($password, $admin['password'])) {
                    // Set admin session
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_email'] = $admin['email'];
                    
                    // Check if first_name and last_name exist in the account table
                    if (isset($admin['first_name']) && isset($admin['last_name'])) {
                        $_SESSION['admin_name'] = $admin['first_name'] . ' ' . $admin['last_name'];
                    } else {
                        $_SESSION['admin_name'] = 'Administrator';
                    }
                    
                    $_SESSION['admin_table'] = 'account';
                    
                    // Redirect to dashboard
                    header('Location: /pages/admin/dashboard.php');
                    exit;
                } elseif ($admin) {
                    $adminFound = true; // Admin exists but password is wrong
                }
            } catch(PDOException $e) {
                // Table might not exist
            }
        }
        
        if ($adminFound) {
            $error = 'Ongeldig wachtwoord';
        } else {
            $error = 'Geen admin account gevonden met dit e-mailadres';
        }
    } catch(PDOException $e) {
        $error = 'Er is een fout opgetreden. Probeer het later opnieuw.';
        // Log the error for admin (not visible to user)
        error_log('Database error: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Rydr</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="icon" type="image/png" href="/assets/images/favicon.ico" sizes="32x32">
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
        
        .login-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            padding: 40px;
        }
        
        .login-header {
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
        
        .login-title {
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
        
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
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
        
        .admin-setup-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            font-size: 13px;
            color: #888;
        }
        
        .admin-setup-link a {
            color: #3563e9;
            text-decoration: none;
        }
        
        .admin-setup-link a:hover {
            text-decoration: underline;
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
    
    <div class="login-container">
        <div class="login-header">
            <div class="logo">Rydr<span class="dot">.</span></div>
            <div class="login-title">Admin Dashboard</div>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">E-mailadres</label>
                <input type="email" id="email" name="email" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Wachtwoord</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="submit-btn">Inloggen</button>
        </form>
        
        <a href="/" class="back-link">Terug naar website</a>
        
        <div class="admin-setup-link">
            Nog geen admin account? <a href="/pages/admin/setup.php">Maak er een aan</a>
        </div>
    </div>
</body>
</html> 