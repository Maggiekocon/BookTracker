<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables from .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Database configuration

$db_host = $_ENV['DB_HOST'];// Database host
$db_user = $_ENV['DB_USER']; // Database username
$db_pass = $_ENV['DB_PASS'];// Database password
$db_name = $_ENV['DB_NAME']; // Database name
$key = $_ENV['API_KEY']; // API key

// Create a new MySQLi connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: set charset to UTF-8
$conn->set_charset("utf8");

//postgres
// postgresql://postgres:[YOUR-PASSWORD]@db.lmyatqnydphsmhgbyosn.supabase.co:5432/postgres
// $host = db.lmyatqnydphsmhgbyosn.supabase.co

// $port = 5432;

// $database = postgres;

// $user =postgres;



?>