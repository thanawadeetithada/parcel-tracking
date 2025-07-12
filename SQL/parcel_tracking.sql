-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 12, 2025 at 04:21 AM
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
-- Database: `parcel_tracking`
--

-- --------------------------------------------------------

--
-- Table structure for table `parcels`
--

CREATE TABLE `parcels` (
  `id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `usage_duration` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `budget_year` varchar(10) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `user_responsible` varchar(255) NOT NULL,
  `note` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'approved',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parcels`
--

INSERT INTO `parcels` (`id`, `item_name`, `category`, `usage_duration`, `price`, `budget_year`, `start_date`, `end_date`, `user_responsible`, `note`, `status`, `created_at`, `updated_at`) VALUES
(46, 'เทสพัสดุuser2', 'โปรแกรม', 13, 900.00, '2567', NULL, NULL, '', 'note เทสพัสดุuser', 'pending', '2025-07-12 02:16:03', '2025-07-12 02:18:59'),
(47, 'เทสพัสดุuser2', 'โปรแกรม', 13, 900.00, '2567', '2025-07-12', '2025-07-25', 'test', 'note เทสพัสดุuser', 'pending', '2025-07-12 02:19:20', '2025-07-12 02:19:20');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `prefix` varchar(10) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `userrole` enum('user','admin','superadmin') NOT NULL DEFAULT 'user',
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `prefix`, `fullname`, `email`, `password`, `userrole`, `reset_token`, `reset_expiry`) VALUES
(77, 'นาย', 'test', 'test@gmail.com', '$2y$10$FKzkeyFKf7BGN4Dl/u.fw.RBIwqTqQ.G/dqSSY41U6.Mgrir04CM6', 'user', NULL, NULL),
(78, 'นาย', 'test1', 'test1@gmail.com', '$2y$10$l8HyozTdbmYe8AuvKnRO7OE7wpM/cL7mckPzEL0mZwK88Tgq6pZdq', 'admin', NULL, NULL),
(79, 'นาย', 'test2', 'test2@gmail.com', '$2y$10$9FwgsefY8tuf73PsCYogPOnGCpzhj8qm7Vm8nCdgywjgV7U5RFRE6', 'superadmin', NULL, NULL),
(80, 'นาย', 'fern', 'baifern24260@gmail.com', '$2y$10$r/RgtoqkAdURqa5kgMfpguk4aib2sfUhAM1KGHa2BQyN.SSkEpetW', 'user', '9b52f4c92faf822844449218d5b3245fbe8ee4cd2fb309edc6a40ed58c2ea5ac3f3a5f219eeb94547d7e4df16476e0429ee1', '2025-07-11 06:17:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `parcels`
--
ALTER TABLE `parcels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `parcels`
--
ALTER TABLE `parcels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
