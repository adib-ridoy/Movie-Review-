<?php
session_start();

// Check admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: ../index.php');
    exit;
}

require_once '../config.php';

$movie_id = intval($_GET['id'] ?? 0);
if ($movie_id <= 0) {
    header('Location: manage_movies.php');
    exit;
}

$error = '';
$success = '';

// Fetch existing movie
$stmt = $conn->prepare("SELECT id, title, genre, release_year, description, IFNULL(image_path, '') AS image_path FROM movies WHERE id = ?");
$stmt->bind_param('i', $movie_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $stmt->close();
    header('Location: manage_movies.php');
    exit;
}
$movie = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $release_year = intval($_POST['release_year'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    if (empty($title) || empty($genre) || $release_year == 0 || empty($description)) {
        $error = 'All fields are required!';
    } elseif (strlen($title) < 2) {
        $error = 'Title must be at least 2 characters!';
    } elseif ($release_year < 1800 || $release_year > date('Y') + 5) {
        $error = 'Invalid release year!';
    } else {
        $new_image_path = $movie['image_path'];

        // Optional new image upload
        if (isset($_FILES['image']) && is_array($_FILES['image']) && ($_FILES['image']['error'] === UPLOAD_ERR_OK)) {
            $allowed = ['jpg','jpeg','png','gif','webp'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed, true)) {
                $safeTitle = preg_replace('/[^a-z0-9\s_\-]/i', '', $title);
                $safeTitle = trim(preg_replace('/\s+/', ' ', $safeTitle));
                $base = strtolower(preg_replace('/[\s_]+/', '-', $safeTitle));

                $destDir = __DIR__ . '/../img';
                if (!is_dir($destDir)) { @mkdir($destDir, 0775, true); }
                $destPath = $destDir . '/' . $base . '.' . $ext;
                $i = 1;
                while (file_exists($destPath)) {
                    $destPath = $destDir . '/' . $base . '-' . $i . '.' . $ext;
                    $i++;
                }
                if (move_uploaded_file($_FILES['image']['tmp_name'], $destPath)) {
                    // Delete old image if it lives under img/
                    if (!empty($movie['image_path'])) {
                        $oldPath = realpath(__DIR__ . '/../' . ltrim($movie['image_path'], '/'));
                        if ($oldPath && strpos($oldPath, realpath(__DIR__ . '/../img')) === 0 && file_exists($oldPath)) {
                            @unlink($oldPath);
                        }
                    }
                    $new_image_path = 'img/' . basename($destPath);
                } else {
                    $error = 'Failed to upload new image.';
                }
            } else {
                $error = 'Invalid image format. Use JPG, JPEG, PNG, GIF, or WebP.';
            }
        }

        if (empty($error)) {
            // Check if image_path column exists
            $hasImagePath = false;
            if ($res = $conn->query("SHOW COLUMNS FROM movies LIKE 'image_path'")) {
                $hasImagePath = $res->num_rows > 0;
                $res->close();
            }

            if ($hasImagePath) {
                $stmt = $conn->prepare("UPDATE movies SET title = ?, genre = ?, release_year = ?, description = ?, image_path = ? WHERE id = ?");
                $stmt->bind_param('ssissi', $title, $genre, $release_year, $description, $new_image_path, $movie_id);
            } else {
                $stmt = $conn->prepare("UPDATE movies SET title = ?, genre = ?, release_year = ?, description = ? WHERE id = ?");
                $stmt->bind_param('ssisi', $title, $genre, $release_year, $description, $movie_id);
            }

            if ($stmt->execute()) {
                $success = 'Movie updated successfully!';
                // Refresh movie data
                $stmt->close();
                $stmt = $conn->prepare("SELECT id, title, genre, release_year, description, IFNULL(image_path, '') AS image_path FROM movies WHERE id = ?");
                $stmt->bind_param('i', $movie_id);
                $stmt->execute();
                $movie = $stmt->get_result()->fetch_assoc();
                $stmt->close();
            } else {
                $error = 'Error updating movie. Please try again!';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Movie - CineRate Admin</title>
  <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
  <nav class="navbar">
    <div class="container">
      <h1 class="logo">🎬 CineRate</h1>
      <ul class="nav-links">
        <li><a href="../index.php">Home</a></li>
        <li><a href="add_movie.php">Add Movie</a></li>
        <li><a href="manage_movies.php">Manage Movies</a></li>
        <li><a href="offensive_list.php">Offensive List</a></li>
        <li><a href="../users/profile.php">Profile</a></li>
        <li><a href="../users/logout.php">Logout</a></li>
      </ul>
    </div>
  </nav>

  <div class="form-container">
    <h2>Edit Movie</h2>

    <?php if ($error): ?>
      <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
      <div class="form-group">
        <label for="title">Movie Title:</label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($movie['title']); ?>" required>
      </div>

      <div class="form-group">
        <label for="genre">Genre:</label>
        <input type="text" id="genre" name="genre" value="<?php echo htmlspecialchars($movie['genre']); ?>" required>
      </div>

      <div class="form-group">
        <label for="release_year">Release Year:</label>
        <input type="number" id="release_year" name="release_year" min="1800" max="<?php echo date('Y') + 5; ?>" value="<?php echo htmlspecialchars($movie['release_year']); ?>" required>
      </div>

      <div class="form-group">
        <label for="description">Description:</label>
        <textarea id="description" name="description" rows="6" required><?php echo htmlspecialchars($movie['description']); ?></textarea>
      </div>

      <div class="form-group">
        <label for="image">Poster Image (optional):</label>
        <?php if (!empty($movie['image_path'])): ?>
          <div class="movie-image" style="max-width:200px; margin-bottom: .5rem;">
            <img src="../<?php echo htmlspecialchars(ltrim($movie['image_path'],'/')); ?>" alt="Current Poster">
          </div>
        <?php endif; ?>
        <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
        <p style="font-size: 0.85rem; color: #888; margin-top: 0.5rem;">Leave empty to keep current image</p>
      </div>

      <button type="submit" class="submit-btn">Update Movie</button>
    </form>

    <p style="margin-top: 2rem; text-align: center;"><a href="manage_movies.php" style="color: #ff6b00;">Back to Manage Movies</a></p>
  </div>

  <footer>
    <p>&copy; 2025 CineRate - Movie Review Site. All rights reserved.</p>
  </footer>
</body>
</html>
<?php $conn->close(); ?>
