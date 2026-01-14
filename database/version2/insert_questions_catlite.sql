-- ============================================================================
-- Insert Questions into CAT-lite Database
-- ============================================================================
-- This script inserts questions into kasubaytech_catlite_db with the proper
-- CAT-lite structure:
-- - category: 'DIAGNOSTIC' or 'ADAPTIVE' (exam phase)
-- - course_tag: 'IT', 'IS', or 'CS' (course identity)
--
-- Transformation Rules:
-- - Old category='DIAGNOSTIC' → New category='DIAGNOSTIC', course_tag inferred
-- - Old category='IS'/'IT'/'CS' → New category='ADAPTIVE', course_tag=old category
-- ============================================================================

USE kasubaytech_catlite_db;

-- ============================================================================
-- DIAGNOSTIC QUESTIONS (Phase 1: Q1-5)
-- ============================================================================
-- These questions are used in the diagnostic phase
-- course_tag is assigned based on which course the question favors
-- You may need to adjust course_tag based on your question content
-- ============================================================================

-- Diagnostic Question 1: ICT meaning (favors IS based on content)
INSERT INTO `questions` (
    `id`, `question_text`, `question_type`, `category`, `course_tag`,
    `difficulty`, `weight`, `correct_option`,
    `option_a`, `option_b`, `option_c`, `option_d`,
    `topic`, `is_correct_answer`, `order_number`, `is_active`,
    `created_at`, `updated_at`
) VALUES
(1, 'What is the full meaning of ICT?', 'single', 'DIAGNOSTIC', 'IS',
 'EASY', 1, 'B',
 'Information and Computer Technology',
 'Information and Communication Technology',
 'Integrated Computer Training',
 'Internal Connection Tool',
 NULL, NULL, 1, 1,
 NOW(), NOW());

-- Diagnostic Question 2: What is a computer? (favors CS based on content)
INSERT INTO `questions` (
    `id`, `question_text`, `question_type`, `category`, `course_tag`,
    `difficulty`, `weight`, `correct_option`,
    `option_a`, `option_b`, `option_c`, `option_d`,
    `topic`, `is_correct_answer`, `order_number`, `is_active`,
    `created_at`, `updated_at`
) VALUES
(2, 'What is a computer?', 'single', 'DIAGNOSTIC', 'CS',
 'EASY', 1, 'A',
 'A device that stores and processes data',
 'A printer',
 'A network cable',
 'A type of storage',
 NULL, NULL, 2, 1,
 NOW(), NOW());

-- Diagnostic Question 3: Application software (favors IT based on content)
INSERT INTO `questions` (
    `id`, `question_text`, `question_type`, `category`, `course_tag`,
    `difficulty`, `weight`, `correct_option`,
    `option_a`, `option_b`, `option_c`, `option_d`,
    `topic`, `is_correct_answer`, `order_number`, `is_active`,
    `created_at`, `updated_at`
) VALUES
(3, 'Which of the following is an example of application software?', 'single', 'DIAGNOSTIC', 'IT',
 'EASY', 1, 'A',
 'MS Word',
 'BIOS',
 'RAM',
 'ROM',
 NULL, NULL, 3, 1,
 NOW(), NOW());

-- Add 2 more diagnostic questions to reach 5 total for Phase 1
-- Diagnostic Question 4: Computer fundamentals (favors IS)
INSERT INTO `questions` (
    `id`, `question_text`, `question_type`, `category`, `course_tag`,
    `difficulty`, `weight`, `correct_option`,
    `option_a`, `option_b`, `option_c`, `option_d`,
    `topic`, `is_correct_answer`, `order_number`, `is_active`,
    `created_at`, `updated_at`
) VALUES
(4, 'What is the full meaning of ICT?', 'single', 'DIAGNOSTIC', 'IS',
 'EASY', 1, 'B',
 'Information and Computer Technology',
 'Information and Communication Technology',
 'Integrated Computer Training',
 'Internal Connection Tool',
 NULL, NULL, 4, 1,
 NOW(), NOW());

