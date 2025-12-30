<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? '';
$message = '';
$error = '';

// Fetch current user info
$stmt = $conn->prepare("SELECT username, email, offense_count FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($new_username) || empty($email)) {
        $error = 'Username and email are required!';
    } elseif (strlen($new_username) < 3) {
        $error = 'Username must be at least 3 characters!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format!';
    } elseif (!empty($new_password) || !empty($confirm_password)) {
        // Only validate password if one is being changed
        if (empty($new_password) || empty($confirm_password)) {
            $error = 'Both password fields must be filled if changing password!';
        } elseif (strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters!';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Passwords do not match!';
        }
    }

    if (empty($error)) {
        // Check if new username is unique (if changing)
        if ($new_username !== $user['username']) {
            $check = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $check->bind_param("si", $new_username, $user_id);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $error = 'Username already taken!';
            }
            $check->close();
        }

        // Check if email is unique (if changing)
        if (empty($error) && $email !== $user['email']) {
            $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check->bind_param("si", $email, $user_id);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $error = 'Email already registered!';
            }
            $check->close();
        }

        if (empty($error)) {
            if (!empty($new_password)) {
                // Update all fields including password
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
                $stmt->bind_param("sssi", $new_username, $email, $new_password, $user_id);
            } else {
                // Update only username and email
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                $stmt->bind_param("ssi", $new_username, $email, $user_id);
            }

            if ($stmt->execute()) {
                // Update session if username changed
                if ($new_username !== $_SESSION['username']) {
                    $_SESSION['username'] = $new_username;
                }
                $message = 'Profile updated successfully!';
                $user['username'] = $new_username;
                $user['email'] = $email;
            } else {
                $error = 'Error updating profile. Please try again!';
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
    <title>Edit Profile - CineRate</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <h1 class="logo">🎬 CineRate</h1>
            <ul class="nav-links">
                <li><a href="../index.php">Home</a></li>
                <?php
                if (isset($_SESSION['user_id'])) {
                    if ($_SESSION['is_admin']) {
                        echo '<li><a href="../admin/add_movie.php">Add Movie</a></li>';
                        echo '<li><a href="../admin/manage_movies.php">Manage Movies</a></li>';
                    }
                    echo '<li><a href="profile.php">Profile</a></li>';
                    echo '<li><a href="logout.php">Logout</a></li>';
                } else {
                    echo '<li><a href="login.php">Login</a></li>';
                    echo '<li><a href="register.php">Register</a></li>';
                }
                ?>
            </ul>
        </div>
    </nav>

    <div class="form-container">
        <h2>Edit Profile</h2>

        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($message)): ?>
            <div class="success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php
        // Refresh offense count from database (in case admin updated it)
        $refreshStmt = $conn->prepare("SELECT offense_count FROM users WHERE id = ?");
        $refreshStmt->bind_param("i", $user_id);
        $refreshStmt->execute();
        $refreshResult = $refreshStmt->get_result();
        $refreshData = $refreshResult->fetch_assoc();
        $refreshStmt->close();
        
        if ($refreshData) {
            $user['offense_count'] = $refreshData['offense_count'];
        }
        ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="form-group">
                <label>Offense Count:</label>
                <div style="padding: 0.75rem; background: #f5f5f5; border-radius: 4px; color: #333; font-weight: bold;">
                    <?php
                    $offense_count = intval($user['offense_count'] ?? 0);
                    if ($offense_count === 0) {
                        echo '<span style="color: #28a745;">✓ No offenses</span>';
                    } elseif ($offense_count >= 3) {
                        echo '<span style="color: #e74c3c;">⚠ ' . $offense_count . ' offense(s) - Account Blocked from commenting</span>';
                    } else {
                        echo '<span style="color: #f39c12;">⚠ ' . $offense_count . ' offense(s)</span>';
                    }
                    ?>
                </div>
                <p style="font-size: 0.85rem; color: #666; margin-top: 0.5rem;">You cannot edit this value. Only admins can modify offense counts. (Refreshed on page load)</p>
            </div>

            <div class="form-group">
                <label for="new_password">New Password (leave blank to keep current):</label>
                <input type="password" id="new_password" name="new_password">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password">
            </div>

            <button type="submit" class="submit-btn">Update Profile</button>
        </form>

        <p style="text-align: center; margin-top: 2rem;">
            <a href="../index.php">Back to Home</a>
        </p>
    </div>

    <footer>
        <p>&copy; 2025 CineRate - Movie Review Site. All rights reserved.</p>
    </footer>
</body>
</html>
