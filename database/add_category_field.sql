-- Add category field to questions table
ALTER TABLE `questions` 
ADD COLUMN `category` ENUM('IS', 'IT', 'CS', 'DIAGNOSTIC') DEFAULT 'DIAGNOSTIC' AFTER `question_type`,
ADD COLUMN `topic` VARCHAR(100) DEFAULT NULL AFTER `category`,
ADD COLUMN `is_correct_answer` INT(11) DEFAULT NULL COMMENT 'Option ID that is the correct answer' AFTER `topic`;

-- Add index for category
ALTER TABLE `questions` ADD INDEX `idx_category` (`category`);

