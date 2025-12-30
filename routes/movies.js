const express = require('express');
const router = express.Router();
const fs = require('fs');
const path = require('path');
const { pool } = require('../db');
const { authenticate, isAdmin } = require('../middleware/auth');
const upload = require('../middleware/upload');

// Helper: find an image in /img that matches a movie title
const IMG_DIR = path.join(__dirname, '..', 'img');
const exts = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];

function normalizeTitle(s) {
  return (s || '')
    .toString()
    .toLowerCase()
    .replace(/&/g, 'and')
    .replace(/[^a-z0-9\s_\-]+/g, '')
    .replace(/[\s_\-]+/g, '');
}

function buildImgIndex() {
  const index = new Map(); // normalized base -> filename
  if (!fs.existsSync(IMG_DIR)) return index;
  try {
    const files = fs.readdirSync(IMG_DIR);
    for (const f of files) {
      const ext = path.extname(f).toLowerCase();
      if (!exts.includes(ext)) continue;
      const base = path.basename(f, ext);
      index.set(normalizeTitle(base), f);
    }
  } catch (e) {
    console.error('Error indexing img folder:', e);
  }
  return index;
}

function findImageForTitle(title, index) {
  if (!title) return null;
  // Try exact filename matches first across common patterns
  const patterns = [
    (t) => t, // exact
    (t) => t.replace(/\s+/g, '_'),
    (t) => t.replace(/\s+/g, '-'),
    (t) => t.toLowerCase(),
    (t) => t.toLowerCase().replace(/\s+/g, '_'),
    (t) => t.toLowerCase().replace(/\s+/g, '-'),
  ];

  for (const mk of patterns) {
    const candBase = mk(title);
    for (const ext of exts) {
      const p = path.join(IMG_DIR, candBase + ext);
      try {
        if (fs.existsSync(p)) {
          return `/img/${candBase}${ext}`;
        }
      } catch { /* ignore */ }
    }
  }

  // Fallback to normalized index lookup (also try without leading 'the')
  const key = normalizeTitle(title);
  const altKey = normalizeTitle(title.replace(/^\s*the\s+/i, ''));
  const fn = index.get(key) || index.get(altKey);
  return fn ? `/img/${fn}` : null;
}

// Get all movies with average rating (with optional search filters)
router.get('/', async (req, res) => {
  try {
    const connection = await pool.getConnection();
    
    // Get search parameter
    const search = req.query.search ? `%${req.query.search}%` : null;
    
    let query = `
      SELECT m.id, m.title, m.genre, m.release_year, m.description, 
             AVG(r.rating) as avg_rating, COUNT(r.id) as review_count
      FROM movies m
      LEFT JOIN reviews r ON m.id = r.movie_id
    `;
    
    const params = [];
    
    // Add WHERE clause if search filter provided (searches both title and genre)
    if (search) {
      query += ' WHERE (m.title LIKE ? OR m.genre LIKE ?)';
      params.push(search, search);
    }
    
    query += ` GROUP BY m.id ORDER BY m.created_at DESC`;
    
    const [movies] = params.length > 0 
      ? await connection.query(query, params)
      : await connection.query(query);

    await connection.release();

    // Auto-assign image_path from img/ if missing
    const index = buildImgIndex();
    const withImages = movies.map(m => {
      if (!m.image_path) {
        const found = findImageForTitle(m.title, index);
        if (found) m.image_path = found;
      }
      return m;
    });

    res.json(withImages);
  } catch (error) {
    console.error('Error fetching movies:', error);
    res.status(500).json({ error: 'Failed to fetch movies' });
  }
});