-- Diagnostic Question 5: Computer basics (favors CS)
INSERT INTO `questions` (
    `id`, `question_text`, `question_type`, `category`, `course_tag`,
    `difficulty`, `weight`, `correct_option`,
    `option_a`, `option_b`, `option_c`, `option_d`,
    `topic`, `is_correct_answer`, `order_number`, `is_active`,
    `created_at`, `updated_at`
) VALUES
(5, 'Which of the following is an example of application software?', 'single', 'DIAGNOSTIC', 'CS',
 'EASY', 1, 'A',
 'MS Word',
 'BIOS',
 'RAM',
 'ROM',
 NULL, NULL, 5, 1,
 NOW(), NOW());

-- ============================================================================
-- ADAPTIVE QUESTIONS - IS COURSE (Phase 2 & 3)
-- ============================================================================
-- These questions have category='ADAPTIVE' and course_tag='IS'
-- Used in adaptive rounds after diagnostic phase
-- ============================================================================

INSERT INTO `questions` (
    `id`, `question_text`, `question_type`, `category`, `course_tag`,
    `difficulty`, `weight`, `correct_option`,
    `option_a`, `option_b`, `option_c`, `option_d`,
    `topic`, `is_correct_answer`, `order_number`, `is_active`,
    `created_at`, `updated_at`
) VALUES
(6, 'Which device is used to display output on a screen?', 'single', 'ADAPTIVE', 'IS',
 'MEDIUM', 2, 'B',
 'Mouse', 'Monitor', 'Keyboard', 'Scanner',
 NULL, NULL, 6, 1, NOW(), NOW()),

(7, 'The main function of a computer\'s CPU is to:', 'single', 'ADAPTIVE', 'IS',
 'HARD', 3, 'B',
 'Store data', 'Perform calculations', 'Print documents', 'Connect to the internet',
 NULL, NULL, 7, 1, NOW(), NOW()),

(8, 'What is the brain of the computer?', 'single', 'ADAPTIVE', 'IS',
 'EASY', 1, 'B',
 'Hard Disk', 'CPU', 'RAM', 'Monitor',
 NULL, NULL, 8, 1, NOW(), NOW()),

(9, 'Which one is an example of software?', 'single', 'ADAPTIVE', 'IS',
 'EASY', 1, 'B',
 'Printer', 'Microsoft Word', 'USB Cable', 'Mouse',
 NULL, NULL, 9, 1, NOW(), NOW()),

(10, 'What does a keyboard help you do?', 'single', 'ADAPTIVE', 'IS',
 'EASY', 1, 'B',
 'Print documents', 'Type data', 'Store files', 'Scan images',
 NULL, NULL, 10, 1, NOW(), NOW()),

(11, 'What is the full form of "WWW"?', 'single', 'ADAPTIVE', 'IS',
 'EASY', 1, 'A',
 'World Wide Web', 'Wide World Web', 'World Web Wide', 'Web World Wide',
 NULL, NULL, 11, 1, NOW(), NOW()),

(12, 'Data is processed into:', 'single', 'ADAPTIVE', 'IS',
 'MEDIUM', 2, 'C',
 'Facts', 'Instructions', 'Information', 'Numbers',
 NULL, NULL, 12, 1, NOW(), NOW()),

(13, 'What part of the computer temporarily stores data?', 'single', 'ADAPTIVE', 'IS',
 'EASY', 1, 'B',
 'Hard Drive', 'RAM', 'CD-ROM', 'USB Drive',
 NULL, NULL, 13, 1, NOW(), NOW()),

(14, 'Which is an example of an input device?', 'single', 'ADAPTIVE', 'IS',
 'EASY', 1, 'B',
 'Monitor', 'Mouse', 'Printer', 'Speaker',
 NULL, NULL, 14, 1, NOW(), NOW()),

