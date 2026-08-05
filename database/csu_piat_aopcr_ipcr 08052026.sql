-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 05, 2026 at 02:50 PM
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
-- Database: `csu_piat_aopcr_ipcr`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(300) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `activity`, `ip_address`, `user_agent`, `created_at`) VALUES
(324, 61, 'Account registered — pending approval', '::1', NULL, '2026-08-04 17:16:49'),
(325, 1, 'Logged in successfully', '::1', NULL, '2026-08-04 17:16:56'),
(326, 1, 'Logged out', '::1', NULL, '2026-08-04 17:17:21'),
(327, 1, 'Logged in successfully', '::1', NULL, '2026-08-04 17:17:40'),
(328, 1, 'Activated account of Gon Freecs', '::1', NULL, '2026-08-04 17:17:47'),
(329, 1, 'Logged out', '::1', NULL, '2026-08-04 17:18:09'),
(330, 59, 'Logged in successfully', '::1', NULL, '2026-08-04 17:18:16'),
(331, 59, 'Logged out', '::1', NULL, '2026-08-04 17:22:27'),
(332, 1, 'Logged in successfully', '::1', NULL, '2026-08-04 17:22:31'),
(333, 1, 'Logged out', '::1', NULL, '2026-08-04 17:22:44'),
(334, 59, 'Logged in successfully', '::1', NULL, '2026-08-04 17:22:50'),
(335, 59, 'Logged out', '::1', NULL, '2026-08-04 17:23:30'),
(336, 61, 'Logged in successfully', '::1', NULL, '2026-08-04 17:23:36'),
(337, 61, 'Logged out', '::1', NULL, '2026-08-04 17:23:49'),
(338, 59, 'Logged in successfully', '::1', NULL, '2026-08-04 17:23:54'),
(339, 59, 'Added KPI: Sample (scope: user, assigned: user ID 61)', '::1', NULL, '2026-08-04 17:28:35'),
(340, 59, 'Logged out', '::1', NULL, '2026-08-04 17:28:41'),
(341, 61, 'Logged in successfully', '::1', NULL, '2026-08-04 17:28:45'),
(342, 61, 'Logged out', '::1', NULL, '2026-08-04 17:30:46'),
(343, 62, 'Logged in successfully', '::1', NULL, '2026-08-04 17:30:57'),
(344, 62, 'Logged out', '::1', NULL, '2026-08-04 17:31:05'),
(345, 1, 'Logged in successfully', '::1', NULL, '2026-08-04 17:34:51'),
(346, 1, 'Logged out', '::1', NULL, '2026-08-04 17:38:55'),
(347, 59, 'Logged in successfully', '::1', NULL, '2026-08-04 17:39:08'),
(348, 59, 'Logged out', '::1', NULL, '2026-08-04 17:40:22'),
(349, 62, 'Logged in successfully', '::1', NULL, '2026-08-04 17:40:25'),
(350, 62, 'Logged out', '::1', NULL, '2026-08-04 17:54:49'),
(351, 59, 'Logged in successfully', '::1', NULL, '2026-08-04 17:54:54'),
(352, 59, 'Logged out', '::1', NULL, '2026-08-04 17:55:17'),
(353, 1, 'Logged in successfully', '::1', NULL, '2026-08-04 17:55:32'),
(354, 1, 'Logged out', '::1', NULL, '2026-08-04 17:58:08');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` varchar(10) NOT NULL,
  `name` varchar(200) NOT NULL,
  `type` enum('admin','academic') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `type`, `is_active`) VALUES
('ACCT', 'Accounting Office', 'admin', 1),
('CAGRI', 'College of Agriculture', 'academic', 1),
('CCJA', 'College of Criminal Justice Administration', 'academic', 1),
('CED', 'College of Education', 'academic', 1),
('CEO', 'Office of the Campus Executive Officer', 'admin', 1),
('CICS', 'College of Information and Computing Sciences', 'academic', 1),
('HR', 'Human Resource Office', 'admin', 1),
('ITO', 'IT Office', 'admin', 1),
('PRMO', 'Partnership & Resource Mobilization Office', 'admin', 1),
('RDE', 'Research, Development & Extension Office', 'admin', 1),
('REG', 'Registrar\'s Office', 'admin', 1);

-- --------------------------------------------------------

--
-- Table structure for table `evidence_files`
--

CREATE TABLE `evidence_files` (
  `id` int(11) NOT NULL,
  `ipcr_form_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) NOT NULL DEFAULT 0,
  `mime_type` varchar(100) DEFAULT NULL,
  `category` enum('core','strategic','support','other') NOT NULL DEFAULT 'other',
  `description` text DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ipcr_forms`
--

CREATE TABLE `ipcr_forms` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `timeline_id` int(11) NOT NULL,
  `covered_period` varchar(100) DEFAULT NULL,
  `date_submitted` date DEFAULT NULL,
  `status` enum('draft','pending','reviewed','approved','disapproved') NOT NULL DEFAULT 'draft',
  `overall_rating` decimal(3,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ipcr_items`
