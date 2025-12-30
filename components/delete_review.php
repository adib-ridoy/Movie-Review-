<?php
session_start();

// Require admin
if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

require_once '../config.php';

$review_id = intval($_POST['review_id'] ?? 0);
$movie_id  = intval($_POST['movie_id'] ?? 0);

if ($review_id <= 0 || $movie_id <= 0) {
    header('Location: ../index.php');
    exit;
}

// Delete review by id
$stmt = $conn->prepare('DELETE FROM reviews WHERE id = ?');
$stmt->bind_param('i', $review_id);
$stmt->execute();

$stmt->close();
$conn->close();

header('Location: ../movie_detail.php?id=' . $movie_id . '&deleted=1');
exit;
