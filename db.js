const mysql = require('mysql2/promise');
require('dotenv').config();

const pool = mysql.createPool({
  host: process.env.DB_HOST,
  user: process.env.DB_USER,
  password: process.env.DB_PASSWORD,
  database: process.env.DB_DATABASE,
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0
});

// Initialize database and create tables
async function initializeDatabase() {
  let connection;
  try {
    // Connect without selecting database first
    const tempPool = mysql.createPool({
      host: process.env.DB_HOST,
      user: process.env.DB_USER,
      password: process.env.DB_PASSWORD,
      waitForConnections: true,
      connectionLimit: 1,
      queueLimit: 0
    });

    connection = await tempPool.getConnection();
    
    // Create database if not exists
    await connection.query(
      `CREATE DATABASE IF NOT EXISTS ${process.env.DB_DATABASE}`
    );

    await connection.release();
    await tempPool.end();

    // Now connect to the database
    connection = await pool.getConnection();

    // Create users table
    await connection.query(`
      CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL,
        is_admin INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `);

    // Create movies table
    await connection.query(`
      CREATE TABLE IF NOT EXISTS movies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        genre VARCHAR(100),
        release_year INT,
        image_path VARCHAR(255),
        image_data LONGBLOB,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id)
      )
    `);

    // Create reviews table
    await connection.query(`
      CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        movie_id INT NOT NULL,
        user_id INT NOT NULL,
        rating INT CHECK (rating >= 1 AND rating <= 5),
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_review (movie_id, user_id)
      )
    `);

    // Seed initial admin/user accounts and a few movies if the tables are empty
    const seedPassword = process.env.SEED_PASSWORD || 'changeme123';
    const adminUsername = process.env.SEED_ADMIN_USERNAME || 'admin';
    const adminEmail = process.env.SEED_ADMIN_EMAIL || 'admin@example.com';
    const userUsername = process.env.SEED_USER_USERNAME || 'user';
    const userEmail = process.env.SEED_USER_EMAIL || 'user@example.com';

    const [existingAdmin] = await connection.query(
      'SELECT id FROM users WHERE username = ? LIMIT 1',
      [adminUsername]
    );

    let adminId;

    if (existingAdmin.length === 0) {
      const [result] = await connection.query(
        'INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, 1)',
        [adminUsername, adminEmail, seedPassword]
      );
      adminId = result.insertId;
      console.log(`Seeded admin account ${adminUsername}`);
    } else {
      adminId = existingAdmin[0].id;
    }

    const [existingUser] = await connection.query(
      'SELECT id FROM users WHERE username = ? LIMIT 1',
      [userUsername]
    );

    if (existingUser.length === 0) {
      await connection.query(
        'INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, 0)',
        [userUsername, userEmail, seedPassword]
      );
      console.log(`Seeded standard user ${userUsername}`);
    }

    const [movieCountRows] = await connection.query(
      'SELECT COUNT(*) AS count FROM movies'
    );

    if (movieCountRows[0].count === 0) {
      const sampleMovies = [
        {
          title: 'Inception',
          genre: 'Sci-Fi',
          release_year: 2010,
          description: 'A thief who steals corporate secrets through dream-sharing tech is tasked with planting an idea.'
        },
        {
          title: 'The Dark Knight',
          genre: 'Action',
          release_year: 2008,
          description: 'Batman faces the Joker, a criminal mastermind who plunges Gotham into chaos.'
        },
        {
          title: 'Interstellar',
          genre: 'Adventure',
          release_year: 2014,
          description: 'Explorers travel through a wormhole in space to ensure humanity’s survival.'
        }
      ];

      for (const movie of sampleMovies) {
        await connection.query(
          'INSERT INTO movies (title, genre, release_year, description, created_by) VALUES (?, ?, ?, ?, ?)',
          [movie.title, movie.genre, movie.release_year, movie.description, adminId]
        );
      }

      console.log('Seeded sample movies');
    }

    await connection.release();
    console.log('Database initialized successfully!');
  } catch (error) {
    console.error('Error initializing database:', error);
    throw error;
  }
}

module.exports = { pool, initializeDatabase };
