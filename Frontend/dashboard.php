<?php 
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head> 
<body>
    <nav class="navbar">
        <div class="nav-left">
            <img src="tasko.png" alt="Lfogo" class="logo">
            <h3 class="website-name">TasKo</h3>
        </div>

        <ul class="nav-center">
            <li><a href="home.html">Home</a></li>
            <li><a href="#">Reviews</a></li>
            <li><a href="#">Services</a></li>
            <li><a href="#">Support</a></li>
            <li><a href="#">About us</a></li>
        </ul>    
        <div class="user-info">
    <span class="username">
        <?= htmlspecialchars($_SESSION['full_name'] ?? '') ?>
    </span>

    <span class="email">
        <?= htmlspecialchars($_SESSION['email'] ?? '') ?>
    </span>
</div>
        
    </nav>
</body>
</html>
