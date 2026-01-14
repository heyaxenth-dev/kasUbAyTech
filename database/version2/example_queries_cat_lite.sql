-- ============================================================================
-- Example SELECT Queries for Re-evaluated Adaptive Algorithm (CAT-lite)
-- ============================================================================
-- These queries demonstrate how to select questions for each exam phase
-- using the new course_tag and category structure
-- ============================================================================

USE kasubaytech_db;

-- ============================================================================
-- PHASE 1: Diagnostic Phase (Questions 1-5)
-- ============================================================================
-- Goal: Select diagnostic questions with mixed IT/IS/CS course_tags
-- Requirements:
--   - category = 'DIAGNOSTIC'
--   - Must have course_tag (IT, IS, or CS)
--   - Should ensure balanced coverage across courses
-- ============================================================================

-- Query 1: Get all available diagnostic questions
-- Use this to get the full pool of diagnostic questions
SELECT 
    q.id,
    q.question_text,
    q.question_type,
    q.category,           -- Should be 'DIAGNOSTIC'
    q.course_tag,          -- IT, IS, or CS
    q.difficulty,
    q.weight,
    q.is_active
FROM questions q
WHERE q.category = 'DIAGNOSTIC'
  AND q.is_active = 1
  AND q.course_tag IN ('IT', 'IS', 'CS')
ORDER BY q.course_tag, q.id;

-- Query 2: Get diagnostic questions for a specific course
-- Use this when you need to ensure balanced coverage (one of each IT/IS/CS)
SELECT 
    q.id,
    q.question_text,
    q.course_tag,
    q.difficulty
FROM questions q
WHERE q.category = 'DIAGNOSTIC'
  AND q.course_tag = 'IT'  -- Change to 'IS' or 'CS' as needed
  AND q.is_active = 1
  AND q.id NOT IN (?)      -- Replace ? with array of already answered question IDs
ORDER BY RAND()            -- Randomize to avoid always selecting same questions
LIMIT 1;

-- Query 3: Get diagnostic questions excluding already answered ones
-- Use this in the adaptive algorithm to get next diagnostic question
SELECT 
    q.id,
    q.question_text,
    q.question_type,
    q.category,
    q.course_tag,
    q.difficulty,
    q.weight
FROM questions q
WHERE q.category = 'DIAGNOSTIC'
  AND q.is_active = 1
  AND q.id NOT IN (?)      -- Replace ? with array of answered question IDs
ORDER BY q.course_tag, RAND();

-- Query 4: Count diagnostic questions by course_tag
-- Use this to check if you have balanced diagnostic questions
SELECT 
    course_tag,
    COUNT(*) as question_count
FROM questions
WHERE category = 'DIAGNOSTIC'
  AND is_active = 1
GROUP BY course_tag
ORDER BY course_tag;

-- ============================================================================
-- PHASE 2: Adaptive Round 1 (Questions 6-10)
-- ============================================================================
-- Goal: Select adaptive questions based on course rankings
-- Distribution: 3 dominant, 1 secondary, 1 weakest
-- Requirements:
--   - category = 'ADAPTIVE'
--   - course_tag matches the target course (dominant/secondary/weakest)
--   - Exclude already answered questions
-- ============================================================================

-- Query 5: Get adaptive questions for dominant course (3 questions needed)
-- Use this to select questions for the dominant course in Phase 2
SELECT 
    q.id,
    q.question_text,
    q.question_type,
    q.category,           -- Should be 'ADAPTIVE'
    q.course_tag,         -- Should match dominant course (e.g., 'IT')
    q.difficulty,
    q.weight
FROM questions q
WHERE q.category = 'ADAPTIVE'
  AND q.course_tag = ?   -- Replace ? with dominant course: 'IT', 'IS', or 'CS'
  AND q.is_active = 1
  AND q.id NOT IN (?)     -- Replace ? with array of answered question IDs
ORDER BY q.difficulty, RAND()  -- You can adjust ordering (by difficulty, weight, etc.)
LIMIT 3;

