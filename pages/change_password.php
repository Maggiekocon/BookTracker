<?php
session_start();
include("../includes/db.php");

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Handle form
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Get user's current password hash
    $result = $conn->query("SELECT password_hash FROM users WHERE user_id = $user_id");
    $user = $result->fetch_assoc();

    // Check current password
    if (!password_verify($current_password, $user['password_hash'])) {
        $error = "Current password is incorrect";
    }
    elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match";
    }
    elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $new_password)) {
    $error = "Password must be at least 8 characters and include:
              - one uppercase letter
              - one number
              - one special character";
    }
    else {
        // Hash new password
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

        // Update password
        $conn->query("UPDATE users SET password_hash='$new_hash' WHERE user_id=$user_id");

        session_unset();
        session_destroy();

        header("Location: login.php?message=password_changed");
        exit();
    }
}

if (isset($_GET['message']) && $_GET['message'] === 'password_changed'): ?>
    <div class="alert alert-success">
        Password updated. Please log in again.
    </div>
<?php endif;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Change Password</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../css/style.css">
</head>
<script>
    fetch("../includes/top-menu.inc")
    .then(r => r.text())
    .then(html => document.getElementById("navbar").innerHTML = html);
</script>
<body class="bg-light">

<nav id="navbar"></nav>
<div class="container mt-5">
    <div class="card p-4 shadow">
        <h2>Change Password</h2>
        <hr>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Current Password</label>
                <input type="password" name="current_password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="new_password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-success">Update Password</button>
            <a href="account.php" class="btn btn-secondary">Cancel</a>

        </form>
        <small class="text-muted">
        Password must be at least 8 characters and include:
        uppercase letter, number, and symbol.
        </small>
    </div>
</div>

</body>
</html>