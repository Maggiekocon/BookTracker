<?php
$key = '';
$books_per_page = 40;
$maxApiItems = 1000; // Google Books API limit

// Default values
$search = '';
$page = 1;
$data = null;

// Handle GET search and pagination
if (isset($_GET['search'])) {
    $search = htmlspecialchars($_GET['search']);
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

    if ($search !== '') {
        $encodedSearch = urlencode($search);
        $startIndex = ($page - 1) * $books_per_page;

        // Build Google Books API URL
        $url = "https://www.googleapis.com/books/v1/volumes?q={$encodedSearch}&maxResults={$books_per_page}&startIndex={$startIndex}&key={$key}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($result, true);
    }
}

// Determine total pages based on Google Books API limits
$totalPages = 0;
if (isset($data['totalItems']) && $data['totalItems'] > 0) {
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
            <a class="nav-link" href="dashboard.php">Home</a>
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

<!-- Page Content -->
<div class="container py-5">

  <div class="mb-4">
    <h2>Search Books</h2>
    <p>Find books by title, author, or ISBN.</p>
  </div>

  <!-- Search Form (GET method for clean URLs) -->
  <div class="card p-3 mb-5">
    <form method="GET" class="d-flex">
      <input class="form-control me-2" type="search" name="search"
             placeholder="Search for books..." value="<?php echo $search; ?>">
      <button class="btn btn-primary" type="submit">Search</button>
    </form>
  </div>

  <!-- Results -->
  <h3 class="mb-4">Search Results</h3>
  <div class="row g-4">
    <?php if (isset($data['items']) && count($data['items']) > 0): ?>
        <?php foreach ($data['items'] as $book): ?>
            <div class="col-6 col-md-3">
              <div class="card h-100">
                <?php if (isset($book['volumeInfo']['imageLinks']['thumbnail'])): ?>
                    <img src="<?php echo $book['volumeInfo']['imageLinks']['thumbnail']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($book['volumeInfo']['title']); ?>">
                <?php else: ?>
                    <div class="p-5 text-center bg-light">No Image</div>
                <?php endif; ?>

                <div class="card-body">
                  <h6 class="card-title"><?php echo $book['volumeInfo']['title'] ?? 'No Title'; ?></h6>
                  <p class="card-text"><?php echo $book['volumeInfo']['authors'][0] ?? 'Unknown Author'; ?></p>
                  <a href="book.php?id=<?php echo $book['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                </div>
              </div>
            </div>
        <?php endforeach; ?>
    <?php elseif ($search !== ''): ?>
        <p>No results found.</p>
    <?php endif; ?>
  </div>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
      <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
          <!-- Previous -->
          <?php if ($page > 1): ?>
            <li class="page-item">
              <a class="page-link" href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page-1; ?>">Previous</a>
            </li>
          <?php endif; ?>

          <?php
          // Show up to 10 pages in pagination
          $startPage = max(1, $page - 5);
          $endPage = min($totalPages, $page + 4);
          for ($i = $startPage; $i <= $endPage; $i++):
          ?>
            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
              <a class="page-link" href="?search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
          <?php endfor; ?>

          <!-- Next -->
          <?php if ($page < $totalPages): ?>
            <li class="page-item">
              <a class="page-link" href="?search=<?php echo urlencode($search); ?>&page=<?php echo $page+1; ?>">Next</a>
            </li>
          <?php endif; ?>
        </ul>
      </nav>
  <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>