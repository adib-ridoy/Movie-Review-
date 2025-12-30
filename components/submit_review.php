<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../users/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

require_once '../config.php';

$movie_id = intval($_POST['movie_id'] ?? 0);
$rating = intval($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');
$user_id = $_SESSION['user_id'];

// Block commenting if user offense_count >= 3
$chk = $conn->prepare("SELECT offense_count FROM users WHERE id = ?");
$chk->bind_param("i", $user_id);
$chk->execute();
$chkRes = $chk->get_result();
if ($row = $chkRes->fetch_assoc()) {
    if ((int)$row['offense_count'] >= 3) {
        header('Location: ../movie_detail.php?id=' . $movie_id . '&blocked=1');
        exit;
    }
}
$chk->close();

// Validation
if ($movie_id <= 0 || $rating < 1 || $rating > 5) {
    header('Location: ../index.php');
    exit;
}

// Check if movie exists
$stmt = $conn->prepare("SELECT id FROM movies WHERE id = ?");
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ../index.php');
    exit;
}

// Insert or update review
$stmt = $conn->prepare("INSERT INTO reviews (movie_id, user_id, rating, comment) 
                        VALUES (?, ?, ?, ?) 
                        ON DUPLICATE KEY UPDATE rating = ?, comment = ?");
$stmt->bind_param("iiisss", $movie_id, $user_id, $rating, $comment, $rating, $comment);

if ($stmt->execute()) {
    header('Location: ../movie_detail.php?id=' . $movie_id . '&success=1');
} else {
    header('Location: ../movie_detail.php?id=' . $movie_id . '&error=1');
}

$stmt->close();
$conn->close();
?>