(15, 'Which symbol is used to end a statement in C?', 'single', 'ADAPTIVE', 'IS',
 'MEDIUM', 2, 'C',
 '.', ',', ';', ':',
 NULL, NULL, 15, 1, NOW(), NOW()),

(16, 'A set of instructions written for a computer is called a:', 'single', 'ADAPTIVE', 'IS',
 'EASY', 1, 'B',
 'Database', 'Program', 'File', 'Record',
 NULL, NULL, 16, 1, NOW(), NOW()),

(17, 'What is used to store data in a program?', 'single', 'ADAPTIVE', 'IS',
 'MEDIUM', 2, 'A',
 'Variable', 'Function', 'Class', 'Loop',
 NULL, NULL, 17, 1, NOW(), NOW()),

(18, 'Which of the following is a programming language?', 'single', 'ADAPTIVE', 'IS',
 'EASY', 1, 'B',
 'HTML', 'Java', 'Google', 'Excel',
 NULL, NULL, 18, 1, NOW(), NOW()),

(19, 'Which operator is used for addition?', 'single', 'ADAPTIVE', 'IS',
 'HARD', 3, 'D',
 '*', '/', '-', '+',
 NULL, NULL, 19, 1, NOW(), NOW()),

(20, 'What does "print()" do in Python?', 'single', 'ADAPTIVE', 'IS',
 'EASY', 1, 'B',
 'Saves a file', 'Displays output', 'Reads input', 'Exits program',
 NULL, NULL, 20, 1, NOW(), NOW());

-- ============================================================================
-- ADAPTIVE QUESTIONS - IT COURSE (Phase 2 & 3)
-- ============================================================================

INSERT INTO `questions` (
    `id`, `question_text`, `question_type`, `category`, `course_tag`,
    `difficulty`, `weight`, `correct_option`,
    `option_a`, `option_b`, `option_c`, `option_d`,
    `topic`, `is_correct_answer`, `order_number`, `is_active`,
    `created_at`, `updated_at`
) VALUES
(21, 'What does "IT" stand for?', 'single', 'ADAPTIVE', 'IT',
 'EASY', 1, 'B',
 'Information Tool', 'Information Technology', 'Internet Technology', 'Internal Transmission',
 NULL, NULL, 21, 1, NOW(), NOW()),

(22, 'Which device is used to process data?', 'single', 'ADAPTIVE', 'IT',
 'MEDIUM', 2, 'B',
 'Mouse', 'CPU', 'Monitor', 'Keyboard',
 NULL, NULL, 22, 1, NOW(), NOW()),

(23, 'Which is a storage device?', 'single', 'ADAPTIVE', 'IT',
 'EASY', 1, 'B',
 'Printer', 'Hard Disk', 'Monitor', 'Speaker',
 NULL, NULL, 23, 1, NOW(), NOW()),

(24, 'What is the function of an operating system?', 'single', 'ADAPTIVE', 'IT',
 'HARD', 3, 'B',
 'Hardware connection', 'Controls computer operations', 'Runs hardware', 'Plays videos',
 NULL, NULL, 24, 1, NOW(), NOW()),

(25, 'What type of device is a keyboard?', 'single', 'ADAPTIVE', 'IT',
 'EASY', 1, 'A',
 'Input', 'Output', 'Storage', 'Processing',
 NULL, NULL, 25, 1, NOW(), NOW()),

(26, 'Which computer generation used microprocessors?', 'single', 'ADAPTIVE', 'IT',
 'HARD', 3, 'C',
 'First', 'Second', 'Fourth', 'Fifth',
 NULL, NULL, 26, 1, NOW(), NOW()),

(27, 'Which port is used to connect USB devices?', 'single', 'ADAPTIVE', 'IT',
 'HARD', 3, 'B',
 'Serial', 'USB', 'HDMI', 'VGA',
 NULL, NULL, 27, 1, NOW(), NOW()),

