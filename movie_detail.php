<?php
session_start();
require_once 'config.php';

// Get current user's offense count (if logged in)
$current_user_offense = 0;
if (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $stmtUser = $conn->prepare('SELECT offense_count FROM users WHERE id = ?');
    $stmtUser->bind_param('i', $uid);
    if ($stmtUser->execute()) {
        $resUser = $stmtUser->get_result();
        if ($u = $resUser->fetch_assoc()) {
            $current_user_offense = (int)$u['offense_count'];
        }
    }
    $stmtUser->close();
}

// Get movie ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$movie_id = intval($_GET['id']);

// Get movie details
$sql = "SELECT m.*, 
        AVG(r.rating) as avg_rating, 
        COUNT(r.id) as review_count
        FROM movies m
        LEFT JOIN reviews r ON m.id = r.movie_id
        WHERE m.id = $movie_id
        GROUP BY m.id";

$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header('Location: index.php');
    exit;
}

$movie = $result->fetch_assoc();
$avg_rating = $movie['avg_rating'] ? round($movie['avg_rating'], 1) : 'N/A';

// Determine poster image: use DB image_path or auto-pick from img/ by title
function normalize_title($s) {
    $s = strtolower($s);
    $s = str_replace('&', 'and', $s);
    $s = preg_replace('/[^a-z0-9\s_\-]+/', '', $s);
    $s = preg_replace('/[\s_\-]+/', '', $s);
    return $s;
}

function build_img_index($dir) {
    $index = [];
    if (!is_dir($dir)) return $index;
    $files = scandir($dir);
    $exts = ['jpg','jpeg','png','gif','webp'];
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        if (!in_array($ext, $exts, true)) continue;
        $base = pathinfo($f, PATHINFO_FILENAME);
        $index[normalize_title($base)] = $f;
    }
    return $index;
}

function find_image_for_title($title, $index) {
    if (!$title) return '';
    $key = normalize_title($title);
    $altKey = normalize_title(preg_replace('/^\s*the\s+/i', '', $title));
    if (isset($index[$key])) return 'img/' . $index[$key];
    if (isset($index[$altKey])) return 'img/' . $index[$altKey];
    // Try direct patterns as a fallback
    $patterns = [
        $title,
        str_replace(' ', '_', $title),
        str_replace(' ', '-', $title),
        strtolower($title),
        str_replace(' ', '_', strtolower($title)),
        str_replace(' ', '-', strtolower($title)),
    ];
    $exts = ['jpg','jpeg','png','gif','webp'];
    foreach ($patterns as $p) {
        foreach ($exts as $e) {
            $candidate = 'img/' . $p . '.' . $e;
            if (file_exists(__DIR__ . '/' . $candidate)) return $candidate;
        }
    }
    return '';
}

