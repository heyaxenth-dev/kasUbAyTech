-- ============================================================================
-- Example: How to Insert a Single Question into CAT-lite Database
-- ============================================================================
-- This shows the pattern for inserting questions with the new structure
-- ============================================================================

USE kasubaytech_catlite_db;

-- ============================================================================
-- EXAMPLE 1: Insert a Diagnostic Question
-- ============================================================================
-- Diagnostic questions have category='DIAGNOSTIC' and must have a course_tag
-- The course_tag indicates which course this diagnostic question favors
-- ============================================================================

INSERT INTO `questions` (
    `question_text`, 
    `question_type`, 
    `category`,           -- 'DIAGNOSTIC' for Phase 1
    `course_tag`,         -- 'IT', 'IS', or 'CS' - which course it favors
    `difficulty`, 
    `weight`, 
    `correct_option`,
    `option_a`, 
    `option_b`, 
    `option_c`, 
    `option_d`,
    `topic`,
    `order_number`,
    `is_active`
) VALUES (
    'What is the full meaning of ICT?',
    'single',
    'DIAGNOSTIC',         -- Exam phase
    'IS',                 -- Course this question favors
    'EASY',
    1,
    'B',
    'Information and Computer Technology',
    'Information and Communication Technology',
    'Integrated Computer Training',
    'Internal Connection Tool',
    'Fundamentals',
    1,
    1
);

-- ============================================================================
-- EXAMPLE 2: Insert an Adaptive Question for IT Course
-- ============================================================================
-- Adaptive questions have category='ADAPTIVE' and course_tag identifies the course
-- These are used in Phase 2 (Q6-10) and Phase 3 (Q11-20)
-- ============================================================================

INSERT INTO `questions` (
    `question_text`, 
    `question_type`, 
    `category`,           -- 'ADAPTIVE' for Phase 2 & 3
    `course_tag`,         -- 'IT', 'IS', or 'CS' - which course this belongs to
    `difficulty`, 
    `weight`, 
    `correct_option`,
    `option_a`, 
    `option_b`, 
    `option_c`, 
    `option_d`,
    `topic`,
    `order_number`,
    `is_active`
) VALUES (
    'What does "IT" stand for?',
    'single',
    'ADAPTIVE',          -- Exam phase
    'IT',                -- Course this question belongs to
    'EASY',
    1,
    'B',
    'Information Tool',
    'Information Technology',
    'Internet Technology',
    'Internal Transmission',
    'Fundamentals',
    21,
    1
);

-- ============================================================================
-- EXAMPLE 3: Insert an Adaptive Question for IS Course
-- ============================================================================

INSERT INTO `questions` (
    `question_text`, 
    `question_type`, 
    `category`, 
    `course_tag`,
    `difficulty`, 
    `weight`, 
    `correct_option`,
    `option_a`, 
    `option_b`, 
    `option_c`, 
    `option_d`,
    `topic`,
    `order_number`,
    `is_active`
) VALUES (
    'Which device is used to display output on a screen?',
    'single',
    'ADAPTIVE',          -- Phase 2 & 3
    'IS',                -- IS course question
    'MEDIUM',
    2,
    'B',
    'Mouse',
    'Monitor',
    'Keyboard',
    'Scanner',
    'Hardware',
    6,
    1
);

-- ============================================================================
-- EXAMPLE 4: Insert an Adaptive Question for CS Course
-- ============================================================================

INSERT INTO `questions` (
    `question_text`, 
    `question_type`, 
    `category`, 
    `course_tag`,
    `difficulty`, 
    `weight`, 
    `correct_option`,
    `option_a`, 
    `option_b`, 
    `option_c`, 
    `option_d`,
    `topic`,
    `order_number`,
    `is_active`
) VALUES (
    'What is the smallest unit of data?',
    'single',
    'ADAPTIVE',          -- Phase 2 & 3
    'CS',                -- CS course question
    'EASY',
    1,
    'A',
    'Bit',
    'Byte',
    'Kilobyte',
    'Megabyte',
    'Fundamentals',
    40,
    1
);

-- ============================================================================
-- QUICK REFERENCE
-- ============================================================================
-- 
-- Category Values:
--   - 'DIAGNOSTIC' = Used in Phase 1 (Questions 1-5)
--   - 'ADAPTIVE' = Used in Phase 2 & 3 (Questions 6-20)
--
-- Course Tag Values:
--   - 'IT' = Information Technology course
--   - 'IS' = Information Systems course
--   - 'CS' = Computer Science course
--
-- Important:
--   - ALL questions (diagnostic and adaptive) MUST have a course_tag
--   - Diagnostic questions should have course_tag based on which course they favor
--   - Adaptive questions have course_tag matching the course they belong to
--
-- ============================================================================
