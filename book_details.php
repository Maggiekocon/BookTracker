<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$key = 'AIzaSyDGnbO9SfFZ9ZjUDSVTvilzKBvxErrda6Q';

// connect to DB
$host = "localhost";
$user = "root";
$pass = "Passw0rd";
$db = "booktrackerdb";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get ID from URL
$id = htmlspecialchars($_GET['id'] ?? '');

if($id !== '') {

    $url = "https://www.googleapis.com/books/v1/volumes/".$id."?key=".$key;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    $result = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($result, true);

    if (!isset($data['error'])) {

        $volume = $data['volumeInfo'];

        // Extract ISBN
        $isbn = '';
        if (isset($volume['industryIdentifiers'])) {
            foreach ($volume['industryIdentifiers'] as $idObj) {
                if ($idObj['type'] == 'ISBN_13') {
                    $isbn = $idObj['identifier'];
                    break;
                }
            }
        }

        // fallback if no ISBN
        if (!$isbn) {
            $isbn = $data['id'];
        }

        // Extract fields
        $title = $volume['title'] ?? '';
        $description = $volume['description'] ?? '';
        $cover = $volume['imageLinks']['thumbnail'] ?? '';
        $genre = '';
        $genre = '';

        if (isset($volume['categories'])) {
            $fullGenre = $volume['categories'][0]; // take first category
            $parts = explode('/', $fullGenre);
            $genre = trim($parts[0]); // take text before first "/"
        }
        $page_count = $volume['pageCount'] ?? null;
        $rating = $volume['averageRating'] ?? null;
        $buy_link = $data['saleInfo']['buyLink'] ?? '';

        // Insert if button is pressed
        if (isset($_POST['category'])) {

            $allowed = ['read_next', 'reading', 'already_read'];

            if (in_array($_POST['category'], $allowed)) {

                $category = $_POST['category'];

                $user_id = 1; //  replace later with logged-in user


                // Check if book exists
                $stmt = $conn->prepare("SELECT isbn FROM BOOKS WHERE isbn = ?");
                $stmt->bind_param("s", $isbn);
                $stmt->execute();
                $result = $stmt->get_result();

                 // Insert book
                if ($result->num_rows == 0) {

                    $stmt = $conn->prepare("
                        INSERT INTO BOOKS 
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

                    if (!$stmt->execute()) {
                        die("Book insert failed: " . $stmt->error);
                    }
                }
                //insert Author and insert bridge table
                if (isset($volume['authors'])) {

                    foreach ($volume['authors'] as $author_name) {

                        // Check if author exists
                        $stmt = $conn->prepare("SELECT author_id FROM authors WHERE name = ?");
                        $stmt->bind_param("s", $author_name);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($row = $result->fetch_assoc()) {
                            $author_id = $row['author_id'];
                        } else {
                            // Insert new author
                            $stmt = $conn->prepare("INSERT INTO authors (name) VALUES (?)");
                            $stmt->bind_param("s", $author_name);

                            if (!$stmt->execute()) {
                                die("Author insert failed: " . $stmt->error);
                            }

                            $author_id = $conn->insert_id;
                        }

                        // Insert into bridge table
                        $stmt = $conn->prepare("
                            INSERT INTO book_author (isbn, author_id)
                            VALUES (?, ?)
                            ON DUPLICATE KEY UPDATE isbn = isbn
                        ");

                        $stmt->bind_param("si", $isbn, $author_id);

                        if (!$stmt->execute()) {
                            echo "Bridge insert failed: " . $stmt->error;
                        }
                    }
                }


                //  Insert into SAVED
                $stmt = $conn->prepare("
                    INSERT INTO SAVED (USER_ID, ISBN, CATEGORY)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE CATEGORY = VALUES(CATEGORY)
                ");

                $stmt->bind_param("iss", $user_id, $isbn, $category);

                // replace with javascript pop - up
                if ($stmt->execute()) {
                    echo "<p><strong>Saved as: $category</strong></p>";
                } else {
                    echo "Saved insert failed: " . $stmt->error;
                }
            }
        }

        // Display book details
        echo '<div>';

        echo '<h2>' . $title . '</h2>';

        echo '<p><strong>ISBN:</strong> ' . $isbn . '</p>';

        if (isset($volume['authors'])) {
            echo '<p><strong>Author:</strong> ' . implode(', ', $volume['authors']) . '</p>';
        }

        echo '<p><strong>Genre:</strong> ' . $genre . '</p>';
        echo '<p><strong>Pages:</strong> ' . ($page_count ?? 'N/A') . '</p>';
        echo '<p><strong>Rating:</strong> ' . ($rating ?? 'N/A') . '</p>';

        if ($buy_link) {
            echo '<p><a href="' . $buy_link . '" target="_blank">Buy Book</a></p>';
        }

        if ($cover) {
            echo '<img src="' . $cover . '"><br>';
        }

        echo '<p>' . $description . '</p>';

        // Buttons 
        echo '
        <form method="post">
            <button name="category" value="read_next">Read Next</button>
            <button name="category" value="reading">Reading</button>
            <button name="category" value="already_read">Already Read</button>
        </form>';

        echo '</div>';
    }

} else {
    echo "No book selected.";
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Book Details | BookTracker</title>
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
            <a class="nav-link" href="dashboard.html">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="browse.html">Search</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="mybooks.html">My Books</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="account.html">Account</a>
          </li>
        </ul>

        <a href="login.html" class="btn btn-outline-light">Logout</a>
      </div>
    </div>
  </nav>

  <!-- Book Details Section -->
  <div class="container py-5">
    <div class="mb-4">
      <h2 class="section-title">Book Details</h2>
      <p class="section-subtitle">View full information about this book and save it to your library.</p>
    </div>

    <div class="card p-4">
      <div class="row g-4 align-items-start">

        <!-- Book Cover -->
        <div class="col-md-4">
          <div class="book-placeholder rounded">Book Cover</div>
        </div>

        <!-- Book Info -->
        <div class="col-md-8">
          <h2 class="mb-3">Book Title</h2>

          <div class="mb-3">
            <p class="mb-2"><strong>Author:</strong> Author Name</p>
            <p class="mb-2"><strong>ISBN:</strong> 1234567890</p>
            <p class="mb-2"><strong>Category:</strong> Fiction</p>
            <p class="mb-2"><strong>Rating:</strong> 4.5 / 5</p>
          </div>

          <div class="mb-4">
            <h5>Description</h5>
            <p class="text-muted">
              This is where the book description will go. It can include a short summary of the story,
              the main ideas, or important information the user may want to know before saving the book.
            </p>
          </div>

          <!-- Action Buttons -->
          <div class="d-flex flex-wrap gap-2">
            <a href="mybooks.html" class="btn btn-primary">Add to Library</a>
            <a href="mybooks.html" class="btn btn-outline-primary">Currently Reading</a>
            <a href="mybooks.html" class="btn btn-outline-success">Already Read</a>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

