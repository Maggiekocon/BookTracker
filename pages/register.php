<?php
include("../includes/db.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get form data safely
    $username = trim($_POST['username'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirmPassword'] ?? '';

    // Validate input
    if (empty($username) || empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm)) {
        $error = "Please fill in all required fields";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match";
    } else {

        // Check if email already exists
        $check = $conn->query("SELECT * FROM users WHERE email='$email'");
        if ($check->num_rows > 0) {
            $error = "Email already registered";
        } else {

            // Hash password
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $created_at = date("Y-m-d H:i:s");

            // Insert new user (make sure your table has 'password_hash' column)
            $result = $conn->query("INSERT INTO users (username, first_name, last_name, email, password_hash, created_at) VALUES ('$username', '$first_name', '$last_name', '$email', '$hashed', '$created_at')");

            if ($result) {
                header("Location: login.php");
                exit();
            } else {
                $error = "Database error: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | BookTracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="bg-light">
<div class="container min-vh-100 d-flex justify-content-center align-items-center ">
    <div class="card shadow p-4 border-0 rounded-4" style="max-width: 420px; width: 100%; margin: 5%;">
        <div class="card-body">

            <h1 class="text-center mb-2">BookTracker</h1>
            <h4 class="text-center mb-4">Create Account</h4>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Corrected form -->
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>

                <div class="mb-3">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                </div>

                <div class="mb-3">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <div class="mb-3">
                    <label for="confirmPassword" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>

            <p class="form-link mt-3 mb-0 text-center">
                Already have an account? <a href="login.php">Login</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
