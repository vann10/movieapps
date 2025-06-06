-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 19, 2025 at 02:12 PM
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
-- Database: `movie_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `poster_path` varchar(255) DEFAULT NULL,
  `users_id` int(11) DEFAULT 1,
  `status` enum('watchlist','watched') DEFAULT 'watchlist',
  `rating` tinyint(4) DEFAULT NULL,
  `loved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `custom_poster` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`id`, `movie_id`, `title`, `poster_path`, `users_id`, `status`, `rating`, `loved`, `created_at`, `custom_poster`) VALUES
(10, 807, 'Se7en', '/191nKfP0ehp3uIvWqgPbFmI4lv9.jpg', 1, 'watchlist', 5, 0, '2025-05-16 14:29:28', NULL),
(14, 693134, 'Dune: Part Two', '/6izwz7rsy95ARzTR3poZ8H6c5pp.jpg', 1, 'watchlist', 2, 0, '2025-05-16 14:31:33', NULL),
(20, 299536, 'Avengers: Infinity War', '/7WsyChQLEftFiDOVTGkv3hFpyyt.jpg', 1, 'watchlist', NULL, 1, '2025-05-16 14:39:57', NULL),
(28, 157336, 'Interstellar', '/gEU2QniE6E77NI6lCU6MxlNBvIx.jpg', 1, 'watchlist', NULL, 0, '2025-05-18 23:10:23', NULL),
(29, 324857, 'Spider-Man: Into the Spider-Verse', '/iiZZdoQBEYBv6id8su7ImL0oCbD.jpg', 1, 'watchlist', NULL, 0, '2025-05-18 23:11:12', NULL),
(31, 246355, 'Saw', '/uvjBl6LHmb5JpAbmSZzeWA1c7Sv.jpg', 1, 'watchlist', NULL, 0, '2025-05-18 23:13:33', NULL),
(32, 215, 'Saw II', '/gTnaTysN8HsvVQqTRUh8m35mmUA.jpg', 1, 'watchlist', NULL, 0, '2025-05-18 23:13:52', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `movie_id` (`movie_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
