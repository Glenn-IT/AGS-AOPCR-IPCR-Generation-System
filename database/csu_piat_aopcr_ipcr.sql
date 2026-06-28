-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 28, 2026 at 05:02 PM
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
(32, 1, 'Logged out', '::1', NULL, '2026-06-20 17:35:53'),
(35, 51, 'Account registered — pending approval', '::1', NULL, '2026-06-20 17:37:26'),
(36, 1, 'Logged in successfully', '::1', NULL, '2026-06-20 17:37:30'),
(37, 1, 'Activated account of Glenard Pagurayan', '::1', NULL, '2026-06-20 17:38:49'),
(38, 1, 'Logged out', '::1', NULL, '2026-06-20 17:38:52'),
(39, 51, 'Logged in successfully', '::1', NULL, '2026-06-20 17:39:08'),
(40, 51, 'Logged out', '::1', NULL, '2026-06-20 17:39:30'),
(41, 51, 'Logged in successfully', '::1', NULL, '2026-06-20 17:40:05'),
(42, 51, 'Logged out', '::1', NULL, '2026-06-20 17:40:27'),
(43, 1, 'Logged in successfully', '::1', NULL, '2026-06-20 17:40:40'),
(44, 1, 'Logged out', '::1', NULL, '2026-06-20 17:41:51'),
(46, 1, 'Logged in successfully', '::1', NULL, '2026-06-20 17:42:33'),
(47, 1, 'Logged out', '::1', NULL, '2026-06-20 17:50:08'),
(48, 1, 'Logged in successfully', '::1', NULL, '2026-06-20 17:52:14'),
(49, 1, 'Deactivated account of Glenard Pagurayan', '::1', NULL, '2026-06-20 17:52:44'),
(50, 1, 'Activated account of Glenard Pagurayan', '::1', NULL, '2026-06-20 17:52:47'),
(51, 1, 'Logged out', '::1', NULL, '2026-06-20 18:03:45'),
(52, 53, 'Account registered — pending approval', '::1', NULL, '2026-06-20 18:04:31'),
(53, 1, 'Logged in successfully', '::1', NULL, '2026-06-20 18:04:35'),
(54, 1, 'Edited account of user ID 53', '::1', NULL, '2026-06-20 18:20:46'),
(55, 1, 'Activated account of Lea Gasmen', '::1', NULL, '2026-06-20 18:24:28'),
(56, 1, 'Edited account of user ID 53', '::1', NULL, '2026-06-20 18:26:50'),
(57, 1, 'Logged out', '::1', NULL, '2026-06-20 18:26:54'),
(58, 53, 'Logged in successfully', '::1', NULL, '2026-06-20 18:27:02'),
(59, 53, 'Logged out', '::1', NULL, '2026-06-20 18:28:36'),
(60, 1, 'Logged in successfully', '::1', NULL, '2026-06-20 18:28:38'),
(61, 1, 'Added timeline: 2026-2027 2nd Semester', '::1', NULL, '2026-06-20 18:39:21'),
(62, 1, 'Added KPI: Instruction', '::1', NULL, '2026-06-20 19:04:32'),
(63, 1, 'Logged out', '::1', NULL, '2026-06-20 19:04:49'),
(64, 51, 'Logged in successfully', '::1', NULL, '2026-06-20 19:05:07'),
(65, 51, 'Logged out', '::1', NULL, '2026-06-20 19:08:40'),
(66, 51, 'Logged in successfully', '::1', NULL, '2026-06-20 19:08:47'),
(67, 51, 'Logged out', '::1', NULL, '2026-06-20 19:09:21'),
(68, 1, 'Logged in successfully', '::1', NULL, '2026-06-20 19:09:23'),
(69, 1, 'Added KPI: Sample', '::1', NULL, '2026-06-20 19:10:13'),
(70, 1, 'Added KPI: Sample', '::1', NULL, '2026-06-20 19:10:36'),
(71, 1, 'Logged out', '::1', NULL, '2026-06-20 19:10:47'),
(72, 51, 'Logged in successfully', '::1', NULL, '2026-06-20 19:10:59'),
(73, 51, 'Logged out', '::1', NULL, '2026-06-20 19:17:42'),
(74, 1, 'Logged in successfully', '::1', NULL, '2026-06-20 19:17:45'),
(75, 1, 'Logged out', '::1', NULL, '2026-06-20 19:18:16'),
(76, 51, 'Logged in successfully', '::1', NULL, '2026-06-20 19:18:24'),
(77, 51, 'Logged out', '::1', NULL, '2026-06-20 19:19:22'),
(78, 1, 'Logged in successfully', '::1', NULL, '2026-06-20 19:19:25'),
(79, 1, 'Logged out', '::1', NULL, '2026-06-20 19:19:43'),
(80, 51, 'Logged in successfully', '::1', NULL, '2026-06-20 19:19:50'),
(81, 51, 'Logged in successfully', '::1', NULL, '2026-06-20 19:29:46'),
(82, 51, 'Logged out', '::1', NULL, '2026-06-20 19:30:12'),
(83, 53, 'Logged in successfully', '::1', NULL, '2026-06-20 19:30:22'),
(84, 53, 'Logged out', '::1', NULL, '2026-06-20 19:31:06'),
(85, 1, 'Logged in successfully', '::1', NULL, '2026-06-20 19:31:09'),
(86, 1, 'Logged out', '::1', NULL, '2026-06-20 19:32:56'),
(87, 51, 'Logged in successfully', '::1', NULL, '2026-06-20 19:33:09'),
(88, 51, 'Logged out', '::1', NULL, '2026-06-20 19:34:30'),
(89, 1, 'Logged in successfully', '::1', NULL, '2026-06-20 19:34:32'),
(90, 1, 'Logged out', '::1', NULL, '2026-06-20 19:40:31'),
(91, 51, 'Logged in successfully', '::1', NULL, '2026-06-20 19:40:58'),
(92, 51, 'Logged out', '::1', NULL, '2026-06-20 19:41:16'),
(93, 53, 'Logged in successfully', '::1', NULL, '2026-06-20 19:41:22'),
(94, 53, 'Logged out', '::1', NULL, '2026-06-20 19:41:49'),
(95, 1, 'Logged in successfully', '::1', NULL, '2026-06-20 19:42:13'),
(96, 1, 'Updated timeline: 2026-2027 2nd Semester', '::1', NULL, '2026-06-20 19:42:25'),
(97, 1, 'Logged out', '::1', NULL, '2026-06-20 19:42:29'),
(98, 51, 'Logged in successfully', '::1', NULL, '2026-06-20 19:42:40'),
(99, 51, 'Logged out', '::1', NULL, '2026-06-20 19:43:57'),
(100, 1, 'Logged in successfully', '::1', NULL, '2026-06-20 19:43:59'),
(101, 1, 'Updated timeline: 2026-2027 2nd Semester', '::1', NULL, '2026-06-20 19:44:10'),
(102, 1, 'Added timeline: 2026 -2027 2nd Semester', '::1', NULL, '2026-06-20 19:44:35'),
(103, 1, 'Logged out', '::1', NULL, '2026-06-20 19:44:39'),
(104, 51, 'Logged in successfully', '::1', NULL, '2026-06-20 19:44:46'),
(105, 51, 'Logged in successfully', '::1', NULL, '2026-06-20 19:51:04'),
(106, 51, 'Submitted IPCR form for 2nd Semester 2026-2027', '::1', NULL, '2026-06-20 19:51:25'),
(107, 51, 'Logged out', '::1', NULL, '2026-06-20 19:54:26'),
(108, 53, 'Logged in successfully', '::1', NULL, '2026-06-20 19:54:31'),
(109, 53, 'Reviewed IPCR #8 — APPROVED (rating: 2)', '::1', NULL, '2026-06-20 19:54:53'),
(110, 1, 'Logged in successfully', '::1', NULL, '2026-06-21 06:35:44'),
(111, 1, 'Logged out', '::1', NULL, '2026-06-21 06:35:51'),
(112, 54, 'Account registered — pending approval', '::1', NULL, '2026-06-21 06:36:43'),
(113, 1, 'Logged in successfully', '::1', NULL, '2026-06-21 06:36:54'),
(114, 1, 'Activated account of Lucky Padua', '::1', NULL, '2026-06-21 06:37:10'),
(115, 1, 'Logged out', '::1', NULL, '2026-06-21 06:37:14'),
(116, 54, 'Logged in successfully', '::1', NULL, '2026-06-21 06:37:19'),
(117, 54, 'Logged out', '::1', NULL, '2026-06-21 06:37:37'),
(118, 1, 'Logged in successfully', '::1', NULL, '2026-06-21 06:37:39'),
(119, 1, 'Logged out', '::1', NULL, '2026-06-21 06:38:02'),
(120, 54, 'Logged in successfully', '::1', NULL, '2026-06-21 06:38:09'),
(121, 54, 'Submitted IPCR form for 2nd Semester 2026-2027', '::1', NULL, '2026-06-21 06:38:24'),
(122, 54, 'Logged out', '::1', NULL, '2026-06-21 06:38:28'),
(123, 55, 'Account registered — pending approval', '::1', NULL, '2026-06-21 06:39:04'),
(124, 1, 'Logged in successfully', '::1', NULL, '2026-06-21 06:39:08'),
(125, 1, 'Activated account of Carl Padua', '::1', NULL, '2026-06-21 06:39:35'),
(126, 1, 'Logged out', '::1', NULL, '2026-06-21 06:39:38'),
(127, 55, 'Logged in successfully', '::1', NULL, '2026-06-21 06:39:43'),
(128, 55, 'Reviewed IPCR #9 — APPROVED (rating: 4)', '::1', NULL, '2026-06-21 06:39:54'),
(129, 1, 'Logged in successfully', '::1', NULL, '2026-06-28 06:09:53'),
(130, 1, 'Logged out', '::1', NULL, '2026-06-28 06:10:02'),
(131, 1, 'Logged in successfully', '::1', NULL, '2026-06-28 06:11:59'),
(132, 1, 'Updated security question', '::1', NULL, '2026-06-28 06:21:39'),
(133, 1, 'Logged out', '::1', NULL, '2026-06-28 06:21:46'),
(134, 1, 'Password reset via Forgot Password', '::1', NULL, '2026-06-28 06:22:22'),
(135, 1, 'Logged in successfully', '::1', NULL, '2026-06-28 06:22:32'),
(136, 1, 'Logged out', '::1', NULL, '2026-06-28 06:22:40'),
(137, 1, 'Logged in successfully', '::1', NULL, '2026-06-28 06:24:27'),
(138, 1, 'Logged out', '::1', NULL, '2026-06-28 06:24:41'),
(139, 55, 'Logged in successfully', '::1', NULL, '2026-06-28 06:24:46'),
(140, 55, 'Logged out', '::1', NULL, '2026-06-28 06:24:50'),
(141, 56, 'Account registered — pending approval', '::1', NULL, '2026-06-28 06:29:21'),
(142, 1, 'Logged in successfully', '::1', NULL, '2026-06-28 06:41:22'),
(143, 1, 'Logged out', '::1', NULL, '2026-06-28 06:41:26'),
(144, 1, 'Logged in successfully', '::1', NULL, '2026-06-28 06:59:46'),
(145, 1, 'Logged out', '::1', NULL, '2026-06-28 06:59:50'),
(146, 1, 'Logged in successfully', '::1', NULL, '2026-06-28 07:07:18'),
(147, 1, 'Logged out', '::1', NULL, '2026-06-28 07:07:22'),
(148, 1, 'Logged in successfully', '::1', NULL, '2026-06-28 07:08:34'),
(149, 1, 'Logged out', '::1', NULL, '2026-06-28 07:08:40'),
(150, 55, 'Logged in successfully', '::1', NULL, '2026-06-28 07:16:18'),
(151, 1, 'Password reset via Forgot Password', '::1', NULL, '2026-06-28 07:38:30'),
(152, 1, 'Password reset via Forgot Password', '::1', NULL, '2026-06-28 07:39:18'),
(153, 1, 'Logged in successfully', '::1', NULL, '2026-06-28 07:48:10'),
(154, 1, 'Logged out', '::1', NULL, '2026-06-28 07:51:48'),
(155, 1, 'Logged in successfully', '::1', NULL, '2026-06-28 07:52:16'),
(156, 1, 'Logged in successfully', '::1', NULL, '2026-06-28 07:58:05'),
(157, 1, 'Logged in successfully', '::1', NULL, '2026-06-28 07:58:42'),
(158, 1, 'Logged out', '::1', NULL, '2026-06-28 07:58:44'),
(159, 1, 'Logged in successfully', '::1', NULL, '2026-06-28 07:59:17'),
(160, 1, 'Logged out', '::1', NULL, '2026-06-28 07:59:19'),
(161, 1, 'Password reset via Forgot Password', '::1', NULL, '2026-06-28 07:59:35'),
(162, 1, 'Logged in successfully', '::1', NULL, '2026-06-28 07:59:42'),
(163, 1, 'Logged out', '::1', NULL, '2026-06-28 07:59:43');

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

