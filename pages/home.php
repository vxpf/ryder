<?php require "includes/header.php" ?>
<?php require "includes/db_connect.php"; ?>

<?php
// Fetch regular cars
try {
    $stmt = $conn->prepare("SELECT * FROM cars WHERE type = 'regular' LIMIT 8");
    $stmt->execute();
    $cars = $stmt->fetchAll();
} catch(PDOException $e) {
    // If there's an error or the table doesn't exist yet, use fallback data
    $cars = [];
    // Fallback will use the static data from the loops below
}

// Fetch business vehicles
try {
    $stmt = $conn->prepare("SELECT * FROM cars WHERE type = 'business' LIMIT 8");
    $stmt->execute();
    $business_vehicles = $stmt->fetchAll();
} catch(PDOException $e) {
    // If there's an error or the table doesn't exist yet, use fallback data
    $business_vehicles = [];
    // Fallback will use the static data from the loops below
}

// Check if we have data from the database
$has_car_data = !empty($cars);
$has_business_data = !empty($business_vehicles);
?>

    <header>
        <div class="advertorials">
            <div class="advertorial">
                <h2>Hét platform om een auto te huren</h2>
                <p>Snel en eenvoudig een auto huren. Natuurlijk voor een lage prijs.</p>
                <a href="ons-aanbod" class="button-primary">Huur nu een auto</a>
                <img src="assets/images/car-rent-header-image-1.png" alt="">
                <img src="assets/images/header-circle-background.svg" alt="" class="background-header-element">
            </div>
            <div class="advertorial">
                <h2>Wij verhuren ook bedrijfswagens</h2>
                <p>Voor een vaste lage prijs met prettig voordelen.</p>
                <a href="ons-aanbod" class="button-primary">Huur een bedrijfswagen</a>
                <img src="../assets/images/business/bedrijfswagen2.webp" alt="Volkswagen Busje" class="bedrijfwagen" style="height: 300px; width: auto; object-fit: contain;">
                <img src="assets/images/header-block-background.svg" alt="" class="background-header-element">
            </div>
        </div>
    </header>

    <main>
        <h2 class="section-title">Populaire auto's</h2>
        <div class="cars">
            <?php 
            if ($has_car_data) {
                // Display first 4 cars from database
                for ($i = 0; $i < 4 && $i < count($cars); $i++) {
                    $car = $cars[$i];
            ?>
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
                        <a href="car-detail?id=<?= htmlspecialchars($car['id']) ?>" class="button-primary">Bekijk nu</a>
                    </div>
                </div>
            <?php 
                }
            } else {
                // Fallback to static data if no database data
                for ($i = 0; $i <= 3; $i++) : 
            ?>
                <div class="car-details">
                    <div class="car-brand">
                        <h3>Koenigsegg Agera</h3>
                        <div class="car-type">
                            Sport
                        </div>
                    </div>
                    <img src="assets/images/products/car%20(<?= $i ?>).svg" alt="">
                    <div class="car-specification">
                        <span><img src="assets/images/icons/gas-station.svg" alt="">90l</span>
                        <span><img src="assets/images/icons/car.svg" alt="">Schakel</span>
                        <span><img src="assets/images/icons/profile-2user.svg" alt="">2 Personen</span>
                    </div>
                    <div class="rent-details">
                        <span><span class="font-weight-bold">€249,00</span> / dag</span>
                        <a href="car-detail?id=<?= $i ?>" class="button-primary">Bekijk nu</a>
                    </div>
                </div>
            <?php 
                endfor; 
            }
            ?>
        </div>

        <h2 class="section-title">Populaire bedrijfswagens</h2>
        <div class="cars">
            <?php 
            if ($has_business_data) {
                // Display first 4 business vehicles from database
                for ($i = 0; $i < 4 && $i < count($business_vehicles); $i++) {
                    $car = $business_vehicles[$i];
            ?>
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
                        <a href="bedrijfswagen-detail?id=<?= htmlspecialchars($car['id']) ?>" class="button-primary">Bekijk nu</a>
                    </div>
                </div>
            <?php 
                }
            }
            ?>
        </div>
        
        <h2 class="section-title">Meer voertuigen</h2>
        <div class="cars" id="recommended-cars">
            <?php 
            if ($has_car_data) {
                // Display cars 5-8 from database
                for ($i = 4; $i < 8 && $i < count($cars); $i++) {
                    $car = $cars[$i];
            ?>
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
                        <a href="car-detail?id=<?= htmlspecialchars($car['id']) ?>" class="button-primary">Bekijk nu</a>
                    </div>
                </div>
            <?php 
                }
            } else {
                // Fallback to static data if no database data
                for ($i = 4; $i <= 7; $i++) : 
            ?>
                <div class="car-details">
                    <div class="car-brand">
                        <h3>Koenigegg</h3>
                        <div class="car-type">
                            Sport
                        </div>
                    </div>
                    <img src="assets/images/products/car%20(<?= $i ?>).svg" alt="">
                    <div class="car-specification">
                        <span><img src="assets/images/icons/gas-station.svg" alt="">90l</span>
                        <span><img src="assets/images/icons/car.svg" alt="">Schakel</span>
                        <span><img src="assets/images/icons/profile-2user.svg" alt="">2 Personen</span>
                    </div>
                    <div class="rent-details">
                        <span><span class="font-weight-bold">€249,00</span> / dag</span>
                        <a href="car-detail?id=<?= $i ?>" class="button-primary">Bekijk nu</a>
                    </div>
                </div>
            <?php 
                endfor; 
            }
            ?>
        </div>
        
        <div id="hidden-cars" style="display: none;">
            <h2 class="section-title">Meer auto's</h2>
            <div class="cars">
                <?php 
                if ($has_car_data && count($cars) > 8) {
                    // If we have more than 8 cars from database, show them
                    for ($i = 8; $i < count($cars); $i++) {
                        $car = $cars[$i];
                ?>
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
                            <a href="car-detail?id=<?= htmlspecialchars($car['id']) ?>" class="button-primary">Bekijk nu</a>
                        </div>
                    </div>
                <?php 
                    }
                } else {
                    // Fallback to static data
                    for ($i = 8; $i <= 11; $i++) : 
                ?>
                    <div class="car-details">
                        <div class="car-brand">
                            <h3>Koenigegg</h3>
                            <div class="car-type">
                                Sport
                            </div>
                        </div>
                        <img src="assets/images/products/car%20(<?= $i ?>).svg" alt="">
                        <div class="car-specification">
                            <span><img src="assets/images/icons/gas-station.svg" alt="">90l</span>
                            <span><img src="assets/images/icons/car.svg" alt="">Schakel</span>
                            <span><img src="assets/images/icons/profile-2user.svg" alt="">2 Personen</span>
                        </div>
                        <div class="rent-details">
                            <span><span class="font-weight-bold">€249,00</span> / dag</span>
                            <a href="car-detail?id=<?= $i ?>" class="button-primary">Bekijk nu</a>
                        </div>
                    </div>
                <?php 
                    endfor;
                }
                ?>
            </div>
            
            <h2 class="section-title">Meer bedrijfswagens</h2>
            <div class="cars">
                <?php 
                if ($has_business_data && count($business_vehicles) > 4) {
                    // If we have more than 4 business vehicles, show the rest
                    for ($i = 4; $i < count($business_vehicles); $i++) {
                        $vehicle = $business_vehicles[$i];
                ?>
                    <div class="car-details">
                        <div class="car-brand">
                            <h3><?= htmlspecialchars($vehicle['brand']) ?></h3>
                            <div class="car-type">
                                <?= htmlspecialchars($vehicle['category']) ?>
                            </div>
                        </div>
                        <img src="assets/images/business/<?= htmlspecialchars($vehicle['brand']) ?>.webp" alt="<?= htmlspecialchars($vehicle['brand']) ?>">
                        <div class="car-specification">
                            <span><img src="assets/images/icons/gas-station.svg" alt=""><?= htmlspecialchars($vehicle['fuel_capacity']) ?></span>
                            <span><img src="assets/images/icons/car.svg" alt=""><?= htmlspecialchars($vehicle['transmission']) ?></span>
                            <span><img src="assets/images/icons/profile-2user.svg" alt=""><?= htmlspecialchars($vehicle['capacity']) ?> Personen</span>
                        </div>
                        <div class="rent-details">
                            <span><span class="font-weight-bold">€<?= htmlspecialchars($vehicle['price']) ?></span> / dag</span>
                            <a href="bedrijfswagen-detail?id=<?= htmlspecialchars($vehicle['id']) ?>" class="button-primary">Bekijk nu</a>
                        </div>
                    </div>
                <?php 
                    }
                } else {
                    // Fallback to static data
                    for ($i = 5; $i <= 8; $i++) : 
                ?>
                    <div class="car-details">
                        <div class="car-brand">
                            <h3><?php if($i === 5): ?>Citroën<?php else: ?>Bedrijfswagen<?php endif; ?></h3>
                            <div class="car-type">
                                Transport
                            </div>
                        </div>
                        <img src="assets/images/<?php if($i === 5): ?>bedrijfswagen3.png<?php else: ?>bedrijfswagen2.png<?php endif; ?>" alt="<?php if($i === 5): ?>Citroën Bestelwagen<?php else: ?>Bedrijfswagen<?php endif; ?>" style="height: 120px; object-fit: contain;">
                        <div class="car-specification">
                            <span><img src="assets/images/icons/gas-station.svg" alt="">80l</span>
                            <span><img src="assets/images/icons/car.svg" alt="">Schakel</span>
                            <span><img src="assets/images/icons/profile-2user.svg" alt="">3 Personen</span>
                        </div>
                        <div class="rent-details">
                            <span><span class="font-weight-bold">€89,00</span> / dag</span>
                            <a href="bedrijfswagen-detail?id=<?= $i === 5 ? 'citroen' : $i ?>" class="button-primary">Bekijk nu</a>
                        </div>
                    </div>
                <?php 
                    endfor;
                }
                ?>
            </div>
        </div>
        
        <div class="show-more">
            <a class="button-primary" href="#" id="toggle-button">Toon alle</a>
        </div>
        
        <script src="assets/javascript/showMore.js"></script>
    </main>

<?php require "includes/footer.php" ?>