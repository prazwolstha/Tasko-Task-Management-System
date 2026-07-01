<?php
session_start();

// Database Connection
$host = "localhost";
$user = "root";
$password = "";
$database = "tasko";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

$message = "";

// Login Process
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $pass = $_POST['password'];
    $role = $_POST['role'];

    // USER LOGIN
    if ($role == "user") {

        $sql = "SELECT * FROM users WHERE email='$email' AND role='user'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {

            $row = mysqli_fetch_assoc($result);

            // If your user passwords are plain text
            if ($pass == $row['password']) {

                $_SESSION['user_name'] = $row['full_name'];
                $_SESSION['role'] = $row['role'];

                header("Location: ../Frontend/dashboard.php");
                exit();

            } else {
                $message = "Incorrect Password!";
            }

        } else {
            $message = "User Not Found!";
        }

    }

    // ADMIN LOGIN
    elseif ($role == "admin") {

        $sql = "SELECT * FROM admins WHERE email='$email' AND role='admin'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {

            $row = mysqli_fetch_assoc($result);

            if (password_verify($pass, $row['password'])) {

                $_SESSION['admin_name'] = $row['full_name'];
                $_SESSION['role'] = $row['role'];

                header("Location: ../Frontend/home.html");
                exit();

            } else {
                $message = "Incorrect Password!";
            }

        } else {
            $message = "Admin Not Found!";
        }

    } else {
        $message = "Please select a role.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page - Tasko</title>
     <link rel="stylesheet" href="style.css">
    <link rel="icon" type="png" href="../images/tasko.png">


    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family: Arial, sans-serif;
        }

        body{
            height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            
        }

        .container{
            width:400px;
            background:#fff;
            padding:40px;
            border-radius:15px;
            box-shadow:0 10px 25px rgba(0,0,0,0.3);
        }

        .container h2{
            text-align:center;
            margin-bottom:30px;
            color:#333;
        }

        .input-box{
            margin-bottom:20px;
        }

        .input-box label{
            display:block;
            margin-bottom:8px;
            font-weight:bold;
            color:#444;
        }

        .input-box input,
        .input-box select{
            width:100%;
            padding:12px;
            border:1px solid #ccc;
            border-radius:8px;
            outline:none;
            transition:0.3s;
            font-size:15px;
        }
        
        .input-box input:focus,
        .input-box select:focus{
            border-color:#2a5298;
            box-shadow:0 0 5px rgba(42,82,152,0.5);
        }

        .options{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:20px;
            font-size:14px;
        }

        .options a{
            text-decoration:none;
            color:#2a5298;
            font-weight:bold;
        }

        .options a:hover{
            text-decoration:underline;
        }

        .btn{
            width:100%;
            padding:12px;
            border:none;
            border-radius:8px;
            background:#000000;
            color:#fff;
            font-size:16px;
            cursor:pointer;
            transition:0.3s;
            margin-bottom:15px;
        }

        .btn:hover{
            background:#000000;
            font-size:17px;
            padding:14px;
        }

        .register{
            text-align:center;
            font-size:14px;
            margin-top:10px;
        }

        .register a{
            text-decoration:none;
            color:#2a5298;
            font-weight:bold;
        }

        .register a:hover{
            text-decoration:underline;
        }

        .back-btn{
            background:#555;
        }

        .back-btn:hover{
            background:#333;
        }

    </style>
</head>

<body>

    <div class="container">

        <h2>Login Page</h2>

        <form method="POST">

            <div class="input-box">
                <label>Enter Email</label>
                <input type="email" name="email" placeholder="example@gmail.com" required>
            </div>

            <div class="input-box">
                <label>Enter Password</label>
                <input type="password" name="password" placeholder="Enter Password" required>
            </div>

            <div class="input-box">
                <label>Select Role</label>

                <select name="role" required>
                    <option value="">-- Select Role --</option>
                    <option value="user">User Login</option>
                    <option value="admin">Admin Login</option>
                </select>
            </div>

            <div class="options">
                <a href="#">Forgot Password?</a>
            </div>

            <input type="submit" name="login" value="login" class="btn">

             <button type="button" class="btn back-btn">
                <a href="../Frontend/home.html" style="color:#fff; text-decoration: none;">Back</a>
            </button>

            <div class="register">
                Don't have an Account?
                <a href="register.html">Register here</a>
            </div>

        </form>

    </div>

</body>
</html>