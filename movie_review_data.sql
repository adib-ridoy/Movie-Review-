-- Movie Review Database - Sample Data Only
-- This file contains INSERT statements to populate the movie_review database
-- Use this file to import data via phpMyAdmin

-- Insert sample users (plain text passwords: "password123")
INSERT IGNORE INTO users (id, username, email, password, is_admin) VALUES 
(1, 'admin', 'admin@example.com', 'password123', 1),
(2, 'user1', 'user1@example.com', 'password123', 0),
(3, 'user2', 'user2@example.com', 'password123', 0);

-- Insert sample movies
INSERT IGNORE INTO movies (id, title, genre, release_year, description, created_by) VALUES 
(1, 'Inception', 'Sci-Fi', 2010, 'A thief who steals corporate secrets through dream-sharing technology is tasked with the inverse: planting an idea into the mind of a C.E.O.', 1),
(2, 'The Dark Knight', 'Action', 2008, 'When the menace known as the Joker wreaks havoc and chaos on the people of Gotham, Batman must accept one of the greatest psychological and physical tests of his ability to fight injustice.', 1),
(3, 'Interstellar', 'Adventure', 2014, 'A team of explorers travel through a wormhole in space in an attempt to ensure humanity\s survival.', 1),
(4, 'The Shawshank Redemption', 'Drama', 1994, 'Two imprisoned men bond over a number of years, finding solace and eventual redemption through acts of common decency.', 1),
(5, 'Pulp Fiction', 'Crime', 1994, 'The lives of two mob hitmen, a boxer, a gangster and his wife intertwine in four tales of violence and redemption.', 1),
(6, 'Forrest Gump', 'Drama', 1994, 'The presidencies of Kennedy and Johnson unfold from the perspective of an Alabama man with an IQ of 75.', 1),
(7, 'The Matrix', 'Sci-Fi', 1999, 'A computer programmer discovers that reality as he knows it is a simulation created by machines to distract humans.', 1),
(8, 'Gladiator', 'Action', 2000, 'A former Roman General sets out to exact vengeance against the corrupt emperor who murdered his family and sent him into slavery.', 1);

-- Insert sample reviews
INSERT IGNORE INTO reviews (id, movie_id, user_id, rating, comment) VALUES 
(1, 1, 2, 5, 'Mind-bending masterpiece! Nolan truly outdid himself with this film.'),
(2, 1, 3, 4, 'Great plot and special effects, but a bit confusing at times.'),
(3, 2, 2, 5, 'The best Batman film ever made. Heath Ledger is absolutely phenomenal!'),
(4, 2, 3, 5, 'Dark, intense, and brilliant. A true classic.'),
(5, 3, 2, 5, 'Absolutely breathtaking. The cinematography and story are incredible.'),
(6, 3, 3, 4, 'Epic space journey. Some parts felt long but overall amazing.'),
(7, 4, 2, 5, 'A timeless masterpiece about redemption and hope.'),
(8, 4, 3, 5, 'One of the greatest films ever made. Simply outstanding.'),
(9, 5, 2, 5, 'Tarantino at his finest. Innovative storytelling and unforgettable scenes.'),
(10, 5, 3, 4, 'Very well written and acted, though quite violent.'),
(11, 6, 2, 5, 'A heartwarming classic that never gets old.'),
(12, 6, 3, 4, 'Great feel-good movie with a touching story.'),
(13, 7, 2, 5, 'Revolutionary sci-fi film that changed cinema forever.'),
(14, 7, 3, 5, 'Incredible action and groundbreaking effects.'),
(15, 8, 2, 5, 'Epic and emotional. Russell Crowe delivers a powerful performance.'),
(16, 8, 3, 4, 'Great historical drama with stunning battle scenes.');
