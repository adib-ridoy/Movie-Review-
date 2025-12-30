<?php
// Minimal DB connection for runtime (migrations should be run separately)
$servername = "localhost";
$username = "root";
$password = "";
$database = "movie_review";

// Connect directly to the application database
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    // Keep message generic to avoid 502 from verbose output
    die("Database connection failed.");
}

// Ensure proper charset
$conn->set_charset('utf8mb4');
?>
