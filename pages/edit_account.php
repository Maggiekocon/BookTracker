<?php
session_start();
include("../includes/db.php");

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get current user data
$result = $conn->query("SELECT username, first_name, last_name, email FROM users WHERE user_id = $user_id");
$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $email = $conn->real_escape_string($_POST['email']);

    $conn->query("
        UPDATE users 
        SET first_name='$first_name', last_name='$last_name', email='$email'
        WHERE user_id=$user_id
    ");

    header("Location: account.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Account</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../css/style.css">
<!-- Fetch and insert top navigation menu -->
<script>
fetch("../includes/top-menu.inc")
  .then(response => response.text())
  .then(data => {
    document.getElementById("navbar").innerHTML = data;
  });
</script>

</head>

<body class="bg-light">
<!-- Navigation bar placeholder -->
<div id="navbar"></div>

<div class="container mt-5">
    <div class="card p-4 shadow">
        <h2>Edit Account</h2>
        <hr>

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" class="form-control"
                       value="<?= $user['first_name'] ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" class="form-control"
                       value="<?= $user['last_name'] ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control"
                       value="<?= $user['email'] ?>" required>
            </div>

            <button type="submit" class="btn btn-success">Save Changes</button>
            <a href="account.php" class="btn btn-secondary">Cancel</a>

        </form>
    </div>
</div>

</body>
</html>