--

CREATE TABLE `ipcr_items` (
  `id` int(11) NOT NULL,
  `ipcr_form_id` int(11) NOT NULL,
  `kpi_id` int(11) DEFAULT NULL,
  `function_type` enum('core','strategic','support') NOT NULL,
  `success_indicator` text DEFAULT NULL,
  `accomplishment` text DEFAULT NULL,
  `rating` decimal(2,1) DEFAULT NULL,
  `remarks` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kpi_items`
--

CREATE TABLE `kpi_items` (
  `id` int(11) NOT NULL,
  `category` enum('core','strategic','support') NOT NULL,
  `mfo` varchar(100) DEFAULT NULL,
  `success_indicator` text NOT NULL,
  `target` varchar(200) DEFAULT NULL,
  `measure` varchar(200) DEFAULT NULL,
  `department_id` varchar(10) DEFAULT NULL,
  `scope` enum('global','department','user') NOT NULL DEFAULT 'global',
  `assigned_to` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kpi_items`
--

INSERT INTO `kpi_items` (`id`, `category`, `mfo`, `success_indicator`, `target`, `measure`, `department_id`, `scope`, `assigned_to`, `is_active`, `created_by`, `created_at`) VALUES
(24, 'core', 'Sample', 'sample', '100', 'QT', 'CICS', 'user', 61, 1, 59, '2026-08-04 17:28:35');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `username`, `ip_address`, `attempted_at`) VALUES
(48, 'admin', '::1', '2026-08-04 17:55:21');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('info','success','warning','danger') NOT NULL DEFAULT 'info',
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `message`, `is_read`, `created_at`) VALUES
(10, 53, 'info', 'Glenard Pagurayan submitted an IPCR form for 2nd Semester 2026-2027.', 0, '2026-06-20 19:51:25'),
(11, 51, 'success', 'Your IPCR form has been approved! Overall Rating: 2.00', 0, '2026-06-20 19:54:53'),
(12, 54, 'success', 'Your IPCR form has been approved! Overall Rating: 4.00', 0, '2026-06-21 06:39:54'),
(15, 59, 'info', 'Glenard Pagurayan submitted an IPCR form for 2nd Semester 2026-2027.', 0, '2026-07-27 15:33:42'),
(17, 59, 'info', 'Gon Freecs submitted an IPCR form for 1st Semester 2026-2027.', 0, '2026-07-28 16:23:10');

-- --------------------------------------------------------

--
-- Table structure for table `opcr_forms`
--

