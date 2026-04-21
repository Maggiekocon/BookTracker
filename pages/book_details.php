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

//Access ID or ISBN 
$google_id = $_GET['id'] ?? ''; // Sent from browse.php
$isbn = $_GET['isbn'] ?? ''; // Sent from dashboard.php or mybooks/php

$book = null;
$data = null;

$title = $description = $cover = $genre = $buy_link = '';
$page_count = $rating = 'N/A';
$authors = [];

//Sent from browse.php -> use API to get book details
if (!empty($google_id)) {

    $url = "https://www.googleapis.com/books/v1/volumes/".$google_id."?key=".$key;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);

    $result = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($result, true);

    if (!empty($data) && empty($data['error'])) {

        $volume = $data['volumeInfo'] ?? [];

        $title = $volume['title'] ?? '';
        $authors = $volume['authors'] ?? [];
        $description = $volume['description'] ?? '';
        $cover = $volume['imageLinks']['thumbnail'] ?? '';
        $page_count = $volume['pageCount'] ?? 'N/A';
        $rating = $volume['averageRating'] ?? 'N/A';
        $buy_link = $data['saleInfo']['buyLink'] ?? '';

        // ISBN extraction
        $isbn = '';
        if (!empty($volume['industryIdentifiers'])) {
            foreach ($volume['industryIdentifiers'] as $idObj) {
                if (($idObj['type'] ?? '') === 'ISBN_13') {
                    $isbn = $idObj['identifier'];
                    break;
                }
            }
        }

        if (!$isbn) {
            $isbn = $data['id'];
        }

        // Genre
        if (!empty($volume['categories'][0])) {
            $genre = explode('/', $volume['categories'][0])[0];
        }
    }
}

//Sent from dashboard.php or mybooks/php -> Use local Database to get book details
if (!empty($isbn) && empty($title)) {

    $stmt = $conn->prepare("SELECT * FROM books WHERE isbn = ?");
    $stmt->bind_param("s", $isbn);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $book = $result->fetch_assoc();

        // Save book values handle emply values
        $title = $book['title'] ?? '';
        $description = $book['description'] ?? '';
        $cover = $book['cover_url'] ?? '';
        $genre = $book['genre'] ?? '';
        $page_count = $book['page_count'] ?? 'N/A';
        $rating = $book['average_rating'] ?? 'N/A';
        $buy_link = $book['buy_link'] ?? '';
    }

    /* AUTHORS FROM DB */
    $stmt = $conn->prepare("
        SELECT a.name
        FROM authors a
        JOIN book_author ba ON a.author_id = ba.author_id
        WHERE ba.isbn = ?
    ");
    $stmt->bind_param("s", $isbn);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $authors[] = $row['name'];
    }
}

