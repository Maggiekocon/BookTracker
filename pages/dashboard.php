<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard | BookTracker</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand" href="dashboard.html">BookTracker</a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link active" href="dashboard.php">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="browse.php">Search</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="mybooks.php">My Books</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="account.php">Account</a>
          </li>
        </ul>

        <a href="login.html" class="btn btn-outline-light">Logout</a>
      </div>
    </div>
  </nav>

  <!-- Dashboard Content -->
  <div class="container py-5">
    <div class="mb-4">
      <h2 class="section-title">Welcome Back</h2>
      <p class="section-subtitle">Manage your reading lists and keep track of your books.</p>
    </div>

    <!-- Search Bar -->
    <div class="card p-3 mb-5">
      <form action="browse.html" method="GET" class="d-flex">
        <input class="form-control me-2" type="search" name="search" placeholder="Search for books by title, author, or ISBN">
        <button class="btn btn-primary" type="submit">Search</button>
      </form>
    </div>

    <!-- Reading Summary Cards -->
    <div class="row g-4 mb-5">
      <div class="col-md-4">
        <div class="card p-4 h-100">
          <h5>Currently Reading</h5>
          <p class="mb-0">Keep track of books you are reading right now.</p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card p-4 h-100">
          <h5>Read Next</h5>
          <p class="mb-0">Save books you want to read later.</p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card p-4 h-100">
          <h5>Already Read</h5>
          <p class="mb-0">View books you have already finished.</p>
        </div>
      </div>
    </div>

    <!-- Library Preview -->
    <div class="mb-4">
      <h3 class="section-title">Your Library Preview</h3>
      <p class="section-subtitle">A quick look at your saved books.</p>
    </div>

    <div class="row g-4">
      <div class="col-md-4">
        <div class="card h-100">
          <div class="book-placeholder">Book Cover</div>
          <div class="card-body">
            <h6 class="card-title">Sample Book Title</h6>
            <p class="card-text">Author Name</p>
            <a href="book.html" class="btn btn-primary btn-sm">View Details</a>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card h-100">
          <div class="book-placeholder">Book Cover</div>
          <div class="card-body">
            <h6 class="card-title">Sample Book Title</h6>
            <p class="card-text">Author Name</p>
            <a href="book.html" class="btn btn-primary btn-sm">View Details</a>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card h-100">
          <div class="book-placeholder">Book Cover</div>
          <div class="card-body">
            <h6 class="card-title">Sample Book Title</h6>
            <p class="card-text">Author Name</p>
            <a href="book.html" class="btn btn-primary btn-sm">View Details</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>