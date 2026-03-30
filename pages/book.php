<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the connection file
include("../includes/db.php");

$key = 'Public_key';

$id = htmlspecialchars($_GET['id'] ?? '');

$data = null;
$message = '';

if ($id !== '') {

    $url = "https://www.googleapis.com/books/v1/volumes/".$id."?key=".$key;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    $result = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($result, true);

    if (!isset($data['error'])) {

        $volume = $data['volumeInfo'];

        // Extract data
        $title = $volume['title'] ?? '';
        $authors = $volume['authors'] ?? [];
        $description = $volume['description'] ?? '';
        $cover = $volume['imageLinks']['thumbnail'] ?? '';
        $page_count = $volume['pageCount'] ?? 'N/A';
        $rating = $volume['averageRating'] ?? 'N/A';
        $buy_link = $data['saleInfo']['buyLink'] ?? '';

        // ISBN
        $isbn = '';
        if (isset($volume['industryIdentifiers'])) {
            foreach ($volume['industryIdentifiers'] as $idObj) {
                if ($idObj['type'] == 'ISBN_13') {
                    $isbn = $idObj['identifier'];
                    break;
                }
            }
        }
        if (!$isbn) $isbn = $data['id'];

        // Genre cleanup
        $genre = '';
        if (isset($volume['categories'])) {
            $parts = explode('/', $volume['categories'][0]);
            $genre = trim($parts[0]);
        }

        // HANDLE SAVE -- ensure this does not go to next page so when you pree <- you go directly to search page
        if (isset($_POST['category'])) {

            $allowed = ['read_next', 'reading', 'already_read'];

            if (in_array($_POST['category'], $allowed)) {

                $category = $_POST['category'];
                $user_id = 1;

                // Insert BOOK
                $stmt = $conn->prepare("SELECT isbn FROM BOOKS WHERE isbn = ?");
                $stmt->bind_param("s", $isbn);
                $stmt->execute();
                $resultCheck = $stmt->get_result();

                if ($resultCheck->num_rows == 0) {

                    $stmt = $conn->prepare("
                        INSERT INTO BOOKS 
                        (isbn, title, description, cover_url, genre, page_count, average_rating, buy_link)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ");

                    $stmt->bind_param(
                        "sssssids",
                        $isbn, $title, $description, $cover,
                        $genre, $page_count, $rating, $buy_link
                    );

                    $stmt->execute();
                }

                // SAVE CATEGORY
                $stmt = $conn->prepare("
                    INSERT INTO SAVED (USER_ID, ISBN, CATEGORY)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE CATEGORY = VALUES(CATEGORY)
                ");

                $stmt->bind_param("iss", $user_id, $isbn, $category);

                if ($stmt->execute()) {
                    $message = "Saved as: " . str_replace('_', ' ', $category);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Book Details | BookTracker</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

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

<div class="container py-5">

  <h2 class="mb-4">Book Details</h2>

  <?php if ($message): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
  <?php endif; ?>

  <?php if ($data): ?>
  <div class="card p-4">
    <div class="row g-4">

      <!-- Cover -->
        <div class="col-md-4">
            <div class="book-cover-wrapper">
                <?php if ($cover): ?>
                <img src="<?php echo $cover; ?>" class="book-cover">
                <?php else: ?>
                <div class="bg-light d-flex align-items-center justify-content-center h-100">
                    No Image
                </div>
                <?php endif; ?>
            </div>
        </div>

      <!-- Info -->
      <div class="col-md-8">

        <h2><?php echo $title; ?></h2>

        <p><strong>Author:</strong> <?php echo implode(', ', $authors); ?></p>
        <p><strong>ISBN:</strong> <?php echo $isbn; ?></p>
        <p><strong>Genre:</strong> <?php echo $genre ?: 'N/A'; ?></p>
        <p><strong>Pages:</strong> <?php echo $page_count; ?></p>
        <p><strong>Rating:</strong> <?php echo $rating; ?></p>

        <?php if ($buy_link): ?>
          <a href="<?php echo $buy_link; ?>" target="_blank" class="btn btn-sm btn-outline-secondary mb-3">
            Buy Book
          </a>
        <?php endif; ?>

        <h5>Description</h5>
        <p class="text-muted"><?php echo $description ?: 'No description available.'; ?></p>

        <!-- BUTTONS -->
        <form method="POST" class="d-flex gap-2 flex-wrap">

          <button name="category" value="read_next" class="btn btn-primary">
            Read Next
          </button>

          <button name="category" value="reading" class="btn btn-outline-primary">
            Currently Reading
          </button>

          <button name="category" value="already_read" class="btn btn-outline-success">
            Already Read
          </button>

        </form>

      </div>
    </div>
  </div>
  <?php else: ?>
    <p>No book selected.</p>
  <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

    <style>
        .book-cover-wrapper {
            width: 100%;
            height: 100%;
            min-height: 350px; /* controls size */
            overflow: hidden;
            border-radius: 0.5rem;

            }

        .book-cover {
            
            width: 100%;
            height: 100%;
            object-fit: contain;
}
    </style>
</html>