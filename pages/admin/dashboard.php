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

// Get database statistics
$stats = [
    'users' => 0,
    'cars' => 0,
    'bookings' => 0,
    'pending_bookings' => 0
];

try {
    // Count users - check which table to use
    if (isset($_SESSION['admin_table']) && $_SESSION['admin_table'] == 'users') {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_admin = FALSE");
        $stats['users'] = $stmt->fetch()['count'];
    } else {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM account WHERE role IS NULL OR role = 0");
        $stats['users'] = $stmt->fetch()['count'];
    }
    
    // Count cars
    try {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM cars");
        $stats['cars'] = $stmt->fetch()['count'];
    } catch(PDOException $e) {
        // Table might not exist
    }
    
    // Count bookings
    try {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM bookings");
        $stats['bookings'] = $stmt->fetch()['count'];
        
        $stmt = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'");
        $stats['pending_bookings'] = $stmt->fetch()['count'];
    } catch(PDOException $e) {
        // Table might not exist
    }
} catch(PDOException $e) {
    // Ignore errors
}

// Get recent users
$recentUsers = [];
try {
    if (isset($_SESSION['admin_table']) && $_SESSION['admin_table'] == 'users') {
        $stmt = $conn->query("SELECT id, first_name, last_name, email, created_at FROM users WHERE is_admin = FALSE ORDER BY created_at DESC LIMIT 5");
        $recentUsers = $stmt->fetchAll();
    } else {
        $stmt = $conn->query("SELECT id, first_name, last_name, email, created_at FROM account WHERE role IS NULL OR role = 0 ORDER BY created_at DESC LIMIT 5");
        $recentUsers = $stmt->fetchAll();
    }
} catch(PDOException $e) {
    // Ignore errors
}

