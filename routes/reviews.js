const express = require('express');
const router = express.Router();
const { pool } = require('../db');
const { authenticate } = require('../middleware/auth');

// Submit or update review
router.post('/', authenticate, async (req, res) => {
  const { movie_id, rating, comment } = req.body;
  const user_id = req.user.id;

  try {
    if (!movie_id || !rating) {
      return res.status(400).json({ error: 'Movie ID and rating are required' });
    }

    if (rating < 1 || rating > 5) {
      return res.status(400).json({ error: 'Rating must be between 1 and 5' });
    }

    const connection = await pool.getConnection();

    // Check user's offense_count
    const [users] = await connection.query('SELECT offense_count FROM users WHERE id = ?', [user_id]);
    if (users.length && users[0].offense_count >= 3) {
      await connection.release();
      return res.status(403).json({ error: 'You can no longer comment due to repeated offenses.' });
    }

    // Check if movie exists
    const [movies] = await connection.query(
      'SELECT id FROM movies WHERE id = ?',
      [movie_id]
    );

    if (movies.length === 0) {
      await connection.release();
      return res.status(404).json({ error: 'Movie not found' });
    }

    // Insert or update review
    try {
      await connection.query(
        `INSERT INTO reviews (movie_id, user_id, rating, comment) 
         VALUES (?, ?, ?, ?) 
         ON DUPLICATE KEY UPDATE rating = ?, comment = ?`,
        [movie_id, user_id, rating, comment, rating, comment]
      );
    } catch (err) {
      // Handle duplicate key error
      if (err.code === 'ER_DUP_ENTRY') {
        await connection.query(
          'UPDATE reviews SET rating = ?, comment = ? WHERE movie_id = ? AND user_id = ?',
          [rating, comment, movie_id, user_id]
        );
      } else {
        throw err;
      }
    }

    await connection.release();
    res.status(201).json({ message: 'Review submitted successfully' });
  } catch (error) {
    console.error('Error submitting review:', error);
    res.status(500).json({ error: 'Failed to submit review' });
  }
});

// Get reviews for a movie
router.get('/movie/:movie_id', async (req, res) => {
  const { movie_id } = req.params;

  try {
    const connection = await pool.getConnection();

    const [reviews] = await connection.query(`
      SELECT r.id, r.rating, r.comment, r.created_at, u.username
      FROM reviews r
      JOIN users u ON r.user_id = u.id
      WHERE r.movie_id = ?
      ORDER BY r.created_at DESC
    `, [movie_id]);

    await connection.release();
    res.json(reviews);
  } catch (error) {
    console.error('Error fetching reviews:', error);
    res.status(500).json({ error: 'Failed to fetch reviews' });
  }
});

// Get user's review for a movie
router.get('/user/:movie_id', authenticate, async (req, res) => {
  const { movie_id } = req.params;
  const user_id = req.user.id;

  try {
    const connection = await pool.getConnection();

    const [reviews] = await connection.query(`
      SELECT id, rating, comment, created_at
      FROM reviews
      WHERE movie_id = ? AND user_id = ?
    `, [movie_id, user_id]);

    await connection.release();

    if (reviews.length === 0) {
      return res.status(404).json({ error: 'No review found' });
    }

    res.json(reviews[0]);
  } catch (error) {
    console.error('Error fetching review:', error);
    res.status(500).json({ error: 'Failed to fetch review' });
  }
});

// Increase offense count (admin only)
router.post('/:review_id/offense', authenticate, async (req, res) => {
  const { review_id } = req.params;

  try {
    // Check if user is admin
    if (!req.user.is_admin) {
      return res.status(403).json({ error: 'Admin access required' });
    }

    const connection = await pool.getConnection();

    // Get offender user
    const [reviews] = await connection.query(
      'SELECT user_id FROM reviews WHERE id = ?',
      [review_id]
    );

    if (reviews.length === 0) {
      await connection.release();
      return res.status(404).json({ error: 'Review not found' });
    }

    const offenderUserId = reviews[0].user_id;

    if (offenderUserId) {
      await connection.query('UPDATE users SET offense_count = offense_count + 1 WHERE id = ?', [offenderUserId]);
    }

    await connection.release();
    return res.json({ message: 'User offense count increased' });
  } catch (error) {
    console.error('Error increasing offense count:', error);
    res.status(500).json({ error: 'Failed to increase offense count' });
  }
});

module.exports = router;
