<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include("../includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.html");
    exit();
}

$books_per_page = 40;
$maxApiItems = 1000;

// Default values
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$filter = $_GET['filter'] ?? '';

$data = ['items' => [], 'totalItems' => 0];

// Query prefix
$queryPrefix = "";

if ($filter === "author") {
    $queryPrefix = "inauthor:";
} elseif ($filter === "title") {
    $queryPrefix = "intitle:";
}

// Run API only if search exists
if (!empty($search)) {

    $encodedSearch = urlencode($search);
    $startIndex = ($page - 1) * $books_per_page;

    // Safe key handling
    $key = $key ?? '';

    $url = "https://www.googleapis.com/books/v1/volumes?q={$queryPrefix}{$encodedSearch}&maxResults={$books_per_page}&startIndex={$startIndex}&key={$key}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);

    $result = curl_exec($ch);

    if (curl_errno($ch) || $result === false) {
        curl_close($ch);
        $data = ['items' => [], 'totalItems' => 0];
    } else {
        curl_close($ch);

        $decoded = json_decode($result, true);

        if (is_array($decoded)) {
            $data = $decoded;
        } else {
            $data = ['items' => [], 'totalItems' => 0];
        }
    }
}

// Pagination
$totalPages = 0;
if (!empty($data['totalItems'])) {
    $availableItems = min($data['totalItems'], $maxApiItems);
    $totalPages = ceil($availableItems / $books_per_page);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Search | BookTracker</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
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

<div id="navbar"></div>

<div class="container py-5">

  <div class="mb-4">
    <h2>Search Books</h2>
    <p>Find books by title, author, or ISBN.</p>
  </div>

  <!-- Search Form -->
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
          <option value="title" <?php echo ($filter === 'title') ? 'selected' : ''; ?>>Title</option>
          <option value="author" <?php echo ($filter === 'author') ? 'selected' : ''; ?>>Author</option>
        </select>
      </div>

      <div class="col-md-2">
        <button class="btn btn-primary w-100" type="submit">Search</button>
      </div>

    </form>
  </div>

  <h3 class="mb-4">Search Results</h3>

  <div class="row g-4">

    <?php if (!empty($data['items'])): ?>
        <?php foreach ($data['items'] as $book): ?>

          <?php
            $title = $book['volumeInfo']['title'] ?? 'No Title';
            $authors = $book['volumeInfo']['authors'] ?? [];
            $author = !empty($authors) ? $authors[0] : 'Unknown Author';
            $image = $book['volumeInfo']['imageLinks']['thumbnail'] ?? '';
          ?>

          <div class="col-6 col-md-3">
            <div class="card h-100">

              <div class="book-placeholder">
                <?php if (!empty($image)): ?>
                  <img src="<?php echo htmlspecialchars($image); ?>"
                       alt="<?php echo htmlspecialchars($title); ?>">
                <?php else: ?>
                  <div class="no-image">No Image Available</div>
                <?php endif; ?>
              </div>

              <div class="card-body">
                <h6 class="card-title">
                  <?php echo htmlspecialchars($title); ?>
                </h6>

                <p class="card-text">
                  <?php echo htmlspecialchars($author); ?>
                </p>

                <a href="book_details.php?id=<?php echo urlencode($book['id']); ?>"
                   class="btn btn-primary btn-sm">
                  View Details
                </a>
              </div>

            </div>
          </div>

        <?php endforeach; ?>
    <?php elseif (!empty($search)): ?>
        <p>No results found.</p>
    <?php endif; ?>

  </div>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
    <nav class="mt-4">
      <ul class="pagination justify-content-center">

        <?php if ($page > 1): ?>
          <li class="page-item">
            <a class="page-link" href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page - 1; ?>">Previous</a>
          </li>
        <?php endif; ?>

        <?php
          $startPage = max(1, $page - 5);
          $endPage = min($totalPages, $page + 4);

          for ($i = $startPage; $i <= $endPage; $i++):
        ?>
          <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
            <a class="page-link" href="?search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>">
              <?php echo $i; ?>
            </a>
          </li>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
          <li class="page-item">
            <a class="page-link" href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page + 1; ?>">Next</a>
          </li>
        <?php endif; ?>

      </ul>
    </nav>
  <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>