(28, 'Which of these is not a hardware device?', 'single', 'ADAPTIVE', 'IT',
 'EASY', 1, 'C',
 'Keyboard', 'Monitor', 'Gmail', 'CPU',
 NULL, NULL, 28, 1, NOW(), NOW()),

(29, 'What is an example of an output device?', 'single', 'ADAPTIVE', 'IT',
 'EASY', 1, 'C',
 'Mouse', 'Keyboard', 'Speaker', 'Scanner',
 NULL, NULL, 29, 1, NOW(), NOW()),

(30, 'Which one is a system software?', 'single', 'ADAPTIVE', 'IT',
 'EASY', 1, 'A',
 'Windows', 'Word', 'Excel', 'Photoshop',
 NULL, NULL, 30, 1, NOW(), NOW()),

(31, 'In programming, "syntax" means:', 'single', 'ADAPTIVE', 'IT',
 'HARD', 3, 'A',
 'The rules of writing code', 'The program\'s result', 'The memory type', 'The variable name',
 NULL, NULL, 31, 1, NOW(), NOW()),

(32, 'In Python, which keyword is used to print output?', 'single', 'ADAPTIVE', 'IT',
 'MEDIUM', 2, 'B',
 'show()', 'print()', 'write()', 'display()',
 NULL, NULL, 32, 1, NOW(), NOW()),

(33, 'What is a loop used for?', 'single', 'ADAPTIVE', 'IT',
 'HARD', 3, 'B',
 'To store data', 'To repeat tasks', 'To stop execution', 'To save files',
 NULL, NULL, 33, 1, NOW(), NOW()),

(34, 'What does "if" statement do?', 'single', 'ADAPTIVE', 'IT',
 'EASY', 1, 'B',
 'Repeats code', 'Checks conditions', 'Prints output', 'Saves data',
 NULL, NULL, 34, 1, NOW(), NOW()),

(35, 'In C language, "printf" is used for:', 'single', 'ADAPTIVE', 'IT',
 'EASY', 1, 'B',
 'Input', 'Output', 'Saving', 'Calculation',
 NULL, NULL, 35, 1, NOW(), NOW());

-- ============================================================================
-- ADAPTIVE QUESTIONS - CS COURSE (Phase 2 & 3)
-- ============================================================================

INSERT INTO `questions` (
    `id`, `question_text`, `question_type`, `category`, `course_tag`,
    `difficulty`, `weight`, `correct_option`,
    `option_a`, `option_b`, `option_c`, `option_d`,
    `topic`, `is_correct_answer`, `order_number`, `is_active`,
    `created_at`, `updated_at`
) VALUES
(36, 'What is a computer?', 'single', 'ADAPTIVE', 'CS',
 'EASY', 1, 'A',
 'A device that stores and processes data', 'A printer', 'A network cable', 'A type of storage',
 NULL, NULL, 36, 1, NOW(), NOW()),

(37, 'Which of the following is an example of application software?', 'single', 'ADAPTIVE', 'CS',
 'EASY', 1, 'A',
 'MS Word', 'BIOS', 'RAM', 'ROM',
 NULL, NULL, 37, 1, NOW(), NOW()),

(38, 'The process of turning data into information is called:', 'single', 'ADAPTIVE', 'CS',
 'MEDIUM', 2, 'A',
 'Processing', 'Input', 'Output', 'Storage',
 NULL, NULL, 38, 1, NOW(), NOW()),

(39, 'The physical parts of a computer are called:', 'single', 'ADAPTIVE', 'CS',
 'EASY', 1, 'B',
 'Software', 'Hardware', 'Data', 'Output',
 NULL, NULL, 39, 1, NOW(), NOW()),

(40, 'What is the smallest unit of data?', 'single', 'ADAPTIVE', 'CS',
 'EASY', 1, 'A',
 'Bit', 'Byte', 'Kilobyte', 'Megabyte',
 NULL, NULL, 40, 1, NOW(), NOW()),

