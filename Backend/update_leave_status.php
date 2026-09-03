<?php

session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login.php");
    exit();
}


$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "tasko"
);

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}


if (!isset($_GET['id']) || !isset($_GET['status'])) {

    die("Invalid Request");

}


$leave_id = (int)$_GET['id'];

$status = $_GET['status'];


if ($status !== "Approved" && $status !== "Rejected") {

    die("Invalid Status");

}


$sql = "UPDATE leave_requests
        SET status='$status'
        WHERE leave_id='$leave_id'";


if (mysqli_query($conn, $sql)) {

    header(
        "Location: ../Frontend/dashboard.php?page=leave-requests"
    );

    exit();

} else {

    die(
        "Update Failed: " .
        mysqli_error($conn)
    );

}

?>