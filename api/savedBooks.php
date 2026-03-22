<?php
session_start();
include("../includes/db.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.html");
    exit();
}

// Fetch saved books for the logged-in user
$user_id = $_SESSION['user_id'];

$sql = "SELECT books.* FROM books JOIN saved ON books.isbn = saved.isbn WHERE saved.user_id='$user_id'";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    echo "<div class='book'>";
    echo "<h3>" . htmlspecialchars($row['title']) . "</h3>";
    echo "<p>ISBN: " . htmlspecialchars($row['isbn']) . "</p>";
    echo "<p>Description: " . htmlspecialchars($row['description']) . "</p>";
    echo "<img src='" . htmlspecialchars($row['cover_url']) . "' width='120'>";
    echo "<p>Rating: " . htmlspecialchars($row['average_rating']) . "</p>";
}
?>