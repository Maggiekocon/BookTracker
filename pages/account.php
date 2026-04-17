<<<<<<< HEAD
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
=======
>>>>>>> main
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Account | BookTracker</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<<<<<<< HEAD
  <link rel="stylesheet" href="css/style.css">
=======
  <link rel="stylesheet" href="../css/style.css">
  <script>
   fetch("../includes/top-menu.inc")
      .then(response => response.text())
      .then(data => {
        document.getElementById("navbar").innerHTML = data;
      });
</script>
>>>>>>> main
</head>
<body>

  <!-- Navbar -->
<<<<<<< HEAD
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand" href="dashboard.html">BookTracker</a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link active" href="dashboard.html">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="browse.html">Search</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="mybooks.html">My Books</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="account.html">Account</a>
          </li>
        </ul>

        <a href="login.html" class="btn btn-outline-light">Logout</a>
      </div>
    </div>
  </nav>
=======
  <nav id="navbar"></nav>
>>>>>>> main

<!-- Account Info -->
<div class="container mt-5">
  <div class="card shadow p-4">
    <h2>Account Details</h2>
    <hr>

<<<<<<< HEAD
    <p><strong>First Name:</strong> <?= $user['first_name'] ?></p>
    <p><strong>Last Name:</strong> <?= $user['last_name'] ?></p>
    <p><strong>Email:</strong> <?= $user['email'] ?></p>
    <p><strong>Username:</strong> <?= $user['username'] ?></p>

    <a href="edit_account.php" class="btn btn-primary mt-3">Edit Information</a>
    
    <a href="change_password.php" class="btn btn-warning mt-3">Change Password</a>
=======
    <p><strong>First Name:</strong> Dawa</p>
    <p><strong>Last Name:</strong> Sherpa</p>
    <p><strong>Email:</strong> dawasherpa@email.com</p>
    <p><strong>Username:</strong> dawa01</p>
    <p><strong>Age:</strong> 22</p>

    <button class="btn btn-primary mt-3">Edit Information</button>
>>>>>>> main
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>