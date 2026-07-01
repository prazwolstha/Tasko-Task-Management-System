<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "tasko";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Database Connection Failed");
}

if (isset($_POST['create'])) {

    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $role = $_POST['role'];

    
    $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

   
    if ($role == "user") {

        $sql = "INSERT INTO users (full_name, email, password, role)
                VALUES ('$full_name', '$email', '$hashed_password', '$role')";

    } elseif ($role == "admin") {

        $sql = "INSERT INTO admins (full_name, email, password, role)
                VALUES ('$full_name', '$email', '$hashed_password', '$role')";
    } else {
        echo "Invalid role selected.";
        exit;
    }

    if ($sql && mysqli_query($conn, $sql)) {

        echo "
        <script type='text/javascript'>
            alert('Account Created Successfully');
            window.location.href='login.html';
        </script>
        ";

    } else {

        echo "Error: " . mysqli_error($conn);
    }

}

?>