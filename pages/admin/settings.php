<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: /pages/admin/login.php');
    exit;
}

// Connect to the database
require_once "../../includes/db_connect.php";

// Get admin info
$admin_name = $_SESSION['admin_name'] ?? 'Administrator';
$admin_email = $_SESSION['admin_email'] ?? 'admin@rydr.nl';

// Initialize variables
$message = '';
$messageType = '';
$action = $_GET['action'] ?? '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Update admin profile
        if ($_POST['action'] === 'update_profile') {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (empty($name) || empty($email)) {
                $message = 'Naam en e-mail zijn verplicht.';
                $messageType = 'error';
            } else {
                try {
                    // Update admin info
                    $adminId = $_SESSION['admin_id'];
                    
                    // Check if password should be updated
                    if (!empty($newPassword)) {
                        // Verify current password
                        $stmt = $conn->prepare("SELECT password FROM account WHERE id = ?");
                        $stmt->execute([$adminId]);
                        $admin = $stmt->fetch();
                        
                        if (!$admin || !password_verify($currentPassword, $admin['password'])) {
                            $message = 'Huidig wachtwoord is onjuist.';
                            $messageType = 'error';
                        } elseif ($newPassword !== $confirmPassword) {
                            $message = 'Nieuwe wachtwoorden komen niet overeen.';
                            $messageType = 'error';
                        } else {
                            // Update name, email, and password
                            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                            $stmt = $conn->prepare("UPDATE account SET first_name = ?, email = ?, password = ? WHERE id = ?");
                            $stmt->execute([$name, $email, $hashedPassword, $adminId]);
                            
                            $_SESSION['admin_name'] = $name;
                            $_SESSION['admin_email'] = $email;
                            
                            $message = 'Profiel en wachtwoord succesvol bijgewerkt.';
                            $messageType = 'success';
                        }
                    } else {
                        // Just update name and email
                        $stmt = $conn->prepare("UPDATE account SET first_name = ?, email = ? WHERE id = ?");
                        $stmt->execute([$name, $email, $adminId]);
                        
                        $_SESSION['admin_name'] = $name;
                        $_SESSION['admin_email'] = $email;
                        
                        $message = 'Profiel succesvol bijgewerkt.';
                        $messageType = 'success';
                    }
                } catch(PDOException $e) {
                    $message = 'Fout bij het bijwerken van profiel: ' . $e->getMessage();
                    $messageType = 'error';
                }
            }
        }
        
        // Update website settings
        if ($_POST['action'] === 'update_settings') {
            $siteName = $_POST['site_name'] ?? '';
            $siteEmail = $_POST['site_email'] ?? '';
            $contactPhone = $_POST['contact_phone'] ?? '';
            $contactAddress = $_POST['contact_address'] ?? '';
            
            try {
                // Check if settings table exists, if not create it
                $tableCheck = $conn->query("SHOW TABLES LIKE 'settings'");
                if ($tableCheck->rowCount() == 0) {
                    $conn->exec("
                        CREATE TABLE settings (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            setting_key VARCHAR(50) NOT NULL UNIQUE,
                            setting_value TEXT,
                            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                        )
                    ");
                }
                
                // Function to update or insert setting
                function updateSetting($conn, $key, $value) {
                    $stmt = $conn->prepare("SELECT * FROM settings WHERE setting_key = ?");
                    $stmt->execute([$key]);
                    
                    if ($stmt->rowCount() > 0) {
                        $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
                        $stmt->execute([$value, $key]);
                    } else {
                        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
                        $stmt->execute([$key, $value]);
                    }
                }
                
                // Update settings
                updateSetting($conn, 'site_name', $siteName);
                updateSetting($conn, 'site_email', $siteEmail);
                updateSetting($conn, 'contact_phone', $contactPhone);
                updateSetting($conn, 'contact_address', $contactAddress);
                
                $message = 'Website instellingen succesvol bijgewerkt.';
                $messageType = 'success';
            } catch(PDOException $e) {
                $message = 'Fout bij het bijwerken van instellingen: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

// Get admin profile
$profile = [
    'name' => $admin_name,
    'email' => $admin_email
];

// Get website settings
$settings = [
    'site_name' => 'Rydr',
    'site_email' => 'info@rydr.nl',
    'contact_phone' => '+31 6 12345678',
    'contact_address' => 'Voorbeeldstraat 123, 1234 AB Amsterdam'
];

try {
    // Check if settings table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'settings'");
    if ($tableCheck->rowCount() > 0) {
        // Get settings from database
        $stmt = $conn->query("SELECT setting_key, setting_value FROM settings");
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
} catch(PDOException $e) {
    // Ignore errors
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instellingen - Rydr Admin</title>
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
            margin: 0;
            color: #333;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background-color: #1a202c;
            color: white;
            padding: 20px 0;
            flex-shrink: 0;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid #2d3748;
            margin-bottom: 20px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 5px;
        }
        
        .logo .dot {
            color: #ff3b58;
        }
        
        .admin-label {
            font-size: 12px;
            color: #a0aec0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #a0aec0;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: #2d3748;
            color: white;
        }
        
        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
            margin-left: 250px;
        }
        
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background-color: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .page-title {
            font-size: 24px;
            font-weight: 700;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-name {
            font-weight: 600;
        }
        
        .user-role {
            font-size: 12px;
            color: #666;
        }
        
        .logout-btn {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            color: #333;
            text-decoration: none;
        }
        
        .logout-btn:hover {
            background-color: #e9ecef;
        }
        
        .content-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .card-header h2 {
            font-size: 18px;
            margin: 0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            font-family: inherit;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #3563e9;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 16px;
            background-color: #3563e9;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(53, 99, 233, 0.2);
        }
        
        .btn:hover {
            background-color: #2954d4;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(53, 99, 233, 0.3);
        }
        
        .btn-secondary {
            background-color: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        
        .btn-secondary:hover {
            background-color: #e9ecef;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
            box-shadow: 0 2px 5px rgba(220, 53, 69, 0.2);
        }
        
        .btn-danger:hover {
            background-color: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }
        
        .tab-container {
            margin-bottom: 20px;
        }
        
        .tab-nav {
            display: flex;
            border-bottom: 1px solid #e1e1e1;
            margin-bottom: 20px;
        }
        
        .tab-btn {
            padding: 10px 20px;
            border: none;
            background: none;
            cursor: pointer;
            font-weight: 600;
            color: #666;
            position: relative;
            transition: all 0.2s;
        }
        
        .tab-btn:hover {
            color: #3563e9;
        }
        
        .tab-btn.active {
            color: #3563e9;
        }
        
        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #3563e9;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .logout-link {
            display: block;
            margin-top: 30px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 6px;
            text-align: center;
            color: #dc3545;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .logout-link:hover {
            background-color: #ffebee;
        }
        
        .notification {
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .notification.success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }
        
        .notification.error {
            background-color: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }
        
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="logo">Rydr<span class="dot">.</span></div>
                <div class="admin-label">Admin Dashboard</div>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="/pages/admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="/pages/admin/users.php"><i class="fas fa-users"></i> Gebruikers</a></li>
                <li><a href="/pages/admin/cars.php"><i class="fas fa-car"></i> Auto's</a></li>
                <li><a href="/pages/admin/reservations.php"><i class="fas fa-calendar-alt"></i> Reserveringen</a></li>
                <li><a href="/pages/admin/settings.php" class="active"><i class="fas fa-cog"></i> Instellingen</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="topbar">
                <div class="page-title">Instellingen</div>
                
                <div class="user-info">
                    <div>
                        <div class="user-name"><?php echo htmlspecialchars($admin_name); ?></div>
                        <div class="user-role">Administrator</div>
                    </div>
                    <a href="/pages/admin/logout.php" class="logout-btn">Uitloggen</a>
                </div>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="notification <?php echo $messageType === 'error' ? 'error' : 'success'; ?>">
                    <i class="fas <?php echo $messageType === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <div class="tab-container">
                <div class="tab-nav">
                    <button class="tab-btn active" data-tab="profile">Mijn Profiel</button>
                    <button class="tab-btn" data-tab="website">Website Instellingen</button>
                    <button class="tab-btn" data-tab="security">Beveiliging</button>
                </div>
                
                <div class="tab-content active" id="profile-tab">
                    <div class="content-card">
                        <div class="card-header">
                            <h2>Mijn Profiel</h2>
                        </div>
                        
                        <form method="POST" action="/pages/admin/settings.php">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="form-group">
                                <label for="name">Naam</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($profile['name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">E-mailadres</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($profile['email']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="current_password">Huidig Wachtwoord (alleen nodig voor wachtwoordwijziging)</label>
                                <input type="password" id="current_password" name="current_password">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="new_password">Nieuw Wachtwoord (leeg laten om ongewijzigd te houden)</label>
                                    <input type="password" id="new_password" name="new_password">
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password">Bevestig Nieuw Wachtwoord</label>
                                    <input type="password" id="confirm_password" name="confirm_password">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn"><i class="fas fa-save"></i> Profiel Bijwerken</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="tab-content" id="website-tab">
                    <div class="content-card">
                        <div class="card-header">
                            <h2>Website Instellingen</h2>
                        </div>
                        
                        <form method="POST" action="/pages/admin/settings.php">
                            <input type="hidden" name="action" value="update_settings">
                            
                            <div class="form-group">
                                <label for="site_name">Website Naam</label>
                                <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($settings['site_name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="site_email">Website E-mail</label>
                                <input type="email" id="site_email" name="site_email" value="<?php echo htmlspecialchars($settings['site_email']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="contact_phone">Contact Telefoonnummer</label>
                                <input type="text" id="contact_phone" name="contact_phone" value="<?php echo htmlspecialchars($settings['contact_phone']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="contact_address">Contact Adres</label>
                                <textarea id="contact_address" name="contact_address" rows="3"><?php echo htmlspecialchars($settings['contact_address']); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn"><i class="fas fa-save"></i> Instellingen Opslaan</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="tab-content" id="security-tab">
                    <div class="content-card">
                        <div class="card-header">
                            <h2>Beveiliging & Sessies</h2>
                        </div>
                        
                        <p>Hier kunt u uw beveiligingsinstellingen beheren en uitloggen.</p>
                        
                        <a href="/pages/admin/logout.php" class="logout-link">
                            <i class="fas fa-sign-out-alt"></i> Uitloggen
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab functionality
            const tabBtns = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    
                    // Remove active class from all buttons and tabs
                    tabBtns.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Add active class to current button and tab
                    this.classList.add('active');
                    document.getElementById(tabId + '-tab').classList.add('active');
                });
            });
            
            // Add active class to current menu item
            const currentPath = window.location.pathname;
            const menuItems = document.querySelectorAll('.sidebar-menu a');
            
            menuItems.forEach(item => {
                if (currentPath.includes(item.getAttribute('href'))) {
                    item.classList.add('active');
                }
            });
        });
    </script>
</body>
</html> 