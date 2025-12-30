<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: ../index.php');
    exit;
}
require_once '../config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Movies - CineRate Admin</title>
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

  <div class="container">
    <h2>Manage Movies</h2>
    
    <?php
    // Display success/error messages
    if (isset($_GET['success']) && $_GET['success'] === 'movie_deleted') {
        echo '<div style="background:#d4edda;color:#155724;padding:1rem;border-radius:4px;margin-bottom:1rem;border:1px solid #c3e6cb;">Movie deleted successfully!</div>';
    } elseif (isset($_GET['error'])) {
        $error = htmlspecialchars($_GET['error']);
        $messages = [
            'invalid_movie' => 'Invalid movie ID.',
            'delete_failed' => 'Failed to delete the movie.',
            'movie_not_found' => 'Movie not found.'
        ];
        $msg = $messages[$error] ?? 'An error occurred.';
        echo '<div style="background:#f8d7da;color:#721c24;padding:1rem;border-radius:4px;margin-bottom:1rem;border:1px solid #f5c6cb;">' . htmlspecialchars($msg) . '</div>';
    }
    ?>
    
    <div class="movies-table" style="background:#1a1a1a;border:1px solid #333;border-radius:8px;overflow:hidden;">
      <table style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="background:#262626;color:#ff6b00;">
            <th style="padding:1rem;text-align:left;">Title</th>
            <th style="padding:1rem;text-align:left;">Genre</th>
            <th style="padding:1rem;text-align:left;">Year</th>
            <th style="padding:1rem;text-align:left;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $res = $conn->query("SELECT id, title, genre, release_year FROM movies ORDER BY created_at DESC");
          if ($res && $res->num_rows > 0) {
              while ($row = $res->fetch_assoc()) {
                  echo '<tr style="border-bottom:1px solid #333;color:#e0e0e0;">';
                  echo '<td style="padding:1rem;">' . htmlspecialchars($row['title']) . '</td>';
                  echo '<td style="padding:1rem;">' . htmlspecialchars($row['genre']) . '</td>';
                  echo '<td style="padding:1rem;">' . intval($row['release_year']) . '</td>';
                  echo '<td style="padding:1rem;display:flex;gap:0.5rem;align-items:center;">'
                      . '<a class="btn-edit" style="background:#4a8fff;color:#fff;padding:.5rem 1rem;border-radius:4px;text-decoration:none;display:inline-block;line-height:1;" '
                      . 'href="edit_movie.php?id=' . intval($row['id']) . '">Edit</a>'
                      . '<form method="POST" action="delete_movie.php" style="display:inline;" onsubmit="return confirm(\'Are you sure you want to delete this movie? This action cannot be undone.\');">'
                      . '<input type="hidden" name="movie_id" value="' . intval($row['id']) . '">'
                      . '<button type="submit" style="background:#e74c3c;color:#fff;padding:.5rem 1rem;border-radius:4px;border:none;cursor:pointer;display:inline-block;line-height:1;font-size:1rem;">Delete</button>'
                      . '</form>'
                      . '</td>';
                  echo '</tr>';
              }
          } else {
              echo '<tr><td colspan="4" style="padding:1rem;text-align:center;color:#888;">No movies found.</td></tr>';
          }
          $conn->close();
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <footer>
    <p>&copy; 2025 CineRate - Movie Review Site. All rights reserved.</p>
  </footer>
</body>
</html>
