<?php
session_start();

// Check if user is admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: ../index.php');
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: manage_movies.php');
    exit;
}

require_once '../config.php';

$movie_id = intval($_POST['movie_id'] ?? 0);

if ($movie_id <= 0) {
    header('Location: manage_movies.php?error=invalid_movie');
    exit;
}

// Delete the movie and its reviews (cascading delete handles reviews)
$stmt = $conn->prepare("DELETE FROM movies WHERE id = ?");
if (!$stmt) {
    header('Location: manage_movies.php?error=delete_failed');
    exit;
}

$stmt->bind_param("i", $movie_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    // Movie deleted successfully
    header('Location: manage_movies.php?success=movie_deleted');
} else {
    // Movie not found
    header('Location: manage_movies.php?error=movie_not_found');
}

$stmt->close();
$conn->close();
?>
