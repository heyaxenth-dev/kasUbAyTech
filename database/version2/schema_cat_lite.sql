-- ============================================================================
-- Database Schema for kasUbAyTech Assessment System - CAT-lite Version
-- ============================================================================
-- This is a NEW database schema designed specifically for the 
-- Re-evaluated Adaptive Algorithm (CAT-lite) upgrade.
--
-- Key Features:
-- - course_tag: Explicitly identifies which course (IT/IS/CS) a question belongs to
-- - category: Represents exam phase (DIAGNOSTIC/ADAPTIVE)
-- - Clean separation of concerns for adaptive algorithm
--
-- Database Name: kasubaytech_catlite_db
-- ============================================================================

-- Create new database
CREATE DATABASE IF NOT EXISTS kasubaytech_catlite_db;
USE kasubaytech_catlite_db;

-- ============================================================================
-- CORE TABLES
-- ============================================================================

-- Students/Client table
CREATE TABLE IF NOT EXISTS `client` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(100) NOT NULL,
  `middlename` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`firstname`, `lastname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin table
CREATE TABLE IF NOT EXISTS `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- QUESTIONS TABLE - CAT-lite Structure
-- ============================================================================
-- Key Changes from old schema:
-- 1. course_tag: ENUM('IT','IS','CS') - Identifies which course
-- 2. category: ENUM('DIAGNOSTIC','ADAPTIVE') - Identifies exam phase
-- 3. Both diagnostic and adaptive questions have course_tag
-- ============================================================================

CREATE TABLE IF NOT EXISTS `questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_text` text NOT NULL,
  `question_type` enum('single','multiple') NOT NULL DEFAULT 'single',
  `category` enum('DIAGNOSTIC','ADAPTIVE') NOT NULL DEFAULT 'DIAGNOSTIC' 
    COMMENT 'Exam phase: DIAGNOSTIC (Q1-5) or ADAPTIVE (Q6-20)',
  `course_tag` enum('IT','IS','CS') NOT NULL DEFAULT 'IT'
    COMMENT 'Which course (IT/IS/CS) this question belongs to',
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
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_course_tag` (`course_tag`),
  KEY `idx_category_course_tag` (`category`, `course_tag`),
  KEY `idx_difficulty` (`difficulty`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_order_number` (`order_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Answer options table (unchanged structure)
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

-- ============================================================================
-- EXAM SYSTEM TABLES
-- ============================================================================

-- Exam sessions table
CREATE TABLE IF NOT EXISTS `exam_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'References client.id',
  `current_question_id` int(11) DEFAULT NULL COMMENT 'References questions.id',
  `dominant_category` enum('IS','IT','CS') DEFAULT NULL COMMENT 'Dominant course from re-evaluation',
  `stage` enum('DIAGNOSTIC','CATEGORY','FINISHED') DEFAULT 'DIAGNOSTIC',
  `confidence_score` float DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_stage` (`stage`),
  KEY `idx_current_question` (`current_question_id`),
  KEY `idx_dominant_category` (`dominant_category`),
  CONSTRAINT `exam_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `client` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_sessions_ibfk_2` FOREIGN KEY (`current_question_id`) REFERENCES `questions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Exam answers table - Updated with course_tag
CREATE TABLE IF NOT EXISTS `exam_answers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) NOT NULL COMMENT 'References exam_sessions.id',
  `question_id` int(11) NOT NULL COMMENT 'References questions.id',
  `selected_option` enum('A','B','C','D') NOT NULL,
  `is_correct` boolean DEFAULT FALSE,
  `category` enum('DIAGNOSTIC','ADAPTIVE') NOT NULL COMMENT 'Exam phase when answered',
  `course_tag` enum('IT','IS','CS') NOT NULL COMMENT 'Course tag from question at time of answer',
  `points_awarded` int DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_question_id` (`question_id`),
  KEY `idx_category` (`category`),
  KEY `idx_course_tag` (`course_tag`),
  KEY `idx_session_question` (`session_id`, `question_id`),
  KEY `idx_session_course_tag` (`session_id`, `course_tag`),
  CONSTRAINT `exam_answers_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `exam_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Exam results table
CREATE TABLE IF NOT EXISTS `exam_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) NOT NULL COMMENT 'References exam_sessions.id',
  `recommended_course` enum('IS','IT','CS','UNDECIDED') DEFAULT 'UNDECIDED',
  `final_score` int DEFAULT 0 COMMENT 'Number of correct answers',
  `confidence_score` float DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_session_id` (`session_id`),
  CONSTRAINT `exam_results_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `exam_sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- LEGACY TABLES (for backward compatibility if needed)
-- ============================================================================

-- Assessment results table (legacy - may not be needed)
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

-- ============================================================================
-- DEFAULT DATA
-- ============================================================================

-- Insert default admin (username: admin, password: admin123)
INSERT INTO `admin` (`username`, `password`, `email`) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@kasubaytech.com')
ON DUPLICATE KEY UPDATE username=username;

-- ============================================================================
-- INDEXES SUMMARY
-- ============================================================================
-- Questions:
--   - category (exam phase)
--   - course_tag (course identity)
--   - category + course_tag (common query pattern)
--   - difficulty, is_active, order_number
--
-- Exam Answers:
--   - session_id, question_id
--   - category, course_tag
--   - session_id + course_tag (for score calculation)
--
-- Exam Sessions:
--   - user_id, stage, dominant_category
-- ============================================================================
