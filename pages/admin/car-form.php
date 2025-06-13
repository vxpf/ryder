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
$car = [
    'id' => '',
    'brand' => '',
    'model' => '',
    'type' => 'regular',
    'category' => '',
    'image_url' => '',
    'description' => '',
    'capacity' => '',
    'transmission' => '',
    'fuel_capacity' => '',
    'price' => '',
    'original_price' => '',
    'is_available' => true
];

$is_edit = false;
$form_title = 'Nieuwe Auto Toevoegen';
$submit_text = 'Toevoegen';

// Check if we're editing an existing car
if (isset($_GET['id'])) {
    $car_id = $_GET['id'];
    $is_edit = true;
    $form_title = 'Auto Bewerken';
    $submit_text = 'Opslaan';
    
    try {
        $stmt = $conn->prepare("SELECT * FROM cars WHERE id = :id");
        $stmt->bindParam(':id', $car_id);
        $stmt->execute();
        $db_car = $stmt->fetch();
        
        if ($db_car) {
            $car = $db_car;
        } else {
            header('Location: /pages/admin/cars.php?error=not_found');
            exit;
        }
    } catch(PDOException $e) {
        $error = "Er is een fout opgetreden bij het ophalen van de auto: " . $e->getMessage();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $required_fields = ['brand', 'type', 'category', 'capacity', 'transmission', 'fuel_capacity', 'price'];
    $error = false;
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $error = true;
            break;
        }
    }
    
    if ($error) {
        $error_message = "Vul alle verplichte velden in.";
    } else {
        // Process form data
        $car = [
            'brand' => $_POST['brand'],
            'model' => $_POST['model'] ?? '',
            'type' => $_POST['type'],
            'category' => $_POST['category'],
            'image_url' => $_POST['image_url'],
            'description' => $_POST['description'] ?? '',
            'capacity' => $_POST['capacity'],
            'transmission' => $_POST['transmission'],
            'fuel_capacity' => $_POST['fuel_capacity'],
            'price' => $_POST['price'],
            'original_price' => !empty($_POST['original_price']) ? $_POST['original_price'] : null,
            'is_available' => isset($_POST['is_available']) ? 1 : 0
        ];
        
        // Handle image upload (if implemented)
        if (isset($_FILES['car_image']) && $_FILES['car_image']['error'] === 0) {
            // Implement file upload logic here
            // Example: $car['image_url'] = upload_file($_FILES['car_image']);
        }
        
        try {
            if ($is_edit) {
                // Update existing car
                $stmt = $conn->prepare("
                    UPDATE cars SET 
                        brand = :brand,
                        model = :model,
                        type = :type,
                        category = :category,
                        image_url = :image_url,
                        description = :description,
                        capacity = :capacity,
                        transmission = :transmission,
                        fuel_capacity = :fuel_capacity,
                        price = :price,
                        original_price = :original_price,
                        is_available = :is_available
                    WHERE id = :id
                ");
                $stmt->bindParam(':id', $car_id);
            } else {
                // Insert new car
                $stmt = $conn->prepare("
                    INSERT INTO cars (
                        brand, model, type, category, image_url, description, capacity, 
                        transmission, fuel_capacity, price, original_price, is_available
                    ) VALUES (
                        :brand, :model, :type, :category, :image_url, :description, :capacity, 
                        :transmission, :fuel_capacity, :price, :original_price, :is_available
                    )
                ");
            }
            
            // Bind parameters
            $stmt->bindParam(':brand', $car['brand']);
            $stmt->bindParam(':model', $car['model']);
            $stmt->bindParam(':type', $car['type']);
            $stmt->bindParam(':category', $car['category']);
            $stmt->bindParam(':image_url', $car['image_url']);
            $stmt->bindParam(':description', $car['description']);
            $stmt->bindParam(':capacity', $car['capacity']);
            $stmt->bindParam(':transmission', $car['transmission']);
            $stmt->bindParam(':fuel_capacity', $car['fuel_capacity']);
            $stmt->bindParam(':price', $car['price']);
            $stmt->bindParam(':original_price', $car['original_price']);
            $stmt->bindParam(':is_available', $car['is_available']);
            
            $stmt->execute();
            
            if (!$is_edit) {
                $car_id = $conn->lastInsertId();
            }
            
            // Redirect back to cars list
            header('Location: /pages/admin/cars.php?success=' . ($is_edit ? 'updated' : 'added'));
            exit;
        } catch(PDOException $e) {
            $error_message = "Er is een fout opgetreden: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $form_title ?> - Admin Dashboard - Rydr</title>
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
        
        .form-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group.full-width {
            grid-column: span 2;
        }
        
        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        
        .form-control:focus {
            border-color: #3563e9;
            outline: none;
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 15px;
        }
        
        .form-check-input {
            width: 16px;
            height: 16px;
        }
        
        .form-check-label {
            font-size: 14px;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 10px 15px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            font-weight: 600;
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
        
        .notification {
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .notification.error {
            background-color: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }
        
        .form-hint {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .image-preview {
            max-width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 6px;
            margin-top: 10px;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 14px;
        }
        
        .image-preview img {
            max-width: 100%;
            max-height: 100%;
            border-radius: 6px;
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
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-group.full-width {
                grid-column: span 1;
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
                <li><a href="/pages/admin/bookings.php"><i class="fas fa-calendar-alt"></i> Reserveringen</a></li>
                <li><a href="/pages/admin/settings.php"><i class="fas fa-cog"></i> Instellingen</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="topbar">
                <div class="page-title"><?= $form_title ?></div>
                
                <div class="user-info">
                    <div>
                        <div class="user-name"><?= htmlspecialchars($admin_name) ?></div>
                        <div class="user-role">Administrator</div>
                    </div>
                    <a href="/pages/admin/logout.php" class="logout-btn">Uitloggen</a>
                </div>
            </div>
            
            <?php if (isset($error_message)): ?>
                <div class="notification error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= htmlspecialchars($error_message) ?></span>
                </div>
            <?php endif; ?>
            
            <div class="form-card">
                <form method="post" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="brand">Merk *</label>
                            <input type="text" class="form-control" id="brand" name="brand" value="<?= htmlspecialchars($car['brand']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="model">Model</label>
                            <input type="text" class="form-control" id="model" name="model" value="<?= htmlspecialchars($car['model']) ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="type">Type *</label>
                            <select class="form-control" id="type" name="type" required>
                                <option value="regular" <?= $car['type'] === 'regular' ? 'selected' : '' ?>>Regulier</option>
                                <option value="business" <?= $car['type'] === 'business' ? 'selected' : '' ?>>Zakelijk</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="category">Categorie *</label>
                            <select class="form-control" id="category" name="category" required>
                                <option value="Sport" <?= $car['category'] === 'Sport' ? 'selected' : '' ?>>Sport</option>
                                <option value="Sedan" <?= $car['category'] === 'Sedan' ? 'selected' : '' ?>>Sedan</option>
                                <option value="SUV" <?= $car['category'] === 'SUV' ? 'selected' : '' ?>>SUV</option>
                                <option value="Hatchback" <?= $car['category'] === 'Hatchback' ? 'selected' : '' ?>>Hatchback</option>
                                <option value="Transport" <?= $car['category'] === 'Transport' ? 'selected' : '' ?>>Transport</option>
                                <option value="Luxury" <?= $car['category'] === 'Luxury' ? 'selected' : '' ?>>Luxury</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="capacity">Capaciteit *</label>
                            <input type="text" class="form-control" id="capacity" name="capacity" value="<?= htmlspecialchars($car['capacity']) ?>" placeholder="Bijv. 2 People" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="transmission">Transmissie *</label>
                            <input type="text" class="form-control" id="transmission" name="transmission" value="<?= htmlspecialchars($car['transmission']) ?>" placeholder="Bijv. Manual" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="fuel_capacity">Brandstof capaciteit *</label>
                            <input type="text" class="form-control" id="fuel_capacity" name="fuel_capacity" value="<?= htmlspecialchars($car['fuel_capacity']) ?>" placeholder="Bijv. 70l" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="price">Prijs per dag (€) *</label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?= htmlspecialchars($car['price']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="original_price">Originele prijs (€) (optioneel)</label>
                            <input type="number" step="0.01" class="form-control" id="original_price" name="original_price" value="<?= htmlspecialchars($car['original_price'] ?? '') ?>">
                            <div class="form-hint">Laat leeg als er geen korting is</div>
                        </div>
                        
                        <div class="form-group full-width">
                            <label class="form-label" for="image_url">Afbeelding URL *</label>
                            <input type="text" class="form-control" id="image_url" name="image_url" value="<?= htmlspecialchars($car['image_url']) ?>" required>
                            <div class="form-hint">Voer het pad naar de afbeelding in (bijv. assets/images/products/car.jpg)</div>
                            
                            <div class="image-preview">
                                <?php if (!empty($car['image_url'])): ?>
                                    <img src="/<?= htmlspecialchars($car['image_url']) ?>" alt="Auto preview">
                                <?php else: ?>
                                    <span>Geen afbeelding</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="form-group full-width">
                            <label class="form-label" for="description">Beschrijving</label>
                            <textarea class="form-control" id="description" name="description"><?= htmlspecialchars($car['description']) ?></textarea>
                        </div>
                        
                        <div class="form-group full-width">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_available" name="is_available" <?= $car['is_available'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_available">Auto is beschikbaar voor verhuur</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <a href="/pages/admin/cars.php" class="btn btn-secondary">Annuleren</a>
                        <button type="submit" class="btn btn-primary"><?= $submit_text ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Preview image when URL changes
        document.getElementById('image_url').addEventListener('change', function() {
            const imagePreview = document.querySelector('.image-preview');
            const imageUrl = this.value.trim();
            
            if (imageUrl) {
                imagePreview.innerHTML = `<img src="/${imageUrl}" alt="Auto preview" onerror="this.onerror=null; this.parentElement.innerHTML='<span>Ongeldige afbeelding URL</span>';">`;
            } else {
                imagePreview.innerHTML = '<span>Geen afbeelding</span>';
            }
        });
    </script>
</body>
</html> 