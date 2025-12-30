<?php
session_start();

// Check if user is admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

require_once '../config.php';

$review_id = intval($_POST['review_id'] ?? 0);
$movie_id = intval($_POST['movie_id'] ?? 0);

if ($review_id <= 0 || $movie_id <= 0) {
    header('Location: ../index.php');
    exit;
}

// Get offending user
$stmt = $conn->prepare("SELECT user_id FROM reviews WHERE id = ?");
$stmt->bind_param("i", $review_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ../movie_detail.php?id=' . $movie_id);
    exit;
}

$row = $result->fetch_assoc();
$offender_user_id = (int)$row['user_id'];

// Increment user's offense count only
if ($offender_user_id > 0) {
    $inc = $conn->prepare("UPDATE users SET offense_count = offense_count + 1 WHERE id = ?");
    $inc->bind_param("i", $offender_user_id);
    $inc->execute();
    $inc->close();
}

header('Location: ../movie_detail.php?id=' . $movie_id . '&offense_increased=1&review_updated=' . $review_id);

$stmt->close();
$conn->close();
?>