// Get single movie with reviews
router.get('/:id', async (req, res) => {
  const { id } = req.params;

  try {
    const connection = await pool.getConnection();

    const [movies] = await connection.query(`
      SELECT m.*, 
             AVG(r.rating) as avg_rating, 
             COUNT(r.id) as review_count
      FROM movies m
      LEFT JOIN reviews r ON m.id = r.movie_id
      WHERE m.id = ?
      GROUP BY m.id
    `, [id]);

    if (movies.length === 0) {
      await connection.release();
      return res.status(404).json({ error: 'Movie not found' });
    }

    const [reviews] = await connection.query(`
      SELECT r.id, r.rating, r.comment, r.created_at, u.username
      FROM reviews r
      JOIN users u ON r.user_id = u.id
      WHERE r.movie_id = ?
      ORDER BY r.created_at DESC
    `, [id]);

    const movie = movies[0];
    // Auto-assign image_path from img/ if missing
    if (!movie.image_path) {
      const index = buildImgIndex();
      const found = findImageForTitle(movie.title, index);
      if (found) movie.image_path = found;
    }

    await connection.release();

    res.json({
      ...movie,
      reviews: reviews
    });
  } catch (error) {
    console.error('Error fetching movie:', error);
    res.status(500).json({ error: 'Failed to fetch movie' });
  }
});

// Add movie (admin only)
router.post('/', authenticate, isAdmin, upload.single('image'), async (req, res) => {
  const { title, genre, release_year, description } = req.body;
  let imagePath = null;

  try {
    if (!title || !genre || !release_year || !description) {
      if (req.file) {
        fs.unlinkSync(req.file.path);
      }
      return res.status(400).json({ error: 'All fields are required' });
    }

    if (title.length < 2) {
      if (req.file) {
        fs.unlinkSync(req.file.path);
      }
      return res.status(400).json({ error: 'Title must be at least 2 characters' });
    }

    if (release_year < 1800 || release_year > new Date().getFullYear() + 5) {
      if (req.file) {
        fs.unlinkSync(req.file.path);
      }
      return res.status(400).json({ error: 'Invalid release year' });
    }

    // Save image path if file was uploaded
    if (req.file) {
      imagePath = `/uploads/${req.file.filename}`;
    }

    const connection = await pool.getConnection();

    await connection.query(
      'INSERT INTO movies (title, genre, release_year, description, image_path, created_by) VALUES (?, ?, ?, ?, ?, ?)',
      [title, genre, release_year, description, imagePath, req.user.id]
    );

    await connection.release();
    res.status(201).json({ message: 'Movie added successfully' });
  } catch (error) {
    // Clean up uploaded file if error occurs
    if (req.file) {
      try {
        fs.unlinkSync(req.file.path);
      } catch (e) {
        console.error('Error deleting uploaded file:', e);
      }
    }
    console.error('Error adding movie:', error);
    res.status(500).json({ error: 'Failed to add movie' });
  }
});

// Update movie (admin only)
router.put('/:id', authenticate, isAdmin, upload.single('image'), async (req, res) => {
  const { id } = req.params;
  const { title, genre, release_year, description } = req.body;
  let imagePath = null;

  try {
    if (!title || !genre || !release_year || !description) {
      if (req.file) {
        fs.unlinkSync(req.file.path);
      }
      return res.status(400).json({ error: 'All fields are required' });
    }

    if (title.length < 2) {
      if (req.file) {
        fs.unlinkSync(req.file.path);
      }
      return res.status(400).json({ error: 'Title must be at least 2 characters' });
    }

    if (release_year < 1800 || release_year > new Date().getFullYear() + 5) {
      if (req.file) {
        fs.unlinkSync(req.file.path);
      }
      return res.status(400).json({ error: 'Invalid release year' });
    }

    const connection = await pool.getConnection();

    // Get current movie to check old image
    const [currentMovie] = await connection.query('SELECT image_path FROM movies WHERE id = ?', [id]);
    
    if (currentMovie.length === 0) {
      if (req.file) {
        fs.unlinkSync(req.file.path);
      }
      await connection.release();
      return res.status(404).json({ error: 'Movie not found' });
    }

    // If new image is uploaded, use it; otherwise keep old one
    if (req.file) {
      imagePath = `/uploads/${req.file.filename}`;
      // Delete old image if it exists
      if (currentMovie[0].image_path) {
        const oldImagePath = path.join(__dirname, '..', 'public', currentMovie[0].image_path);
        try {
          if (fs.existsSync(oldImagePath)) {
            fs.unlinkSync(oldImagePath);
          }
        } catch (e) {
          console.error('Error deleting old image:', e);
        }
      }
    } else {
      imagePath = currentMovie[0].image_path;
    }

    await connection.query(
      'UPDATE movies SET title = ?, genre = ?, release_year = ?, description = ?, image_path = ? WHERE id = ?',
      [title, genre, release_year, description, imagePath, id]
    );

    await connection.release();
    res.json({ message: 'Movie updated successfully' });
  } catch (error) {
    if (req.file) {
      try {
        fs.unlinkSync(req.file.path);
      } catch (e) {
        console.error('Error deleting uploaded file:', e);
      }
    }
    console.error('Error updating movie:', error);
    res.status(500).json({ error: 'Failed to update movie' });
  }
});

