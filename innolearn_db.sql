-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 04, 2025 at 10:30 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `innolearn_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','super_admin') NOT NULL DEFAULT 'admin',
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `role`, `is_active`) VALUES
(1, 'admin', 'admin123', 'admin', 1),
(2, 'superadmin', 'admin123', 'super_admin', 1),
(3, 'admin2', 'admin222', 'admin', 1);

-- --------------------------------------------------------

--
-- Table structure for table `nominations`
--

CREATE TABLE `nominations` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `category` varchar(50) NOT NULL,
  `reason` text NOT NULL,
  `nominated_by` int(11) NOT NULL,
  `nomination_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nominations`
--

INSERT INTO `nominations` (`id`, `student_id`, `category`, `reason`, `nominated_by`, `nomination_date`) VALUES
(1, 2, 'Leadership', 'yyg', 1, '2025-05-03 15:40:56');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `student_id`, `project_id`, `message`, `created_at`) VALUES
(1, 2, 4, 'Your project \'noname\' has been graded. Grade: 0%', '2025-05-03 18:57:44'),
(2, 2, 4, 'Your project \'noname\' has been graded. Grade: 0%', '2025-05-03 18:57:50');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `thumbnail_path` varchar(255) DEFAULT NULL,
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `student_id`, `title`, `description`, `category`, `file_path`, `thumbnail_path`, `submission_date`) VALUES
(4, 2, 'noname', 'noname', 'Research', 'C:\\xampp\\htdocs\\TopTrack\\uploads\\projects\\680c13e3b0eb1.jpg', 'C:\\xampp\\htdocs\\TopTrack\\uploads\\projects\\680c13e3b0eb1.jpg', '2025-05-03 14:35:32'),
(5, 2, 'hi', 'jyfhjhj', 'Development', NULL, NULL, '2025-05-03 22:28:01');

-- --------------------------------------------------------

--
-- Table structure for table `project_grades`
--

CREATE TABLE `project_grades` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `grade` decimal(5,2) NOT NULL,
  `feedback` text DEFAULT NULL,
  `graded_by` int(11) NOT NULL,
  `graded_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_grades`
--

INSERT INTO `project_grades` (`id`, `project_id`, `grade`, `feedback`, `graded_by`, `graded_at`) VALUES
(1, 4, 0.00, 'Consider adding more image references to the description (found 0)\nProject description could be more detailed', 1, '2025-05-03 18:57:42'),
(2, 4, 0.00, 'Consider adding more image references to the description (found 0)\nProject description could be more detailed', 1, '2025-05-03 18:57:48');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `class` varchar(50) NOT NULL,
  `department` varchar(100) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_id`, `first_name`, `last_name`, `email`, `class`, `department`, `profile_image`, `created_at`, `admin_id`) VALUES
(1, '[value-2]', '[value-3]', 'ilazgashi@gmail.com', '[value-5]', '[value-6]', '[value-7]', '[value-8]', '0000-00-00 00:00:00', 1),
(2, 'STU1001', 'Ariana', 'Kelmendi', 'ariana.kelmendi@example.com', '10A', 'Computer Science', 'uploads/students/profile_1746279382_STU1001.jpg', '2025-05-03 13:32:56', 1),
(3, 'STU1002', 'Dren', 'Gashi', 'dren.gashi@example.com', '11B', 'Mathematics', 'dren.jpg', '2025-05-03 13:32:56', 1),
(4, 'STU1003', 'Ilir', 'Berisha', 'ilir.berisha@example.com', '12C', 'Physics', 'ilir.jpg', '2025-05-03 13:32:56', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `nominations`
--
ALTER TABLE `nominations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `nominated_by` (`nominated_by`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `project_grades`
--
ALTER TABLE `project_grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `graded_by` (`graded_by`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD KEY `fk_admin_id` (`admin_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `nominations`
--
ALTER TABLE `nominations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `project_grades`
--
ALTER TABLE `project_grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `nominations`
--
ALTER TABLE `nominations`
  ADD CONSTRAINT `nominations_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `nominations_ibfk_2` FOREIGN KEY (`nominated_by`) REFERENCES `admins` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`);

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- Constraints for table `project_grades`
--
ALTER TABLE `project_grades`
  ADD CONSTRAINT `project_grades_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`),
  ADD CONSTRAINT `project_grades_ibfk_2` FOREIGN KEY (`graded_by`) REFERENCES `admins` (`id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_admin_id` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
