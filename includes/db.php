<?php

// Database configuration
$db_host = 'localhost';       // Database host
$db_user = 'root';   // Database username
$db_pass = '';   // Database password
$db_name = 'booktrackerdb';   // Database name

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