// Delete movie (admin only)
router.delete('/:id', authenticate, isAdmin, async (req, res) => {
  const { id } = req.params;

  try {
    const connection = await pool.getConnection();

    // Get movie to delete associated image
    const [movies] = await connection.query('SELECT image_path FROM movies WHERE id = ?', [id]);
    
    if (movies.length === 0) {
      await connection.release();
      return res.status(404).json({ error: 'Movie not found' });
    }

    const movie = movies[0];

    // Delete associated reviews first (due to foreign key constraint)
    await connection.query('DELETE FROM reviews WHERE movie_id = ?', [id]);

    // Delete movie
    await connection.query('DELETE FROM movies WHERE id = ?', [id]);

    // Delete image if it exists
    if (movie.image_path) {
      const imagePath = path.join(__dirname, '..', 'public', movie.image_path);
      try {
        if (fs.existsSync(imagePath)) {
          fs.unlinkSync(imagePath);
        }
      } catch (e) {
        console.error('Error deleting image:', e);
      }
    }

    await connection.release();
    res.json({ message: 'Movie deleted successfully' });
  } catch (error) {
    console.error('Error deleting movie:', error);
    res.status(500).json({ error: 'Failed to delete movie' });
  }
});

// Auto-link posters to movies by filename (admin only)
router.post('/assign-posters', authenticate, isAdmin, async (req, res) => {
  // Helper to normalize names for matching
  const normalize = (s) => (s || '')
    .toString()
    .toLowerCase()
    .replace(/\.(jpg|jpeg|png|gif|webp)$/i, '') // drop extension if present
    .replace(/&/g, 'and')
    .replace(/[^a-z0-9\s_\-]+/g, '') // remove punctuation
    .replace(/[\s_\-]+/g, ''); // collapse separators

  // Build a map of filename -> public URL
  const filesMap = new Map();

  const candidates = [
    { dir: path.join(__dirname, '..', 'public', 'uploads'), urlPrefix: '/uploads' },
    { dir: path.join(__dirname, '..', 'img'), urlPrefix: '/img' },
  ];

  for (const c of candidates) {
    try {
      if (fs.existsSync(c.dir)) {
        const files = fs.readdirSync(c.dir);
        for (const f of files) {
          const ext = path.extname(f).toLowerCase();
          if (!['.jpg', '.jpeg', '.png', '.gif', '.webp'].includes(ext)) continue;
          const base = path.basename(f, ext);
          filesMap.set(normalize(base), `${c.urlPrefix}/${f}`);
        }
      }
    } catch (e) {
      console.error('Error scanning images in', c.dir, e);
    }
  }

  let updated = 0;
  const matched = [];

  try {
    const connection = await pool.getConnection();
    const [movies] = await connection.query('SELECT id, title, image_path FROM movies');

    for (const m of movies) {
      const key = normalize(m.title);
      const altKey = normalize(m.title.replace(/^\s*the\s+/i, '')); // allow matching without leading 'The'
      let url = filesMap.get(key) || filesMap.get(altKey);
      if (url && url !== m.image_path) {
        await connection.query('UPDATE movies SET image_path = ? WHERE id = ?', [url, m.id]);
        updated++;
        matched.push({ id: m.id, title: m.title, image_path: url });
      }
    }

    await connection.release();
    return res.json({ updated, matched });
  } catch (error) {
    console.error('Error assigning posters:', error);
    return res.status(500).json({ error: 'Failed to assign posters' });
  }
});

module.exports = router;
