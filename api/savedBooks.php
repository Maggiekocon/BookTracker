<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM books WHERE user_id='$user_id'";
$result = $conn->query($sql);
?>