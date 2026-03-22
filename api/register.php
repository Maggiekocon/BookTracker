<?php
include("../includes/db.php");

// Get form data safely
$username = $conn->real_escape_string($_POST['username']);
$password = $_POST['password'];
$confirm = $_POST['confirm'];
$first_name = $conn->real_escape_string($_POST['first_name']);
$last_name = $conn->real_escape_string($_POST['last_name']);
$email = $conn->real_escape_string($_POST['email']);

// Generate timestamp
$created_at = date("Y-m-d H:i:s");

// Check if passwords match
if ($password !== $confirm) {
    echo "Passwords do not match";
    exit();
}

// Validate email format
if (empty($username) || empty($password) || empty($first_name) || empty($email)) {
    echo "Please fill in all required fields";
    exit();
}

// Hash password
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Check if username exists
$check = $conn->query("SELECT * FROM users WHERE username='$username'");

if ($check->num_rows > 0) {
    echo "Username already exists";
    exit();
}

// Insert new user
$result = $conn->query("INSERT INTO users (username, password, first_name, last_name, email, created_at) VALUES ('$username', '$hashed', '$first_name', '$last_name', '$email', '$created_at')");

if ($result) {
    header("Location: ../public/login.html");
    exit();
} else {
    echo "Error: " . $conn->error;
}
?>