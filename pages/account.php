<?php
session_start();
include("../includes/db.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user data from database
$user_id = $_SESSION['user_id'];

$result = $conn->query("SELECT username, first_name, last_name, email FROM users WHERE user_id = $user_id");

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Account | BookTracker</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">
  <script>
  fetch("../includes/top-menu.inc")
    .then(r => r.text())
    .then(html => document.getElementById("navbar").innerHTML = html);
  </script>

</head>
<body>

<!-- Navbar -->
<nav id="navbar"></nav>
   
<!-- Account Info -->
<div class="container mt-5">
  <div class="card shadow p-4">
    <h2>Account Details</h2>
    <hr>

    <p><strong>First Name:</strong> <?= $user['first_name'] ?></p>
    <p><strong>Last Name:</strong> <?= $user['last_name'] ?></p>
    <p><strong>Email:</strong> <?= $user['email'] ?></p>
    <p><strong>Username:</strong> <?= $user['username'] ?></p>

    <a href="edit_account.php" class="btn btn-primary mt-3">Edit Information</a>
    
    <a href="change_password.php" class="btn btn-warning mt-3">Change Password</a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>