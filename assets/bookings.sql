-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 24, 2026 at 05:47 PM
-- Server version: 10.11.16-MariaDB
-- PHP Version: 8.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `shanu_haven`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `room_id` int(11) NOT NULL,
  `guest_name` varchar(100) NOT NULL,
  `guest_email` varchar(150) NOT NULL,
  `guest_phone` varchar(20) NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `guests` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `booking_status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `room_id`, `guest_name`, `guest_email`, `guest_phone`, `check_in`, `check_out`, `guests`, `total_price`, `booking_status`, `created_at`) VALUES
(1, NULL, 1, 'Test guest', 'test@example.com', '0771234567', '2026-12-10', '2026-12-12', 2, 30000.00, 'pending', '2026-11-09 04:24:03'),
(2, NULL, 1, 'Maleesha Shanari', 'ug.maleesha.shanari@gmail.com', '+94740659750', '2026-11-26', '2026-11-28', 2, 30000.00, 'pending', '2026-11-09 04:26:29'),
(3, NULL, 1, 'Maleesha Shanari', 'ug.maleesha.shanari@gmail.com', '+94740659750', '2026-11-11', '2026-11-25', 1, 210000.00, 'pending', '2026-11-09 11:19:03'),
(4, NULL, 2, 'test 1', 'test1@gmail.com', '1234567890', '2026-11-09', '2026-11-10', 1, 22000.00, 'cancelled', '2026-11-09 13:32:19'),
(5, NULL, 2, 'test 2', 'test2@gmail.com', '1234567890', '2026-11-12', '2026-11-13', 1, 22000.00, 'cancelled', '2026-11-09 13:45:25'),
(6, NULL, 1, 'test 5', 'test3@gmail.com', '0712345678', '2027-01-10', '2027-01-13', 2, 45000.00, 'cancelled', '2026-11-12 03:06:52'),
(7, NULL, 1, 'test 10', 'test10@gmail.com', '0751234567', '2027-02-12', '2027-02-16', 1, 60000.00, 'confirmed', '2026-11-12 04:46:12'),
(8, NULL, 3, 'test7', 'test7@gmail.com', '0777894561', '2026-11-28', '2026-12-01', 2, 90000.00, 'confirmed', '2026-11-13 15:21:42'),
(9, NULL, 1, 'test 4', 'test4@gmail.com', '0784561238', '2026-12-30', '2027-01-04', 1, 75000.00, 'pending', '2026-11-24 04:36:11'),
(10, NULL, 3, 'test 8', 'test8@gmail.com', '0751236547', '2027-01-12', '2027-01-15', 2, 90000.00, 'pending', '2026-11-24 04:39:05');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `fk_bookings_user` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`),
  ADD CONSTRAINT `fk_bookings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
