<?php
session_start();

// Disable browser cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

// Check login
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TasKo</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <style>
        #logout-btn{
            text-decoration: none;
            border: 1px solid black;
            border-radius: 6px;
            background-color: black;
            color: white;
            padding: 8px 10px;
            font-size: 15px;
            font-weight: bold;
            box-shadow: 0px 3px 10px 1px rgb(219, 219, 219);
        }

        #logout-btn:hover{
            background-color:white;
            color: black;
            font-size: 15px;
            padding: 8px 10px;
            transition: 0.5s;
            box-shadow: 0px 3px 10px 1px rgb(206, 204, 204);
        }
        .nav-center li a:hover{
            font-size: 18px;
            font-weight: medium;
            transition: 0.5s;
            color: blue;
        }
    </style>
</head> 
<body>

    <nav class="navbar">
        <div class="nav-left">
            <img src="../images/tasko.png" alt="Logo" class="logo">
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
            <span class="user-name">
                <b>Admin:</b> <?php echo htmlspecialchars($_SESSION['admin_name']); ?>
            </span> 
            <button id="logout-btn" onclick="window.location.href='../auth/logout.php'"><i class="fa-solid fa-right-from-bracket" style="margin-right: 8px;"></i>Logout</button>
        </div>
    </nav>

    <div style="margin-top: 80px; color:black; ">
            <li><a href="dashboard.php"><i class="fa-solid fa-house" style="height:12px; width:12px; margin-right: 10px;"></i>Dashboard</a></li>
            <li><a href="#"><i class="fa-solid fa-user-plus" style="height:12px; width:12px; margin-right: 10px;"></i>Add User</a></li>
            <li><a href="#"><i class="fa-solid fa-users" style="height:12px; width:12px; margin-right: 10px;"></i>Manage Users</a></li>
            <li><a href="#"><i class="fa-solid fa-tasks" style="height:12px; width:12px; margin-right: 10px;"></i>Assign Tasks</a></li>
            <li><a href="#"><i class="fa-solid fa-list" style="height:12px; width:12px; margin-right: 10px;"></i>Manage Tasks</a></li>
            <li><a href="#"><i class="fa-solid fa-calendar-alt" style="height:12px; width:12px; margin-right: 10px;"></i>Leave Requests</a></li>
    </div>

</body>
</html>
