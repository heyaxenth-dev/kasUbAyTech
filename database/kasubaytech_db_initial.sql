-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 23, 2025 at 03:11 PM
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
-- Database: `kasubaytech_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `email`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@kasubaytech.com', '2025-11-16 15:02:29');

-- --------------------------------------------------------

--
-- Table structure for table `answer_options`
--

CREATE TABLE `answer_options` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_text` varchar(255) NOT NULL,
  `it_score` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Score for IT course compatibility',
  `cs_score` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Score for CS course compatibility',
  `is_score` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Score for IS course compatibility',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `answer_options`
--

INSERT INTO `answer_options` (`id`, `question_id`, `option_text`, `it_score`, `cs_score`, `is_score`, `created_at`) VALUES
(1, 1, 'Information and Computer Technology', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(2, 1, 'Information and Communication Technology', 3.00, 3.00, 3.00, '2025-11-23 07:48:07'),
(3, 1, 'Integrated Computer Training', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(4, 1, 'Internal Connection Tool', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(5, 2, 'A device that stores and processes data', 3.00, 3.00, 3.00, '2025-11-23 07:48:07'),
(6, 2, 'A printer', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(7, 2, 'A network cable', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(8, 2, 'A type of storage', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(9, 3, 'MS Word', 3.00, 3.00, 3.00, '2025-11-23 07:48:07'),
(10, 3, 'BIOS', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(11, 3, 'RAM', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(12, 3, 'ROM', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(13, 4, 'Information and Computer Technology', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(14, 4, 'Information and Communication Technology', 2.00, 1.00, 5.00, '2025-11-23 07:48:07'),
(15, 4, 'Integrated Computer Training', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(16, 4, 'Internal Connection Tool', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(17, 5, 'Mouse', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(18, 5, 'Monitor', 2.00, 1.00, 5.00, '2025-11-23 07:48:07'),
(19, 5, 'Keyboard', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(20, 5, 'Scanner', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(21, 6, 'Store data', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(22, 6, 'Perform calculations', 2.00, 1.00, 5.00, '2025-11-23 07:48:07'),
(23, 6, 'Print documents', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(24, 6, 'Connect to the internet', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(25, 7, 'Hard Disk', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(26, 7, 'CPU', 2.00, 1.00, 5.00, '2025-11-23 07:48:07'),
(27, 7, 'RAM', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(28, 7, 'Monitor', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(29, 8, 'Printer', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(30, 8, 'Microsoft Word', 2.00, 1.00, 5.00, '2025-11-23 07:48:07'),
(31, 8, 'USB Cable', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(32, 8, 'Mouse', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(33, 9, 'Print documents', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(34, 9, 'Type data', 2.00, 1.00, 5.00, '2025-11-23 07:48:07'),
(35, 9, 'Store files', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(36, 9, 'Scan images', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(37, 10, 'World Wide Web', 2.00, 1.00, 5.00, '2025-11-23 07:48:07'),
(38, 10, 'Wide World Web', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(39, 10, 'World Web Wide', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(40, 10, 'Web World Wide', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(41, 11, 'Facts', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(42, 11, 'Instructions', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(43, 11, 'Information', 2.00, 1.00, 5.00, '2025-11-23 07:48:07'),
(44, 11, 'Numbers', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(45, 12, 'Hard Drive', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(46, 12, 'RAM', 2.00, 1.00, 5.00, '2025-11-23 07:48:07'),
(47, 12, 'CD-ROM', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(48, 12, 'USB Drive', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(49, 13, 'Monitor', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(50, 13, 'Mouse', 2.00, 1.00, 5.00, '2025-11-23 07:48:07'),
(51, 13, 'Printer', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(52, 13, 'Speaker', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(53, 14, 'Information Tool', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(54, 14, 'Information Technology', 5.00, 1.00, 2.00, '2025-11-23 07:48:07'),
(55, 14, 'Internet Technology', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(56, 14, 'Internal Transmission', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(57, 15, 'Mouse', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(58, 15, 'CPU', 5.00, 1.00, 2.00, '2025-11-23 07:48:07'),
(59, 15, 'Monitor', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(60, 15, 'Keyboard', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(61, 16, 'Printer', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(62, 16, 'Hard Disk', 5.00, 1.00, 2.00, '2025-11-23 07:48:07'),
(63, 16, 'Monitor', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(64, 16, 'Speaker', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(65, 17, 'Hardware connection', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(66, 17, 'Controls computer operations', 5.00, 1.00, 2.00, '2025-11-23 07:48:07'),
(67, 17, 'Runs hardware', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(68, 17, 'Plays videos', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(69, 18, 'Input', 5.00, 1.00, 2.00, '2025-11-23 07:48:07'),
(70, 18, 'Output', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(71, 18, 'Storage', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(72, 18, 'Processing', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(73, 19, 'First', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(74, 19, 'Second', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(75, 19, 'Fourth', 5.00, 1.00, 2.00, '2025-11-23 07:48:07'),
(76, 19, 'Fifth', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(77, 20, 'Serial', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(78, 20, 'USB', 5.00, 1.00, 2.00, '2025-11-23 07:48:07'),
(79, 20, 'HDMI', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(80, 20, 'VGA', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(81, 21, 'Keyboard', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(82, 21, 'Monitor', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(83, 21, 'Gmail', 5.00, 1.00, 2.00, '2025-11-23 07:48:07'),
(84, 21, 'CPU', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(85, 22, 'Mouse', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(86, 22, 'Keyboard', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(87, 22, 'Speaker', 5.00, 1.00, 2.00, '2025-11-23 07:48:07'),
(88, 22, 'Scanner', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(89, 23, 'Windows', 5.00, 1.00, 2.00, '2025-11-23 07:48:07'),
(90, 23, 'Word', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(91, 23, 'Excel', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(92, 23, 'Photoshop', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(93, 24, 'A device that stores and processes data', 1.00, 5.00, 2.00, '2025-11-23 07:48:07'),
(94, 24, 'A printer', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(95, 24, 'A network cable', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(96, 24, 'A type of storage', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(97, 25, 'MS Word', 1.00, 5.00, 2.00, '2025-11-23 07:48:07'),
(98, 25, 'BIOS', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(99, 25, 'RAM', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(100, 25, 'ROM', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(101, 26, 'Processing', 1.00, 5.00, 2.00, '2025-11-23 07:48:07'),
(102, 26, 'Input', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(103, 26, 'Output', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(104, 26, 'Storage', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(105, 27, 'Software', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(106, 27, 'Hardware', 1.00, 5.00, 2.00, '2025-11-23 07:48:07'),
(107, 27, 'Data', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(108, 27, 'Output', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(109, 28, 'Bit', 1.00, 5.00, 2.00, '2025-11-23 07:48:07'),
(110, 28, 'Byte', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(111, 28, 'Kilobyte', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(112, 28, 'Megabyte', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(113, 29, 'Save data', 1.00, 5.00, 2.00, '2025-11-23 07:48:07'),
(114, 29, 'Process data', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(115, 29, 'Input data', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(116, 29, 'Display data', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(117, 30, 'ROM', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(118, 30, 'RAM', 1.00, 5.00, 2.00, '2025-11-23 07:48:07'),
(119, 30, 'Hard Disk', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(120, 30, 'Flash Drive', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(121, 31, 'Graphical User Interface', 1.00, 5.00, 2.00, '2025-11-23 07:48:07'),
(122, 31, 'General User Interaction', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(123, 31, 'Graphical Unit Integration', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(124, 31, 'General Utility Input', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(125, 32, 'RAM', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(126, 32, 'Hard Drive', 1.00, 5.00, 2.00, '2025-11-23 07:48:07'),
(127, 32, 'Cache', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(128, 32, 'Register', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(129, 33, 'Operating System', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(130, 33, 'Application Software', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(131, 33, 'Utility Software', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(132, 33, 'BIOS', 1.00, 5.00, 2.00, '2025-11-23 07:48:07'),
(133, 34, '.', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(134, 34, ',', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(135, 34, ';', 2.00, 1.00, 5.00, '2025-11-23 07:48:07'),
(136, 34, ':', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(137, 35, 'Database', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(138, 35, 'Program', 2.00, 1.00, 5.00, '2025-11-23 07:48:07'),
(139, 35, 'File', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(140, 35, 'Record', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(141, 36, 'Variable', 2.00, 1.00, 5.00, '2025-11-23 07:48:07'),
(142, 36, 'Function', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(143, 36, 'Class', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(144, 36, 'Loop', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(145, 37, 'HTML', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(146, 37, 'Java', 2.00, 1.00, 5.00, '2025-11-23 07:48:07'),
(147, 37, 'Google', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(148, 37, 'Excel', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(149, 38, '*', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(150, 38, '/', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(151, 38, '-', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(152, 38, '+', 2.00, 1.00, 5.00, '2025-11-23 07:48:07'),
(153, 39, 'Saves a file', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(154, 39, 'Displays output', 2.00, 1.00, 5.00, '2025-11-23 07:48:07'),
(155, 39, 'Reads input', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(156, 39, 'Exits program', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(157, 40, 'Stop a program', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(158, 40, 'Repeat actions', 2.00, 1.00, 5.00, '2025-11-23 07:48:07'),
(159, 40, 'Delete data', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(160, 40, 'Format output', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(161, 41, '//', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(162, 41, '#', 2.00, 1.00, 5.00, '2025-11-23 07:48:07'),
(163, 41, ';', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(164, 41, '*', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(165, 42, 'loop', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(166, 42, 'if statement', 2.00, 1.00, 5.00, '2025-11-23 07:48:07'),
(167, 42, 'function', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(168, 42, 'variable', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(169, 43, 'Bug', 2.00, 1.00, 5.00, '2025-11-23 07:48:07'),
(170, 43, 'Loop', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(171, 43, 'Variable', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(172, 43, 'Output', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(173, 44, 'The rules of writing code', 5.00, 1.00, 2.00, '2025-11-23 07:48:07'),
(174, 44, 'The program\'s result', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(175, 44, 'The memory type', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(176, 44, 'The variable name', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(177, 45, 'show()', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(178, 45, 'print()', 5.00, 1.00, 2.00, '2025-11-23 07:48:07'),
(179, 45, 'write()', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(180, 45, 'display()', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(181, 46, 'To store data', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(182, 46, 'To repeat tasks', 5.00, 1.00, 2.00, '2025-11-23 07:48:07'),
(183, 46, 'To stop execution', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(184, 46, 'To save files', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(185, 47, 'Repeats code', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(186, 47, 'Checks conditions', 5.00, 1.00, 2.00, '2025-11-23 07:48:07'),
(187, 47, 'Prints output', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(188, 47, 'Saves data', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(189, 48, 'Input', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(190, 48, 'Output', 5.00, 1.00, 2.00, '2025-11-23 07:48:07'),
(191, 48, 'Saving', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(192, 48, 'Calculation', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(193, 49, 'Windows', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(194, 49, 'Chrome', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(195, 49, 'Python', 5.00, 1.00, 2.00, '2025-11-23 07:48:07'),
(196, 49, 'PowerPoint', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(197, 50, 'HyperText Markup Language', 5.00, 1.00, 2.00, '2025-11-23 07:48:07'),
(198, 50, 'HighText Machine Language', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(199, 50, 'Hyper Transfer Markup Language', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(200, 50, 'Home Tool Markup Language', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(201, 51, 'Variable', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(202, 51, 'Loop', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(203, 51, 'Function', 5.00, 1.00, 2.00, '2025-11-23 07:48:07'),
(204, 51, 'Output', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(205, 52, 'Integrated Development Environment', 5.00, 1.00, 2.00, '2025-11-23 07:48:07'),
(206, 52, 'Internal Data Editor', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(207, 52, 'Input Device Emulator', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(208, 52, 'Integrated Device Encoder', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(209, 53, 'Syntax Error', 5.00, 1.00, 2.00, '2025-11-23 07:48:07'),
(210, 53, 'Logical Error', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(211, 53, 'Comment', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(212, 53, 'Loop', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(213, 54, 'A name used to store data', 1.00, 5.00, 2.00, '2025-11-23 07:48:07'),
(214, 54, 'A command', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(215, 54, 'A loop', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(216, 54, 'A bug', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(217, 55, 'A function', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(218, 55, 'A repeating structure', 1.00, 5.00, 2.00, '2025-11-23 07:48:07'),
(219, 55, 'A condition', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(220, 55, 'A comment', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(221, 56, 'Add', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(222, 56, 'Assign', 1.00, 5.00, 2.00, '2025-11-23 07:48:07'),
(223, 56, 'Compare', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(224, 56, 'Subtract', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(225, 57, '1101', 1.00, 5.00, 2.00, '2025-11-23 07:48:07'),
(226, 57, '1021', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(227, 57, '1234', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(228, 57, '2004', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(229, 58, 'def', 1.00, 5.00, 2.00, '2025-11-23 07:48:07'),
(230, 58, 'function', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(231, 58, 'create', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(232, 58, 'start', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(233, 59, 'Fixing errors', 1.00, 5.00, 2.00, '2025-11-23 07:48:07'),
(234, 59, 'Creating loops', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(235, 59, 'Declaring variables', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(236, 59, 'Writing syntax', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(237, 60, '8', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(238, 60, '12', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(239, 60, '16', 1.00, 5.00, 2.00, '2025-11-23 07:48:07'),
(240, 60, '32', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(241, 61, 'Displayed result', 1.00, 5.00, 2.00, '2025-11-23 07:48:07'),
(242, 61, 'Input data', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(243, 61, 'Error', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(244, 61, 'Storage', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(245, 62, 'Repeat code for a range', 1.00, 5.00, 2.00, '2025-11-23 07:48:07'),
(246, 62, 'Stop the program', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(247, 62, 'Declare variable', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(248, 62, 'Print one time', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(249, 63, 'A mistake in code writing', 1.00, 5.00, 2.00, '2025-11-23 07:48:07'),
(250, 63, 'A correct command', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(251, 63, 'A math error', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(252, 63, 'A logic rule', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(253, 64, 'RAM', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(254, 64, 'Hard Drive', 2.00, 1.00, 5.00, '2025-11-23 07:48:07'),
(255, 64, 'CD-ROM', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(256, 64, 'Flash Drive', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(257, 65, 'Monitor', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(258, 65, 'Mouse', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(259, 65, 'Printer', 2.00, 1.00, 5.00, '2025-11-23 07:48:07'),
(260, 65, 'Keyboard', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(261, 66, 'Motherboard', 2.00, 1.00, 5.00, '2025-11-23 07:48:07'),
(262, 66, 'Processor', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(263, 66, 'Hard Disk', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(264, 66, 'Power Supply', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(265, 67, 'Mouse', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(266, 67, 'Speaker', 2.00, 1.00, 5.00, '2025-11-23 07:48:07'),
(267, 67, 'Monitor', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(268, 67, 'Scanner', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(269, 68, 'RAM', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(270, 68, 'Cache', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(271, 68, 'Hard Disk', 2.00, 1.00, 5.00, '2025-11-23 07:48:07'),
(272, 68, 'Register', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(273, 69, 'Mouse', 2.00, 1.00, 5.00, '2025-11-23 07:48:07'),
(274, 69, 'Monitor', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(275, 69, 'CPU', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(276, 69, 'Printer', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(277, 70, 'Permanent storage', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(278, 70, 'Temporary storage', 2.00, 1.00, 5.00, '2025-11-23 07:48:07'),
(279, 70, 'Display images', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(280, 70, 'Print text', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(281, 71, 'AC to DC', 2.00, 1.00, 5.00, '2025-11-23 07:48:07'),
(282, 71, 'DC to AC', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(283, 71, 'Sound to Power', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(284, 71, 'Data to Information', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(285, 72, 'Viewing images', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(286, 72, 'Typing', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(287, 72, 'Transferring files', 2.00, 1.00, 5.00, '2025-11-23 07:48:07'),
(288, 72, 'Printing', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(289, 73, 'Mouse', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(290, 73, 'Keyboard', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(291, 73, 'Touchscreen', 2.00, 1.00, 5.00, '2025-11-23 07:48:07'),
(292, 73, 'Scanner', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(293, 74, 'Central Processing Unit', 5.00, 1.00, 2.00, '2025-11-23 07:48:07'),
(294, 74, 'Computer Power Unit', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(295, 74, 'Central Print Unit', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(296, 74, 'Core Processor Unit', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(297, 75, 'ROM', 5.00, 1.00, 2.00, '2025-11-23 07:48:07'),
(298, 75, 'RAM', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(299, 75, 'Hard Disk', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(300, 75, 'Cache', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(301, 76, 'General Power Unit', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(302, 76, 'Graphics Processing Unit', 5.00, 1.00, 2.00, '2025-11-23 07:48:07'),
(303, 76, 'Graphic Print Utility', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(304, 76, 'General Processing Unit', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(305, 77, 'Printer', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(306, 77, 'Flash Drive', 5.00, 1.00, 2.00, '2025-11-23 07:48:07'),
(307, 77, 'Monitor', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(308, 77, 'Keyboard', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(309, 78, 'Binary Digit', 5.00, 1.00, 2.00, '2025-11-23 07:48:07'),
(310, 78, 'Basic Instruction Table', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(311, 78, 'Bit Information Type', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(312, 78, 'Byte in Transfer', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(313, 79, 'Router', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(314, 79, 'Switch', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(315, 79, 'Network Card', 5.00, 1.00, 2.00, '2025-11-23 07:48:07'),
(316, 79, 'CPU', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(317, 80, 'DVD Drive', 5.00, 1.00, 2.00, '2025-11-23 07:48:07'),
(318, 80, 'ROM', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(319, 80, 'RAM', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(320, 80, 'SSD', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(321, 81, 'Control Unit', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(322, 81, 'ALU', 5.00, 1.00, 2.00, '2025-11-23 07:48:07'),
(323, 81, 'Memory', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(324, 81, 'Input Unit', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(325, 82, 'Basic Input Output System', 5.00, 1.00, 2.00, '2025-11-23 07:48:07'),
(326, 82, 'Binary Input Output Setup', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(327, 82, 'Basic Internal Operation System', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(328, 82, 'Built-in Operating System', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(329, 83, 'HDMI', 5.00, 1.00, 2.00, '2025-11-23 07:48:07'),
(330, 83, 'RJ45', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(331, 83, 'USB', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(332, 83, 'PS/2', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(333, 84, 'CPU', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(334, 84, 'Motherboard', 1.00, 5.00, 2.00, '2025-11-23 07:48:07'),
(335, 84, 'RAM', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(336, 84, 'ROM', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(337, 85, 'System Bus', 1.00, 5.00, 2.00, '2025-11-23 07:48:07'),
(338, 85, 'HDMI Cable', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(339, 85, 'Hard Disk', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(340, 85, 'Power Supply', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(341, 86, 'Modem', 1.00, 5.00, 2.00, '2025-11-23 07:48:07'),
(342, 86, 'Router', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(343, 86, 'Switch', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(344, 86, 'Printer', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(345, 87, 'Solid-State Drive', 1.00, 5.00, 2.00, '2025-11-23 07:48:07'),
(346, 87, 'System Storage Device', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(347, 87, 'Serial Storage Disk', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(348, 87, 'Standard State Drive', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(349, 88, 'Controls input', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(350, 88, 'Performs calculations', 1.00, 5.00, 2.00, '2025-11-23 07:48:07'),
(351, 88, 'Stores data', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(352, 88, 'Displays output', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(353, 89, 'Control Unit', 1.00, 5.00, 2.00, '2025-11-23 07:48:07'),
(354, 89, 'Keyboard', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(355, 89, 'Mouse', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(356, 89, 'Router', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(357, 90, 'HDMI', 1.00, 5.00, 2.00, '2025-11-23 07:48:07'),
(358, 90, 'RJ45', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(359, 90, 'USB', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(360, 90, 'PS/2', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(361, 91, 'Projector', 1.00, 5.00, 2.00, '2025-11-23 07:48:07'),
(362, 91, 'Speaker', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(363, 91, 'Microphone', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(364, 91, 'Keyboard', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(365, 92, 'Volatile', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(366, 92, 'Temporary', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(367, 92, 'Non-volatile', 1.00, 5.00, 2.00, '2025-11-23 07:48:07'),
(368, 92, 'Random', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(369, 93, 'Hard Disk', 1.00, 5.00, 2.00, '2025-11-23 07:48:07'),
(370, 93, 'CD-ROM', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(371, 93, 'USB', 1.00, 1.00, 1.00, '2025-11-23 07:48:07'),
(372, 93, 'RAM', 1.00, 1.00, 1.00, '2025-11-23 07:48:07');

-- --------------------------------------------------------

--
-- Table structure for table `assessment_results`
--

CREATE TABLE `assessment_results` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `total_questions` int(11) NOT NULL DEFAULT 0,
  `answered_questions` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

CREATE TABLE `client` (
  `id` int(11) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `middlename` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client`
--

INSERT INTO `client` (`id`, `firstname`, `middlename`, `lastname`, `created_at`) VALUES
(1, 'Hya Cynth', 'Genodepa', 'Dojillo', '2025-11-23 14:05:16');

-- --------------------------------------------------------

--
-- Table structure for table `compatibility_scores`
--

CREATE TABLE `compatibility_scores` (
  `id` int(11) NOT NULL,
  `result_id` int(11) NOT NULL,
  `it_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `cs_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `is_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `recommended_course` enum('IT','CS','IS') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_answers`
--

CREATE TABLE `exam_answers` (
  `id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL COMMENT 'References exam_sessions.id',
  `question_id` int(11) NOT NULL COMMENT 'References questions.id',
  `selected_option` enum('A','B','C','D') NOT NULL,
  `is_correct` tinyint(1) DEFAULT 0,
  `category` enum('IS','IT','CS','DIAGNOSTIC') NOT NULL,
  `points_awarded` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exam_answers`
--

INSERT INTO `exam_answers` (`id`, `session_id`, `question_id`, `selected_option`, `is_correct`, `category`, `points_awarded`, `created_at`) VALUES
(1, 1, 1, 'A', 0, 'DIAGNOSTIC', 0, '2025-11-23 14:05:32'),
(2, 1, 2, 'B', 0, 'DIAGNOSTIC', 0, '2025-11-23 14:05:49'),
(3, 1, 3, 'A', 1, 'DIAGNOSTIC', 1, '2025-11-23 14:05:56'),
(4, 1, 14, 'A', 0, 'IT', 0, '2025-11-23 14:07:53'),
(5, 1, 16, 'A', 0, 'IT', 0, '2025-11-23 14:07:58'),
(6, 1, 18, 'A', 1, 'IT', 1, '2025-11-23 14:08:04'),
(7, 1, 21, 'A', 0, 'IT', 0, '2025-11-23 14:08:07'),
(8, 1, 22, 'A', 0, 'IT', 0, '2025-11-23 14:08:12'),
(9, 1, 23, 'A', 1, 'IT', 1, '2025-11-23 14:08:26'),
(10, 1, 47, 'B', 1, 'IT', 1, '2025-11-23 14:08:37'),
(11, 1, 48, 'B', 1, 'IT', 1, '2025-11-23 14:08:47'),
(12, 1, 49, 'C', 1, 'IT', 1, '2025-11-23 14:08:55'),
(13, 1, 50, 'A', 1, 'IT', 1, '2025-11-23 14:09:03'),
(14, 1, 51, 'C', 1, 'IT', 1, '2025-11-23 14:09:20'),
(15, 1, 52, 'A', 1, 'IT', 1, '2025-11-23 14:09:26'),
(16, 1, 53, 'A', 1, 'IT', 1, '2025-11-23 14:09:37'),
(17, 1, 74, 'A', 1, 'IT', 1, '2025-11-23 14:09:42'),
(18, 1, 78, 'A', 1, 'IT', 1, '2025-11-23 14:09:46'),
(19, 1, 81, 'A', 0, 'IT', 0, '2025-11-23 14:09:59'),
(20, 1, 83, 'A', 1, 'IT', 1, '2025-11-23 14:10:08');

-- --------------------------------------------------------

--
-- Table structure for table `exam_results`
--

CREATE TABLE `exam_results` (
  `id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL COMMENT 'References exam_sessions.id',
  `recommended_course` enum('IS','IT','CS','UNDECIDED') DEFAULT 'UNDECIDED',
  `final_score` int(11) DEFAULT 0,
  `confidence_score` float DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exam_results`
--

INSERT INTO `exam_results` (`id`, `session_id`, `recommended_course`, `final_score`, `confidence_score`, `created_at`) VALUES
(1, 1, 'IT', 13, 0.514286, '2025-11-23 14:10:10');

-- --------------------------------------------------------

--
-- Table structure for table `exam_sessions`
--

CREATE TABLE `exam_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'References client.id',
  `current_question_id` int(11) DEFAULT NULL COMMENT 'References questions.id',
  `dominant_category` enum('IS','IT','CS') DEFAULT NULL,
  `stage` enum('DIAGNOSTIC','CATEGORY','FINISHED') DEFAULT 'DIAGNOSTIC',
  `confidence_score` float DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exam_sessions`
--

INSERT INTO `exam_sessions` (`id`, `user_id`, `current_question_id`, `dominant_category`, `stage`, `confidence_score`, `created_at`) VALUES
(1, 1, 83, 'IS', 'FINISHED', 0.514286, '2025-11-23 14:05:20');

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `question_type` enum('single','multiple') NOT NULL DEFAULT 'single',
  `category` enum('IS','IT','CS','DIAGNOSTIC') DEFAULT 'DIAGNOSTIC',
  `difficulty` enum('EASY','MEDIUM','HARD') DEFAULT 'MEDIUM',
  `weight` int(11) DEFAULT 1,
  `correct_option` enum('A','B','C','D') DEFAULT NULL,
  `option_a` varchar(255) DEFAULT NULL,
  `option_b` varchar(255) DEFAULT NULL,
  `option_c` varchar(255) DEFAULT NULL,
  `option_d` varchar(255) DEFAULT NULL,
  `topic` varchar(100) DEFAULT NULL,
  `is_correct_answer` int(11) DEFAULT NULL COMMENT 'Option ID that is the correct answer',
  `order_number` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `question_text`, `question_type`, `category`, `difficulty`, `weight`, `correct_option`, `option_a`, `option_b`, `option_c`, `option_d`, `topic`, `is_correct_answer`, `order_number`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'What is the full meaning of ICT?', 'single', 'DIAGNOSTIC', 'EASY', 1, 'B', 'Information and Computer Technology', 'Information and Communication Technology', 'Integrated Computer Training', 'Internal Connection Tool', NULL, NULL, 1, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(2, 'What is a computer?', 'single', 'DIAGNOSTIC', 'EASY', 1, 'A', 'A device that stores and processes data', 'A printer', 'A network cable', 'A type of storage', NULL, NULL, 2, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(3, 'Which of the following is an example of application software?', 'single', 'DIAGNOSTIC', 'EASY', 1, 'A', 'MS Word', 'BIOS', 'RAM', 'ROM', NULL, NULL, 3, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(4, 'What is the full meaning of ICT?', 'single', 'IS', 'EASY', 1, 'B', 'Information and Computer Technology', 'Information and Communication Technology', 'Integrated Computer Training', 'Internal Connection Tool', NULL, NULL, 4, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(5, 'Which device is used to display output on a screen?', 'single', 'IS', 'MEDIUM', 2, 'B', 'Mouse', 'Monitor', 'Keyboard', 'Scanner', NULL, NULL, 5, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(6, 'The main function of a computer\'s CPU is to:', 'single', 'IS', 'HARD', 3, 'B', 'Store data', 'Perform calculations', 'Print documents', 'Connect to the internet', NULL, NULL, 6, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(7, 'What is the brain of the computer?', 'single', 'IS', 'EASY', 1, 'B', 'Hard Disk', 'CPU', 'RAM', 'Monitor', NULL, NULL, 7, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(8, 'Which one is an example of software?', 'single', 'IS', 'EASY', 1, 'B', 'Printer', 'Microsoft Word', 'USB Cable', 'Mouse', NULL, NULL, 8, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(9, 'What does a keyboard help you do?', 'single', 'IS', 'EASY', 1, 'B', 'Print documents', 'Type data', 'Store files', 'Scan images', NULL, NULL, 9, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(10, 'What is the full form of \"WWW\"?', 'single', 'IS', 'EASY', 1, 'A', 'World Wide Web', 'Wide World Web', 'World Web Wide', 'Web World Wide', NULL, NULL, 10, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(11, 'Data is processed into:', 'single', 'IS', 'MEDIUM', 2, 'C', 'Facts', 'Instructions', 'Information', 'Numbers', NULL, NULL, 11, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(12, 'What part of the computer temporarily stores data?', 'single', 'IS', 'EASY', 1, 'B', 'Hard Drive', 'RAM', 'CD-ROM', 'USB Drive', NULL, NULL, 12, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(13, 'Which is an example of an input device?', 'single', 'IS', 'EASY', 1, 'B', 'Monitor', 'Mouse', 'Printer', 'Speaker', NULL, NULL, 13, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(14, 'What does \"IT\" stand for?', 'single', 'IT', 'EASY', 1, 'B', 'Information Tool', 'Information Technology', 'Internet Technology', 'Internal Transmission', NULL, NULL, 14, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(15, 'Which device is used to process data?', 'single', 'IT', 'MEDIUM', 2, 'B', 'Mouse', 'CPU', 'Monitor', 'Keyboard', NULL, NULL, 15, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(16, 'Which is a storage device?', 'single', 'IT', 'EASY', 1, 'B', 'Printer', 'Hard Disk', 'Monitor', 'Speaker', NULL, NULL, 16, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(17, 'What is the function of an operating system?', 'single', 'IT', 'HARD', 3, 'B', 'Hardware connection', 'Controls computer operations', 'Runs hardware', 'Plays videos', NULL, NULL, 17, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(18, 'What type of device is a keyboard?', 'single', 'IT', 'EASY', 1, 'A', 'Input', 'Output', 'Storage', 'Processing', NULL, NULL, 18, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(19, 'Which computer generation used microprocessors?', 'single', 'IT', 'HARD', 3, 'C', 'First', 'Second', 'Fourth', 'Fifth', NULL, NULL, 19, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(20, 'Which port is used to connect USB devices?', 'single', 'IT', 'HARD', 3, 'B', 'Serial', 'USB', 'HDMI', 'VGA', NULL, NULL, 20, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(21, 'Which of these is not a hardware device?', 'single', 'IT', 'EASY', 1, 'C', 'Keyboard', 'Monitor', 'Gmail', 'CPU', NULL, NULL, 21, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(22, 'What is an example of an output device?', 'single', 'IT', 'EASY', 1, 'C', 'Mouse', 'Keyboard', 'Speaker', 'Scanner', NULL, NULL, 22, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(23, 'Which one is a system software?', 'single', 'IT', 'EASY', 1, 'A', 'Windows', 'Word', 'Excel', 'Photoshop', NULL, NULL, 23, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(24, 'What is a computer?', 'single', 'CS', 'EASY', 1, 'A', 'A device that stores and processes data', 'A printer', 'A network cable', 'A type of storage', NULL, NULL, 24, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(25, 'Which of the following is an example of application software?', 'single', 'CS', 'EASY', 1, 'A', 'MS Word', 'BIOS', 'RAM', 'ROM', NULL, NULL, 25, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(26, 'The process of turning data into information is called:', 'single', 'CS', 'MEDIUM', 2, 'A', 'Processing', 'Input', 'Output', 'Storage', NULL, NULL, 26, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(27, 'The physical parts of a computer are called:', 'single', 'CS', 'EASY', 1, 'B', 'Software', 'Hardware', 'Data', 'Output', NULL, NULL, 27, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(28, 'What is the smallest unit of data?', 'single', 'CS', 'EASY', 1, 'A', 'Bit', 'Byte', 'Kilobyte', 'Megabyte', NULL, NULL, 28, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(29, 'The main function of a storage device is to:', 'single', 'CS', 'HARD', 3, 'A', 'Save data', 'Process data', 'Input data', 'Display data', NULL, NULL, 29, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(30, 'Which memory is volatile?', 'single', 'CS', 'EASY', 1, 'B', 'ROM', 'RAM', 'Hard Disk', 'Flash Drive', NULL, NULL, 30, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(31, 'What does GUI stand for?', 'single', 'CS', 'HARD', 3, 'A', 'Graphical User Interface', 'General User Interaction', 'Graphical Unit Integration', 'General Utility Input', NULL, NULL, 31, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(32, 'Which device is used for permanent data storage?', 'single', 'CS', 'EASY', 1, 'B', 'RAM', 'Hard Drive', 'Cache', 'Register', NULL, NULL, 32, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(33, 'Which software helps the computer to start?', 'single', 'CS', 'MEDIUM', 2, 'D', 'Operating System', 'Application Software', 'Utility Software', 'BIOS', NULL, NULL, 33, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(34, 'Which symbol is used to end a statement in C?', 'single', 'IS', 'MEDIUM', 2, 'C', '.', ',', ';', ':', NULL, NULL, 34, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(35, 'A set of instructions written for a computer is called a:', 'single', 'IS', 'EASY', 1, 'B', 'Database', 'Program', 'File', 'Record', NULL, NULL, 35, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(36, 'What is used to store data in a program?', 'single', 'IS', 'MEDIUM', 2, 'A', 'Variable', 'Function', 'Class', 'Loop', NULL, NULL, 36, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(37, 'Which of the following is a programming language?', 'single', 'IS', 'EASY', 1, 'B', 'HTML', 'Java', 'Google', 'Excel', NULL, NULL, 37, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(38, 'Which operator is used for addition?', 'single', 'IS', 'HARD', 3, 'D', '*', '/', '-', '+', NULL, NULL, 38, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(39, 'What does \"print()\" do in Python?', 'single', 'IS', 'EASY', 1, 'B', 'Saves a file', 'Displays output', 'Reads input', 'Exits program', NULL, NULL, 39, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(40, 'A loop is used to:', 'single', 'IS', 'HARD', 3, 'B', 'Stop a program', 'Repeat actions', 'Delete data', 'Format output', NULL, NULL, 40, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(41, 'What symbol is used for comments in Python?', 'single', 'IS', 'EASY', 1, 'B', '//', '#', ';', '*', NULL, NULL, 41, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(42, 'Which of these is used for decision-making?', 'single', 'IS', 'EASY', 1, 'B', 'loop', 'if statement', 'function', 'variable', NULL, NULL, 42, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(43, 'A programming error is called:', 'single', 'IS', 'EASY', 1, 'A', 'Bug', 'Loop', 'Variable', 'Output', NULL, NULL, 43, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(44, 'In programming, \"syntax\" means:', 'single', 'IT', 'HARD', 3, 'A', 'The rules of writing code', 'The program\'s result', 'The memory type', 'The variable name', NULL, NULL, 44, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(45, 'In Python, which keyword is used to print output?', 'single', 'IT', 'MEDIUM', 2, 'B', 'show()', 'print()', 'write()', 'display()', NULL, NULL, 45, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(46, 'What is a loop used for?', 'single', 'IT', 'HARD', 3, 'B', 'To store data', 'To repeat tasks', 'To stop execution', 'To save files', NULL, NULL, 46, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(47, 'What does \"if\" statement do?', 'single', 'IT', 'EASY', 1, 'B', 'Repeats code', 'Checks conditions', 'Prints output', 'Saves data', NULL, NULL, 47, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(48, 'In C language, \"printf\" is used for:', 'single', 'IT', 'EASY', 1, 'B', 'Input', 'Output', 'Saving', 'Calculation', NULL, NULL, 48, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(49, 'Which of these is a programming language?', 'single', 'IT', 'EASY', 1, 'C', 'Windows', 'Chrome', 'Python', 'PowerPoint', NULL, NULL, 49, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(50, 'What does HTML stand for?', 'single', 'IT', 'EASY', 1, 'A', 'HyperText Markup Language', 'HighText Machine Language', 'Hyper Transfer Markup Language', 'Home Tool Markup Language', NULL, NULL, 50, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(51, 'A group of related statements in a program is called a:', 'single', 'IT', 'EASY', 1, 'C', 'Variable', 'Loop', 'Function', 'Output', NULL, NULL, 51, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(52, 'What does IDE stand for?', 'single', 'IT', 'EASY', 1, 'A', 'Integrated Development Environment', 'Internal Data Editor', 'Input Device Emulator', 'Integrated Device Encoder', NULL, NULL, 52, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(53, 'Which of these errors stops a program from running?', 'single', 'IT', 'EASY', 1, 'A', 'Syntax Error', 'Logical Error', 'Comment', 'Loop', NULL, NULL, 53, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(54, 'What is a variable?', 'single', 'CS', 'HARD', 3, 'A', 'A name used to store data', 'A command', 'A loop', 'A bug', NULL, NULL, 54, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(55, 'In programming, what is a \"loop\"?', 'single', 'CS', 'HARD', 3, 'B', 'A function', 'A repeating structure', 'A condition', 'A comment', NULL, NULL, 55, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(56, 'What does the operator \"=\" mean?', 'single', 'CS', 'HARD', 3, 'B', 'Add', 'Assign', 'Compare', 'Subtract', NULL, NULL, 56, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(57, 'Which of the following is a valid binary number?', 'single', 'CS', 'HARD', 3, 'A', '1101', '1021', '1234', '2004', NULL, NULL, 57, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(58, 'Which keyword is used to create a function in Python?', 'single', 'CS', 'HARD', 3, 'A', 'def', 'function', 'create', 'start', NULL, NULL, 58, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(59, 'What is debugging?', 'single', 'CS', 'HARD', 3, 'A', 'Fixing errors', 'Creating loops', 'Declaring variables', 'Writing syntax', NULL, NULL, 59, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(60, 'What is the value can be represented by 2^4?', 'single', 'CS', 'HARD', 3, 'C', '8', '12', '16', '32', NULL, NULL, 60, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(61, 'In a program, \"output\" refers to:', 'single', 'CS', 'EASY', 1, 'A', 'Displayed result', 'Input data', 'Error', 'Storage', NULL, NULL, 61, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(62, 'What does \"for loop\" do?', 'single', 'CS', 'HARD', 3, 'A', 'Repeat code for a range', 'Stop the program', 'Declare variable', 'Print one time', NULL, NULL, 62, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(63, 'What is a syntax error?', 'single', 'CS', 'HARD', 3, 'A', 'A mistake in code writing', 'A correct command', 'A math error', 'A logic rule', NULL, NULL, 63, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(64, 'What is the main storage device in a computer?', 'single', 'IS', 'EASY', 1, 'B', 'RAM', 'Hard Drive', 'CD-ROM', 'Flash Drive', NULL, NULL, 64, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(65, 'Which device is used to print documents?', 'single', 'IS', 'MEDIUM', 2, 'C', 'Monitor', 'Mouse', 'Printer', 'Keyboard', NULL, NULL, 65, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(66, 'What connects all computer parts together?', 'single', 'IS', 'MEDIUM', 2, 'A', 'Motherboard', 'Processor', 'Hard Disk', 'Power Supply', NULL, NULL, 66, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(67, 'Which hardware is used to hear sounds?', 'single', 'IS', 'MEDIUM', 2, 'B', 'Mouse', 'Speaker', 'Monitor', 'Scanner', NULL, NULL, 67, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(68, 'A device that stores data permanently is:', 'single', 'IS', 'EASY', 1, 'C', 'RAM', 'Cache', 'Hard Disk', 'Register', NULL, NULL, 68, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(69, 'Which is used to move the cursor on the screen?', 'single', 'IS', 'MEDIUM', 2, 'A', 'Mouse', 'Monitor', 'CPU', 'Printer', NULL, NULL, 69, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(70, 'What is the function of RAM?', 'single', 'IS', 'HARD', 3, 'B', 'Permanent storage', 'Temporary storage', 'Display images', 'Print text', NULL, NULL, 70, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(71, 'The power supply converts:', 'single', 'IS', 'MEDIUM', 2, 'A', 'AC to DC', 'DC to AC', 'Sound to Power', 'Data to Information', NULL, NULL, 71, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(72, 'What is a USB used for?', 'single', 'IS', 'EASY', 1, 'C', 'Viewing images', 'Typing', 'Transferring files', 'Printing', NULL, NULL, 72, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(73, 'Which device allows you to enter data by touch?', 'single', 'IS', 'MEDIUM', 2, 'C', 'Mouse', 'Keyboard', 'Touchscreen', 'Scanner', NULL, NULL, 73, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(74, 'What does CPU stand for?', 'single', 'IT', 'EASY', 1, 'A', 'Central Processing Unit', 'Computer Power Unit', 'Central Print Unit', 'Core Processor Unit', NULL, NULL, 74, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(75, 'Which part stores the BIOS?', 'single', 'IT', 'HARD', 3, 'A', 'ROM', 'RAM', 'Hard Disk', 'Cache', NULL, NULL, 75, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(76, 'What does GPU stand for?', 'single', 'IT', 'HARD', 3, 'B', 'General Power Unit', 'Graphics Processing Unit', 'Graphic Print Utility', 'General Processing Unit', NULL, NULL, 76, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(77, 'Which device is used to store backups?', 'single', 'IT', 'MEDIUM', 2, 'B', 'Printer', 'Flash Drive', 'Monitor', 'Keyboard', NULL, NULL, 77, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(78, 'What does \"bit\" represent?', 'single', 'IT', 'EASY', 1, 'A', 'Binary Digit', 'Basic Instruction Table', 'Bit Information Type', 'Byte in Transfer', NULL, NULL, 78, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(79, 'What hardware connects the computer to a network?', 'single', 'IT', 'MEDIUM', 2, 'C', 'Router', 'Switch', 'Network Card', 'CPU', NULL, NULL, 79, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(80, 'Which hardware is used to read CDs?', 'single', 'IT', 'MEDIUM', 2, 'A', 'DVD Drive', 'ROM', 'RAM', 'SSD', NULL, NULL, 80, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(81, 'Which part of CPU performs calculations?', 'single', 'IT', 'EASY', 1, 'B', 'Control Unit', 'ALU', 'Memory', 'Input Unit', NULL, NULL, 81, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(82, 'What is the full form of BIOS?', 'single', 'IT', 'HARD', 3, 'A', 'Basic Input Output System', 'Binary Input Output Setup', 'Basic Internal Operation System', 'Built-in Operating System', NULL, NULL, 82, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(83, 'Which connector is used for monitors?', 'single', 'IT', 'EASY', 1, 'A', 'HDMI', 'RJ45', 'USB', 'PS/2', NULL, NULL, 83, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(84, 'What is the main circuit board called?', 'single', 'CS', 'EASY', 1, 'B', 'CPU', 'Motherboard', 'RAM', 'ROM', NULL, NULL, 84, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(85, 'What connects the CPU to memory?', 'single', 'CS', 'MEDIUM', 2, 'A', 'System Bus', 'HDMI Cable', 'Hard Disk', 'Power Supply', NULL, NULL, 85, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(86, 'The device that converts digital signals to analog is:', 'single', 'CS', 'MEDIUM', 2, 'A', 'Modem', 'Router', 'Switch', 'Printer', NULL, NULL, 86, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(87, 'What does SSD stand for?', 'single', 'CS', 'HARD', 3, 'A', 'Solid-State Drive', 'System Storage Device', 'Serial Storage Disk', 'Standard State Drive', NULL, NULL, 87, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(88, 'What does the ALU do?', 'single', 'CS', 'HARD', 3, 'B', 'Controls input', 'Performs calculations', 'Stores data', 'Displays output', NULL, NULL, 88, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(89, 'What hardware controls data flow between computer parts?', 'single', 'CS', 'EASY', 1, 'A', 'Control Unit', 'Keyboard', 'Mouse', 'Router', NULL, NULL, 89, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(90, 'Which port connects external monitors?', 'single', 'CS', 'HARD', 3, 'A', 'HDMI', 'RJ45', 'USB', 'PS/2', NULL, NULL, 90, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(91, 'Which device is used for video output?', 'single', 'CS', 'EASY', 1, 'A', 'Projector', 'Speaker', 'Microphone', 'Keyboard', NULL, NULL, 91, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(92, 'What type of memory is ROM?', 'single', 'CS', 'EASY', 1, 'C', 'Volatile', 'Temporary', 'Non-volatile', 'Random', NULL, NULL, 92, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07'),
(93, 'The device used to store data magnetically is:', 'single', 'CS', 'MEDIUM', 2, 'A', 'Hard Disk', 'CD-ROM', 'USB', 'RAM', NULL, NULL, 93, 1, '2025-11-23 07:48:07', '2025-11-23 07:48:07');

-- --------------------------------------------------------

--
-- Table structure for table `student_answers`
--

CREATE TABLE `student_answers` (
  `id` int(11) NOT NULL,
  `result_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `answer_options`
--
ALTER TABLE `answer_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `assessment_results`
--
ALTER TABLE `assessment_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `compatibility_scores`
--
ALTER TABLE `compatibility_scores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `result_id` (`result_id`);

--
-- Indexes for table `exam_answers`
--
ALTER TABLE `exam_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_session_id` (`session_id`),
  ADD KEY `idx_question_id` (`question_id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_session_question` (`session_id`,`question_id`);

--
-- Indexes for table `exam_results`
--
ALTER TABLE `exam_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_session_id` (`session_id`);

--
-- Indexes for table `exam_sessions`
--
ALTER TABLE `exam_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_stage` (`stage`),
  ADD KEY `idx_current_question` (`current_question_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_number` (`order_number`),
  ADD KEY `is_active` (`is_active`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_difficulty` (`difficulty`),
  ADD KEY `idx_category_difficulty` (`category`,`difficulty`);

--
-- Indexes for table `student_answers`
--
ALTER TABLE `student_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `result_id` (`result_id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `option_id` (`option_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `answer_options`
--
ALTER TABLE `answer_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=373;

--
-- AUTO_INCREMENT for table `assessment_results`
--
ALTER TABLE `assessment_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client`
--
ALTER TABLE `client`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `compatibility_scores`
--
ALTER TABLE `compatibility_scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_answers`
--
ALTER TABLE `exam_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `exam_results`
--
ALTER TABLE `exam_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `exam_sessions`
--
ALTER TABLE `exam_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT for table `student_answers`
--
ALTER TABLE `student_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `answer_options`
--
ALTER TABLE `answer_options`
  ADD CONSTRAINT `answer_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assessment_results`
--
ALTER TABLE `assessment_results`
  ADD CONSTRAINT `assessment_results_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `compatibility_scores`
--
ALTER TABLE `compatibility_scores`
  ADD CONSTRAINT `compatibility_scores_ibfk_1` FOREIGN KEY (`result_id`) REFERENCES `assessment_results` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_answers`
--
ALTER TABLE `exam_answers`
  ADD CONSTRAINT `exam_answers_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `exam_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_results`
--
ALTER TABLE `exam_results`
  ADD CONSTRAINT `exam_results_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `exam_sessions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_sessions`
--
ALTER TABLE `exam_sessions`
  ADD CONSTRAINT `exam_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `client` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_sessions_ibfk_2` FOREIGN KEY (`current_question_id`) REFERENCES `questions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `student_answers`
--
ALTER TABLE `student_answers`
  ADD CONSTRAINT `student_answers_ibfk_1` FOREIGN KEY (`result_id`) REFERENCES `assessment_results` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_answers_ibfk_3` FOREIGN KEY (`option_id`) REFERENCES `answer_options` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
