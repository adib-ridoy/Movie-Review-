-- Complete Database Setup for Movie Review System
-- This file contains all database modifications and the final schema
-- Created: December 30, 2025

-- Create database if it doesn't exist
-- CREATE DATABASE IF NOT EXISTS movie_review;
-- USE movie_review;

-- -- Drop existing tables to ensure clean setup
-- DROP TABLE IF EXISTS reviews;
-- DROP TABLE IF EXISTS movies;
-- DROP TABLE IF EXISTS users;

-- Create users table with offense tracking
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    offense_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create movies table with image path support
CREATE TABLE movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    genre VARCHAR(100) NOT NULL,
    release_year INT NOT NULL,
    description TEXT,
    image_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create reviews table (offense_count removed, only user-level tracking)
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_movie (user_id, movie_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (username: admin, password: admin123)
INSERT INTO users (username, password, email, is_admin, offense_count) 
VALUES ('admin', 'admin123', 'admin@cinerate.com', 1, 0);

-- Insert sample regular user
INSERT INTO users (username, password, email, is_admin, offense_count) 
VALUES ('john_doe', 'password123', 'john@example.com', 0, 0);

-- Insert sample movies
INSERT INTO movies (title, genre, release_year, description, image_path) VALUES
('The Shawshank Redemption', 'Drama', 1994, 'Two imprisoned men bond over a number of years, finding solace and eventual redemption through acts of common decency.', NULL),
('The Godfather', 'Crime', 1972, 'The aging patriarch of an organized crime dynasty transfers control of his clandestine empire to his reluctant son.', NULL),
('The Dark Knight', 'Action', 2008, 'When the menace known as the Joker wreaks havoc and chaos on the people of Gotham, Batman must accept one of the greatest psychological and physical tests of his ability to fight injustice.', NULL),
('Pulp Fiction', 'Crime', 1994, 'The lives of two mob hitmen, a boxer, a gangster and his wife intertwine in four tales of violence and redemption.', NULL),
('Forrest Gump', 'Drama', 1994, 'The presidencies of Kennedy and Johnson, the Vietnam War, and other historical events unfold from the perspective of an Alabama man with an IQ of 75.', NULL),
('Inception', 'Sci-Fi', 2010, 'A thief who steals corporate secrets through the use of dream-sharing technology is given the inverse task of planting an idea into the mind of a C.E.O.', NULL),
('The Matrix', 'Sci-Fi', 1999, 'A computer hacker learns from mysterious rebels about the true nature of his reality and his role in the war against its controllers.', NULL),
('Goodfellas', 'Crime', 1990, 'The story of Henry Hill and his life in the mob, covering his relationship with his wife Karen Hill and his mob partners.', NULL),
('The Silence of the Lambs', 'Thriller', 1991, 'A young FBI cadet must receive the help of an incarcerated and manipulative cannibal killer to help catch another serial killer.', NULL),
('Saving Private Ryan', 'War', 1998, 'Following the Normandy Landings, a group of U.S. soldiers go behind enemy lines to retrieve a paratrooper whose brothers have been killed in action.', NULL);

-- Insert sample reviews
INSERT INTO reviews (movie_id, user_id, rating, comment) VALUES
(1, 2, 5, 'One of the greatest films ever made. A masterpiece of storytelling and cinematography.'),
(2, 2, 5, 'An offer I cannot refuse! Brilliant acting and direction.'),
(3, 2, 5, 'Heath Ledger\'s performance as the Joker is unforgettable.'),
(4, 2, 4, 'Tarantino at his finest. Non-linear storytelling done right.'),
(5, 2, 5, 'Tom Hanks delivers an incredible performance. Emotional and uplifting.');

-- Create index for better query performance
CREATE INDEX idx_movie_title ON movies(title);
CREATE INDEX idx_movie_genre ON movies(genre);
CREATE INDEX idx_review_movie ON reviews(movie_id);
CREATE INDEX idx_review_user ON reviews(user_id);
CREATE INDEX idx_user_offense ON users(offense_count);

-- Summary of Key Changes:
-- 1. Added offense_count column to users table (user-level offense tracking)
-- 2. Removed offense_count from reviews table (no longer tracking per-comment)
-- 3. Added image_path column to movies table (for poster images)
-- 4. Plain text password storage (as requested, not recommended for production)
-- 5. Users with offense_count >= 3 are blocked from posting reviews (enforced in application logic)

-- Application Rules:
-- - Plain text passwords (bcrypt removed as requested)
-- - User offense tracking: incremented when admin marks review as offensive
-- - Users with 3+ offenses cannot post new reviews
-- - Movie posters auto-matched from img/ folder by normalized title
-- - Single search parameter searches both title and genre with OR logic

SELECT 'Database setup complete!' AS status;