-- Query 6: Get adaptive questions for secondary course (1 question needed)
-- Use this to select question for the secondary course in Phase 2
SELECT 
    q.id,
    q.question_text,
    q.course_tag,
    q.difficulty
FROM questions q
WHERE q.category = 'ADAPTIVE'
  AND q.course_tag = ?   -- Replace ? with secondary course
  AND q.is_active = 1
  AND q.id NOT IN (?)     -- Replace ? with array of answered question IDs
ORDER BY RAND()
LIMIT 1;

-- Query 7: Get adaptive questions for weakest course (1 question needed)
-- Use this to select question for the weakest course in Phase 2
SELECT 
    q.id,
    q.question_text,
    q.course_tag,
    q.difficulty
FROM questions q
WHERE q.category = 'ADAPTIVE'
  AND q.course_tag = ?   -- Replace ? with weakest course
  AND q.is_active = 1
  AND q.id NOT IN (?)     -- Replace ? with array of answered question IDs
ORDER BY RAND()
LIMIT 1;

-- Query 8: Get adaptive questions with difficulty filter
-- Use this if you want to filter by difficulty level
SELECT 
    q.id,
    q.question_text,
    q.course_tag,
    q.difficulty
FROM questions q
WHERE q.category = 'ADAPTIVE'
  AND q.course_tag = ?   -- Target course
  AND q.difficulty = ?    -- 'EASY', 'MEDIUM', or 'HARD'
  AND q.is_active = 1
  AND q.id NOT IN (?)     -- Answered question IDs
ORDER BY RAND()
LIMIT ?;                  -- Number of questions needed

-- ============================================================================
-- PHASE 3: Adaptive Round 2 (Questions 11-20)
-- ============================================================================
-- Goal: Select adaptive questions with tie-aware distribution
-- Distribution (no tie): 6 dominant, 3 secondary, 1 weakest
-- Distribution (tie): 5 each for tied courses, 1 for third
-- Requirements:
--   - category = 'ADAPTIVE'
--   - course_tag matches target course
--   - Exclude already answered questions
-- ============================================================================

-- Query 9: Get adaptive questions for Phase 3 - No tie scenario
-- Use this when one course is clearly dominant
SELECT 
    q.id,
    q.question_text,
    q.course_tag,
    q.difficulty,
    q.weight
FROM questions q
WHERE q.category = 'ADAPTIVE'
  AND q.course_tag = ?   -- Replace ? with target course
  AND q.is_active = 1
  AND q.id NOT IN (?)     -- Answered question IDs
ORDER BY q.difficulty DESC, RAND()  -- Prefer harder questions in Phase 3
LIMIT ?;                  -- 6 for dominant, 3 for secondary, 1 for weakest

-- Query 10: Get adaptive questions for Phase 3 - Tie scenario
-- Use this when two courses are tied (5 questions each)
SELECT 
    q.id,
    q.question_text,
    q.course_tag,
    q.difficulty
FROM questions q
WHERE q.category = 'ADAPTIVE'
  AND q.course_tag IN (?, ?)  -- Replace ? with the two tied courses
  AND q.is_active = 1
  AND q.id NOT IN (?)         -- Answered question IDs
ORDER BY q.course_tag, RAND()
LIMIT 10;                     -- 5 for each tied course

-- Query 11: Count available questions by course_tag for Phase 3
-- Use this to check if you have enough questions for each course
SELECT 
    course_tag,
    COUNT(*) as available_count
FROM questions
WHERE category = 'ADAPTIVE'
  AND is_active = 1
  AND id NOT IN (?)      -- Answered question IDs
GROUP BY course_tag
ORDER BY course_tag;

-- ============================================================================
-- RE-EVALUATION CHECKPOINT (After Question 10)
-- ============================================================================
-- Goal: Recalculate course scores and determine new rankings
-- Use these queries to support the re-evaluation logic
-- ============================================================================

