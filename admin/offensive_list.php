<?php
session_start();

// Check if user is admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: ../index.php');
    exit;
}

require_once '../config.php';

$message = '';
$error = '';

// Handle offense count update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['new_offense_count'])) {
    $user_id = intval($_POST['user_id']);
    $new_offense_count = intval($_POST['new_offense_count']);
    
    if ($user_id > 0 && $new_offense_count >= 0) {
        $stmt = $conn->prepare("UPDATE users SET offense_count = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_offense_count, $user_id);
        
        if ($stmt->execute()) {
            $message = 'Offense count updated successfully!';
        } else {
            $error = 'Failed to update offense count.';
        }
        $stmt->close();
    } else {
        $error = 'Invalid user ID or offense count.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Offensive List - CineRate Admin</title>
  <link rel="stylesheet" href="../public/css/style.css">
  <style>
    .offensive-list-table {
      background: #1a1a1a;
      border: 1px solid #333;
      border-radius: 8px;
      overflow: hidden;
      margin-top: 2rem;
    }
    
    .offensive-list-table table {
      width: 100%;
      border-collapse: collapse;
    }
    
    .offensive-list-table thead {
      background: #262626;
      color: #ff6b00;
    }
    
    .offensive-list-table th {
      padding: 1rem;
      text-align: left;
      font-weight: bold;
    }
    
    .offensive-list-table td {
      padding: 1rem;
      border-bottom: 1px solid #333;
      color: #e0e0e0;
    }
    
    .offensive-list-table tr:hover {
      background: #252525;
    }
    
    .offense-edit-form {
      display: flex;
      gap: 0.5rem;
      align-items: center;
    }
    
    .offense-edit-form input {
      width: 80px;
      padding: 0.5rem;
      background: #262626;
      border: 1px solid #444;
      color: #e0e0e0;
      border-radius: 4px;
    }
    
    .offense-edit-form button {
      padding: 0.5rem 1rem;
      background: #4a8fff;
      color: #fff;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: bold;
    }
    
    .offense-edit-form button:hover {
      background: #357ae8;
    }
    
    .message {
      padding: 1rem;
      border-radius: 4px;
      margin-bottom: 1rem;
      border: 1px solid;
    }
    
    .message.success {
      background: #d4edda;
      color: #155724;
      border-color: #c3e6cb;
    }
    
    .message.error {
      background: #f8d7da;
      color: #721c24;
      border-color: #f5c6cb;
    }
    
    .no-users {
      text-align: center;
      padding: 2rem;
      color: #888;
    }
  </style>
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
    <h2>User Offensive List</h2>
    <p style="color: #888;">Users with offense count of 1 or more</p>
    
    <?php if ($message): ?>
      <div class="message success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
      <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="offensive-list-table">
      <table>
        <thead>
          <tr>
            <th>User ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Offense Count</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $res = $conn->query("SELECT id, username, email, offense_count FROM users WHERE offense_count >= 1 ORDER BY offense_count DESC, username ASC");
          
          if ($res && $res->num_rows > 0) {
              while ($row = $res->fetch_assoc()) {
                  echo '<tr>';
                  echo '<td>' . intval($row['id']) . '</td>';
                  echo '<td>' . htmlspecialchars($row['username']) . '</td>';
                  echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                  echo '<td>';
                  echo '<form method="POST" action="offensive_list.php" class="offense-edit-form">';
                  echo '<input type="number" name="new_offense_count" value="' . intval($row['offense_count']) . '" min="0" max="100" required>';
                  echo '<input type="hidden" name="user_id" value="' . intval($row['id']) . '">';
                  echo '<button type="submit">Update</button>';
                  echo '</form>';
                  echo '</td>';
                  echo '<td>';
                  // Show status badge
                  if ($row['offense_count'] >= 3) {
                      echo '<span style="background:#e74c3c;color:#fff;padding:0.25rem 0.75rem;border-radius:4px;font-size:0.85rem;font-weight:bold;">BLOCKED</span>';
                  } else {
                      echo '<span style="background:#f39c12;color:#fff;padding:0.25rem 0.75rem;border-radius:4px;font-size:0.85rem;font-weight:bold;">WARNING</span>';
                  }
                  echo '</td>';
                  echo '</tr>';
              }
          } else {
              echo '<tr><td colspan="5" class="no-users">No users with offenses found.</td></tr>';
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
