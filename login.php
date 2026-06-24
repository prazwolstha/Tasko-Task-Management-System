<?php

session_start();

$host = "localhost";
$user = "root";
$password = "";
$database = "tasko";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Connection Failed");
}

if (isset($_POST['login'])) {

    $email = $_POST['email'];
    $pass = $_POST['password'];
    $role = $_POST['role'];

    // USER LOGIN
    if ($role == "user") {

        $sql = "SELECT * FROM users 
                WHERE email='$email' AND role='user'";

        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {

            $row = mysqli_fetch_assoc($result);

            if ($pass == $row['password']) {

                $_SESSION['user_name'] = $row['full_name'];
                $_SESSION['role'] = $row['role'];

                header("Location: home.html");
                exit();

                } else {
                echo "Incorrect Password";
            }

        } else {
            echo "User Not Found";
        }
    }

    // ADMIN LOGIN
    else if ($role == "admin") {

        $sql = "SELECT * FROM admins 
                WHERE email='$email' AND role='admin'";

        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {

            $row = mysqli_fetch_assoc($result);

            if (password_verify($pass, $row['password'])) {

                $_SESSION['admin_name'] = $row['full_name'];
                $_SESSION['role'] = $row['role'];

                header("Location: dashboard.html");
                exit();

            } else {
                echo "Incorrect Password";
            }

        } else {
            echo "Admin Not Found";
        }
    }
}

?>