$poster = '';
if (!empty($movie['image_path'])) {
    $poster = ltrim($movie['image_path'], '/');
}
if (empty($poster)) {
    $imgIndex = build_img_index(__DIR__ . '/img');
    $poster = find_image_for_title($movie['title'], $imgIndex);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($movie['title']); ?> - Movie Review</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1 class="logo">🎬 CineRate</h1>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <?php
                if (isset($_SESSION['user_id'])) {
                    if ($_SESSION['is_admin']) {
                        echo '<li><a href="admin/add_movie.php">Add Movie</a></li>';
                        echo '<li><a href="admin/manage_movies.php">Manage Movies</a></li>';
                    }
                    echo '<li><a href="users/profile.php">Profile</a></li>';
                    echo '<li><a href="users/logout.php">Logout</a></li>';
                } else {
                    echo '<li><a href="users/login.php">Login</a></li>';
                    echo '<li><a href="users/register.php">Register</a></li>';
                }
                ?>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div style="margin: 2rem 0; display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center;">
            <form method="GET" action="index.php" style="display: flex; gap: 0.5rem; flex-wrap: wrap; justify-content: center;">
                <input type="text" name="search" placeholder="Search by title or genre..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" style="padding: 0.7rem; border-radius: 4px; border: 1px solid #ff6b00; background: #1a1a1a; color: #e0e0e0; width: 300px;">
                <button type="submit" style="padding: 0.7rem 1.5rem; background-color: #ff6b00; color: #000; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">Search</button>
                <a href="index.php" style="padding: 0.7rem 1rem; background-color: #555; color: #fff; border: none; border-radius: 4px; font-weight: bold; text-decoration: none; display: inline-block;">Clear</a>
            </form>
        </div>

        <div class="movie-detail">
            <h2><?php echo htmlspecialchars($movie['title']); ?></h2>
            
            <?php if (isset($_GET['offense_increased'])): ?>
                <div class="success">Offense count increased successfully!</div>
            <?php endif; ?>
            
            <?php if (isset($_GET['offense_deleted'])): ?>
                <div class="success">Review deleted due to excessive offenses (>3).</div>
            <?php endif; ?>

            <?php if (isset($_GET['deleted'])): ?>
                <div class="success">Review deleted successfully.</div>
            <?php endif; ?>
            
            <?php if (isset($_GET['blocked'])): ?>
                <script>
                    alert('You can no longer comment due to repeated offenses.');
                </script>
            <?php endif; ?>
            
            <?php if (!empty($poster)): ?>
                <div class="movie-detail-image">
                    <img src="<?php echo htmlspecialchars($poster); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                </div>
            <?php endif; ?>
            <div class="movie-meta">
                <p><strong>Genre:</strong> <?php echo htmlspecialchars($movie['genre']); ?></p>
                <p><strong>Year:</strong> <?php echo $movie['release_year']; ?></p>
                <p><strong>Average Rating:</strong> ⭐ <?php echo $avg_rating; ?> (<?php echo $movie['review_count']; ?> reviews)</p>
            </div>
            <div class="movie-description">
                <p><?php echo htmlspecialchars($movie['description']); ?></p>
            </div>

            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($current_user_offense >= 3): ?>
                    <div class="error">You can no longer comment due to repeated offenses.</div>
                <?php else: ?>
                <div class="review-form">
                    <h3>Share Your Review</h3>
                    <form method="POST" action="components/submit_review.php">
                        <input type="hidden" name="movie_id" value="<?php echo $movie_id; ?>">
                        
                        <label for="rating">Rating (1-5 stars):</label>
                        <div class="star-rating">
                            <input type="radio" name="rating" value="5" id="star5" required>
                            <label for="star5">★</label>
                            <input type="radio" name="rating" value="4" id="star4">
                            <label for="star4">★</label>
                            <input type="radio" name="rating" value="3" id="star3">
                            <label for="star3">★</label>
                            <input type="radio" name="rating" value="2" id="star2">
                            <label for="star2">★</label>
                            <input type="radio" name="rating" value="1" id="star1">
                            <label for="star1">★</label>
                        </div>

                        <label for="comment">Your Comment:</label>
                        <textarea name="comment" id="comment" rows="4" placeholder="Share your thoughts about this movie..."></textarea>

                        <button type="submit" class="btn">Submit Review</button>
                    </form>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="login-prompt">
                    <p><a href="users/login.php">Login</a> or <a href="users/register.php">Register</a> to leave a review!</p>
                </div>
            <?php endif; ?>

            <div class="reviews-section">
                <h3>Reviews</h3>
                <?php
                $sql = "SELECT r.*, u.username
                        FROM reviews r
                        JOIN users u ON r.user_id = u.id
                        WHERE r.movie_id = $movie_id
                        ORDER BY r.created_at DESC";
                
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    while($review = $result->fetch_assoc()) {
                        echo '
                        <div class="review-card">
                            <div class="review-header">
                                <strong>' . htmlspecialchars($review['username']) . '</strong>
                                <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap; justify-content: flex-end;">
                                    <span class="rating">⭐ ' . $review['rating'] . '/5</span>';

                        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
                            $hideBtns = isset($_GET['review_updated']) && intval($_GET['review_updated']) === (int)$review['id'];
                            if (!$hideBtns) {
                                echo '<form method="POST" action="components/increase_offense.php" style="display: inline; margin: 0;">
                                        <input type="hidden" name="review_id" value="' . (int)$review['id'] . '">
                                        <input type="hidden" name="movie_id" value="' . (int)$movie_id . '">
                                        <button type="submit" style="background: #ff6b00; color: #000; border: none; padding: 0.3rem 0.6rem; border-radius: 4px; cursor: pointer; font-size: 0.85rem; font-weight: bold;">+1 Offense</button>
                                    </form>
                                    <form method="POST" action="components/delete_review.php" style="display: inline; margin: 0;" onsubmit="return confirm(\'Delete this review?\');">
                                        <input type="hidden" name="review_id" value="' . (int)$review['id'] . '">
                                        <input type="hidden" name="movie_id" value="' . (int)$movie_id . '">
                                        <button type="submit" style="background: #ff4d4f; color: #000; border: none; padding: 0.3rem 0.6rem; border-radius: 4px; cursor: pointer; font-size: 0.85rem; font-weight: bold;">Delete</button>
                                    </form>';
                            } else {
                                echo '<span style="color:#6bff6b; font-size:0.85rem;">Updated</span>';
                            }
                        }

                        echo '</div>
                            </div>
                            <p class="review-date">' . date('F j, Y', strtotime($review['created_at'])) . '</p>
                            <p class="review-comment">' . htmlspecialchars($review['comment']) . '</p>
                        </div>
                        ';
                    }
                } else {
                    echo '<p>No reviews yet. Be the first to review this movie!</p>';
                }
                $conn->close();
                ?>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 CineRate - Movie Review Site. All rights reserved.</p>
    </footer>
</body>
</html>
