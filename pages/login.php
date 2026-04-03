<?php
session_start();
include("../includes/db.php");

$error = "";

// If user is already logged in, send them to dashboard
//if (isset($_SESSION['user_id'])) {
//    header("Location: ../pages/browse.php");
//    exit();
//}

// Only process login form when submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get form data
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validate input
    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields";
    } else {

        // Use prepared statement for security
        $stmt = $conn->prepare("SELECT user_id, username, password_hash FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password_hash'])) {

                // Prevent session fixation
                session_regenerate_id(true);

                // Store user data in session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];

                // Redirect to browse page
                header("Location: ../public/browse.php");
                exit();
            }
        }

        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | BookTracker</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="bg-light">

    <div class="container min-vh-100 d-flex justify-content-center align-items-center">
        <div class="card shadow p-4 border-0 rounded-4" style="max-width: 400px; width: 100%;">
            <div class="card-body">

                <h1 class="text-center mb-2">BookTracker</h1>
                <h4 class="text-center mb-4">Login</h4>

                <?php if (!empty($error)) : ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="username" 
                            name="username" 
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input 
                            type="password" 
                            class="form-control" 
                            id="password" 
                            name="password" 
                            required
                        >
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        Login
                    </button>
                </form>

                <p class="text-center mt-3 mb-0">
                    Don't have an account?
                    <a href="register.php">Create an account</a>
                </p>

            </div>
        </div>
    </div>

</body>
</html>