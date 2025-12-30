<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: ../index.php');
    exit;
}

require_once '../config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $release_year = intval($_POST['release_year'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $user_id = $_SESSION['user_id'];
    $image_path = null;
    
    if (empty($title) || empty($genre) || $release_year == 0 || empty($description)) {
        $error = 'All fields are required!';
    } elseif (strlen($title) < 2) {
        $error = 'Title must be at least 2 characters!';
    } elseif ($release_year < 1800 || $release_year > date('Y') + 5) {
        $error = 'Invalid release year!';
    } else {
        // Handle optional image upload
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
                    $image_path = 'img/' . basename($destPath);
                } else {
                    $error = 'Failed to upload image.';
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
                $stmt = $conn->prepare("INSERT INTO movies (title, genre, release_year, description, image_path, created_by) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssisss", $title, $genre, $release_year, $description, $image_path, $user_id);
            } else {
                $stmt = $conn->prepare("INSERT INTO movies (title, genre, release_year, description, created_by) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssiss", $title, $genre, $release_year, $description, $user_id);
            }

            if ($stmt->execute()) {
                $success = 'Movie added successfully!';
                // Clear form
                $_POST = array();
            } else {
                $error = 'Error adding movie. Please try again!';
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Movie - CineRate Admin</title>
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
        <h2>Add New Movie</h2>
        <p style="text-align: center; color: #ff6b00; margin-bottom: 1.5rem;">Admin Panel</p>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="image">Movie Poster Image (optional):</label>
                            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                            <p style="font-size: 0.85rem; color: #888; margin-top: 0.5rem;">Supported formats: JPEG, PNG, GIF, WebP (Max 5MB)</p>
                        </div>
            <div class="form-group">
                <label for="title">Movie Title:</label>
                <input type="text" id="title" name="title" placeholder="Enter movie title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="genre">Genre:</label>
                <input type="text" id="genre" name="genre" placeholder="e.g., Action, Drama, Comedy" value="<?php echo htmlspecialchars($_POST['genre'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="release_year">Release Year:</label>
                <input type="number" id="release_year" name="release_year" placeholder="e.g., 2024" min="1800" max="<?php echo date('Y') + 5; ?>" value="<?php echo htmlspecialchars($_POST['release_year'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="6" placeholder="Enter movie description..." required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>
            
            <button type="submit" class="submit-btn">Add Movie</button>
        </form>
        
        <p style="margin-top: 2rem; text-align: center;"><a href="../index.php" style="color: #ff6b00;">Back to Home</a></p>
    </div>

    <footer>
        <p>&copy; 2025 CineRate - Movie Review Site. All rights reserved.</p>
    </footer>
</body>
</html>

<?php
$conn->close();
?>
