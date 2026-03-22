<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM books WHERE user_id='$user_id'";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    echo "<div class='book'>";
    echo "<h3>" . htmlspecialchars($row['title']) . "</h3>";
    echo "<p>Author: " . htmlspecialchars($row['author']) . "</p>";
    echo "<p>ISBN: " . htmlspecialchars($row['isbn']) . "</p>";
    echo "<p>Description: " . htmlspecialchars($row['description']) . "</p>";
    echo "<p>Rating: " . htmlspecialchars($row['rating']) . "</p>";
    echo "<img src='" . htmlspecialchars($row['image_url']) . "' width='120'>";
    echo "</div>";
}
?>