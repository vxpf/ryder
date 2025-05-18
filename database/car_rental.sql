CREATE DATABASE IF NOT EXISTS rental;
USE rental;

-- Create cars table
CREATE TABLE IF NOT EXISTS cars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brand VARCHAR(100) NOT NULL,
    model VARCHAR(100),
    type ENUM('regular', 'business') NOT NULL,
    category VARCHAR(50) NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    description TEXT,
    capacity VARCHAR(50) NOT NULL,
    transmission VARCHAR(50) NOT NULL,
    fuel_capacity VARCHAR(20) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    original_price DECIMAL(10,2),
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert regular cars based on Figma design
INSERT INTO cars (brand, model, type, category, image_url, description, capacity, transmission, fuel_capacity, price, original_price) VALUES
('Koenigsegg', '', 'regular', 'Sport', 'assets/images/products/car (0).svg', 'De Koenigsegg is een Zweedse hypercar met adembenemende prestaties. Met zijn lichtgewicht constructie en krachtige motor biedt deze auto een ongeëvenaarde rijervaring voor liefhebbers van pure snelheid.', '2 People', 'Manual', '90l', 99.00, NULL),
('Nissan GT - R', '', 'regular', 'Sport', 'assets/images/products/car (1).svg', 'De Nissan GT-R, ook bekend als "Godzilla", is een technologisch wonder met een handgemaakte twin-turbo V6. De AWD-configuratie en geavanceerde elektronica maken het een van de snelste productieauto\'s op het circuit.', '2 People', 'Manual', '70l', 80.00, 100.00),
('Rolls - Royce', '', 'regular', 'Sedan', 'assets/images/products/car (2).svg', 'De Rolls-Royce staat synoniem voor ultieme luxe en verfijning. Met zijn handgemaakte interieur en fluisterstille motor biedt deze auto een ongeëvenaarde comfortabele rijervaring voor de meest veeleisende klanten.', '4 People', 'Manual', '80l', 96.00, NULL),
('Nissan GT - R', '', 'regular', 'Sport', 'assets/images/products/car (3).svg', 'De Nissan GT-R combineert Japanse technologie met indrukwekkende prestaties. Deze iconische sportwagen biedt superieure handling en snelheid, met een twin-turbo V6 motor die pure kracht levert in alle omstandigheden.', '2 People', 'Manual', '70l', 80.00, 100.00),
('All New Rush', '', 'regular', 'SUV', 'assets/images/products/car (4).svg', 'De All New Rush is een veelzijdige SUV die ruimte en comfort combineert met betrouwbaarheid. Perfect voor zowel stedelijke ritten als avontuurlijke uitstapjes, met voldoende ruimte voor het hele gezin.', '6 People', 'Manual', '70l', 72.00, 80.00),
('CR - V', '', 'regular', 'SUV', 'assets/images/products/car (5).svg', 'De CR-V is een premium SUV met uitstekende rijeigenschappen en een ruim interieur. Deze veelzijdige auto biedt een comfortabele en veilige rijervaring voor het hele gezin, met de nieuwste technologische snufjes.', '6 People', 'Manual', '80l', 80.00, NULL),
('All New Terios', '', 'regular', 'SUV', 'assets/images/products/car (6).svg', 'De All New Terios is een compacte SUV die wendbaarheid combineert met avontuurlijke capaciteiten. Met zijn zuinige motor en verhoogde bodemvrijheid is deze auto perfect voor zowel stadsritten als uitstapjes buiten de gebaande paden.', '6 People', 'Manual', '70l', 74.00, NULL),
('CR - V', '', 'regular', 'SUV', 'assets/images/products/car (7).svg', 'De CR-V is bekend om zijn betrouwbaarheid en veelzijdigheid. Met een ruim interieur, zuinige motor en uitstekende veiligheidsvoorzieningen is dit een ideale gezinswagen voor dagelijks gebruik en langere reizen.', '6 People', 'Manual', '80l', 80.00, NULL),
('MG ZX Exclusice', '', 'regular', 'Hatchback', 'assets/images/products/car (8).svg', 'De MG ZX Exclusice is een stijlvolle hatchback met een modern design en uitstekende prestaties. Deze auto biedt een perfecte balans tussen comfort, stijl en rijplezier voor de moderne bestuurder.', '4 People', 'Manual', '70l', 76.00, NULL),
('New MG ZS', '', 'regular', 'SUV', 'assets/images/products/car (9).svg', 'De New MG ZS is een compacte SUV die stijl en betaalbaarheid combineert. Met zijn ruime interieur, moderne technologie en efficiënte motor biedt deze auto uitstekende waarde voor gezinnen en avonturiers.', '6 People', 'Manual', '80l', 80.00, NULL),
('MG ZX Excite', '', 'regular', 'Hatchback', 'assets/images/products/car (10).svg', 'De MG ZX Excite biedt een opwindende rijervaring in een compacte en stijlvolle hatchback. Met sportieve handling, moderne technologie en een efficiënte motor is dit de perfecte auto voor de enthousiaste bestuurder.', '4 People', 'Manual', '70l', 74.00, NULL),
('New MG ZS', '', 'regular', 'SUV', 'assets/images/products/car (11).svg', 'De New MG ZS is een moderne SUV met een stijlvol design en uitstekende prestaties. Met zijn ruime interieur, geavanceerde veiligheidsvoorzieningen en zuinige motor biedt deze auto een comfortabele rijervaring voor het hele gezin.', '6 People', 'Manual', '80l', 80.00, NULL);

