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

                if ($result->num_rows == 0) {

                    // Insert book
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

                //  Insert into SAVED
                $stmt = $conn->prepare("
                    INSERT INTO SAVED (USER_ID, ISBN, CATEGORY)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE CATEGORY = VALUES(CATEGORY)
                ");

                $stmt->bind_param("iss", $user_id, $isbn, $category);

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
