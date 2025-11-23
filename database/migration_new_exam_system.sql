-- Migration script for new Adaptive Exam System
-- This adds the new tables and updates existing ones

USE kasubaytech_db;

-- Update questions table with new fields
ALTER TABLE `questions` 
ADD COLUMN IF NOT EXISTS `category` ENUM('IS', 'IT', 'CS', 'DIAGNOSTIC') DEFAULT 'DIAGNOSTIC' AFTER `question_type`,
ADD COLUMN IF NOT EXISTS `difficulty` ENUM('EASY', 'MEDIUM', 'HARD') DEFAULT 'MEDIUM' AFTER `category`,
ADD COLUMN IF NOT EXISTS `weight` INT DEFAULT 1 AFTER `difficulty`,
ADD COLUMN IF NOT EXISTS `correct_option` ENUM('A', 'B', 'C', 'D') DEFAULT NULL AFTER `weight`,
ADD COLUMN IF NOT EXISTS `option_a` VARCHAR(255) DEFAULT NULL AFTER `correct_option`,
ADD COLUMN IF NOT EXISTS `option_b` VARCHAR(255) DEFAULT NULL AFTER `option_a`,
ADD COLUMN IF NOT EXISTS `option_c` VARCHAR(255) DEFAULT NULL AFTER `option_b`,
ADD COLUMN IF NOT EXISTS `option_d` VARCHAR(255) DEFAULT NULL AFTER `option_c`;

-- Add indexes for better query performance
ALTER TABLE `questions` 
ADD INDEX IF NOT EXISTS `idx_category` (`category`),
ADD INDEX IF NOT EXISTS `idx_difficulty` (`difficulty`),
ADD INDEX IF NOT EXISTS `idx_category_difficulty` (`category`, `difficulty`);

-- Create exam_sessions table
CREATE TABLE IF NOT EXISTS `exam_sessions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL COMMENT 'References client.id',
  `current_question_id` INT(11) DEFAULT NULL COMMENT 'References questions.id',
  `dominant_category` ENUM('IS', 'IT', 'CS') DEFAULT NULL,
  `stage` ENUM('DIAGNOSTIC', 'CATEGORY', 'FINISHED') DEFAULT 'DIAGNOSTIC',
  `confidence_score` FLOAT DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_stage` (`stage`),
  KEY `idx_current_question` (`current_question_id`),
  CONSTRAINT `exam_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `client` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_sessions_ibfk_2` FOREIGN KEY (`current_question_id`) REFERENCES `questions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create exam_answers table
CREATE TABLE IF NOT EXISTS `exam_answers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `session_id` INT(11) NOT NULL COMMENT 'References exam_sessions.id',
  `question_id` INT(11) NOT NULL COMMENT 'References questions.id',
  `selected_option` ENUM('A', 'B', 'C', 'D') NOT NULL,
  `is_correct` BOOLEAN DEFAULT FALSE,
  `category` ENUM('IS', 'IT', 'CS', 'DIAGNOSTIC') NOT NULL,
  `points_awarded` INT DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_question_id` (`question_id`),
  KEY `idx_category` (`category`),
  KEY `idx_session_question` (`session_id`, `question_id`),
  CONSTRAINT `exam_answers_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `exam_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create exam_results table
CREATE TABLE IF NOT EXISTS `exam_results` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `session_id` INT(11) NOT NULL COMMENT 'References exam_sessions.id',
  `recommended_course` ENUM('IS', 'IT', 'CS', 'UNDECIDED') DEFAULT 'UNDECIDED',
  `final_score` INT DEFAULT 0,
  `confidence_score` FLOAT DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_session_id` (`session_id`),
  CONSTRAINT `exam_results_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `exam_sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

