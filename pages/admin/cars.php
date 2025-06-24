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

// Handle filters
$filters = [
    'type' => $_GET['type'] ?? 'all',
    'available' => $_GET['available'] ?? 'all',
    'search' => $_GET['search'] ?? ''
];

// Build query based on filters
$query = "SELECT * FROM cars WHERE 1=1";
$params = [];

if ($filters['type'] !== 'all') {
    $query .= " AND type = :type";
    $params[':type'] = $filters['type'];
}

if ($filters['available'] !== 'all') {
    $query .= " AND is_available = :available";
    $params[':available'] = ($filters['available'] === 'yes') ? 1 : 0;
}

if (!empty($filters['search'])) {
    $query .= " AND (brand LIKE :search OR model LIKE :search OR category LIKE :search)";
    $params[':search'] = "%" . $filters['search'] . "%";
}

$query .= " ORDER BY id DESC";

// Get cars
$cars = [];
try {
    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $cars = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Er is een fout opgetreden bij het ophalen van de auto's: " . $e->getMessage();
}

// Handle car deletion
if (isset($_POST['delete_car']) && isset($_POST['car_id'])) {
    $car_id = $_POST['car_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM cars WHERE id = :id");
        $stmt->bindParam(':id', $car_id);
        $stmt->execute();
        
        // Redirect to refresh the page
        header('Location: /pages/admin/cars.php?deleted=success');
        exit;
    } catch(PDOException $e) {
        $error = "Er is een fout opgetreden bij het verwijderen van de auto: " . $e->getMessage();
    }
}