CREATE TABLE `opcr_forms` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `department_id` varchar(10) NOT NULL,
  `timeline_id` int(11) NOT NULL,
  `covered_period` varchar(100) DEFAULT NULL,
  `date_submitted` date DEFAULT NULL,
  `status` enum('draft','pending','reviewed','approved','disapproved') NOT NULL DEFAULT 'draft',
  `overall_rating` decimal(3,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `opcr_items`
--

CREATE TABLE `opcr_items` (
  `id` int(11) NOT NULL,
  `opcr_form_id` int(11) NOT NULL,
  `function_type` enum('core','strategic','support') NOT NULL,
  `mfo` varchar(100) DEFAULT NULL,
  `success_indicator` text DEFAULT NULL,
  `target` varchar(200) DEFAULT NULL,
  `actual` text DEFAULT NULL,
  `budget` decimal(12,2) NOT NULL DEFAULT 0.00,
  `rating` tinyint(4) DEFAULT NULL CHECK (`rating` between 1 and 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `timelines`
--

CREATE TABLE `timelines` (
  `id` int(11) NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `semester` varchar(30) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `submission_deadline` date NOT NULL,
  `status` enum('open','closed') NOT NULL DEFAULT 'open',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `timelines`
--

INSERT INTO `timelines` (`id`, `academic_year`, `semester`, `start_date`, `end_date`, `submission_deadline`, `status`, `created_by`, `created_at`) VALUES
(5, '2026-2027', '2nd Semester', '2026-06-01', '2026-08-31', '2026-08-30', 'closed', 1, '2026-06-20 18:39:21'),
(7, '2026-2027', '1st Semester', '2026-07-29', '2026-09-30', '2026-09-29', 'open', 1, '2026-07-28 16:15:17');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('superadmin','admin','user') NOT NULL DEFAULT 'user',
  `name` varchar(100) NOT NULL,
  `position` varchar(150) DEFAULT NULL,
  `designation` enum('Dean','Department Head','Office Head','Faculty','Staff') DEFAULT NULL,
  `department_id` varchar(10) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `status` enum('active','inactive','pending') NOT NULL DEFAULT 'pending',
  `avatar` varchar(10) DEFAULT NULL,
  `security_question` varchar(200) DEFAULT NULL,
  `security_answer` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `name`, `position`, `designation`, `department_id`, `email`, `gender`, `status`, `avatar`, `security_question`, `security_answer`, `last_login`, `created_at`) VALUES
(1, 'superadmin', '$2y$10$XAtupKVIHwg7n51qimiygeFvbhsojniAdsikLpXpK.KcegtlMLxhS', 'superadmin', 'System Administrator', 'System Administrator', NULL, 'CEO', 'sysadmin@piat.csu.edu.ph', 'Male', 'active', 'SA', 'What is your mother\'s maiden name?', '$2y$10$Llfh/g05ebLzW03I.GxqDulB4WuVJdRvybc1un8bLsSYAUxrTYjEG', '2026-08-05 01:55:32', '2026-06-20 13:29:16'),
(59, 'warlito', '$2y$10$ZzJGhYvfC9zBQULQbqBSn.uJA2YMOwpmIZQguSiN3e8pZ/W0dLP6W', 'admin', 'Warlito Biraquit', 'Professor V', 'Dean', 'CICS', 'warlito@gmail.com', 'Female', 'active', 'WB', 'What is your mother\'s maiden name?', '$2y$10$7RbC4kih3J65iNOtBVhBl.p.HHm6hHfXQtWjxuvQO6Xz2HJBfxflW', '2026-08-05 01:54:54', '2026-07-22 16:19:47'),
(61, 'gons', '$2y$10$uWDi0uwCkww9Fm296BYIluRIknQMuBEwXMtm3GcQ4NHtuNVPhBKBa', 'user', 'Gon Freecs', 'Instructor II', 'Faculty', 'CICS', 'gons@gmail.com', 'Male', 'active', 'GF', 'What is your mother\'s maiden name?', '$2y$10$c8ik.hEnVLtbp4l34CvlQeU8p53qPX7JizdTCVRI5Ef/HNQ5DW79y', '2026-08-05 01:28:45', '2026-08-04 17:16:49'),
(62, 'cics.faculty1', '$2y$10$.77Nr96i7mrIoY2M4K/FzuYxhiN9W2ffmNjFlVlJV8EvsswmhXYXm', 'user', 'Faculty One CICS', 'Instructor I', NULL, 'CICS', 'cics.faculty1@piat.csu.edu.ph', 'Male', 'active', 'F1', 'What city were you born in?', '$2y$10$05ZIurX.tS382mT7gI./iuNiVgKcjpfaXEQNZkPGp1henikb4dZPa', '2026-08-05 01:40:25', '2026-08-04 17:21:21'),
(63, 'cics.faculty2', '$2y$10$.77Nr96i7mrIoY2M4K/FzuYxhiN9W2ffmNjFlVlJV8EvsswmhXYXm', 'user', 'Faculty Two CICS', 'Instructor II', NULL, 'CICS', 'cics.faculty2@piat.csu.edu.ph', 'Female', 'active', 'F2', 'What city were you born in?', '$2y$10$05ZIurX.tS382mT7gI./iuNiVgKcjpfaXEQNZkPGp1henikb4dZPa', NULL, '2026-08-04 17:21:21'),
(64, 'cics.faculty3', '$2y$10$.77Nr96i7mrIoY2M4K/FzuYxhiN9W2ffmNjFlVlJV8EvsswmhXYXm', 'user', 'Faculty Three CICS', 'Assistant Professor I', NULL, 'CICS', 'cics.faculty3@piat.csu.edu.ph', 'Male', 'active', 'F3', 'What city were you born in?', '$2y$10$05ZIurX.tS382mT7gI./iuNiVgKcjpfaXEQNZkPGp1henikb4dZPa', NULL, '2026-08-04 17:21:21'),
(65, 'cics.faculty4', '$2y$10$.77Nr96i7mrIoY2M4K/FzuYxhiN9W2ffmNjFlVlJV8EvsswmhXYXm', 'user', 'Faculty Four CICS', 'Assistant Professor II', NULL, 'CICS', 'cics.faculty4@piat.csu.edu.ph', 'Female', 'active', 'F4', 'What city were you born in?', '$2y$10$05ZIurX.tS382mT7gI./iuNiVgKcjpfaXEQNZkPGp1henikb4dZPa', NULL, '2026-08-04 17:21:21'),
(66, 'cics.faculty5', '$2y$10$.77Nr96i7mrIoY2M4K/FzuYxhiN9W2ffmNjFlVlJV8EvsswmhXYXm', 'user', 'Faculty Five CICS', 'Instructor I', NULL, 'CICS', 'cics.faculty5@piat.csu.edu.ph', 'Male', 'active', 'F5', 'What city were you born in?', '$2y$10$05ZIurX.tS382mT7gI./iuNiVgKcjpfaXEQNZkPGp1henikb4dZPa', NULL, '2026-08-04 17:21:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_log_user` (`user_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `evidence_files`
--
ALTER TABLE `evidence_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_evidence_ipcr` (`ipcr_form_id`),
  ADD KEY `fk_evidence_user` (`user_id`);

--
-- Indexes for table `ipcr_forms`
--
ALTER TABLE `ipcr_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ipcr_user` (`user_id`),
  ADD KEY `fk_ipcr_timeline` (`timeline_id`),
  ADD KEY `fk_ipcr_reviewer` (`reviewed_by`);

--
-- Indexes for table `ipcr_items`
--
ALTER TABLE `ipcr_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ipcr_item_form` (`ipcr_form_id`),
  ADD KEY `fk_ipcr_item_kpi` (`kpi_id`);

--
-- Indexes for table `kpi_items`
--
ALTER TABLE `kpi_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_kpi_dept` (`department_id`),
  ADD KEY `fk_kpi_creator` (`created_by`),
  ADD KEY `fk_kpi_assigned` (`assigned_to`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_login_attempts_username` (`username`),
  ADD KEY `idx_login_attempts_ip` (`ip_address`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notif_user` (`user_id`);

--
-- Indexes for table `opcr_forms`
--
ALTER TABLE `opcr_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_opcr_admin` (`admin_id`),
  ADD KEY `fk_opcr_dept` (`department_id`),
  ADD KEY `fk_opcr_timeline` (`timeline_id`),
  ADD KEY `fk_opcr_reviewer` (`reviewed_by`);

--
-- Indexes for table `opcr_items`
--
ALTER TABLE `opcr_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_opcr_item_form` (`opcr_form_id`);

--
-- Indexes for table `timelines`
--
ALTER TABLE `timelines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_timeline_creator` (`created_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_username` (`username`),
  ADD KEY `fk_users_dept` (`department_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=355;

--
-- AUTO_INCREMENT for table `evidence_files`
--
ALTER TABLE `evidence_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ipcr_forms`
--
ALTER TABLE `ipcr_forms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `ipcr_items`
--
ALTER TABLE `ipcr_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `kpi_items`
--
ALTER TABLE `kpi_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `opcr_forms`
--
ALTER TABLE `opcr_forms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `opcr_items`
--
ALTER TABLE `opcr_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `timelines`
--
ALTER TABLE `timelines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `fk_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `evidence_files`
--
ALTER TABLE `evidence_files`
  ADD CONSTRAINT `fk_evidence_ipcr` FOREIGN KEY (`ipcr_form_id`) REFERENCES `ipcr_forms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_evidence_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `ipcr_forms`
--
ALTER TABLE `ipcr_forms`
  ADD CONSTRAINT `fk_ipcr_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ipcr_timeline` FOREIGN KEY (`timeline_id`) REFERENCES `timelines` (`id`),
  ADD CONSTRAINT `fk_ipcr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `ipcr_items`
--
ALTER TABLE `ipcr_items`
  ADD CONSTRAINT `fk_ipcr_item_form` FOREIGN KEY (`ipcr_form_id`) REFERENCES `ipcr_forms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ipcr_item_kpi` FOREIGN KEY (`kpi_id`) REFERENCES `kpi_items` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `kpi_items`
--
ALTER TABLE `kpi_items`
  ADD CONSTRAINT `fk_kpi_assigned` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_kpi_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_kpi_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `opcr_forms`
--
ALTER TABLE `opcr_forms`
  ADD CONSTRAINT `fk_opcr_admin` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_opcr_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `fk_opcr_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_opcr_timeline` FOREIGN KEY (`timeline_id`) REFERENCES `timelines` (`id`);

--
-- Constraints for table `opcr_items`
--
ALTER TABLE `opcr_items`
  ADD CONSTRAINT `fk_opcr_item_form` FOREIGN KEY (`opcr_form_id`) REFERENCES `opcr_forms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `timelines`
--
ALTER TABLE `timelines`
  ADD CONSTRAINT `fk_timeline_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
