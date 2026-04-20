<?php
// Start session
session_start();

// Check if User is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// Save user ID
$user_id = $_SESSION['user_id'];

// Connect to Database
include("../includes/db.php");

// Get search query and filter from URL parameters
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? '';

// Function to search saved books -> used ChatGPT for Function
function getBooks($conn, $user_id, $category, $search, $filter) {

    $sql = "
        SELECT b.isbn, b.title, b.cover_url,
        GROUP_CONCAT(a.name SEPARATOR ', ') AS authors
        FROM saved s
        JOIN books b ON s.isbn = b.isbn
        LEFT JOIN book_author ba ON b.isbn = ba.isbn
        LEFT JOIN authors a ON ba.author_id = a.author_id
        WHERE s.user_id = ? AND s.category = ?
    ";

    // Initialize parameters and types for prepared statement
    $params = [$user_id, $category];
    $types = "is";

    // Add search condition based on filter (author or title)
    if (!empty($search)) {
        if ($filter === 'author') {
            $sql .= " AND a.name LIKE ?";
        } else {
            $sql .= " AND b.title LIKE ?";
        }
        $params[] = "%$search%";
        $types .= "s";
    }

    // Group by ISBN and order by title
    $sql .= " GROUP BY b.isbn ORDER BY b.title ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    // Return the result set
    return $stmt->get_result();
}

// store all saved books
$current_books = getBooks($conn, $user_id, 'reading', $search, $filter);
$next_books    = getBooks($conn, $user_id, 'read_next', $search, $filter);
$read_books    = getBooks($conn, $user_id, 'already_read', $search, $filter);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Books | BookTracker</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Include Bootstrap CSS for styling -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../css/style.css">

<!-- Fetch and insert top navigation menu -->
<script>
fetch("../includes/top-menu.inc")
  .then(res => res.text())
  .then(data => {
    document.getElementById("navbar").innerHTML = data;
  });
</script>
</head>

<body>

<!-- Navigation bar placeholder -->
<nav id="navbar"></nav>

<div class="container mt-4">

  <h2>My Books</h2>

  <!-- Search -->
  <div class="card p-3 my-4">
    <form method="GET" class="row g-2">

      <div class="col-md-6">
        <!-- Search input field -->
        <input class="form-control" type="search" name="search"
          placeholder="Search your books..."
          value="<?= htmlspecialchars($search); ?>">
      </div>

      <div class="col-md-4">
        <!-- Filter dropdown for search type -->
        <select name="filter" class="form-select">
          <option value="">All Categories</option>
          <option value="">Title</option>
          <option value="author" <?= $filter=='author' ? 'selected' : '' ?>>Author</option>
        </select>
      </div>

      <div class="col-md-2">
        <!-- Submit button for search -->
        <button class="btn btn-primary w-100">Search</button>
      </div>

    </form>
  </div>

  <!-- Jump to links -->
  <div class="mb-4">
    <a href="#reading" class="btn btn-outline-primary btn-sm me-2">Currently Reading</a>
    <a href="#next" class="btn btn-outline-primary btn-sm me-2">Read Next</a>
    <a href="#read" class="btn btn-outline-primary btn-sm">Already Read</a>
  </div>

  <!-- Display books -->
  <?php
  function renderSection($title, $id, $books) {
      // Output section header and start row
      echo "<h4 id='$id' class='mt-4'>$title</h4>";
      echo "<div class='row g-4'>";

      // Handle case with no books
      if ($books->num_rows === 0) {
          echo "<p>No books found.</p>";
      }

      // Loop through books and render each as a card
      while ($book = $books->fetch_assoc()):
  ?>

      <div class="col-md-3">
        <div class="card h-100">

          <!-- COVER -->
          <div class="book-placeholder">
            <?php if (!empty($book['cover_url'])): ?>
              <img src="<?= htmlspecialchars($book['cover_url']) ?>" class="img-fluid">
            <?php else: ?>
              Book Cover
            <?php endif; ?>
          </div>

          <!-- BODY -->
          <div class="card-body">
            <h6 class="card-title">
              <?= htmlspecialchars($book['title']) ?>
            </h6>

            <p class="card-text">
              <?= htmlspecialchars($book['authors']) ?>
            </p>

            <!-- Link to book details page -->
            <a href="book_details.php?isbn=<?= urlencode($book['isbn']) ?>" 
               class="btn btn-primary btn-sm">
              View Details
            </a>
          </div>

        </div>
      </div>

  <?php
      endwhile;

      // Close the row
      echo "</div>";
  }
  ?>

  <!-- Render each book section -->
  <?php
    renderSection("Currently Reading", "reading", $current_books);
    renderSection("Read Next", "next", $next_books);
    renderSection("Already Read", "read", $read_books);
  ?>

</div>

<!-- Include Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>