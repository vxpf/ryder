<?php require "includes/header.php" ?>
<?php require "includes/db_connect.php"; ?>

<?php
// Get search query from URL
$search_term = $_GET['query'] ?? '';

// Initialize cars array
$cars = [];

if (!empty($search_term)) {
    try {
        // Search in both regular cars and business vehicles
        $stmt = $conn->prepare("
            SELECT * FROM cars 
            WHERE (brand LIKE :search OR model LIKE :search OR category LIKE :search)
            ORDER BY type ASC, brand ASC
        ");
        
        // Use wildcards for partial matches
        $search_param = "%{$search_term}%";
        $stmt->bindParam(':search', $search_param);
        $stmt->execute();
        $cars = $stmt->fetchAll();
    } catch(PDOException $e) {
        // If there's an error, set empty array
        $cars = [];
    }
}
?>

<main>
    <div class="search-results">
        <h1>Zoekresultaten voor "<?= htmlspecialchars($search_term) ?>"</h1>
        
        <?php if (empty($search_term)): ?>
            <div class="message">
                <p>Voer een zoekterm in om auto's te vinden.</p>
            </div>
        <?php elseif (empty($cars)): ?>
            <div class="message">
                <p>Geen auto's gevonden die overeenkomen met "<?= htmlspecialchars($search_term) ?>".</p>
            </div>
        <?php else: ?>
            <p><?= count($cars) ?> resultaten gevonden</p>
            
            <div class="cars">
                <?php foreach ($cars as $car): ?>
                    <div class="car-details">
                        <div class="car-brand">
                            <h3><?= htmlspecialchars($car['brand']) ?></h3>
                            <div class="car-type">
                                <?= htmlspecialchars($car['category']) ?>
                            </div>
                        </div>
                        <img src="<?= htmlspecialchars($car['image_url']) ?>" alt="<?= htmlspecialchars($car['brand']) ?>">
                        <div class="car-specification">
                            <span><img src="assets/images/icons/gas-station.svg" alt=""><?= htmlspecialchars($car['fuel_capacity']) ?></span>
                            <span><img src="assets/images/icons/car.svg" alt=""><?= htmlspecialchars($car['transmission']) ?></span>
                            <span><img src="assets/images/icons/profile-2user.svg" alt=""><?= htmlspecialchars($car['capacity']) ?> Personen</span>
                        </div>
                        <div class="rent-details">
                            <span><span class="font-weight-bold">€<?= htmlspecialchars($car['price']) ?></span> / dag</span>
                            <?php if ($car['type'] === 'regular'): ?>
                                <a href="car-detail?id=<?= htmlspecialchars($car['id']) ?>" class="button-primary">Bekijk nu</a>
                            <?php else: ?>
                                <a href="bedrijfswagen-detail?id=<?= htmlspecialchars($car['id']) ?>" class="button-primary">Bekijk nu</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
.search-results {
    padding: 2rem;
}
.search-results h1 {
    margin-bottom: 1.5rem;
}
.message {
    background-color: #f8f9fa;
    padding: 2rem;
    border-radius: 8px;
    text-align: center;
    margin: 2rem 0;
}

/* Search button styles */
.search-button {
    background: none;
    border: none;
    cursor: pointer;
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
}
</style>

<?php require "includes/footer.php" ?> 