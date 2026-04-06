<?php
session_start();
include("../includes/db.php");

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../public/login.html");
    exit();
}

// Get form data safely
$username = $conn->real_escape_string($_POST['username']);
$password = $_POST['password'];

// Validate input
if (empty($username) || empty($password)) {
    echo "Please fill in all fields";
    exit();
}

// Check if user exists
$result = $conn->query("SELECT * FROM users WHERE username='$username'");

// Verify password
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];

        header("Location: ../public/dashboard.php");
        exit();
    }
}

echo "Invalid username or password";
?>