--
-- Dumping data for table `ipcr_forms`
--

INSERT INTO `ipcr_forms` (`id`, `user_id`, `timeline_id`, `covered_period`, `date_submitted`, `status`, `overall_rating`, `remarks`, `reviewed_by`, `reviewed_at`, `created_at`, `updated_at`) VALUES
(8, 51, 5, '2nd Semester 2026-2027', '2026-06-20', 'approved', 2.00, '', 53, '2026-06-21 03:54:53', '2026-06-20 19:51:25', '2026-06-20 19:54:53'),
(9, 54, 5, '2nd Semester 2026-2027', '2026-06-21', 'approved', 4.00, '', 55, '2026-06-21 14:39:54', '2026-06-21 06:38:24', '2026-06-21 06:39:54');

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
  `rating` tinyint(4) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `remarks` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ipcr_items`
--

INSERT INTO `ipcr_items` (`id`, `ipcr_form_id`, `kpi_id`, `function_type`, `success_indicator`, `accomplishment`, `rating`, `remarks`) VALUES
(36, 8, 14, 'core', 'Sample', 'qwe', 2, 'qwe'),
(37, 8, 15, 'strategic', 'Sample', 'qwe', 2, 'qwe'),
(38, 8, 16, 'support', 'Sample', 'qwe', 2, 'qwe'),
(39, 9, 14, 'core', 'Sample', 'qwe', 4, 'qwe'),
(40, 9, 15, 'strategic', 'Sample', 'qwe', 4, 'qwe'),
(41, 9, 16, 'support', 'Sample', 'qwe', 4, 'qwe');

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
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kpi_items`
--