(41, 'The main function of a storage device is to:', 'single', 'ADAPTIVE', 'CS',
 'HARD', 3, 'A',
 'Save data', 'Process data', 'Input data', 'Display data',
 NULL, NULL, 41, 1, NOW(), NOW()),

(42, 'Which memory is volatile?', 'single', 'ADAPTIVE', 'CS',
 'EASY', 1, 'B',
 'ROM', 'RAM', 'Hard Disk', 'Flash Drive',
 NULL, NULL, 42, 1, NOW(), NOW()),

(43, 'What does GUI stand for?', 'single', 'ADAPTIVE', 'CS',
 'HARD', 3, 'A',
 'Graphical User Interface', 'General User Interaction', 'Graphical Unit Integration', 'General Utility Input',
 NULL, NULL, 43, 1, NOW(), NOW()),

(44, 'Which device is used for permanent data storage?', 'single', 'ADAPTIVE', 'CS',
 'EASY', 1, 'B',
 'RAM', 'Hard Drive', 'Cache', 'Register',
 NULL, NULL, 44, 1, NOW(), NOW()),

(45, 'Which software helps the computer to start?', 'single', 'ADAPTIVE', 'CS',
 'MEDIUM', 2, 'D',
 'Operating System', 'Application Software', 'Utility Software', 'BIOS',
 NULL, NULL, 45, 1, NOW(), NOW()),

(46, 'What is a variable?', 'single', 'ADAPTIVE', 'CS',
 'HARD', 3, 'A',
 'A name used to store data', 'A command', 'A loop', 'A bug',
 NULL, NULL, 46, 1, NOW(), NOW()),

(47, 'In programming, what is a "loop"?', 'single', 'ADAPTIVE', 'CS',
 'HARD', 3, 'B',
 'A function', 'A repeating structure', 'A condition', 'A comment',
 NULL, NULL, 47, 1, NOW(), NOW()),

(48, 'What does the operator "=" mean?', 'single', 'ADAPTIVE', 'CS',
 'HARD', 3, 'B',
 'Add', 'Assign', 'Compare', 'Subtract',
 NULL, NULL, 48, 1, NOW(), NOW()),

(49, 'Which of the following is a valid binary number?', 'single', 'ADAPTIVE', 'CS',
 'HARD', 3, 'A',
 '1101', '1021', '1234', '2004',
 NULL, NULL, 49, 1, NOW(), NOW()),

(50, 'Which keyword is used to create a function in Python?', 'single', 'ADAPTIVE', 'CS',
 'HARD', 3, 'A',
 'def', 'function', 'create', 'start',
 NULL, NULL, 50, 1, NOW(), NOW());

-- ============================================================================
-- VERIFICATION QUERIES
-- ============================================================================

-- Check question distribution
SELECT 
    category, 
    course_tag, 
    COUNT(*) as count 
FROM questions 
GROUP BY category, course_tag
ORDER BY category, course_tag;

-- Check diagnostic questions
SELECT id, question_text, category, course_tag 
FROM questions 
WHERE category = 'DIAGNOSTIC'
ORDER BY id;

-- Check adaptive questions by course
SELECT course_tag, COUNT(*) as count 
FROM questions 
WHERE category = 'ADAPTIVE'
GROUP BY course_tag;

-- ============================================================================
-- NOTES
-- ============================================================================
-- 1. This script inserts 50 sample questions:
--    - 5 Diagnostic questions (Phase 1)
--    - 15 IS questions (Adaptive)
--    - 15 IT questions (Adaptive)
--    - 15 CS questions (Adaptive)
--
-- 2. You can add more questions following the same pattern
--
-- 3. For diagnostic questions, adjust course_tag based on which course
--    the question actually favors (you may need to review question content)
--
-- 4. After inserting questions, you'll need to insert answer_options
--    with it_score, cs_score, is_score for each question
-- ============================================================================
