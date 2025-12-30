-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 29, 2025 at 08:28 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `movie_review`
--

-- --------------------------------------------------------

--
-- Table structure for table `movies`
--

CREATE TABLE `movies` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `genre` varchar(100) DEFAULT NULL,
  `release_year` int(11) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `image_data` longblob DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `movies`
--

INSERT INTO `movies` (`id`, `title`, `description`, `genre`, `release_year`, `image_path`, `image_data`, `created_by`, `created_at`) VALUES
(1, 'Inception', 'A thief who steals corporate secrets through dream-sharing technology is tasked with the inverse: planting an idea into the mind of a C.E.O.', 'Sci-Fi', 2010, 'img/inception-1.jpg', NULL, 1, '2025-12-29 16:35:41'),
(2, 'The Dark Knight', 'When the menace known as the Joker wreaks havoc and chaos on the people of Gotham, Batman must accept one of the greatest psychological and physical tests of his ability to fight injustice.', 'Action', 2008, 'img/the-dark-knight.jpg', NULL, 1, '2025-12-29 16:35:41'),
(3, 'Interstellar', 'A team of explorers travel through a wormhole in space in an attempt to ensure humanity\'s survival.', 'Adventure', 2014, 'img/interstellar-1.jpg', NULL, 1, '2025-12-29 16:35:41'),
(4, 'The Shawshank Redemption', 'Two imprisoned men bond over a number of years, finding solace and eventual redemption through acts of common decency.', 'Drama', 1994, 'img/the-shawshank-redemption.jpg', NULL, 1, '2025-12-29 16:35:41'),
(5, 'Pulp Fiction', 'The lives of two mob hitmen, a boxer, a gangster and his wife intertwine in four tales of violence and redemption.', 'Crime', 1994, 'img/pulp-fiction.jpg', NULL, 1, '2025-12-29 16:35:41'),
(6, 'Forrest Gump', 'The presidencies of Kennedy and Johnson unfold from the perspective of an Alabama man with an IQ of 75.', 'Drama', 1994, 'img/forrest-gump.jpg', NULL, 1, '2025-12-29 16:35:41'),
(7, 'The Matrix', 'A computer programmer discovers that reality as he knows it is a simulation created by machines to distract humans.', 'Sci-Fi', 1999, 'img/the-matrix.jpg', NULL, 1, '2025-12-29 16:35:41'),
(8, 'Gladiator', 'A former Roman General sets out to exact vengeance against the corrupt emperor who murdered his family and sent him into slavery.', 'Action', 2000, 'img/gladiator-1.jpg', NULL, 1, '2025-12-29 16:35:41'),
(9, 'Knives Out', 'Released in 2019, Knives Out is a modern \"whodunnit\" murder mystery written and directed by Rian Johnson. The film is celebrated for its twist-filled plot, sharp social commentary, and an all-star ensemble cast.', 'Mystery/Thriller', 2019, 'img/knives-out.jpg', NULL, 1, '2025-12-29 17:52:39');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `movie_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 2, 5, 'Mind-bending masterpiece! Nolan truly outdid himself with this film.', '2025-12-29 16:35:41'),
(2, 1, 3, 4, 'Great plot and special effects, but a bit confusing at times.', '2025-12-29 16:35:41'),
(4, 2, 3, 5, 'Dark, intense, and brilliant. A true classic.', '2025-12-29 16:35:41'),
(5, 3, 2, 5, 'Absolutely breathtaking. The cinematography and story are incredible.', '2025-12-29 16:35:41'),
(6, 3, 3, 4, 'Epic space journey. Some parts felt long but overall amazing.', '2025-12-29 16:35:41'),
(7, 4, 2, 5, 'A timeless masterpiece about redemption and hope.', '2025-12-29 16:35:41'),
(8, 4, 3, 5, 'One of the greatest films ever made. Simply outstanding.', '2025-12-29 16:35:41'),
(9, 5, 2, 5, 'Tarantino at his finest. Innovative storytelling and unforgettable scenes.', '2025-12-29 16:35:41'),
(10, 5, 3, 4, 'Very well written and acted, though quite violent.', '2025-12-29 16:35:41'),
(11, 6, 2, 5, 'A heartwarming classic that never gets old.', '2025-12-29 16:35:41'),
(12, 6, 3, 4, 'Great feel-good movie with a touching story.', '2025-12-29 16:35:41'),
(13, 7, 2, 5, 'Revolutionary sci-fi film that changed cinema forever.', '2025-12-29 16:35:41'),
(14, 7, 3, 5, 'Incredible action and groundbreaking effects.', '2025-12-29 16:35:41'),
(15, 8, 2, 5, 'Epic and emotional. Russell Crowe delivers a powerful performance.', '2025-12-29 16:35:41'),
(16, 8, 3, 4, 'Great historical drama with stunning battle scenes.', '2025-12-29 16:35:41');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `is_admin` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `offense_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `is_admin`, `created_at`, `offense_count`) VALUES
(1, 'admin', 'password123', 'admin@example.com', 1, '2025-12-29 16:35:41', 0),
(2, 'user1', 'password123', 'user1@example.com', 0, '2025-12-29 16:35:41', 2),
(3, 'user2', 'password123', 'user2@example.com', 0, '2025-12-29 16:35:41', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `movies`
--
ALTER TABLE `movies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_review` (`movie_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `movies`
--
ALTER TABLE `movies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `movies`
--
ALTER TABLE `movies`
  ADD CONSTRAINT `movies_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
