<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include("../includes/db.php");

// ==========================
// AUTH CHECK
// ==========================
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// ==========================
// GET USER NAME
// ==========================
$stmt = $conn->prepare("SELECT first_name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$first_name = $user['first_name'] ?? 'User';

// ==========================
// SEARCH + FILTER
// ==========================
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? '';

$query = "
    SELECT b.*, s.category, GROUP_CONCAT(a.name SEPARATOR ', ') AS authors
    FROM books b
    LEFT JOIN book_author ba ON b.isbn = ba.isbn
    LEFT JOIN authors a ON ba.author_id = a.author_id
    INNER JOIN saved s ON b.isbn = s.isbn
    WHERE s.user_id = ?
";

$params = [$user_id];
$types = "i";

if (!empty($search)) {
    if ($filter === 'author') {
        $query .= " AND a.name LIKE ?";
    } else {
        $query .= " AND b.title LIKE ?";
    }
    $params[] = "%" . $search . "%";
    $types .= "s";
}

$query .= " GROUP BY b.isbn ORDER BY s.saved_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$search_results = $stmt->get_result();

// ==========================
// RECENT 3 BOOKS
// ==========================
$recent_query = "
    SELECT b.*, s.category, GROUP_CONCAT(a.name SEPARATOR ', ') AS authors
    FROM saved s
    JOIN books b ON s.isbn = b.isbn
    LEFT JOIN book_author ba ON b.isbn = ba.isbn
    LEFT JOIN authors a ON ba.author_id = a.author_id
    WHERE s.user_id = ?
    GROUP BY b.isbn
    ORDER BY s.saved_at DESC
    LIMIT 3
";

$stmt = $conn->prepare($recent_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_books = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard | BookTracker</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/style.css">

  <script>
    fetch("../includes/top-menu.inc")
      .then(res => res.text())
      .then(data => {
        document.getElementById("navbar").innerHTML = data;
      });
  </script>
</head>

<body>

<nav id="navbar"></nav>

<div class="container py-5">

  <!-- Welcome -->
  <div class="mb-4">
    <h2 class="section-title">
      Welcome Back <?php echo htmlspecialchars($first_name);?>! 
    </h2>
    <p class="section-subtitle">
      Manage your reading lists and keep track of your books.
    </p>
  </div>

  <!-- Search -->
  <div class="card p-3 mb-5">
    <form method="GET" class="row g-2">

      <div class="col-md-6">
        <input class="form-control" type="search" name="search"
          placeholder="Search by title or author..."
          value="<?php echo htmlspecialchars($search); ?>">
      </div>

      <div class="col-md-4">
        <select name="filter" class="form-select">
          <option value="">All Categories</option>
          <option value="title" <?php if($filter=='title') echo 'selected'; ?>>Title</option>
          <option value="author" <?php if($filter=='author') echo 'selected'; ?>>Author</option>
        </select>
      </div>

      <div class="col-md-2">
        <button class="btn btn-primary w-100" type="submit">Search</button>
      </div>

    </form>
  </div>

  <!-- Search Results -->
  <?php if (!empty($search)): ?>
  <div class="mb-4">
    <h4>Search Results</h4>
  </div>

  <div class="row g-4 mb-5">
    <?php while($book = $search_results->fetch_assoc()): ?>
      <div class="col-md-4">

        <div class="card h-100">
          <!-- Cover -->
          <div class="book-placeholder">
            <?php if (!empty($book['cover_url'])): ?>
              <img src="<?php echo htmlspecialchars($book['cover_url']); ?>" class="img-fluid">
            <?php else: ?>
              Book Cover
            <?php endif; ?>
          </div>

          <!-- Body -->
          <div class="card-body">
            <h6 class="card-title">
              <?php echo htmlspecialchars($book['title']); ?>
            </h6>

            <p class="card-text">
              <?php echo htmlspecialchars($book['authors']); ?>
            </p>

            <!-- Category -->
            <span class="badge bg-secondary mb-2">
              <?php echo ucfirst(str_replace('_', ' ', $book['category'])); ?>
            </span>

            <br>

            <!-- Button -->
            <a href="book_details.php?isbn=<?= urlencode($book['isbn']) ?>" class="btn btn-primary btn-sm">
              View Details
            </a>
          </div>

        </div>
      </div>
    <?php endwhile; ?>
  </div>
<?php endif; ?>

  <!-- Summary Cards -->
  <div class="row g-4 mb-5">
    <div class="col-md-4">
      <div class="card p-4 h-100">
        <h5>Currently Reading</h5>
        <p>Keep track of books you are reading right now.</p>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card p-4 h-100">
        <h5>Read Next</h5>
        <p>Save books you want to read later.</p>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card p-4 h-100">
        <h5>Already Read</h5>
        <p>View books you have already finished.</p>
      </div>
    </div>
  </div>

  <!-- Library Preview -->
  <div class="mb-4">
    <h3 class="section-title">Your Library Preview</h3>
    <p class="section-subtitle">A quick look at your saved books.</p>
  </div>

  <div class="row g-4">
    <?php while($book = $recent_books->fetch_assoc()): ?>
      <div class="col-md-4">
        <div class="card h-100">

          <div class="book-placeholder">
            <?php if (!empty($book['cover_url'])): ?>
              <img src="<?php echo htmlspecialchars($book['cover_url']); ?>" class="img-fluid">
            <?php else: ?>
              Book Cover
            <?php endif; ?>
          </div>

          <div class="card-body">
            <h6 class="card-title">
              <?php echo htmlspecialchars($book['title']); ?>
            </h6>

            <p class="card-text">
              <?php echo htmlspecialchars($book['authors']); ?>
            </p>

            <span class="badge bg-secondary mb-2">
              <?php echo ucfirst(str_replace('_', ' ', $book['category'])); ?>
            </span>

            <br>

            <a href="book_details.php?isbn=<?= urlencode($book['isbn']) ?>" class="btn btn-primary btn-sm">
              View Details
            </a>
          </div>

        </div>
      </div>
    <?php endwhile; ?>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>