-- Query 12: Get all answered questions with course_tag for score calculation
-- Use this to recalculate scores after question 10
SELECT 
    ea.question_id,
    ea.selected_option,
    ea.is_correct,
    ea.points_awarded,
    q.course_tag,        -- Use this for course-based scoring
    q.category           -- Should be 'DIAGNOSTIC' or 'ADAPTIVE'
FROM exam_answers ea
INNER JOIN questions q ON ea.question_id = q.id
WHERE ea.session_id = ?  -- Replace ? with session ID
ORDER BY ea.created_at ASC;

-- Query 13: Calculate scores by course_tag
-- Use this to determine dominant/secondary/weakest courses
SELECT 
    q.course_tag,
    COUNT(*) as total_questions,
    SUM(CASE WHEN ea.is_correct = 1 THEN 1 ELSE 0 END) as correct_answers,
    SUM(ea.points_awarded) as total_points,
    AVG(CASE WHEN ea.is_correct = 1 THEN 1.0 ELSE 0.0 END) * 100 as accuracy_percent
FROM exam_answers ea
INNER JOIN questions q ON ea.question_id = q.id
WHERE ea.session_id = ?  -- Replace ? with session ID
GROUP BY q.course_tag
ORDER BY total_points DESC, accuracy_percent DESC;

-- Query 14: Get course rankings for re-evaluation
-- Use this to determine which course is dominant, secondary, weakest
SELECT 
    q.course_tag,
    SUM(ea.points_awarded) as score,
    COUNT(*) as question_count
FROM exam_answers ea
INNER JOIN questions q ON ea.question_id = q.id
WHERE ea.session_id = ?  -- Replace ? with session ID
GROUP BY q.course_tag
ORDER BY score DESC, question_count DESC;

-- ============================================================================
-- UTILITY QUERIES
-- ============================================================================
-- Helper queries for common operations
-- ============================================================================

-- Query 15: Get question with options (for API response)
-- Use this when you need to return a question with its options
SELECT 
    q.id,
    q.question_text,
    q.question_type,
    q.category,
    q.course_tag,
    q.difficulty,
    q.weight,
    ao.id as option_id,
    ao.option_text,
    ao.it_score,
    ao.cs_score,
    ao.is_score
FROM questions q
LEFT JOIN answer_options ao ON q.id = ao.question_id
WHERE q.id = ?           -- Replace ? with question ID
  AND q.is_active = 1
ORDER BY ao.id;

-- Query 16: Check if question exists and is available
-- Use this to validate question before selection
SELECT 
    q.id,
    q.category,
    q.course_tag,
    q.is_active,
    CASE 
        WHEN q.id IN (?) THEN 'already_answered'
        ELSE 'available'
    END as status
FROM questions q
WHERE q.id = ?            -- Replace ? with question ID
  AND q.is_active = 1;

-- Query 17: Get question statistics by phase and course
-- Use this for reporting and analysis
SELECT 
    q.category,
    q.course_tag,
    q.difficulty,
    COUNT(*) as question_count
FROM questions q
WHERE q.is_active = 1
GROUP BY q.category, q.course_tag, q.difficulty
ORDER BY q.category, q.course_tag, q.difficulty;

-- ============================================================================
-- NOTES ON USAGE
-- ============================================================================
-- 
-- 1. Replace ? placeholders with actual values in your application code
-- 2. For arrays (answered question IDs), use prepared statements with IN clause
-- 3. Adjust ORDER BY clauses based on your selection strategy
-- 4. Consider adding additional WHERE conditions for difficulty, weight, etc.
-- 5. Use transactions when selecting questions to prevent race conditions
-- 
-- Example PHP usage:
-- $stmt = $conn->prepare("SELECT ... WHERE q.id NOT IN (?)");
-- $answeredIds = [1, 2, 3, 4, 5];
-- $placeholders = implode(',', array_fill(0, count($answeredIds), '?'));
-- $stmt = $conn->prepare("SELECT ... WHERE q.id NOT IN ($placeholders)");
-- $stmt->execute($answeredIds);
-- 
-- ============================================================================
