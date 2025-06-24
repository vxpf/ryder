<?php
session_start();
require_once '../database/connection.php';

if (!isset($_SESSION['id'])) {
    header('Location: /pages/login-form.php');
    exit;
}

$user_id = $_SESSION['id'];
$car_id = isset($_POST['car_id']) ? intval($_POST['car_id']) : 0;
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
$comment = trim($_POST['comment'] ?? '');

if ($car_id && $rating >= 1 && $rating <= 5 && $comment !== '') {
    try {
        $stmt = $conn->prepare('INSERT INTO reviews (car_id, user_id, rating, comment) VALUES (:car_id, :user_id, :rating, :comment)');
        $stmt->bindParam(':car_id', $car_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':rating', $rating);
        $stmt->bindParam(':comment', $comment);
        $stmt->execute();
    } catch (Exception $e) {
        // Optionally log error
    }
}
header('Location: /pages/car-detail.php?id=' . urlencode($car_id));
exit; 