-- Insert sample business vehicles
INSERT INTO cars (brand, model, type, category, image_url, description, capacity, transmission, fuel_capacity, price) VALUES
('Citroën', 'Berlingo', 'business', 'Transport', 'assets/images/citroene.avif', 'De Citroën Berlingo combineert functionaliteit met comfort. Deze compacte bedrijfswagen biedt een verrassend ruim laadvolume van 3,3m³ en heeft innovatieve schuifdeuren aan beide zijden voor gemakkelijke toegang. Perfect voor stedelijk gebruik.', '3 Personen', 'Schakel', '50l', 89.00),
('Volkswagen', 'Transporter T6', 'business', 'Transport', 'assets/images/bedrijfswagens/bedrijfswagen1.jpg', 'De Volkswagen Transporter T6 is een van de meest betrouwbare bedrijfswagens op de markt. Met een laadvolume tot 9,3m³ en de nieuwste veiligheidstechnologie biedt het de perfecte balans tussen capaciteit en rijcomfort.', '3 Personen', 'Schakel', '70l', 94.00),
('Mercedes-Benz', 'Sprinter', 'business', 'Transport', 'assets/images/bedrijfswagens/bedrijfswagen2.jpg', 'De Mercedes-Benz Sprinter staat voor premium kwaliteit in het bedrijfswagensegment. Met zijn ruime interieur, connectiviteitsdiensten en assistentiesystemen levert deze grote bestelwagen topprestaties voor veeleisende professionals.', '3 Personen', 'Automaat', '75l', 109.00),
('Ford', 'Transit Custom', 'business', 'Transport', 'assets/images/bedrijfswagens/bedrijfswagen3.jpg', 'De Ford Transit Custom biedt een laadvermogen tot 1.450 kg en slimme opbergoplossingen. Deze middelgrote bestelwagen is uitgerust met de nieuwste Ford EcoBlue diesel-motoren voor optimale brandstofefficiëntie.', '3 Personen', 'Schakel', '70l', 92.00),
('Renault', 'Trafic', 'business', 'Transport', 'assets/images/bedrijfswagens/bedrijfswagen4.jpg', 'De Renault Trafic is ontworpen met de moderne ondernemer in gedachten. Met een laadruimte die tot 8,6m³ kan bereiken en innovatieve oplossingen zoals het doorlaadluik onder de passagiersstoel, is veelzijdigheid gegarandeerd.', '3 Personen', 'Schakel', '80l', 89.00),
('Opel', 'Vivaro', 'business', 'Transport', 'assets/images/bedrijfswagens/bedrijfswagen5.jpg', 'De Opel Vivaro is een bedrijfswagen met veel comfort en functionaliteit. Met een payload tot 1.400 kg en innovatieve FlexCargo-functie kan je lange voorwerpen gemakkelijk vervoeren. De zuinige motoren houden de operationele kosten laag.', '3 Personen', 'Schakel', '70l', 88.00),
('Peugeot', 'Expert', 'business', 'Transport', 'assets/images/bedrijfswagens/bedrijfswagen6.jpg', 'De Peugeot Expert biedt de wendbaarheid van een personenauto met de capaciteit van een bedrijfswagen. Met een hoogte van minder dan 1,90 m heeft u toegang tot de meeste parkeergarages, terwijl u nog steeds een laadvolume tot 6,6m³ heeft.', '3 Personen', 'Schakel', '69l', 90.00),
('Fiat', 'Ducato', 'business', 'Transport', 'assets/images/bedrijfswagens/bedrijfswagen7.jpg', 'De Fiat Ducato is een grote bedrijfswagen met een laadvolume tot 17m³ en een payload tot 2.200 kg. De MultiJet-motoren bieden een uitstekende combinatie van kracht en zuinigheid voor zwaar transport.', '3 Personen', 'Schakel', '90l', 99.00),
('Iveco', 'Daily', 'business', 'Transport', 'assets/images/bedrijfswagens/bedrijfswagen8.jpg', 'De Iveco Daily onderscheidt zich door zijn robuuste constructie en veelzijdigheid. Met een laadvolume tot 19,6m³ en een trekvermogen tot 3.500 kg is geen enkele taak te groot. De geavanceerde connectiviteitsfuncties maken wagenparkbeheer eenvoudig.', '3 Personen', 'Schakel', '100l', 105.00);

-- Create account table
CREATE TABLE IF NOT EXISTS account (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    profile_photo VARCHAR(255) DEFAULT 'assets/images/default-profile.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create favorites table
CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    car_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY user_car_unique (user_id, car_id),
    FOREIGN KEY (user_id) REFERENCES account(id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
);

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert admin user
INSERT INTO users (first_name, last_name, email, password, is_admin) VALUES
('Admin', 'User', 'admin@carrental.com', '$2y$10$7CQRQbALYHkzQV0oqtB8JeIm/dKMST5wAKXPp/bn.7TbHQTn6B6ry', TRUE); -- password: admin123

-- Create bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    car_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (car_id) REFERENCES cars(id)
);

-- Add an index for faster queries
CREATE INDEX idx_car_type ON cars(type); 