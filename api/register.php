<?php
include("../includes/db.php");

// Get form data safely
$username = $conn->real_escape_string($_POST['username']);
$password = $_POST['password'];
$confirm = $_POST['confirm'];
$firstname = $conn->real_escape_string($_POST['firstname']);
$lastname = $conn->real_escape_string($_POST['lastname']);
$email = $conn->real_escape_string($_POST['email']);
$phone_number = $conn->real_escape_string($_POST['phone_number']);
$dob = $conn->real_escape_string($_POST['dob']);

if ($password !== $confirm) {
    echo "Passwords do not match";
    exit();
}

if (empty($username) || empty($password) || empty($firstname) || empty($email)) {
    echo "Please fill in all required fields";
    exit();
}

// Hash the password before storing it in the database
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Check if the username already exists
$check = $conn->query("SELECT * FROM user WHERE username='$username'");

if ($check->num_rows > 0) {
    echo "Username already exists";
} else {
    $conn->query("INSERT INTO user (username, password, first_name, last_name, email, phone_number, dob) VALUES ('$username', '$hashed', '$firstname', '$lastname', '$email', '$phone_number', '$dob')");
    header("Location: ../public/login.html");
    exit();
}
?>