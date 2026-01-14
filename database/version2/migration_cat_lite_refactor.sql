-- ============================================================================
-- Database Refactoring for Re-evaluated Adaptive Algorithm (CAT-lite)
-- ============================================================================
-- Purpose: Separate course identity (course_tag) from exam phase (category)
-- 
-- Changes:
-- 1. Add course_tag column to identify which course (IT/IS/CS) a question belongs to
-- 2. Redefine category to represent exam phase (DIAGNOSTIC/ADAPTIVE)
-- 3. Migrate existing data safely
-- 4. Update related tables (exam_answers) for consistency
--
-- IMPORTANT: Backup your database before running this script!
-- ============================================================================

USE kasubaytech_db;

-- ============================================================================
-- STEP 1: Add course_tag column to questions table
-- ============================================================================
-- This column explicitly identifies which course (IT/IS/CS) a question belongs to
-- Both diagnostic and adaptive questions must have a course_tag
-- ============================================================================

ALTER TABLE `questions` 
ADD COLUMN `course_tag` ENUM('IT', 'IS', 'CS') NOT NULL DEFAULT 'IT' 
AFTER `category`
COMMENT 'Identifies which course (IT/IS/CS) this question belongs to';

-- Add index for course_tag for better query performance
ALTER TABLE `questions` 
ADD INDEX `idx_course_tag` (`course_tag`);

-- Add composite index for common query patterns (category + course_tag)
ALTER TABLE `questions` 
ADD INDEX `idx_category_course_tag` (`category`, `course_tag`);

-- ============================================================================
-- STEP 2: Migrate existing data - Set course_tag based on current category
-- ============================================================================
-- For questions where category is 'IS', 'IT', or 'CS', set course_tag = category
-- For diagnostic questions (category = 'DIAGNOSTIC'), we need to determine course_tag
-- 
-- NOTE: Diagnostic questions may need manual review to assign correct course_tag
-- This migration uses a default strategy, but you should verify diagnostic questions
-- ============================================================================

-- First, set course_tag for non-diagnostic questions (IS/IT/CS)
UPDATE `questions` 
SET `course_tag` = `category`
WHERE `category` IN ('IS', 'IT', 'CS');

-- For diagnostic questions, we need to determine course_tag
-- Strategy: Use option scores to determine which course the question favors
-- If option scores are not available, default to 'IT' (you may need to adjust)
-- 
-- This query attempts to determine course_tag for diagnostic questions
-- by checking which course has the highest average option scores
-- You may need to manually review and adjust diagnostic questions

-- Option 1: Infer course_tag from answer_options scores (RECOMMENDED)
-- This determines which course a diagnostic question favors based on option scores
-- Uncomment and run this if you have answer_options table with it_score, cs_score, is_score
/*
UPDATE `questions` q
INNER JOIN (
    SELECT 
        ao.question_id,
        CASE 
            WHEN AVG(ao.it_score) >= AVG(ao.cs_score) AND AVG(ao.it_score) >= AVG(ao.is_score) THEN 'IT'
            WHEN AVG(ao.cs_score) >= AVG(ao.is_score) THEN 'CS'
            ELSE 'IS'
        END AS inferred_course
    FROM answer_options ao
    GROUP BY ao.question_id
) AS inferred ON q.id = inferred.question_id
SET q.course_tag = inferred.inferred_course
WHERE q.category = 'DIAGNOSTIC';
*/

-- Option 2: Manual assignment for diagnostic questions
-- If option scores are not available or you want to review manually,
-- you can update diagnostic questions individually:
-- 
-- Example: Update specific diagnostic questions based on their content
-- UPDATE questions SET course_tag = 'IS' WHERE id = 1 AND category = 'DIAGNOSTIC';
-- UPDATE questions SET course_tag = 'IT' WHERE id = 2 AND category = 'DIAGNOSTIC';
-- UPDATE questions SET course_tag = 'CS' WHERE id = 3 AND category = 'DIAGNOSTIC';
--
-- Or update in batches if you know the pattern:
-- UPDATE questions SET course_tag = 'IS' WHERE category = 'DIAGNOSTIC' AND id BETWEEN 1 AND 10;
--
-- IMPORTANT: Review diagnostic questions after migration to ensure correct course_tag assignment

-- ============================================================================
-- STEP 3: Modify category column to represent exam phase
-- ============================================================================
-- Change category from ENUM('IS','IT','CS','DIAGNOSTIC') 
-- to ENUM('DIAGNOSTIC','ADAPTIVE')
-- 
-- Rules:
-- - Questions with old category='DIAGNOSTIC' → new category='DIAGNOSTIC'
-- - Questions with old category='IS','IT','CS' → new category='ADAPTIVE'
-- ============================================================================

