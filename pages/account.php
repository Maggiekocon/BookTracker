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
      .then(response => response.text())
      .then(data => {
        document.getElementById("navbar").innerHTML = data;
      });
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

    <p><strong>First Name:</strong> Dawa</p>
    <p><strong>Last Name:</strong> Sherpa</p>
    <p><strong>Email:</strong> dawasherpa@email.com</p>
    <p><strong>Username:</strong> dawa01</p>
    <p><strong>Age:</strong> 22</p>

    <button class="btn btn-primary mt-3">Edit Information</button>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>