// Get recent bookings
$recentBookings = [];
try {
    $stmt = $conn->query("SELECT b.id, b.start_date, b.end_date, b.total_price, b.status, 
                          CASE 
                            WHEN u.id IS NOT NULL THEN CONCAT(u.first_name, ' ', u.last_name)
                            WHEN a.id IS NOT NULL THEN CONCAT(a.first_name, ' ', a.last_name)
                            ELSE 'Onbekend'
                          END as customer_name,
                          c.brand, c.model
                          FROM bookings b
                          LEFT JOIN users u ON b.user_id = u.id
                          LEFT JOIN account a ON b.user_id = a.id
                          LEFT JOIN cars c ON b.car_id = c.id
                          ORDER BY b.created_at DESC LIMIT 5");
    $recentBookings = $stmt->fetchAll();
} catch(PDOException $e) {
    // Ignore errors
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Rydr</title>
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
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .dashboard-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 20px;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
        }
        
        .card-actions {
            display: flex;
            gap: 10px;
        }
        
        .card-btn {
            background-color: #3563e9;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(53, 99, 233, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .card-btn:hover {
            background-color: #2954d4;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(53, 99, 233, 0.3);
        }
        
        .card-btn.secondary {
            background-color: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        
        .card-btn.secondary:hover {
            background-color: #e9ecef;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .action-card {
            height: 70px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 12px;
            background-image: linear-gradient(135deg, #3563e9, #4e7df7);
            box-shadow: 0 4px 10px rgba(53, 99, 233, 0.25);
            transition: all 0.3s ease;
        }
        
        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(53, 99, 233, 0.35);
            background-image: linear-gradient(135deg, #2954d4, #3563e9);
        }
        
        .action-card i {
            font-size: 20px;
            margin-right: 8px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .stat-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: 700;
            color: #3563e9;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 14px;
            color: #666;
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
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-pending {
            background-color: #fff8e1;
            color: #f57c00;
        }
        
        .status-confirmed {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .status-cancelled {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .status-completed {
            background-color: #e3f2fd;
            color: #1565c0;
        }
        
        .action-btn {
            background: none;
            border: none;
            color: #3563e9;
            cursor: pointer;
            padding: 4px;
            font-size: 14px;
        }
        
        .action-btn:hover {
            color: #2954d4;
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
            
            .dashboard-grid {
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
                <li><a href="/pages/admin/dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="/pages/admin/users.php"><i class="fas fa-users"></i> Gebruikers</a></li>
                <li><a href="/pages/admin/cars.php"><i class="fas fa-car"></i> Auto's</a></li>
                <li><a href="/pages/admin/reservations.php"><i class="fas fa-calendar-alt"></i> Reserveringen</a></li>
                <li><a href="/pages/admin/settings.php"><i class="fas fa-cog"></i> Instellingen</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="topbar">
                <div class="page-title">Dashboard</div>
                
                <div class="user-info">
                    <div>
                        <div class="user-name"><?= htmlspecialchars($admin_name) ?></div>
                        <div class="user-role">Administrator</div>
                    </div>
                    <a href="/pages/admin/logout.php" class="logout-btn">Uitloggen</a>
                </div>
            </div>
            
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="card-header">
                        <div class="card-title">Statistieken</div>
                        <div class="card-actions">
                            <a href="/pages/admin/dashboard.php" class="card-btn secondary"><i class="fas fa-sync-alt"></i> Vernieuwen</a>
                        </div>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number"><?= $stats['users'] ?></div>
                            <div class="stat-label">Gebruikers</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?= $stats['cars'] ?></div>
                            <div class="stat-label">Auto's</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?= $stats['bookings'] ?></div>
                            <div class="stat-label">Reserveringen</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?= $stats['pending_bookings'] ?></div>
                            <div class="stat-label">Wachtend</div>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-header">
                        <div class="card-title">Recente Gebruikers</div>
                        <div class="card-actions">
                            <a href="/pages/admin/users.php" class="card-btn"><i class="fas fa-users"></i> Alle gebruikers</a>
                        </div>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Naam</th>
                                    <th>Email</th>
                                    <th>Datum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentUsers)): ?>
                                <tr>
                                    <td colspan="3" style="text-align: center;">Geen gebruikers gevonden</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($recentUsers as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td><?= date('d-m-Y', strtotime($user['created_at'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-header">
                        <div class="card-title">Recente Reserveringen</div>
                        <div class="card-actions">
                            <a href="/pages/admin/reservations.php" class="card-btn"><i class="fas fa-calendar-alt"></i> Alle reserveringen</a>
                        </div>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Klant</th>
                                    <th>Auto</th>
                                    <th>Datum</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentBookings)): ?>
                                <tr>
                                    <td colspan="4" style="text-align: center;">Geen reserveringen gevonden</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($recentBookings as $booking): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($booking['customer_name']) ?></td>
                                        <td><?= htmlspecialchars($booking['brand'] . ' ' . $booking['model']) ?></td>
                                        <td><?= date('d-m-Y', strtotime($booking['start_date'])) ?></td>
                                        <td>
                                            <span class="status-badge status-<?= strtolower($booking['status']) ?>">
                                                <?= htmlspecialchars(ucfirst($booking['status'])) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="card-header">
                        <div class="card-title">Snelle Acties</div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <a href="/pages/admin/users.php?action=add" class="card-btn action-card" style="text-decoration: none;">
                            <i class="fas fa-user-plus"></i> Nieuwe Gebruiker
                        </a>
                        <a href="/pages/admin/cars.php?action=add" class="card-btn action-card" style="text-decoration: none;">
                            <i class="fas fa-car-side"></i> Nieuwe Auto
                        </a>
                        <a href="/pages/admin/reservations.php?action=edit" class="card-btn action-card" style="text-decoration: none;">
                            <i class="fas fa-calendar-plus"></i> Beheer Reserveringen
                        </a>
                        <a href="/pages/admin/settings.php" class="card-btn action-card" style="text-decoration: none;">
                            <i class="fas fa-cog"></i> Instellingen
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 