// Save book details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category'])) {
    
    // keep system from going to a new page
    header('Content-Type: application/json');

    $allowed = ['read_next', 'reading', 'already_read'];

    if (!in_array($_POST['category'], $allowed)) {
        echo json_encode(["status" => "error"]);
        exit;
    }

    $category = $_POST['category'];

    //Save book if not saved
    if (!empty($isbn)) {

        // Check if book exists in DB
        $stmt = $conn->prepare("SELECT isbn FROM books WHERE isbn = ?");
        $stmt->bind_param("s", $isbn);
        $stmt->execute();

        // Save in BOOKS table
        if ($stmt->get_result()->num_rows == 0 && !empty($title)) {

            $stmt = $conn->prepare("
                INSERT INTO books 
                (isbn, title, description, cover_url, genre, page_count, average_rating, buy_link)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "sssssids",
                $isbn,
                $title,
                $description,
                $cover,
                $genre,
                $page_count,
                $rating,
                $buy_link
            );

            $stmt->execute();
        }

        // Save in AUTHORS table 
        if (empty($authors)) {
            $authors = ['Unknown'];
        }

        foreach ($authors as $name) {

            $stmt = $conn->prepare("SELECT author_id FROM authors WHERE name = ?");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($row = $res->fetch_assoc()) {
                $author_id = $row['author_id'];
            } else {
                $stmt = $conn->prepare("INSERT INTO authors (name) VALUES (?)");
                $stmt->bind_param("s", $name);
                $stmt->execute();
                $author_id = $conn->insert_id;
            }

            $stmt = $conn->prepare("
                INSERT IGNORE INTO book_author (isbn, author_id)
                VALUES (?, ?)
            ");
            $stmt->bind_param("si", $isbn, $author_id);
            $stmt->execute();
        }
    }

    // Save in SAVED table (SAVED table = books and users bridge table)
    $stmt = $conn->prepare("
        INSERT INTO SAVED (USER_ID, ISBN, CATEGORY)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE CATEGORY = VALUES(CATEGORY)
    ");

    $stmt->bind_param("iss", $user_id, $isbn, $category);

    echo json_encode([
        "status" => $stmt->execute() ? "success" : "error"
    ]);
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Book Details</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../css/style.css">

<!-- Javascript to get reusable nav element -->
<script>
fetch("../includes/top-menu.inc")
  .then(r => r.text())
  .then(html => document.getElementById("navbar").innerHTML = html);
</script>

<!-- Styling for book cover image -->
<style> 
    .book-cover-box {
    width: 100%;
    min-height: 350px;
    display: flex;
    align-items: flex-start; 
    justify-content: center;
    border-radius: 8px;
    overflow: hidden;
    }

    .book-cover-img {
    width: 100%;
    height: auto; /* keeps aspect ratio */
    object-fit: contain;
    display: block;
    }

    .text-white {
        text-decoration-color: white;
    }
  
</style>
</head>

<body>

<nav id="navbar"></nav>

<div class="container py-5">

<h2>Book Details</h2>

<?php if (!empty($title)): ?>

<div class="card p-4">

  <div class="row g-4">

    <div class="col-md-4 ">
        <div class="book-cover-box">
            <?php if ($cover): ?>
            <img src="<?php echo htmlspecialchars($cover); ?>" class="book-cover-img">
            <?php else: ?>
            <div class="no-image">No Image</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-md-8">

      <h2><?php echo htmlspecialchars($title); ?></h2>

        <p><strong>Author:</strong> <?php echo htmlspecialchars(implode(', ', $authors)); ?></p>
        <p><strong>ISBN:</strong> <?php echo htmlspecialchars($isbn); ?></p>
        <p><strong>Genre:</strong> <?php echo htmlspecialchars($genre ?: 'N/A'); ?></p>
        <p><strong>Pages:</strong> <?php echo htmlspecialchars($page_count); ?></p>
        <p><strong>Rating:</strong> <?php echo htmlspecialchars($rating ?: 'N/A'); ?></p>
        
        <?php if (!empty($buy_link)): ?> 
            <p>
                <a href = '<?php echo htmlspecialchars($buy_link);?>' class = 'text-white text-decoration-none'>
                    <button class = 'btn btn-secondary'>Buy</button>
                </a>
            </p>
        <?php endif; ?>
        
        <p><?php echo $description ?: 'No description available.'; ?></p>

        <div class="mt-3">
        <button class="btn btn-primary save-btn" data-category="read_next">Read Next</button>
        <button class="btn btn-outline-primary save-btn" data-category="reading">Currently Reading</button>
        <button class="btn btn-outline-success save-btn" data-category="already_read">Already Read</button>
        </div>

    </div>
  </div>

</div>

<!-- handle no book found -->
<?php else: ?> 
  <p>No book found.</p>
<?php endif; ?>

</div>

<script>
// Create Success alert when book is saved
document.querySelectorAll('.save-btn').forEach(btn => {
  btn.addEventListener('click', function () {

    fetch(window.location.href, {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'category=' + encodeURIComponent(this.dataset.category)
    })
    .then(r => r.json())
    .then(res => {
      if (res.status === 'success') {
        alert('Book saved successfully');
      }
    });

  });
});
</script>

</body>
</html>