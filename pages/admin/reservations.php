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
$bookings = [];
$message = '';
$messageType = '';
$action = $_GET['action'] ?? '';
$bookingId = $_GET['id'] ?? '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Update booking status
        if ($_POST['action'] === 'update_status' && !empty($_POST['booking_id'])) {
            $bookingId = $_POST['booking_id'];
            $status = $_POST['status'] ?? '';
            
            if (empty($status)) {
                $message = 'Status is verplicht.';
                $messageType = 'error';
            } else {
                try {
                    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
                    $stmt->execute([$status, $bookingId]);
                    
                    $message = 'Reserveringsstatus succesvol bijgewerkt.';
                    $messageType = 'success';
                    $action = ''; // Reset action to show booking list
                } catch(PDOException $e) {
                    $message = 'Fout bij het bijwerken van reserveringsstatus: ' . $e->getMessage();
                    $messageType = 'error';
                }
            }
        }
        
        // Delete booking
        if ($_POST['action'] === 'delete' && !empty($_POST['booking_id'])) {
            $bookingId = $_POST['booking_id'];
            
            try {
                $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
                $stmt->execute([$bookingId]);
                
                $message = 'Reservering succesvol verwijderd.';
                $messageType = 'success';
            } catch(PDOException $e) {
                $message = 'Fout bij het verwijderen van reservering: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

// Get booking data for editing
$editBooking = null;
if ($action === 'edit' && !empty($bookingId)) {
    try {
        $stmt = $conn->prepare("
            SELECT b.*, 
                   CONCAT(a.first_name, ' ', a.last_name) as customer_name,
                   a.email as customer_email,
                   c.brand, c.model
            FROM bookings b
            LEFT JOIN account a ON b.user_id = a.id
            LEFT JOIN cars c ON b.car_id = c.id
            WHERE b.id = ?
        ");
        $stmt->execute([$bookingId]);
        $editBooking = $stmt->fetch();
        
        if (!$editBooking) {
            $message = 'Reservering niet gevonden.';
            $messageType = 'error';
            $action = '';
        }
    } catch(PDOException $e) {
        $message = 'Fout bij het ophalen van reserveringsgegevens: ' . $e->getMessage();
        $messageType = 'error';
        $action = '';
    }
}

// Get all bookings if not in edit mode
if (empty($action)) {
    try {
        $stmt = $conn->prepare("
            SELECT b.id, b.start_date, b.end_date, b.total_price, b.status, b.created_at,
                   CONCAT(a.first_name, ' ', a.last_name) as customer_name,
                   a.email as customer_email,
                   c.brand, c.model
            FROM bookings b
            LEFT JOIN account a ON b.user_id = a.id
            LEFT JOIN cars c ON b.car_id = c.id
            ORDER BY b.created_at DESC
        ");
        $stmt->execute();
        $bookings = $stmt->fetchAll();
    } catch(PDOException $e) {
        $message = 'Fout bij het ophalen van reserveringen: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Format status labels
$status_labels = [
    'pending' => 'In afwachting',
    'confirmed' => 'Bevestigd',
    'cancelled' => 'Geannuleerd',
    'completed' => 'Voltooid'
];

$status_colors = [
    'pending' => '#ff9800',
    'confirmed' => '#4caf50',
    'cancelled' => '#f44336',
    'completed' => '#2196f3'
];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserveringen Beheren - Rydr Admin</title>
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
        
        .card-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #3563e9;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .btn:hover {
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
        
        .btn-warning {
            background-color: #ff9800;
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #f57c00;
        }
        
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #218838;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #555;
        }
        
        tr:hover {
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
            background-color: #fff3e0;
            color: #ff9800;
        }
        
        .status-confirmed {
            background-color: #e8f5e9;
            color: #28a745;
        }
        
        .status-cancelled {
            background-color: #ffebee;
            color: #dc3545;
        }
        
        .status-completed {
            background-color: #e3f2fd;
            color: #2196f3;
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
        
        .action-column {
            width: 150px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
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
        
        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .detail-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
        }
        
        .detail-title {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .detail-value {
            font-size: 16px;
            font-weight: 600;
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
            
            .booking-details {
                grid-template-columns: 1fr;
            }
            
            .table-responsive {
                overflow-x: auto;
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
                <li><a href="/pages/admin/reservations.php" class="active"><i class="fas fa-calendar-alt"></i> Reserveringen</a></li>
                <li><a href="/pages/admin/settings.php"><i class="fas fa-cog"></i> Instellingen</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="topbar">
                <div class="page-title">Reserveringen Beheren</div>
                
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
            
            <?php if ($action === 'edit' && $editBooking): ?>
                <div class="content-card">
                    <div class="card-header">
                        <h2>Reservering Bewerken</h2>
                        <div class="card-actions">
                            <a href="/pages/admin/reservations.php" class="btn btn-secondary">Terug naar overzicht</a>
                        </div>
                    </div>
                    
                    <div class="booking-details">
                        <div class="detail-card">
                            <div class="detail-title">Reserveringsnummer</div>
                            <div class="detail-value">#<?php echo htmlspecialchars($editBooking['id']); ?></div>
                        </div>
                        
                        <div class="detail-card">
                            <div class="detail-title">Klant</div>
                            <div class="detail-value"><?php echo htmlspecialchars($editBooking['customer_name']); ?></div>
                        </div>
                        
                        <div class="detail-card">
                            <div class="detail-title">E-mail</div>
                            <div class="detail-value"><?php echo htmlspecialchars($editBooking['customer_email']); ?></div>
                        </div>
                        
                        <div class="detail-card">
                            <div class="detail-title">Voertuig</div>
                            <div class="detail-value"><?php echo htmlspecialchars($editBooking['brand'] . ' ' . $editBooking['model']); ?></div>
                        </div>
                        
                        <div class="detail-card">
                            <div class="detail-title">Ophaaldatum</div>
                            <div class="detail-value"><?php echo date('d-m-Y', strtotime($editBooking['start_date'])); ?></div>
                        </div>
                        
                        <div class="detail-card">
                            <div class="detail-title">Retourdatum</div>
                            <div class="detail-value"><?php echo date('d-m-Y', strtotime($editBooking['end_date'])); ?></div>
                        </div>
                        
                        <div class="detail-card">
                            <div class="detail-title">Totaalprijs</div>
                            <div class="detail-value">€<?php echo number_format($editBooking['total_price'], 2, ',', '.'); ?></div>
                        </div>
                        
                        <div class="detail-card">
                            <div class="detail-title">Aangemaakt op</div>
                            <div class="detail-value"><?php echo date('d-m-Y H:i', strtotime($editBooking['created_at'])); ?></div>
                        </div>
                    </div>
                    
                    <form method="POST" action="/pages/admin/reservations.php">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($editBooking['id']); ?>">
                        
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" required>
                                <?php foreach ($status_labels as $value => $label): ?>
                                    <option value="<?php echo $value; ?>" <?php echo $editBooking['status'] === $value ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn">Status bijwerken</button>
                            <a href="/pages/admin/reservations.php" class="btn btn-secondary">Annuleren</a>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="content-card">
                    <div class="card-header">
                        <h2>Alle Reserveringen</h2>
                    </div>
                    
                    <?php if (empty($bookings)): ?>
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-calendar-times"></i></div>
                            <div class="empty-title">Geen reserveringen gevonden</div>
                            <div class="empty-description">Er zijn momenteel geen reserveringen in het systeem.</div>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Klant</th>
                                        <th>Auto</th>
                                        <th>Periode</th>
                                        <th>Prijs</th>
                                        <th>Status</th>
                                        <th>Datum</th>
                                        <th class="action-column">Acties</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bookings as $booking): ?>
                                        <tr>
                                            <td>#<?php echo htmlspecialchars($booking['id']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['brand'] . ' ' . $booking['model']); ?></td>
                                            <td><?php echo date('d-m-Y', strtotime($booking['start_date'])); ?> t/m <?php echo date('d-m-Y', strtotime($booking['end_date'])); ?></td>
                                            <td>€<?php echo number_format($booking['total_price'], 2, ',', '.'); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo htmlspecialchars($booking['status']); ?>">
                                                    <?php echo htmlspecialchars($status_labels[$booking['status']] ?? $booking['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d-m-Y', strtotime($booking['created_at'])); ?></td>
                                            <td class="action-column">
                                                <a href="/pages/admin/reservations.php?action=edit&id=<?php echo $booking['id']; ?>" class="btn btn-secondary" title="Bewerken">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" action="/pages/admin/reservations.php" style="display: inline;" onsubmit="return confirm('Weet je zeker dat je deze reservering wilt verwijderen?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                    <button type="submit" class="btn btn-danger" title="Verwijderen">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Add active class to current menu item
        document.addEventListener('DOMContentLoaded', function() {
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