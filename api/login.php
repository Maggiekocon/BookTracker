<?php
session_start();
include("../includes/db.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../public/login.html");
    exit();
}

$username = $conn->real_escape_string($_POST['username']);
$password = $_POST['password'];

if (empty($username) || empty($password)) {
    echo "Please fill in all fields";
    exit();
}

$result = $conn->query("SELECT * FROM user WHERE username='$username'");

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        header("Location: ../public/dashboard.php");
        exit();
    }
}

echo "Invalid username or password";
?>