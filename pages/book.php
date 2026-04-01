<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("../includes/db.php");

$key = '';

$id = htmlspecialchars($_GET['id'] ?? '');
$data = null;

/* =========================
   FETCH BOOK DATA (GET)
========================= */
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

        // Genre
        $genre = '';
        if (isset($volume['categories'])) {
            $parts = explode('/', $volume['categories'][0]);
            $genre = trim($parts[0]);
        }
    }
}

/* =========================
   HANDLE AJAX SAVE (POST)
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category'])) {

    header('Content-Type: application/json');

    $allowed = ['read_next', 'reading', 'already_read'];

    if (in_array($_POST['category'], $allowed)) {

        $category = $_POST['category'];
        $user_id = 1; // TODO: replace with session later

        // Insert book if not exists
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

        // Save category
        $stmt = $conn->prepare("
            INSERT INTO SAVED (USER_ID, ISBN, CATEGORY)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE CATEGORY = VALUES(CATEGORY)
        ");

        $stmt->bind_param("iss", $user_id, $isbn, $category);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
            exit;
        }
    }

    echo json_encode(["status" => "error"]);
    exit;
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

  <?php if ($data): ?>
  <div class="card p-4">
    <div class="row g-4 book-cover-wrapper">

      <!-- Cover -->
      <div class="col-md-4 ">
        <?php if ($cover): ?>
          <img src="<?php echo $cover; ?>" class="img-fluid book-cover">
        <?php else: ?>
          <div class="bg-light p-5 text-center">No Image</div>
        <?php endif; ?>
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
          <a href="<?php echo $buy_link; ?>" target="_blank" class="btn btn-outline-secondary mb-3">
            Buy Book
          </a>
        <?php endif; ?>

        <h5>Description</h5>
        <p><?php echo $description ?: 'No description available.'; ?></p>

        <!-- SAVE BUTTONS -->
        <div class="d-flex gap-2 flex-wrap mt-3">
          <button class="btn btn-primary save-btn" data-category="read_next">Read Next</button>
          <button class="btn btn-outline-primary save-btn" data-category="reading">Currently Reading</button>
          <button class="btn btn-outline-success save-btn" data-category="already_read">Already Read</button>
        </div>

      </div>
    </div>
  </div>
  <?php else: ?>
    <p>No book selected.</p>
  <?php endif; ?>

</div>

<!-- ✅ JAVASCRIPT FIX -->
<script>
document.addEventListener('DOMContentLoaded', function () {

  document.querySelectorAll('.save-btn').forEach(button => {
    button.addEventListener('click', function () {

      const category = this.dataset.category;
      const clickedBtn = this;

      fetch(window.location.href, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'category=' + encodeURIComponent(category)
      })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'success') {

          showSavedMessage();

          // clickedBtn.innerText = "Saved ✓";
          // clickedBtn.disabled = true;
        }
      });

    });
  });

  function showSavedMessage() {
    let alert = document.createElement('div');
    alert.className = 'alert alert-success position-fixed top-0 end-0 m-3';
    alert.innerText = 'Book saved';

    document.body.appendChild(alert);

    setTimeout(() => {
      alert.remove();
    }, 2000);
  }

});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>