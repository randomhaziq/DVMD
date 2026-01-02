-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 02, 2026 at 09:32 AM
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
-- Database: `dvmd_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `role` varchar(50) NOT NULL COMMENT 'Role at time of action',
  `action_type` varchar(50) NOT NULL COMMENT 'LOGIN, UPDATE, DELETE, etc.',
  `details` text NOT NULL COMMENT 'Description of what happened',
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `role`, `action_type`, `details`, `ip_address`, `created_at`) VALUES
(1, 6, 'hq', 'LOGIN', 'User logged into system', '::1', '2025-12-28 19:11:28'),
(2, 6, 'hq', 'LOGIN', 'User logged into system', '::1', '2025-12-29 04:19:33'),
(3, 2, 'citizen', 'LOGIN', 'User logged into system', '::1', '2025-12-29 04:20:51'),
(4, 1, 'district', 'LOGIN', 'User logged into system', '::1', '2025-12-29 04:23:32'),
(5, 6, 'hq', 'LOGIN', 'User logged into system', '::1', '2025-12-29 04:31:47'),
(6, 1, 'district', 'LOGIN', 'User logged into system', '::1', '2025-12-29 04:33:37'),
(7, 6, 'hq', 'LOGIN', 'User logged into system', '::1', '2025-12-29 14:06:11'),
(8, 6, 'hq', 'LOGIN', 'User logged into system', '::1', '2025-12-29 15:40:56'),
(9, 1, 'district', 'LOGIN', 'User logged into system', '::1', '2025-12-29 16:14:00'),
(10, 6, 'hq', 'LOGIN', 'User logged into system', '::1', '2025-12-29 16:14:42'),
(11, 1, 'district', 'LOGIN', 'User logged into system', '::1', '2025-12-29 16:17:55'),
(12, 6, 'hq', 'LOGIN', 'User logged into system', '::1', '2025-12-29 16:21:31'),
(13, 1, 'district', 'LOGIN', 'User logged into system', '::1', '2025-12-29 16:25:01'),
(14, 6, 'hq', 'LOGIN', 'User logged into system', '::1', '2025-12-29 16:26:35'),
(15, 6, 'KPLB HQ', 'CREATE_USER', 'Created District Officer: jyny@mailinator.com', '::1', '2025-12-29 16:31:31'),
(16, 6, 'KPLB HQ', 'DELETE_USER', 'Deleted user ID: 12', '::1', '2025-12-29 16:31:36'),
(17, 1, 'district', 'LOGIN', 'User logged into system', '::1', '2025-12-29 16:57:19'),
(18, 1, 'District', 'CREATE_USER', 'Created ketua_kampung: zafogyvuvo@mailinator.com', '::1', '2025-12-29 16:57:28'),
(19, 1, 'District', 'DELETE_USER', 'Deleted user ID: 13', '::1', '2025-12-29 16:57:31'),
(20, 1, 'District', 'CREATE_USER', 'Created penghulu: nybihe@mailinator.com', '::1', '2025-12-29 17:22:35'),
(21, 1, 'District', 'DELETE_USER', 'Deleted user ID: 14', '::1', '2025-12-29 17:22:40'),
(22, 6, 'hq', 'LOGIN', 'User logged into system', '::1', '2025-12-29 17:24:33'),
(23, 6, 'hq', 'LOGIN', 'User logged into system', '::1', '2025-12-29 19:05:40'),
(24, 2, 'citizen', 'LOGIN', 'User logged into system', '::1', '2025-12-29 19:25:40'),
(25, 3, 'citizen', 'LOGIN', 'User logged into system', '::1', '2025-12-29 19:26:03'),
(26, 2, 'citizen', 'LOGIN', 'User logged into system', '::1', '2025-12-29 19:28:38'),
(27, 2, 'citizen', 'LOGIN', 'User logged into system', '::1', '2025-12-29 19:30:05'),
(28, 6, 'hq', 'LOGIN', 'User logged into system', '::1', '2025-12-29 19:45:47'),
(29, 2, 'citizen', 'LOGIN', 'User logged into system', '::1', '2025-12-29 19:46:16'),
(30, 6, 'hq', 'LOGIN', 'User logged into system', '::1', '2025-12-29 19:46:49'),
(31, 6, 'hq', 'LOGIN', 'User logged into system', '::1', '2025-12-30 00:48:57'),
(32, 2, 'citizen', 'LOGIN', 'User logged into system', '::1', '2025-12-30 01:33:45'),
(33, 6, 'hq', 'LOGIN', 'User logged into system', '::1', '2025-12-30 02:51:34'),
(34, 2, 'citizen', 'LOGIN', 'User logged into system', '::1', '2025-12-30 02:53:18'),
(35, 7, 'penghulu', 'LOGIN', 'User logged into system', '::1', '2025-12-30 03:03:45'),
(36, 1, 'district', 'LOGIN', 'User logged into system', '::1', '2025-12-30 03:04:20'),
(37, 1, 'District', 'CREATE_USER', 'Created citizen: ninyheg@mailinator.com', '::1', '2025-12-30 03:05:25'),
(38, 6, 'hq', 'LOGIN', 'User logged into system', '::1', '2025-12-30 03:09:21'),
(39, 6, 'KPLB HQ', 'DELETE_USER', 'Deleted user ID: 10', '::1', '2025-12-30 03:15:12'),
(40, 2, 'citizen', 'LOGIN', 'User logged into system', '::1', '2025-12-30 03:21:17'),
(41, 6, 'hq', 'LOGIN', 'User logged into system', '::1', '2026-01-02 08:28:46');

-- --------------------------------------------------------

--
-- Table structure for table `incidents`
--

CREATE TABLE `incidents` (
  `id` int(11) NOT NULL,
  `incident_type` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `affected` int(11) NOT NULL DEFAULT 0,
  `lat` decimal(10,6) NOT NULL,
  `lng` decimal(10,6) NOT NULL,
  `severity` enum('Low','Medium','High','Critical') NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Pending',
  `reported_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `incidents`
