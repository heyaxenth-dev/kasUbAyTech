-- Database Schema for kasUbAyTech Assessment System

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS kasubaytech_db;
USE kasubaytech_db;

-- Students/Client table (if not exists)
CREATE TABLE IF NOT EXISTS `client` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(100) NOT NULL,
  `middlename` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin table
CREATE TABLE IF NOT EXISTS `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Questions table
CREATE TABLE IF NOT EXISTS `questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_text` text NOT NULL,
  `question_type` enum('single','multiple') NOT NULL DEFAULT 'single',
  `order_number` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_number` (`order_number`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Answer options table
CREATE TABLE IF NOT EXISTS `answer_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) NOT NULL,
  `option_text` varchar(255) NOT NULL,
  `it_score` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Score for IT course compatibility',
  `cs_score` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Score for CS course compatibility',
  `is_score` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Score for IS course compatibility',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `question_id` (`question_id`),
  CONSTRAINT `answer_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Assessment results table
CREATE TABLE IF NOT EXISTS `assessment_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `started_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` timestamp NULL DEFAULT NULL,
  `total_questions` int(11) NOT NULL DEFAULT 0,
  `answered_questions` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  CONSTRAINT `assessment_results_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Student answers table
CREATE TABLE IF NOT EXISTS `student_answers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `result_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `result_id` (`result_id`),
  KEY `question_id` (`question_id`),
  KEY `option_id` (`option_id`),
  CONSTRAINT `student_answers_ibfk_1` FOREIGN KEY (`result_id`) REFERENCES `assessment_results` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_answers_ibfk_3` FOREIGN KEY (`option_id`) REFERENCES `answer_options` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Compatibility scores table
CREATE TABLE IF NOT EXISTS `compatibility_scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `result_id` int(11) NOT NULL,
  `it_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `cs_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `is_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `recommended_course` enum('IT','CS','IS') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `result_id` (`result_id`),
  CONSTRAINT `compatibility_scores_ibfk_1` FOREIGN KEY (`result_id`) REFERENCES `assessment_results` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin (username: admin, password: admin123)
INSERT INTO `admin` (`username`, `password`, `email`) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@kasubaytech.com')
ON DUPLICATE KEY UPDATE username=username;

-- Insert sample questions (you can modify these)
INSERT INTO `questions` (`question_text`, `question_type`, `order_number`) VALUES
('What is your favorite programming language?', 'single', 1),
('Which front-end framework do you prefer?', 'single', 2),
('Which databases have you used? (Select all that apply)', 'multiple', 3),
('What interests you most in technology?', 'single', 4),
('How do you prefer to solve problems?', 'single', 5)
ON DUPLICATE KEY UPDATE question_text=question_text;

-- Insert answer options with compatibility scores
-- Question 1: Programming Language
INSERT INTO `answer_options` (`question_id`, `option_text`, `it_score`, `cs_score`, `is_score`) VALUES
(1, 'Python', 2.0, 5.0, 3.0),
(1, 'JavaScript', 4.0, 3.0, 5.0),
(1, 'PHP', 5.0, 2.0, 4.0),
(1, 'Java', 3.0, 5.0, 2.0),
(1, 'C++', 2.0, 5.0, 1.0)
ON DUPLICATE KEY UPDATE option_text=option_text;

-- Question 2: Front-end Framework
INSERT INTO `answer_options` (`question_id`, `option_text`, `it_score`, `cs_score`, `is_score`) VALUES
(2, 'Bootstrap', 4.0, 2.0, 5.0),
(2, 'Tailwind', 3.0, 3.0, 4.0),
(2, 'Material UI', 2.0, 4.0, 3.0),
(2, 'React', 3.0, 5.0, 4.0),
(2, 'Vue.js', 3.0, 4.0, 4.0)
ON DUPLICATE KEY UPDATE option_text=option_text;

-- Question 3: Databases
INSERT INTO `answer_options` (`question_id`, `option_text`, `it_score`, `cs_score`, `is_score`) VALUES
(3, 'MySQL', 5.0, 3.0, 5.0),
(3, 'PostgreSQL', 4.0, 4.0, 4.0),
(3, 'MongoDB', 3.0, 5.0, 4.0),
(3, 'SQLite', 3.0, 3.0, 3.0),
(3, 'Oracle', 4.0, 2.0, 5.0)
ON DUPLICATE KEY UPDATE option_text=option_text;

-- Question 4: Technology Interest
INSERT INTO `answer_options` (`question_id`, `option_text`, `it_score`, `cs_score`, `is_score`) VALUES
(4, 'Web Development', 5.0, 3.0, 5.0),
(4, 'Software Engineering', 3.0, 5.0, 3.0),
(4, 'Data Science', 2.0, 5.0, 4.0),
(4, 'Network Administration', 5.0, 2.0, 3.0),
(4, 'System Analysis', 3.0, 3.0, 5.0)
ON DUPLICATE KEY UPDATE option_text=option_text;

-- Question 5: Problem Solving
INSERT INTO `answer_options` (`question_id`, `option_text`, `it_score`, `cs_score`, `is_score`) VALUES
(5, 'Logical step-by-step approach', 3.0, 5.0, 3.0),
(5, 'Creative and innovative solutions', 4.0, 4.0, 5.0),
(5, 'Following established procedures', 5.0, 2.0, 4.0),
(5, 'Collaborative team approach', 4.0, 3.0, 5.0),
(5, 'Research and analysis first', 3.0, 5.0, 4.0)
ON DUPLICATE KEY UPDATE option_text=option_text;

