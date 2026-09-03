<?php
session_start();

$conn = mysqli_connect("localhost","root","","tasko");

if(!$conn){
    die("Connection Failed");
}

if(!isset($_SESSION['admin_id'])){
    header("Location: ../auth/login.php");
    exit();
}

$message = "";

if(isset($_POST['changePassword'])){

    $admin_id = $_SESSION['admin_id'];

    $current = trim($_POST['current_password']);
    $new     = trim($_POST['new_password']);
    $confirm = trim($_POST['confirm_password']);

    $sql = mysqli_query($conn,"SELECT * FROM admins WHERE admin_id='$admin_id'");
    $admin = mysqli_fetch_assoc($sql);

    if($current != $admin['password']){

        $message = "Current password is incorrect.";

    }
    elseif($new != $confirm){

        $message = "New passwords do not match.";

    }
    elseif(strlen($new) < 6){

        $message = "Password must be at least 6 characters.";

    }
    else{

        mysqli_query($conn,"UPDATE admins
        SET password='$new'
        WHERE admin_id='$admin_id'");

        $message = "Password changed successfully.";

    }

}
?>