-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql102.byetcluster.com
-- Generation Time: Mar 04, 2025 at 11:03 PM
-- Server version: 10.6.19-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tracking_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `faculty_progress`
--

CREATE TABLE `faculty_progress` (
  `id` int(11) NOT NULL,
  `registration_number` varchar(255) NOT NULL,
  `prefix` varchar(10) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `college` varchar(255) NOT NULL,
  `date_faculty_received` date NOT NULL,
  `committee_approval_date` date DEFAULT NULL,
  `faculty_approval_date` date DEFAULT NULL,
  `book_number_HR` varchar(255) DEFAULT NULL,
  `book_number_HR_date` date DEFAULT NULL,
  `passed_institution` varchar(255) DEFAULT NULL,
  `passed_institution_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty_progress`
--

INSERT INTO `faculty_progress` (`id`, `registration_number`, `prefix`, `fullname`, `college`, `date_faculty_received`, `committee_approval_date`, `faculty_approval_date`, `book_number_HR`, `book_number_HR_date`, `passed_institution`, `passed_institution_date`) VALUES
(41, '1104.03/811 20 พย. 66', 'นาย', 'ธนาวดี ติธาดา', 'วสส.ตรัง', '2066-11-28', '2067-01-30', '2067-02-22', '8 มี.ค. 67', '1969-12-31', '', '0000-00-00');

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
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `prefix`, `fullname`, `email`, `password`, `userrole`, `email_verified`, `verification_token`, `reset_token`, `reset_expiry`) VALUES
(01, 'นางสาว', 'ธนาวดี ติธาดา', 'thanawadee.titha@gmail.com', '$2y$10$tnnHoZyaph8iykn5yN.b3ODrJFPz5Te9YzyZOvv15csY0vphjZxPm', 'superadmin', 1, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `faculty_progress`
--
ALTER TABLE `faculty_progress`
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
-- AUTO_INCREMENT for table `faculty_progress`
--
ALTER TABLE `faculty_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