--

INSERT INTO `incidents` (`id`, `incident_type`, `description`, `affected`, `lat`, `lng`, `severity`, `status`, `reported_by`, `created_at`, `updated_at`) VALUES
(5, 'Landslide', 'Iste molestias enim', 10, 4.189183, 102.008584, 'High', 'Pending', 2, '2025-12-29 19:44:35', '2025-12-29 19:44:35'),
(6, 'Others', 'Id sunt excepteur ut', 1, 4.199393, 101.967711, 'Low', 'Pending', 2, '2025-12-29 19:45:01', '2025-12-29 19:45:01'),
(7, 'Fire', 'Asperiores et unde n', 4, 4.204185, 102.026423, 'Medium', 'Pending', 2, '2025-12-29 19:45:13', '2025-12-29 19:45:13'),
(8, 'Others', 'Nostrum voluptas vel', 3, 4.231509, 101.835858, 'High', 'Pending', 2, '2025-12-29 19:45:31', '2025-12-29 19:45:31'),
(9, 'Flood', 'Assumenda sed volupt', 1, 4.214565, 102.051071, 'Critical', 'Pending', 2, '2025-12-29 19:46:32', '2025-12-29 19:46:32'),
(10, 'Fire', 'Kebakaran di kompleks', 15, 4.305259, 102.078515, 'High', 'Pending', 2, '2025-12-30 02:58:04', '2025-12-30 02:58:04');

-- --------------------------------------------------------

--
-- Table structure for table `sos_alerts`
--

CREATE TABLE `sos_alerts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sos_id` varchar(20) NOT NULL COMMENT 'Format: SOS-XXXXXX',
  `emergency_type` varchar(50) NOT NULL,
  `additional_info` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sos_alerts`
--

INSERT INTO `sos_alerts` (`id`, `user_id`, `sos_id`, `emergency_type`, `additional_info`, `location`, `created_at`) VALUES
(1, 2, 'SOS-2025122721445721', 'accident', 'Seramai 3 orang', 'Kampung Gali', '2025-12-27 13:44:57'),
(2, 3, 'SOS-2025122721542175', 'other', 'Kucing di atas pokok', 'Kampung Batu Malim', '2025-12-27 13:54:21'),
(3, 2, 'SOS-2025122912215777', 'medical', 'b tu braderlah', 'Kampung Gali', '2025-12-29 04:21:57'),
(4, 2, 'SOS-2025123010545931', 'other', 'Kucing atas pokok', 'Bangsar South, Kuala Lumpur', '2025-12-30 02:54:59');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('citizen','ketua_kampung','penghulu','district','hq') NOT NULL,
  `village` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `name`, `phone`, `role`, `village`, `district`, `created_at`) VALUES
(1, 'admin@gmail.com', '$2y$10$QTOFo0L26izxeWNza4A5Fu8bsFzb.80kovUAyTEA1S7qgJAEnQv9G', 'Hana', '0173483206', 'district', 'Raub', 'Raub', '2025-12-27 10:18:31'),
(2, 'ain@gmail.com', '$2y$10$4IF/jpdPnrCVoh6UOkB/jO.VpLPvFvVidvCBXXOHySj1dFA0CZuhq', 'Ain', '01111434813', 'citizen', 'Kampung Gali', 'Raub', '2025-12-27 10:20:10'),
(3, 'haziq@gmail.com', '$2y$10$HHE3VTg6MCQLaikYKiuP/uzOJBQ5uRhdzANBDqRfY17K5OpoV4kYW', 'Haziq', '012345678', 'citizen', 'Kampung Batu Malim', 'Raub', '2025-12-27 13:51:18'),
(4, 'halimah@gmail.com', '$2y$10$4IF/jpdPnrCVoh6UOkB/jO.VpLPvFvVidvCBXXOHySj1dFA0CZuhq', 'Halimah', '0147258369', 'citizen', 'Kampung Batu Talam', 'Raub', '2025-12-27 13:51:18'),
(6, 'superadmin@gmail.com', '$2y$10$2a42z2CcIZFfPmyexGGo/.hNHJokizaf0P2o/nODCfb6B/FY4e0Jy', 'Azri', '0123451234', 'hq', 'Kampung Cin', 'Raub', '2025-12-28 09:14:48'),
(7, 'penghulu@gmail.com', '$2y$10$gebc8cxOk8gPBkadVYZx3u.xrAiH8tVy5OZTAqQzT0BrA6rlv6Bau', 'Azerul', '01234322345', 'penghulu', 'Kampung Ulu Gali', 'Raub', '2025-12-28 09:27:38'),
(15, 'ninyheg@mailinator.com', '$2y$10$cUFJ9/b0cfR1NQOACq081OHrlmdzoKDEfxI2dlL0Ag/yoaCrCvT6m', 'Price Terry', '+1 (412) 415-2255', 'citizen', 'Dolor in molestias m', 'Facilis aute eiusmod', '2025-12-30 03:05:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action_type`);

--
-- Indexes for table `incidents`
--
ALTER TABLE `incidents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sos_alerts`
--
ALTER TABLE `sos_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_sos_id` (`sos_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `incidents`
--
ALTER TABLE `incidents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `sos_alerts`
--
ALTER TABLE `sos_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sos_alerts`
--
ALTER TABLE `sos_alerts`
  ADD CONSTRAINT `sos_alerts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
