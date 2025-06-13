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
$users = [];
$message = '';
$messageType = '';
$action = $_GET['action'] ?? '';
$userId = $_GET['id'] ?? '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Add user
        if ($_POST['action'] === 'add') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $firstName = $_POST['first_name'] ?? '';
            $lastName = $_POST['last_name'] ?? '';
            
            if (empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
                $message = 'Alle velden zijn verplicht.';
                $messageType = 'error';
            } else {
                try {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Determine which table to use
                    if (isset($_SESSION['admin_table']) && $_SESSION['admin_table'] == 'users') {
                        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, is_admin) VALUES (?, ?, ?, ?, FALSE)");
                        $stmt->execute([$firstName, $lastName, $email, $hashedPassword]);
                    } else {
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
                        
                        $stmt = $conn->prepare("INSERT INTO account (email, password, first_name, last_name, role) VALUES (?, ?, ?, ?, 0)");
                        $stmt->execute([$email, $hashedPassword, $firstName, $lastName]);
                    }
                    
                    $message = 'Gebruiker succesvol toegevoegd.';
                    $messageType = 'success';
                    $action = ''; // Reset action to show user list
                } catch(PDOException $e) {
                    $message = 'Fout bij het toevoegen van gebruiker: ' . $e->getMessage();
                    $messageType = 'error';
                }
            }
        }
        
        // Edit user
        if ($_POST['action'] === 'edit' && !empty($_POST['user_id'])) {
            $userId = $_POST['user_id'];
            $email = $_POST['email'] ?? '';
            $firstName = $_POST['first_name'] ?? '';
            $lastName = $_POST['last_name'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($email) || empty($firstName) || empty($lastName)) {
                $message = 'E-mail, voornaam en achternaam zijn verplicht.';
                $messageType = 'error';
            } else {
                try {
                    // Determine which table to use
                    if (isset($_SESSION['admin_table']) && $_SESSION['admin_table'] == 'users') {
                        if (!empty($password)) {
                            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                            $stmt = $conn->prepare("UPDATE users SET email = ?, first_name = ?, last_name = ?, password = ? WHERE id = ?");
                            $stmt->execute([$email, $firstName, $lastName, $hashedPassword, $userId]);
                        } else {
                            $stmt = $conn->prepare("UPDATE users SET email = ?, first_name = ?, last_name = ? WHERE id = ?");
                            $stmt->execute([$email, $firstName, $lastName, $userId]);
                        }
                    } else {
                        if (!empty($password)) {
                            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                            $stmt = $conn->prepare("UPDATE account SET email = ?, first_name = ?, last_name = ?, password = ? WHERE id = ?");
                            $stmt->execute([$email, $firstName, $lastName, $hashedPassword, $userId]);
                        } else {
                            $stmt = $conn->prepare("UPDATE account SET email = ?, first_name = ?, last_name = ? WHERE id = ?");
                            $stmt->execute([$email, $firstName, $lastName, $userId]);
                        }
                    }
                    
                    $message = 'Gebruiker succesvol bijgewerkt.';
                    $messageType = 'success';
                    $action = ''; // Reset action to show user list
                } catch(PDOException $e) {
                    $message = 'Fout bij het bijwerken van gebruiker: ' . $e->getMessage();
                    $messageType = 'error';
                }
            }
        }
        
        // Delete user
        if ($_POST['action'] === 'delete' && !empty($_POST['user_id'])) {
            $userId = $_POST['user_id'];
            
            try {
                // Determine which table to use
                if (isset($_SESSION['admin_table']) && $_SESSION['admin_table'] == 'users') {
                    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND is_admin = FALSE");
                    $stmt->execute([$userId]);
                } else {
                    $stmt = $conn->prepare("DELETE FROM account WHERE id = ? AND (role IS NULL OR role = 0)");
                    $stmt->execute([$userId]);
                }
                
                $message = 'Gebruiker succesvol verwijderd.';
                $messageType = 'success';
            } catch(PDOException $e) {
                $message = 'Fout bij het verwijderen van gebruiker: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

// Get user data for editing
$editUser = null;
if ($action === 'edit' && !empty($userId)) {
    try {
        // Determine which table to use
        if (isset($_SESSION['admin_table']) && $_SESSION['admin_table'] == 'users') {
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND is_admin = FALSE");
            $stmt->execute([$userId]);
            $editUser = $stmt->fetch();
        } else {
            $stmt = $conn->prepare("SELECT * FROM account WHERE id = ? AND (role IS NULL OR role = 0)");
            $stmt->execute([$userId]);
            $editUser = $stmt->fetch();
        }
        
        if (!$editUser) {
            $message = 'Gebruiker niet gevonden.';
            $messageType = 'error';
            $action = '';
        }
    } catch(PDOException $e) {
        $message = 'Fout bij het ophalen van gebruikersgegevens: ' . $e->getMessage();
        $messageType = 'error';
        $action = '';
    }
}

// Get all users if not in add/edit mode
if (empty($action)) {
    try {
        // Determine which table to use
        if (isset($_SESSION['admin_table']) && $_SESSION['admin_table'] == 'users') {
            $stmt = $conn->query("SELECT id, first_name, last_name, email, created_at FROM users WHERE is_admin = FALSE ORDER BY created_at DESC");
            $users = $stmt->fetchAll();
        } else {
            $stmt = $conn->query("SELECT id, first_name, last_name, email, created_at FROM account WHERE role IS NULL OR role = 0 ORDER BY created_at DESC");
            $users = $stmt->fetchAll();
        }
    } catch(PDOException $e) {
        $message = 'Fout bij het ophalen van gebruikers: ' . $e->getMessage();
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gebruikersbeheer - Admin Dashboard - Rydr</title>
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
        
        .card {
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
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
        }
        
        .card-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }
        
        .btn-primary {
            background-color: #3563e9;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2954d4;
        }
        
        .btn-secondary {
            background-color: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
        }
        
        .btn-secondary:hover {
            background-color: #e9ecef;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        
        table tr:hover {
            background-color: #f8f9fa;
        }
        
        .table-actions {
            display: flex;
            gap: 8px;
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
            box-sizing: border-box;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #3563e9;
            box-shadow: 0 0 0 2px rgba(53, 99, 233, 0.2);
        }
        
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
            margin-bottom: 0;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .alert-error {
            background-color: #ffebee;
            color: #c62828;
        }
        
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                padding: 10px 0;
            }
            
            .form-row {
                flex-direction: column;
                gap: 10px;
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
                <li><a href="/pages/admin/users.php" class="active"><i class="fas fa-users"></i> Gebruikers</a></li>
                <li><a href="/pages/admin/cars.php"><i class="fas fa-car"></i> Auto's</a></li>
                <li><a href="/pages/admin/reservations.php"><i class="fas fa-calendar-alt"></i> Reserveringen</a></li>
                <li><a href="/pages/admin/settings.php"><i class="fas fa-cog"></i> Instellingen</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="topbar">
                <div class="page-title">Gebruikersbeheer</div>
                
                <div class="user-info">
                    <div>
                        <div class="user-name"><?= htmlspecialchars($admin_name) ?></div>
                        <div class="user-role">Administrator</div>
                    </div>
                    <a href="/pages/admin/logout.php" class="logout-btn">Uitloggen</a>
                </div>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?= $messageType ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($action === 'add'): ?>
                <!-- Add User Form -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Nieuwe Gebruiker Toevoegen</div>
                    </div>
                    
                    <form method="POST" action="/pages/admin/users.php">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">Voornaam</label>
                                <input type="text" id="first_name" name="first_name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name">Achternaam</label>
                                <input type="text" id="last_name" name="last_name" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">E-mailadres</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Wachtwoord</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        
                        <div class="form-group" style="text-align: right;">
                            <a href="/pages/admin/users.php" class="btn btn-secondary">Annuleren</a>
                            <button type="submit" class="btn btn-primary">Gebruiker Toevoegen</button>
                        </div>
                    </form>
                </div>
            <?php elseif ($action === 'edit' && $editUser): ?>
                <!-- Edit User Form -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Gebruiker Bewerken</div>
                    </div>
                    
                    <form method="POST" action="/pages/admin/users.php">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($editUser['id']) ?>">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">Voornaam</label>
                                <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($editUser['first_name'] ?? '') ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name">Achternaam</label>
                                <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($editUser['last_name'] ?? '') ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">E-mailadres</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($editUser['email']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Wachtwoord (leeg laten om niet te wijzigen)</label>
                            <input type="password" id="password" name="password">
                        </div>
                        
                        <div class="form-group" style="text-align: right;">
                            <a href="/pages/admin/users.php" class="btn btn-secondary">Annuleren</a>
                            <button type="submit" class="btn btn-primary">Wijzigingen Opslaan</button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <!-- User List -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Gebruikers</div>
                        <div class="card-actions">
                            <a href="/pages/admin/users.php?action=add" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Nieuwe Gebruiker
                            </a>
                        </div>
                    </div>
                    
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Naam</th>
                                    <th>E-mail</th>
                                    <th>Aangemaakt op</th>
                                    <th>Acties</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center;">Geen gebruikers gevonden</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($user['id']) ?></td>
                                            <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td><?= date('d-m-Y', strtotime($user['created_at'])) ?></td>
                                            <td>
                                                <div class="table-actions">
                                                    <a href="/pages/admin/users.php?action=edit&id=<?= $user['id'] ?>" class="btn btn-secondary btn-sm">
                                                        <i class="fas fa-edit"></i> Bewerken
                                                    </a>
                                                    <form method="POST" action="/pages/admin/users.php" onsubmit="return confirm('Weet je zeker dat je deze gebruiker wilt verwijderen?');" style="display: inline;">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            <i class="fas fa-trash"></i> Verwijderen
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 