// Handle availability toggle
if (isset($_POST['toggle_availability']) && isset($_POST['car_id'])) {
    $car_id = $_POST['car_id'];
    try {
        $stmt = $conn->prepare("UPDATE cars SET is_available = NOT is_available WHERE id = :id");
        $stmt->bindParam(':id', $car_id);
        $stmt->execute();
        
        // Redirect to refresh the page
        header('Location: /pages/admin/cars.php?updated=success');
        exit;
    } catch(PDOException $e) {
        $error = "Er is een fout opgetreden bij het wijzigen van de beschikbaarheid: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto's Beheren - Admin Dashboard - Rydr</title>
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
        
        .filter-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            background-color: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .filter-label {
            font-size: 14px;
            font-weight: 600;
        }
        
        .filter-select, .filter-input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .filter-button {
            background-color: #3563e9;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px 15px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: auto;
        }
        
        .filter-button:hover {
            background-color: #2954d4;
        }
        
        .filter-button.reset {
            background-color: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
        }
        
        .filter-button.reset:hover {
            background-color: #e9ecef;
        }
        
        .add-new {
            margin-left: auto;
        }
        
        .cars-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .car-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .car-image {
            height: 180px;
            background-color: #f8f9fa;
            position: relative;
            overflow: hidden;
        }
        
        .car-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .car-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .car-badge.regular {
            background-color: #3563e9;
        }
        
        .car-badge.business {
            background-color: #f59e0b;
        }
        
        .car-availability {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .car-availability.available {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .car-availability.unavailable {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .car-details {
            padding: 15px;
        }
        
        .car-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .car-category {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .car-features {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            font-size: 14px;
            color: #555;
        }
        
        .car-feature {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .car-price {
            font-size: 20px;
            font-weight: 700;
            color: #3563e9;
            margin-bottom: 15px;
        }
        
        .car-actions {
            display: flex;
            gap: 10px;
        }
        
        .car-btn {
            flex: 1;
            text-align: center;
            padding: 8px 0;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .car-btn.edit {
            background-color: #3563e9;
            color: white;
            border: none;
        }
        
        .car-btn.edit:hover {
            background-color: #2954d4;
        }
        
        .car-btn.toggle {
            background-color: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
        }
        
        .car-btn.toggle:hover {
            background-color: #e9ecef;
        }
        
        .car-btn.delete {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        
        .car-btn.delete:hover {
            background-color: #ffcdd2;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .empty-icon {
            font-size: 48px;
            color: #ddd;
            margin-bottom: 15px;
        }
        
        .empty-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .empty-description {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
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
                padding: 10px 0;
                position: relative;
                height: auto;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .filter-bar {
                flex-direction: column;
            }
            
            .cars-grid {
                grid-template-columns: 1fr;
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
                <li><a href="/pages/admin/cars.php" class="active"><i class="fas fa-car"></i> Auto's</a></li>
                <li><a href="/pages/admin/reservations.php"><i class="fas fa-calendar-alt"></i> Reserveringen</a></li>
                <li><a href="/pages/admin/settings.php"><i class="fas fa-cog"></i> Instellingen</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="topbar">
                <div class="page-title">Auto's Beheren</div>
                
                <div class="user-info">
                    <div>
                        <div class="user-name"><?= htmlspecialchars($admin_name) ?></div>
                        <div class="user-role">Administrator</div>
                    </div>
                    <a href="/pages/admin/logout.php" class="logout-btn">Uitloggen</a>
                </div>
            </div>
            
            <?php if (isset($_GET['deleted']) && $_GET['deleted'] === 'success'): ?>
                <div class="notification success">
                    <i class="fas fa-check-circle"></i>
                    <span>De auto is succesvol verwijderd.</span>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['updated']) && $_GET['updated'] === 'success'): ?>
                <div class="notification success">
                    <i class="fas fa-check-circle"></i>
                    <span>De auto is succesvol bijgewerkt.</span>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="notification error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>
            
            <div class="filter-bar">
                <form action="" method="get" style="display: flex; gap: 15px; flex-wrap: wrap; width: 100%;">
                    <div class="filter-group">
                        <label class="filter-label" for="type">Type</label>
                        <select class="filter-select" name="type" id="type">
                            <option value="all" <?= $filters['type'] === 'all' ? 'selected' : '' ?>>Alle</option>
                            <option value="regular" <?= $filters['type'] === 'regular' ? 'selected' : '' ?>>Regulier</option>
                            <option value="business" <?= $filters['type'] === 'business' ? 'selected' : '' ?>>Zakelijk</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label" for="available">Beschikbaarheid</label>
                        <select class="filter-select" name="available" id="available">
                            <option value="all" <?= $filters['available'] === 'all' ? 'selected' : '' ?>>Alle</option>
                            <option value="yes" <?= $filters['available'] === 'yes' ? 'selected' : '' ?>>Beschikbaar</option>
                            <option value="no" <?= $filters['available'] === 'no' ? 'selected' : '' ?>>Niet beschikbaar</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label" for="search">Zoeken</label>
                        <input class="filter-input" type="text" name="search" id="search" placeholder="Merk, model, categorie..." value="<?= htmlspecialchars($filters['search']) ?>">
                    </div>
                    
                    <div class="filter-group">
                        <button type="submit" class="filter-button">Filteren</button>
                    </div>
                    
                    <div class="filter-group">
                        <a href="/pages/admin/cars.php" class="filter-button reset" style="display: inline-block; text-decoration: none;">Reset</a>
                    </div>
                    
                    <div class="filter-group add-new">
                        <a href="/pages/admin/car-form.php" class="filter-button" style="display: inline-block; text-decoration: none;">
                            <i class="fas fa-plus"></i> Nieuwe Auto
                        </a>
                    </div>
                </form>
            </div>
            
            <?php if (empty($cars)): ?>
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-car"></i></div>
                    <div class="empty-title">Geen auto's gevonden</div>
                    <div class="empty-description">Er zijn geen auto's die voldoen aan de geselecteerde filters.</div>
                    <a href="/pages/admin/car-form.php" class="filter-button" style="display: inline-block; text-decoration: none;">
                        <i class="fas fa-plus"></i> Nieuwe Auto Toevoegen
                    </a>
                </div>
            <?php else: ?>
                <div class="cars-grid">
                    <?php foreach ($cars as $car): ?>
                        <div class="car-card">
                            <div class="car-image">
                                <img src="/<?= htmlspecialchars($car['image_url']) ?>" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>">
                                <div class="car-badge <?= $car['type'] ?>">
                                    <?= $car['type'] === 'regular' ? 'Regulier' : 'Zakelijk' ?>
                                </div>
                                <div class="car-availability <?= $car['is_available'] ? 'available' : 'unavailable' ?>">
                                    <?= $car['is_available'] ? 'Beschikbaar' : 'Niet beschikbaar' ?>
                                </div>
                            </div>
                            <div class="car-details">
                                <div class="car-title"><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></div>
                                <div class="car-category"><?= htmlspecialchars($car['category']) ?></div>
                                
                                <div class="car-features">
                                    <div class="car-feature">
                                        <i class="fas fa-user"></i> <?= htmlspecialchars($car['capacity']) ?>
                                    </div>
                                    <div class="car-feature">
                                        <i class="fas fa-cog"></i> <?= htmlspecialchars($car['transmission']) ?>
                                    </div>
                                    <div class="car-feature">
                                        <i class="fas fa-gas-pump"></i> <?= htmlspecialchars($car['fuel_capacity']) ?>
                                    </div>
                                </div>
                                
                                <div class="car-price">
                                    €<?= number_format($car['price'], 2, ',', '.') ?> / dag
                                    <?php if (!empty($car['original_price'])): ?>
                                        <span style="text-decoration: line-through; font-size: 14px; color: #666; margin-left: 5px;">
                                            €<?= number_format($car['original_price'], 2, ',', '.') ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="car-actions">
                                    <a href="/pages/admin/car-form.php?id=<?= $car['id'] ?>" class="car-btn edit">
                                        Bewerken
                                    </a>
                                    
                                    <form method="post" style="flex: 1;">
                                        <input type="hidden" name="car_id" value="<?= $car['id'] ?>">
                                        <input type="hidden" name="toggle_availability" value="1">
                                        <button type="submit" class="car-btn toggle" style="width: 100%;">
                                            <?= $car['is_available'] ? 'Zet niet beschikbaar' : 'Zet beschikbaar' ?>
                                        </button>
                                    </form>
                                </div>
                                
                                <form method="post" style="margin-top: 10px;" onsubmit="return confirm('Weet je zeker dat je deze auto wilt verwijderen?');">
                                    <input type="hidden" name="car_id" value="<?= $car['id'] ?>">
                                    <input type="hidden" name="delete_car" value="1">
                                    <button type="submit" class="car-btn delete" style="width: 100%;">
                                        Verwijderen
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 