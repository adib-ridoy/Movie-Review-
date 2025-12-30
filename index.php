<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Review Site</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1 class="logo">🎬 CineRate</h1>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <?php
                session_start();
                if (isset($_SESSION['user_id'])) {
                    if ($_SESSION['is_admin']) {
                        echo '<li><a href="admin/add_movie.php">Add Movie</a></li>';
                        echo '<li><a href="admin/manage_movies.php">Manage Movies</a></li>';
                        echo '<li><a href="admin/offensive_list.php">Offensive List</a></li>';
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
        <h2>Popular Movies</h2>
        
        <div style="margin: 2rem 0; display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center;">
            <form method="GET" action="index.php" style="display: flex; gap: 0.5rem; flex-wrap: wrap; justify-content: center;">
                <input type="text" name="search" placeholder="Search by title or genre..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" style="padding: 0.7rem; border-radius: 4px; border: 1px solid #ff6b00; background: #1a1a1a; color: #e0e0e0; width: 300px;">
                <button type="submit" style="padding: 0.7rem 1.5rem; background-color: #ff6b00; color: #000; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">Search</button>
                <a href="index.php" style="padding: 0.7rem 1rem; background-color: #555; color: #fff; border: none; border-radius: 4px; font-weight: bold; text-decoration: none; display: inline-block;">Clear</a>
            </form>
        </div>
        
        <div class="movies-grid">
            <?php
            require_once 'config.php';
            
            // Helpers to auto-pick poster from img/ if DB image_path is empty
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
                // Try some direct patterns if not indexed (defensive)
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

            $imgIndex = build_img_index(__DIR__ . '/img');

            // Build WHERE clause based on search filters
            $where = "1=1";
            $params = [];
            $searchTerm = trim($_GET['search'] ?? '');
            
            if (!empty($searchTerm)) {
                $where .= " AND (m.title LIKE ? OR m.genre LIKE ?)";
                $params[] = '%' . $searchTerm . '%';
                $params[] = '%' . $searchTerm . '%';
            }
            
            $sql = "SELECT m.id, m.title, m.genre, m.release_year, m.description, m.image_path,
                    AVG(r.rating) as avg_rating, COUNT(r.id) as review_count
                    FROM movies m
                    LEFT JOIN reviews r ON m.id = r.movie_id
                    WHERE $where
                    GROUP BY m.id
                    ORDER BY m.created_at DESC";
            
            if (empty($params)) {
                $result = $conn->query($sql);
            } else {
                $stmt = $conn->prepare($sql);
                $types = str_repeat('s', count($params));
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $result = $stmt->get_result();
            }
            
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $avg_rating = $row['avg_rating'] ? round($row['avg_rating'], 1) : 'N/A';
                    $review_count = $row['review_count'];
                    $poster = '';
                    if (!empty($row['image_path'])) {
                        $poster = ltrim($row['image_path'], '/'); // make relative if stored with leading slash
                    }
                    if (empty($poster)) {
                        $poster = find_image_for_title($row['title'], $imgIndex);
                    }

                    echo '
                    <div class="movie-card">
                        ' . (!empty($poster)
                            ? '<div class="movie-image movie-thumb"><img src="' . htmlspecialchars($poster) . '" alt="' . htmlspecialchars($row['title']) . '"></div>'
                            : '<div class="movie-image movie-thumb placeholder">📽️</div>') . '
                        <div class="movie-header">
                            <h3>' . htmlspecialchars($row['title']) . '</h3>
                            <p class="year">' . $row['release_year'] . '</p>
                        </div>
                        <p class="genre">' . htmlspecialchars($row['genre']) . '</p>
                        <p class="description">' . htmlspecialchars(substr($row['description'], 0, 100)) . '...</p>
                        <div class="rating-info">
                            <span class="avg-rating">⭐ ' . $avg_rating . '</span>
                            <span class="review-count">(' . $review_count . ' reviews)</span>
                        </div>
                        <a href="movie_detail.php?id=' . $row['id'] . '" class="btn-view">View Details</a>
                    </div>
                    ';
                }
            } else {
                echo '<p style="grid-column: 1/-1; text-align: center;">No movies yet. Check back soon!</p>';
            }
            $conn->close();
            ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 CineRate - Movie Review Site. All rights reserved.</p>
    </footer>
</body>
</html>
