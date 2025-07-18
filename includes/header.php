<?php 
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    
    // Check if there's a "remember me" cookie but no active session
    // This would be where you'd check a remember_me cookie if that feature exists
    
    // If no explicit login action was performed, clear the session variables
    // This prevents automatic login when just opening the webpage
    if (!isset($_SESSION['login_verified'])) {
        // Unset all session variables related to user authentication
        unset($_SESSION['id']);
        unset($_SESSION['email']);
        unset($_SESSION['profile_photo']);
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_email']);
        unset($_SESSION['admin_name']);
        unset($_SESSION['admin_table']);
    }
} 
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="ISO-8859-1">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Rydr</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/dark-mode.css">
    <link rel="icon" type="image/png" href="assets/images/Ricon.png" sizes="32x32">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
</head>
<body>
<div class="topbar">
    <div class="logo">
        <a href="/">
            Rydr.
        </a>
    </div>
    <form action="search" method="GET">
        <input type="search" name="query" id="search-input" placeholder="Welke auto wilt u huren?">
        <button type="submit" class="search-button">
            <img src="assets/images/icons/search-normal.svg" alt="" class="search-icon">
        </button>
    </form>
    <nav>
        <ul>
            <li><a href="/">Home</a></li>
            <li><a href="/ons-aanbod">Ons aanbod</a></li>
            <li><a href="/hulp-nodig">Hulp nodig?</a></li>
        </ul>
    </nav>
    <div class="menu">
        <div class="dark-mode-toggle">
            <img src="assets/images/icons/moon.svg" alt="Dark Mode" class="toggle-icon moon-icon">
            <img src="assets/images/icons/sun.svg" alt="Light Mode" class="toggle-icon sun-icon">
        </div>
        <?php if(isset($_SESSION['id'])){ ?>
        <div class="account">
            <img src="<?= isset($_SESSION['profile_photo']) ? $_SESSION['profile_photo'] : 'assets/images/profil.png' ?>" alt="Profielfoto" class="profile-img">
            <div class="account-dropdown">
                <ul>
                    <li><img src="assets/images/icons/setting.svg" alt=""><a href="/profile">Mijn Profiel</a></li>
                    <li><img src="assets/images/icons/heart.svg" alt=""><a href="/mijn-favorieten">Mijn Favorieten</a></li>
                    <li><img src="assets/images/icons/calendar.svg" alt=""><a href="/my-bookings">Mijn Reserveringen</a></li>
                    <li><img src="assets/images/icons/logout.svg" alt=""><a href="/logout">Uitloggen</a></li>
                </ul>
            </div>
        </div>
        <?php }else{ ?>
            <div class="auth-buttons">
                <a href="/login-form" class="button-secondary">Inloggen</a>
                <a href="/register-form" class="button-primary">Registreren</a>
            </div>
        <?php } ?>

    </div>
</div>
<div class="content">
