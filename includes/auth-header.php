<?php session_start(); ?>
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
    <link rel="icon" type="image/png" href="assets/images/favicon.ico" sizes="32x32">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .home-button {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #fff;
            color: #3563e9;
            border: 2px solid #3563e9;
            border-radius: 30px;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .home-button:hover {
            background-color: #3563e9;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(53, 99, 233, 0.2);
        }
        
        body.dark-mode .home-button {
            background-color: #1e3148;
            color: #4d7cff;
            border: 2px solid #4d7cff;
        }
        
        body.dark-mode .home-button:hover {
            background-color: #3563e9;
            color: #e0e6f0;
        }
        
        @media (max-width: 768px) {
            .home-button {
                top: 15px;
                right: 15px;
                padding: 6px 12px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
<div class="topbar auth-topbar">
    <div class="logo">
        <a href="/">
            Rydr.
        </a>
    </div>
    <div class="dark-mode-toggle">
        <img src="assets/images/icons/moon.svg" alt="Dark Mode" class="toggle-icon moon-icon">
        <img src="assets/images/icons/sun.svg" alt="Light Mode" class="toggle-icon sun-icon">
    </div>
    <a href="/" class="home-button">
        <i class="fas fa-home"></i> Terug naar homepage
    </a>
</div>
<div class="content"> 