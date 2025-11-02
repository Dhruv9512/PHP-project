-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 02, 2025 at 01:53 PM
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
-- Database: `event_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `email_verifications`
--

CREATE TABLE `email_verifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `main_events`
--

CREATE TABLE `main_events` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `main_image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `main_events`
--

INSERT INTO `main_events` (`id`, `name`, `description`, `main_image_url`) VALUES
(1, 'Tech Conference 2026', 'A three-day conference exploring the future of technology, AI, and software development.', 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?w=600&q=80'),
(2, 'The Midnight Art Gala', 'An exclusive evening exhibition of modern contemporary art, with live music and refreshments.', 'https://images.unsplash.com/photo-1543269865-cbf427effbad?w=600&q=80'),
(3, 'Annual Community Park Cleanup', 'Join us to help clean, plant, and beautify our local city park. All are welcome!', 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=600&q=80');

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `id` int(11) NOT NULL,
  `sub_event_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `registration_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registrations`
--

INSERT INTO `registrations` (`id`, `sub_event_id`, `name`, `email`, `phone`, `registration_time`) VALUES
(1, 6, 'Dhruv Sharma', 'dhruvsharma56780@gmail.com', '9512456895', '2025-10-29 15:57:35');

-- --------------------------------------------------------

--
-- Table structure for table `sub_events`
--

CREATE TABLE `sub_events` (
  `id` int(11) NOT NULL,
  `main_event_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `date` datetime NOT NULL,
  `venue` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sub_events`
--

INSERT INTO `sub_events` (`id`, `main_event_id`, `name`, `date`, `venue`, `description`, `price`) VALUES
(1, 1, 'Workshop: Advanced AI', '2026-03-10 09:00:00', 'Room 101A', 'A deep-dive workshop on machine learning models.', 49.99),
(2, 1, 'Keynote: The Future of Web', '2026-03-10 13:00:00', 'Main Auditorium', 'By industry visionary Alex Chen.', 1.00),
(3, 1, 'Panel: The Rise of Decentralization', '2026-03-11 11:00:00', 'Room 102B', 'Discussion on blockchain and Web3.', 15.00),
(4, 1, 'Career Fair & Networking', '2026-03-12 10:00:00', 'Exhibition Hall', 'Meet with top tech companies.', 0.00),
(5, 2, 'General Admission', '2026-04-18 19:00:00', 'Grand City Museum', 'Grants full access to all exhibits and the main hall.', 75.00),
(6, 3, 'Volunteer Registration', '2026-05-01 09:00:00', 'City Park Entrance', 'All supplies (gloves, bags) will be provided. Free t-shirt for all volunteers.', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `verified`, `created_at`) VALUES
(1, 'Dhruv', 'dhruvsharma56780@gmail.com', '$2y$10$8egW9Px0zmIBcUMFifhyYOO8CS9PpWEbI.tGCRJcl4O06.mAGwPQq', 1, '2025-10-29 15:54:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `main_events`
--
ALTER TABLE `main_events`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `main_events` ADD FULLTEXT KEY `main_image_url` (`name`);

--
-- Indexes for table `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sub_event_id` (`sub_event_id`);

--
-- Indexes for table `sub_events`
--
ALTER TABLE `sub_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `main_event_id` (`main_event_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `email_verifications`
--
ALTER TABLE `email_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `main_events`
--
ALTER TABLE `main_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sub_events`
--
ALTER TABLE `sub_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD CONSTRAINT `email_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`sub_event_id`) REFERENCES `sub_events` (`id`);

--
-- Constraints for table `sub_events`
--
ALTER TABLE `sub_events`
  ADD CONSTRAINT `sub_events_ibfk_1` FOREIGN KEY (`main_event_id`) REFERENCES `main_events` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
