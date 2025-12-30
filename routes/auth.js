const express = require('express');
const router = express.Router();
// NOTE: Plain text passwords as requested. This removes bcrypt usage.
const jwt = require('jsonwebtoken');
const { pool } = require('../db');

// Register
router.post('/register', async (req, res) => {
  const { username, email, password, confirmPassword } = req.body;

  try {
    // Validation
    if (!username || !email || !password || !confirmPassword) {
      return res.status(400).json({ error: 'All fields are required' });
    }

    if (username.length < 3) {
      return res.status(400).json({ error: 'Username must be at least 3 characters' });
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      return res.status(400).json({ error: 'Invalid email format' });
    }

    if (password !== confirmPassword) {
      return res.status(400).json({ error: 'Passwords do not match' });
    }

    if (password.length < 6) {
      return res.status(400).json({ error: 'Password must be at least 6 characters' });
    }

    const connection = await pool.getConnection();

    // Check if username or email exists
    const [existing] = await connection.query(
      'SELECT id FROM users WHERE username = ? OR email = ?',
      [username, email]
    );

    if (existing.length > 0) {
      await connection.release();
      return res.status(400).json({ error: 'Username or email already exists' });
    }

    // Insert user with plain text password (not recommended for production)
    await connection.query(
      'INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, ?)',
      [username, email, password, 0]
    );

    await connection.release();
    res.status(201).json({ message: 'User registered successfully' });
  } catch (error) {
    console.error('Registration error:', error);
    res.status(500).json({ error: 'Registration failed' });
  }
});

// Login
router.post('/login', async (req, res) => {
  const { username, password } = req.body;

  try {
    if (!username || !password) {
      return res.status(400).json({ error: 'Username and password are required' });
    }

    const connection = await pool.getConnection();

    const [users] = await connection.query(
      'SELECT id, username, password, is_admin FROM users WHERE username = ?',
      [username]
    );

    if (users.length === 0) {
      await connection.release();
      return res.status(401).json({ error: 'Invalid username or password' });
    }

    const user = users[0];
    // Plain text comparison
    if (password !== user.password) {
      await connection.release();
      return res.status(401).json({ error: 'Invalid username or password' });
    }

    const token = jwt.sign(
      { id: user.id, username: user.username, is_admin: user.is_admin },
      process.env.JWT_SECRET,
      { expiresIn: '24h' }
    );

    await connection.release();
    res.json({ token, user: { id: user.id, username: user.username, is_admin: user.is_admin } });
  } catch (error) {
    console.error('Login error:', error);
    res.status(500).json({ error: 'Login failed' });
  }
});

// Middleware to verify JWT token
const verifyToken = (req, res, next) => {
  const token = req.headers.authorization?.split(' ')[1];
  if (!token) {
    return res.status(401).json({ error: 'No token provided' });
  }
  try {
    const decoded = jwt.verify(token, process.env.JWT_SECRET);
    req.user = decoded;
    next();
  } catch (error) {
    return res.status(401).json({ error: 'Invalid token' });
  }
};

// Get user profile
router.get('/profile', verifyToken, async (req, res) => {
  try {
    const connection = await pool.getConnection();
    const [users] = await connection.query(
      'SELECT id, username, email FROM users WHERE id = ?',
      [req.user.id]
    );
    await connection.release();

    if (users.length === 0) {
      return res.status(404).json({ error: 'User not found' });
    }

    res.json(users[0]);
  } catch (error) {
    console.error('Profile fetch error:', error);
    res.status(500).json({ error: 'Failed to fetch profile' });
  }
});

// Update user profile
router.put('/profile', verifyToken, async (req, res) => {
  const { username, email, password, newPassword, confirmPassword } = req.body;

  try {
    // Validation
    if (!username || !email) {
      return res.status(400).json({ error: 'Username and email are required' });
    }

    if (username.length < 3) {
      return res.status(400).json({ error: 'Username must be at least 3 characters' });
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      return res.status(400).json({ error: 'Invalid email format' });
    }

    const connection = await pool.getConnection();

    // Get current user
    const [users] = await connection.query(
      'SELECT password FROM users WHERE id = ?',
      [req.user.id]
    );

    if (users.length === 0) {
      await connection.release();
      return res.status(404).json({ error: 'User not found' });
    }

    // Verify current password if password change requested
    if (newPassword) {
      if (!password) {
        await connection.release();
        return res.status(400).json({ error: 'Current password required to change password' });
      }
      if (password !== users[0].password) {
        await connection.release();
        return res.status(400).json({ error: 'Current password is incorrect' });
      }
      if (newPassword !== confirmPassword) {
        await connection.release();
        return res.status(400).json({ error: 'New passwords do not match' });
      }
      if (newPassword.length < 6) {
        await connection.release();
        return res.status(400).json({ error: 'New password must be at least 6 characters' });
      }
    }

    // Check if username or email already exists (excluding current user)
    const [existing] = await connection.query(
      'SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?',
      [username, email, req.user.id]
    );

    if (existing.length > 0) {
      await connection.release();
      return res.status(400).json({ error: 'Username or email already in use' });
    }

    // Update user
    const updatePassword = newPassword || users[0].password;
    await connection.query(
      'UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?',
      [username, email, updatePassword, req.user.id]
    );

    await connection.release();
    res.json({ message: 'Profile updated successfully' });
  } catch (error) {
    console.error('Profile update error:', error);
    res.status(500).json({ error: 'Failed to update profile' });
  }
});

module.exports = router;
