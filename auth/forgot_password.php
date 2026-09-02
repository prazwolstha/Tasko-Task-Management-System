<?php

session_start();

$conn = mysqli_connect("localhost", "root", "", "tasko");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

/*
|--------------------------------------------------------------------------
| Check user type
|--------------------------------------------------------------------------
*/

if (!isset($_GET['type'])) {
    die("Invalid request.");
}

$type = $_GET['type'];

if ($type === "admin") {

    $table = "admins";
    $loginPage = "login.php";
    $title = "Admin Forgot Password";

} elseif ($type === "user") {

    $table = "users";
    $loginPage = "user_login.php";
    $title = "User Forgot Password";

} else {

    die("Invalid user type.");
}

$message = "";
$emailStatus = "";
$email = "";


/*
|--------------------------------------------------------------------------
| Check Email
|--------------------------------------------------------------------------
*/

if (isset($_POST['checkEmail'])) {

    $email = trim($_POST['email']);

    if ($email === "") {

        $message = "Please enter your email address.";
        $emailStatus = "invalid";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email format.";
        $emailStatus = "invalid";

    } else {

        $emailSafe = mysqli_real_escape_string($conn, $email);

        $query = "SELECT * FROM $table WHERE email='$emailSafe' LIMIT 1";

        $result = mysqli_query($conn, $query);

        if (!$result) {

            die("Database error: " . mysqli_error($conn));

        }

        if (mysqli_num_rows($result) == 1) {

            $emailStatus = "valid";
            $message = "Valid email ✓";

        } else {

            $emailStatus = "invalid";
            $message = "Invalid email ✕";

        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?php echo htmlspecialchars($title); ?></title>

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial,sans-serif;
}

body{

    min-height:100vh;

    display:flex;

    justify-content:center;

    align-items:center;

    background:#eef3fb;

}

.forgot-card{

    width:420px;

    background:white;

    padding:40px;

    border-radius:18px;

    box-shadow:0 10px 30px rgba(0,0,0,.12);

}

.icon{

    width:70px;

    height:70px;

    margin:0 auto 20px;

    background:#2563eb;

    border-radius:50%;

    display:flex;

    justify-content:center;

    align-items:center;

}

.icon i{

    color:white;

    font-size:28px;

}

h2{

    text-align:center;

    color:#1f2937;

    margin-bottom:10px;

}

.description{

    text-align:center;

    color:#6b7280;

    margin-bottom:25px;

    line-height:1.5;

}

.input-box{

    margin-bottom:15px;

}

.input-box label{

    display:block;

    margin-bottom:8px;

    font-weight:bold;

    color:#374151;

}

.input-box input{

    width:100%;

    padding:14px;

    border:1px solid #d1d5db;

    border-radius:10px;

    outline:none;

    font-size:15px;

}

.input-box input:focus{

    border-color:#2563eb;

}

.message{

    padding:12px;

    border-radius:8px;

    margin-bottom:15px;

    text-align:center;

    font-weight:bold;

}

.valid{

    background:#dcfce7;

    color:#15803d;

    border:1px solid #86efac;

}

.invalid{

    background:#fee2e2;

    color:#dc2626;

    border:1px solid #fca5a5;

}

.btn{

    width:100%;

    padding:14px;

    border:none;

    border-radius:10px;

    font-size:16px;

    cursor:pointer;

    margin-top:5px;

}

.check-btn{

    background:#2563eb;

    color:white;

}

.check-btn:hover{

    background:#1d4ed8;

}

.send-btn{

    background:#16a34a;

    color:white;

}

.send-btn:hover{

    background:#15803d;

}

.send-btn:disabled{

    background:#9ca3af;

    cursor:not-allowed;

}

.back{

    display:block;

    text-align:center;

    margin-top:20px;

    color:#2563eb;

    text-decoration:none;

    font-weight:bold;

}

.back:hover{

    text-decoration:underline;

}

</style>

</head>


<body>

<div class="forgot-card">

    <div class="icon">

        <i class="fa-solid fa-key"></i>

    </div>


    <h2>

        Forgot Password

    </h2>


    <p class="description">

        Enter your registered email address to reset your password.

    </p>


    <?php if ($message != "") { ?>

        <div class="message <?php echo $emailStatus; ?>">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php } ?>


    <form method="POST">

        <div class="input-box">

            <label>Email Address</label>

            <input
                type="email"
                name="email"
                value="<?php echo htmlspecialchars($email); ?>"
                placeholder="Enter your registered email"
                required
            >

        </div>


        <button
            type="submit"
            name="checkEmail"
            class="btn check-btn"
        >

            <i class="fa-solid fa-magnifying-glass"></i>

            Check Email

        </button>

    </form>


    <?php if ($emailStatus === "valid") { ?>

        <form
            method="POST"
            action="../Backend/send_reset_email.php"
        >

            <input
                type="hidden"
                name="email"
                value="<?php echo htmlspecialchars($email); ?>"
            >

            <input
                type="hidden"
                name="type"
                value="<?php echo htmlspecialchars($type); ?>"
            >

            <button
                type="submit"
                name="sendResetEmail"
                class="btn send-btn"
            >

                <i class="fa-solid fa-paper-plane"></i>

                Send Reset Email

            </button>

        </form>

    <?php } ?>


    <a
        href="<?php echo htmlspecialchars($loginPage); ?>"
        class="back"
    >

        <i class="fa-solid fa-arrow-left"></i>

        Back to Login

    </a>

</div>

</body>

</html>