-- First, update category values: IS/IT/CS → ADAPTIVE
UPDATE `questions` 
SET `category` = 'ADAPTIVE'
WHERE `category` IN ('IS', 'IT', 'CS');

-- Now modify the ENUM definition
-- Note: MySQL doesn't support direct ENUM modification, so we need to:
-- 1. Add a temporary column with new ENUM
-- 2. Copy data (DIAGNOSTIC stays DIAGNOSTIC, others become ADAPTIVE)
-- 3. Drop old column
-- 4. Rename new column

-- Add temporary column with new ENUM values
ALTER TABLE `questions` 
ADD COLUMN `category_new` ENUM('DIAGNOSTIC', 'ADAPTIVE') NOT NULL DEFAULT 'DIAGNOSTIC' 
AFTER `question_type`;

-- Copy data: DIAGNOSTIC stays DIAGNOSTIC, everything else becomes ADAPTIVE
-- (We already converted IS/IT/CS to ADAPTIVE in previous step)
UPDATE `questions` 
SET `category_new` = CASE 
    WHEN `category` = 'DIAGNOSTIC' THEN 'DIAGNOSTIC'
    ELSE 'ADAPTIVE'
END;

-- Drop old category column (this will also drop the index)
ALTER TABLE `questions` 
DROP COLUMN `category`;

-- Rename new column to category
ALTER TABLE `questions` 
CHANGE COLUMN `category_new` `category` ENUM('DIAGNOSTIC', 'ADAPTIVE') NOT NULL DEFAULT 'DIAGNOSTIC'
AFTER `question_type`
COMMENT 'Represents exam phase: DIAGNOSTIC (Q1-5) or ADAPTIVE (Q6-20)';

-- Recreate indexes
ALTER TABLE `questions` 
ADD INDEX `idx_category` (`category`);

-- Recreate composite index
ALTER TABLE `questions` 
ADD INDEX `idx_category_course_tag` (`category`, `course_tag`);

-- ============================================================================
-- STEP 4: Update exam_answers table for consistency
-- ============================================================================
-- The exam_answers table also has a category column that should be updated
-- to use course_tag instead, or we can keep it for historical tracking
-- 
-- Option A: Keep category in exam_answers for historical data (recommended)
-- Option B: Add course_tag to exam_answers and update it
-- 
-- We'll do Option B to maintain consistency
-- ============================================================================

-- Add course_tag to exam_answers
ALTER TABLE `exam_answers` 
ADD COLUMN `course_tag` ENUM('IT', 'IS', 'CS') NULL
AFTER `category`
COMMENT 'Course tag from the question at time of answer';

-- Update course_tag in exam_answers based on question's course_tag
UPDATE `exam_answers` ea
INNER JOIN `questions` q ON ea.question_id = q.id
SET ea.course_tag = q.course_tag;

-- Make course_tag NOT NULL after data is populated
ALTER TABLE `exam_answers` 
MODIFY COLUMN `course_tag` ENUM('IT', 'IS', 'CS') NOT NULL;

-- Add index for course_tag in exam_answers
ALTER TABLE `exam_answers` 
ADD INDEX `idx_course_tag` (`course_tag`);

-- Note: The category column in exam_answers can remain for backward compatibility
-- or you can update it similarly. For now, we'll leave it as-is for historical tracking.

-- ============================================================================
-- STEP 5: Verification Queries
-- ============================================================================
-- Run these queries to verify the migration was successful
-- ============================================================================

-- Check category distribution
SELECT category, COUNT(*) as count 
FROM questions 
GROUP BY category;

-- Check course_tag distribution
SELECT course_tag, COUNT(*) as count 
FROM questions 
GROUP BY course_tag;

-- Check category + course_tag combination
SELECT category, course_tag, COUNT(*) as count 
FROM questions 
GROUP BY category, course_tag
ORDER BY category, course_tag;

-- Verify no NULL course_tags
SELECT COUNT(*) as null_course_tags 
FROM questions 
WHERE course_tag IS NULL;

-- Verify exam_answers course_tag is populated
SELECT COUNT(*) as null_course_tags_in_answers 
FROM exam_answers 
WHERE course_tag IS NULL;

-- ============================================================================
-- MIGRATION COMPLETE
-- ============================================================================
-- Next steps:
-- 1. Review diagnostic questions and manually adjust course_tag if needed
-- 2. Update your application code to use course_tag instead of category for course identification
-- 3. Update Python adaptive service to use new schema
-- 4. Test the adaptive algorithm with new structure
-- ============================================================================
