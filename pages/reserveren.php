<?php require "includes/header.php" ?>
<?php require "includes/db_connect.php" ?>

<?php
// Get the car ID from the URL parameter
$car_id = $_GET['car_id'] ?? null;
$car = null;

// Try to fetch car details from database
if ($car_id) {
    try {
        $stmt = $conn->prepare("SELECT * FROM cars WHERE id = :id");
        $stmt->bindParam(':id', $car_id);
        $stmt->execute();
        $car = $stmt->fetch();
    } catch(PDOException $e) {
        // If there's an error, handle it gracefully
        $car = null;
    }
}

// If no car found, show message
if (!$car) {
    $car = [
        'brand' => 'Unknown',
        'model' => '',
        'price' => '0.00',
        'image_url' => 'assets/images/products/car (0).svg'
    ];
}

// Full car name
$car_full_name = trim($car['brand'] . ' ' . ($car['model'] ?? ''));
?>

<main class="reservation-page">
    <div class="container">
        <h1>Rent a Car</h1>
        
        <div class="reservation-content">
            <div class="car-summary">
                <h2>Your selected vehicle</h2>
                <div class="selected-car">
                    <img src="<?= htmlspecialchars($car['image_url']) ?>" alt="<?= htmlspecialchars($car_full_name) ?>">
                    <div class="car-info">
                        <h3><?= htmlspecialchars($car_full_name) ?></h3>
                        <p>Price: €<?= htmlspecialchars($car['price']) ?> / day</p>
                    </div>
                </div>
            </div>
            
            <div class="reservation-form">
                <h2>Reservation Details</h2>
                <form action="#" method="post">
                    <input type="hidden" name="car_id" value="<?= htmlspecialchars($car_id) ?>">
                    
                    <div class="form-group">
                        <label for="pickup_date">Pickup Date</label>
                        <input type="date" id="pickup_date" name="pickup_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="return_date">Return Date</label>
                        <input type="date" id="return_date" name="return_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" required></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="button-primary">Complete Reservation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<style>
.reservation-page {
    padding: 40px 0;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
}

.reservation-content {
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
    margin-top: 30px;
}

.car-summary, .reservation-form {
    flex: 1;
    min-width: 300px;
}

.selected-car {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px;
    background-color: #f5f5f5;
    border-radius: 8px;
    margin-top: 20px;
}

.selected-car img {
    width: 150px;
    height: auto;
    object-fit: contain;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.form-group input, .form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.form-group textarea {
    height: 100px;
}

.form-actions {
    margin-top: 30px;
}

.button-primary {
    background-color: #3563E9;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
    display: inline-block;
    text-decoration: none;
}

.button-primary:hover {
    background-color: #2a4fba;
}
</style>

<?php require "includes/footer.php" ?> 