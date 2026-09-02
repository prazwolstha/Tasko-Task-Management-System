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

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $pass  = trim($_POST['password']);

    // Check Admin
    $sql = "SELECT * FROM admins WHERE email='$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {

        $admin = mysqli_fetch_assoc($result);

        // Plain text password
        if ($pass == $admin['password']) {

            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['full_name'];

            header("Location: ../Frontend/dashboard.php");
            exit();

        } else {
            $message = "Incorrect Email or Password!";
        }

    } else {
        $message = "Incorrect Email or Password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - TasKo</title>

    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="../images/tasko.png">

    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:Arial, sans-serif;
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

        .input-box input{
            width:100%;
            padding:12px;
            border:1px solid #ccc;
            border-radius:8px;
            outline:none;
            transition:.3s;
            font-size:15px;
        }

        .input-box input:focus{
            border-color:#2a5298;
            box-shadow:0 0 5px rgba(42,82,152,.5);
        }

        .options{
            display:flex;
            justify-content:flex-end;
            margin-bottom:20px;
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
            background:#000;
            color:#fff;
            font-size:16px;
            cursor:pointer;
            transition:.3s;
            margin-bottom:15px;
        }

        .btn:hover{
            font-size:17px;
            padding:14px;
        }

        .back-btn{
            background:#555;
        }

        .back-btn:hover{
            background:#333;
        }

        .message{
            color:red;
            text-align:center;
            margin-bottom:15px;
            font-weight:bold;
        }

        .error-message{
    color: red;
    font-size: 14px;
    margin-top: 5px;
}
    </style>
</head>

<body>

<div class="container">

    <h2>Admin Login</h2>

    <form method="POST">

        <div class="input-box">
            <label>Email</label>
            <input type="email" name="email" placeholder="example@gmail.com" required>
        </div>

        <div class="input-box">
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter Password" required>
        </div>

        <?php if (!empty($message)) : ?>
        <p class="error-message"><?php echo $message; ?></p>
    <?php endif; ?>

        <div class="options">
            <a href="forgot_password.php?type=admin">Forgot Password?</a>
        </div>

        <input type="submit" name="login" value="Login" class="btn">

        <button type="button" class="btn back-btn"
            onclick="window.location.href='../Frontend/home.html'">
            Back
        </button>

    </form>

</div>

</body>
</html>