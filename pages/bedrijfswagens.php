<?php require "includes/header.php" ?>
    <header>
        <div class="advertorials">
            <div class="advertorial">
                <h2>Verhuur van bedrijfswagens</h2>
                <p>Snel en eenvoudig een bedrijfswagen huren voor een lage prijs.</p>
                <a href="" class="button-primary">Huur nu een bedrijfswagen</a>
                <img src="assets/images/bedrijfswagen1.png" alt="">
                <img src="assets/images/header-circle-background.svg" alt="" class="background-header-element">
            </div>
            <div class="advertorial">
                <h2>Voor zakelijk en particulier gebruik</h2>
                <p>Voor een vaste lage prijs met prettig voordelen.</p>
                <a href="#" class="button-primary">Bekijk alle bedrijfswagens</a>
                <img src="assets/images/bedrijfswagen1.png" alt="" class="bedrijfwagen" size="200px">
                <img src="assets/images/header-block-background.svg" alt="" class="background-header-element">
            </div>
        </div>
    </header>

    <main>
    <h2 class="section-title">Populaire bedrijfswagens</h2>
    <div class="cars">
        <?php for ($i = 1; $i <= 4; $i++) : ?>
            <div class="car-details">
                <div class="car-brand">
                    <h3>Bedrijfswagen</h3>
                    <div class="car-type">
                        Transport
                    </div>
                </div>
                <img src="assets/images/bedrijfswagens/bedrijfswagen<?= $i ?>.jpg" alt="">
                <div class="car-specification">
                    <span><img src="assets/images/icons/gas-station.svg" alt="">80l</span>
                    <span><img src="assets/images/icons/car.svg" alt="">Schakel</span>
                    <span><img src="assets/images/icons/profile-2user.svg" alt="">3 Personen</span>
                </div>
                <div class="rent-details">
                    <span><span class="font-weight-bold">€89,00</span> / dag</span>
                    <a href="bedrijfswagen-detail.php" class="button-primary">Bekijk nu</a>
                </div>
            </div>
        <?php endfor; ?>
    </div>
    <h2 class="section-title">Alle bedrijfswagens</h2>
    <div class="cars" id="recommended-cars">
        <?php for ($i = 5; $i <= 8; $i++) : ?>
            <div class="car-details">
                <div class="car-brand">
                    <h3>Bedrijfswagen</h3>
                    <div class="car-type">
                        Transport
                    </div>
                </div>
                <img src="assets/images/bedrijfswagens/bedrijfswagen<?= $i ?>.jpg" alt="">
                <div class="car-specification">
                    <span><img src="assets/images/icons/gas-station.svg" alt="">80l</span>
                    <span><img src="assets/images/icons/car.svg" alt="">Schakel</span>
                    <span><img src="assets/images/icons/profile-2user.svg" alt="">3 Personen</span>
                </div>
                <div class="rent-details">
                    <span><span class="font-weight-bold">€89,00</span> / dag</span>
                    <a href="bedrijfswagen-detail.php" class="button-primary">Bekijk nu</a>
                </div>
            </div>
        <?php endfor; ?>
        
        <div class="hidden-cars" style="display: none;">
        <?php for ($i = 9; $i <= 12; $i++) : ?>
            <div class="car-details">
                <div class="car-brand">
                    <h3>Bedrijfswagen</h3>
                    <div class="car-type">
                        Transport
                    </div>
                </div>
                <img src="assets/images/bedrijfswagens/bedrijfswagen<?= $i ?>.jpg" alt="">
                <div class="car-specification">
                    <span><img src="assets/images/icons/gas-station.svg" alt="">80l</span>
                    <span><img src="assets/images/icons/car.svg" alt="">Schakel</span>
                    <span><img src="assets/images/icons/profile-2user.svg" alt="">3 Personen</span>
                </div>
                <div class="rent-details">
                    <span><span class="font-weight-bold">€89,00</span> / dag</span>
                    <a href="bedrijfswagen-detail.php" class="button-primary">Bekijk nu</a>
                </div>
            </div>
        <?php endfor; ?>
        </div>
    </div>
    <div class="show-more">
        <a class="button-primary" href="#" id="toggle-button">Toon alle</a>
    </div>
    
    <script src="assets/javascript/showMore.js"></script>
    </main>

<?php require "includes/footer.php" ?> 