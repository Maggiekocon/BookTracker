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

// Query to fetch user's first name
$stmt = $conn->prepare("SELECT first_name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$first_name = $user['first_name'] ?? 'User';


// Get search query and filter from URL parameters
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? '';

// Base query to select saved books with authors, filtered by user
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

// Add search condition based on filter (author or title)
if (!empty($search)) {
    if ($filter === 'author') {
        $query .= " AND a.name LIKE ?";
    } else {
        $query .= " AND b.title LIKE ?";
    }
    $params[] = "%" . $search . "%";
    $types .= "s";
}

// Group by ISBN and order by save date descending
$query .= " GROUP BY b.isbn ORDER BY s.saved_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$search_results = $stmt->get_result();


// Query to fetch the 3 most recently saved books for the user
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

  <!-- Include Bootstrap CSS -->
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
        <!-- Search input for title or author -->
        <input class="form-control" type="search" name="search"
          placeholder="Search by title or author..."
          value="<?php echo htmlspecialchars($search); ?>">
      </div>

      <div class="col-md-4">
        <!-- Filter dropdown -->
        <select name="filter" class="form-select">
          <option value="">All Categories</option>
          <option value="title" <?php if($filter=='title') echo 'selected'; ?>>Title</option>
          <option value="author" <?php if($filter=='author') echo 'selected'; ?>>Author</option>
        </select>
      </div>

      <div class="col-md-2">
        <!-- Submit button -->
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
    <!-- Loop through search results and display each book -->
    <?php while($book = $search_results->fetch_assoc()): ?>
      <div class="col-md-4">

        <div class="card h-100">
          <!-- Cover -->
          <div class="book-placeholder">
            <?php if (!empty($book['cover_url'])): ?>
              <img src="<?php echo htmlspecialchars($book['cover_url']); ?>" class="h-100 object-fit-cover">
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
    <!-- Loop through recent books and display -->
    <?php while($book = $recent_books->fetch_assoc()): ?>
      <div class="col-md-4">
        <div class="card h-100">

          <!-- Book cover -->
          <div class="book-placeholder">
            <?php if (!empty($book['cover_url'])): ?>
              <img src="<?php echo htmlspecialchars($book['cover_url']); ?>" class="h-100 object-fit-cover">
            <?php else: ?>
              Book Cover
            <?php endif; ?>
          </div>

          <!-- Card body -->
          <div class="card-body">
            <h6 class="card-title">
              <?php echo htmlspecialchars($book['title']); ?>
            </h6>

            <p class="card-text">
              <?php echo htmlspecialchars($book['authors']); ?>
            </p>

            <!-- Category badge -->
            <span class="badge bg-secondary mb-2">
              <?php echo ucfirst(str_replace('_', ' ', $book['category'])); ?>
            </span>

            <br>

            <!-- View details button -->
            <a href="book_details.php?isbn=<?= urlencode($book['isbn']) ?>" class="btn btn-primary btn-sm">
              View Details
            </a>
          </div>

        </div>
      </div>
    <?php endwhile; ?>
  </div>

</div>

<!-- Include Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>