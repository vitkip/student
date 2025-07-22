-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 21, 2025 at 04:17 PM
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
-- Database: `register-learning`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_years`
--

CREATE TABLE `academic_years` (
  `id` int(11) NOT NULL,
  `year` varchar(9) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `academic_years`
--

INSERT INTO `academic_years` (`id`, `year`, `status`) VALUES
(1, '2025-2026', 'active'),
(2, '2026-2027', 'active'),
(3, '2027-2028', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `majors`
--

CREATE TABLE `majors` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(10) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `majors`
--

INSERT INTO `majors` (`id`, `name`, `code`, `description`, `status`) VALUES
(1, 'ສາຍ ຄູພຸດທະສາດສະໜາ ແລະ ພາສາລາວ-ວັນນະຄະດີ', 'BL', NULL, 'active'),
(2, 'ສາຍຄູ ພາສາອັງກິດ', 'ENG', NULL, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `gender` enum('ພຣະ','ສ.ນ','ຊາຍ','ຍິງ','ອຶ່ນໆ') NOT NULL,
  `dob` date NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `village` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `accommodation_type` enum('ຫາວັດໃຫ້','ມີວັດຢູ່ແລ້ວ') DEFAULT 'ມີວັດຢູ່ແລ້ວ',
  `photo` varchar(255) DEFAULT NULL,
  `registered_at` datetime DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `major_id` int(11) DEFAULT NULL,
  `academic_year_id` int(11) DEFAULT NULL,
  `previous_school` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `full_name`, `email`, `created_at`, `updated_at`, `last_login`, `is_active`) VALUES
(1, 'admin', '$2a$12$nQOCZpDj7CK7ZUoSgrgZmOC.owLx/17T6oMuGE7QhDl0w/Mxfke8S', 'admin', 'ຜູ້ຄຸ້ມຄອງລະບົບ', 'admin@example.com', '2025-07-20 13:46:47', '2025-07-21 13:50:27', '2025-07-21 13:50:27', 1),
(2, 'user', '$2a$12$NulsAr57Jh6zJ4AK/jiLluywQuKiqSR7Xvga.uPs77dA11tjpWZvC', 'user', 'ຜູ້ໃຊ້ທົ່ວໄປ', 'user@example.com', '2025-07-20 13:46:47', '2025-07-21 02:57:08', '2025-07-21 02:57:08', 1),
(6, 'ananthasak', '$2y$10$SA7qS0r8czoZHZ.Lfli4OuHK2YH4UKSum4AbgbfbwKu52IVspP.U6', 'admin', 'ອານັນທະສັກ ພັດທະສີລາ', 'phathasyla@gmail.com', '2025-07-21 02:36:15', '2025-07-21 03:04:57', '2025-07-21 03:04:57', 1),
(7, 'admin1', '$2y$10$TeERJqxlpIY9k/iXAxmT2u7FkeJ3LQRkhGjD8ZuIOVLOQ1lcbSeUC', 'user', 'admin', 'admin@gmail.com', '2025-07-21 03:00:23', '2025-07-21 03:00:47', '2025-07-21 03:00:47', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_years`
--
ALTER TABLE `academic_years`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `year` (`year`);

--
-- Indexes for table `majors`
--
ALTER TABLE `majors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD KEY `major_id` (`major_id`),
  ADD KEY `academic_year_id` (`academic_year_id`),
  ADD KEY `idx_student_id` (`student_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_years`
--
ALTER TABLE `academic_years`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `majors`
--
ALTER TABLE `majors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`major_id`) REFERENCES `majors` (`id`),
  ADD CONSTRAINT `students_ibfk_2` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
