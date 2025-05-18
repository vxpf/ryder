<?php
// Connect to the database
$host = "localhost";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $conn->exec("CREATE DATABASE IF NOT EXISTS rental");
    $conn->exec("USE rental");
    
    // Create account table
    $sql = "CREATE TABLE IF NOT EXISTS account (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $conn->exec($sql);
    echo "Account table created successfully!";
    
    // Alter account table to add new fields
    $sql = "ALTER TABLE account 
            ADD COLUMN IF NOT EXISTS first_name VARCHAR(100) AFTER password,
            ADD COLUMN IF NOT EXISTS last_name VARCHAR(100) AFTER first_name,
            ADD COLUMN IF NOT EXISTS profile_photo VARCHAR(255) DEFAULT 'assets/images/default-profile.png' AFTER last_name";
    
    $conn->exec($sql);
    echo "Account table updated successfully!<br>";
    
    // Create favorites table
    $sql = "CREATE TABLE IF NOT EXISTS favorites (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        car_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY user_car_unique (user_id, car_id),
        FOREIGN KEY (user_id) REFERENCES account(id) ON DELETE CASCADE,
        FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
    )";
    
    $conn->exec($sql);
    echo "Favorites table created successfully!";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 