INSERT INTO `kpi_items` (`id`, `category`, `mfo`, `success_indicator`, `target`, `measure`, `department_id`, `is_active`, `created_by`, `created_at`) VALUES
(14, 'core', 'Instruction', 'Sample', '90', 'QT', NULL, 1, 1, '2026-06-20 19:04:32'),
(15, 'strategic', 'Sample', 'Sample', '89', 'Q', NULL, 1, 1, '2026-06-20 19:10:13'),
(16, 'support', 'Sample', 'Sample', '80', 'QnQt', NULL, 1, 1, '2026-06-20 19:10:36');

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
(12, 54, 'success', 'Your IPCR form has been approved! Overall Rating: 4.00', 0, '2026-06-21 06:39:54');

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
(5, '2026-2027', '2nd Semester', '2026-06-01', '2026-07-31', '2026-07-30', 'open', 1, '2026-06-20 18:39:21');

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
(1, 'superadmin', '$2y$10$ohhlHAu9a8i4NNOPiNsbeOw6wnOex7UPBj2LV7PS.euBxn6iM8vvO', 'superadmin', 'System Administrator', 'System Administrator', NULL, 'CEO', 'sysadmin@piat.csu.edu.ph', 'Male', 'active', 'SA', 'What is your mother\'s maiden name?', '$2y$10$Llfh/g05ebLzW03I.GxqDulB4WuVJdRvybc1un8bLsSYAUxrTYjEG', '2026-06-28 15:59:42', '2026-06-20 13:29:16'),
(51, 'Glenn', '$2y$10$W1YUDGHlpAfFDDxUh7/BpeKUno5asAPXgQ9ntWEL8luCwIMwMpbqO', 'user', 'Glenard Pagurayan', 'Instructor I', NULL, 'CICS', 'Glenn@gmail.com', 'Male', 'active', 'GP', 'What is your mother\'s maiden name?', '$2y$10$59LQ62X.yEljRN.ZXR753O6NU0r4u4qUu2xH/Q42PTvE8W7KwEiDy', '2026-06-21 03:51:04', '2026-06-20 17:37:26'),
(53, 'lea', '$2y$10$if0xyIH2CrFORFOGbTAvB.14I5xR2LPLz53f6tIBUk8TIzlv3tizS', 'admin', 'Lea Gasmen', 'Instructor II', 'Dean', 'CICS', 'lea@gmail.com', 'Female', 'active', 'LG', 'What is your mother\'s maiden name?', '$2y$10$vgxykOzJ1NLLcE7jblICTuUS1pLqiqNesE9XYdqP5XCZ269wpJZlW', '2026-06-21 03:54:31', '2026-06-20 18:04:31'),
(54, 'Lucky', '$2y$10$aNUeWznDpQ8CaFjXK2Xa4e6scTkd.aLwceNLl7Aow9ABevvn4b.g.', 'user', 'Lucky Padua', 'Staff I', 'Staff', 'ACCT', 'lucky@gmail.com', 'Male', 'active', 'LP', 'What is your mother\'s maiden name?', '$2y$10$XXhVmLCyfBVZCvb6aGwnoOmVtWoly125utuzKohP7PtpYnegVMM3O', '2026-06-21 14:38:09', '2026-06-21 06:36:43'),
(55, 'carl', '$2y$10$TSJ3RlfhpkT/CVCi4ZeXgu6gDjBV4Tcye/5DjaWtqZxOCPSoDNPKi', 'admin', 'Carl Padua', 'Staff III', 'Office Head', 'ACCT', 'carl@gmail.com', 'Male', 'active', 'CP', 'What is your mother\'s maiden name?', '$2y$10$vXfmmTIPG5OpWEEIJQ6fketMB1WnzziWC5M.H5hx63A607AqSsDe6', '2026-06-28 15:16:18', '2026-06-21 06:39:04'),
(56, 'sample', '$2y$10$vrzotbF0u2ab8YAhNa0MmuaX.yvmrpclOyrYJs6Y6d4sCxtiXoQ5e', 'user', 'Sample', 'Sample', 'Faculty', 'ACCT', 'sample@gmail.com', 'Male', 'pending', 'S', 'What is your mother\'s maiden name?', '$2y$10$fAhgytAN2L.qWrqJIeEhBOGPffhqwVJqUTaw/c5oHI2wcJgxp5N1G', NULL, '2026-06-28 06:29:21');

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
  ADD KEY `fk_kpi_creator` (`created_by`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=164;

--
-- AUTO_INCREMENT for table `evidence_files`
--
ALTER TABLE `evidence_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ipcr_forms`
--
ALTER TABLE `ipcr_forms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `ipcr_items`
--
ALTER TABLE `ipcr_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `kpi_items`
--
ALTER TABLE `kpi_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

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
