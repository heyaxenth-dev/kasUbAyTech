-- ============================================================================
-- Data Migration Script: Old Database → New CAT-lite Database
-- ============================================================================
-- Purpose: Copy data from kasubaytech_db to kasubaytech_catlite_db
-- 
-- This script:
-- 1. Copies client and admin data
-- 2. Migrates questions with proper course_tag and category assignment
-- 3. Copies answer_options
-- 4. Optionally migrates exam data (if you want to preserve sessions)
--
-- IMPORTANT: 
-- - Run schema_cat_lite.sql first to create the new database
-- - Backup both databases before running
-- - Review and adjust diagnostic question course_tag assignments
-- ============================================================================

-- ============================================================================
-- STEP 1: Copy Client Data
-- ============================================================================

INSERT INTO kasubaytech_catlite_db.client (
    id, firstname, middlename, lastname, created_at
)
SELECT 
    id, firstname, middlename, lastname, created_at
FROM kasubaytech_db.client
ON DUPLICATE KEY UPDATE 
    firstname = VALUES(firstname),
    middlename = VALUES(middlename),
    lastname = VALUES(lastname);

-- ============================================================================
-- STEP 2: Copy Admin Data
-- ============================================================================

INSERT INTO kasubaytech_catlite_db.admin (
    id, username, password, email, created_at
)
SELECT 
    id, username, password, email, created_at
FROM kasubaytech_db.admin
ON DUPLICATE KEY UPDATE 
    username = VALUES(username),
    password = VALUES(password),
    email = VALUES(email);

-- ============================================================================
-- STEP 3: Migrate Questions with CAT-lite Structure
-- ============================================================================
-- This is the critical step that transforms the old structure to new:
-- - Old category='DIAGNOSTIC' → new category='DIAGNOSTIC', course_tag inferred
-- - Old category='IS','IT','CS' → new category='ADAPTIVE', course_tag=old category
-- ============================================================================

-- Step 3a: Migrate non-diagnostic questions (IS/IT/CS → ADAPTIVE)
INSERT INTO kasubaytech_catlite_db.questions (
    id, question_text, question_type, category, course_tag, 
    difficulty, weight, correct_option, 
    option_a, option_b, option_c, option_d, 
    topic, is_correct_answer, order_number, is_active, 
    created_at, updated_at
)
SELECT 
    id, 
    question_text, 
    question_type,
    'ADAPTIVE' as category,  -- Convert IS/IT/CS to ADAPTIVE
    category as course_tag,  -- Old category becomes course_tag
    COALESCE(difficulty, 'MEDIUM') as difficulty,
    COALESCE(weight, 1) as weight,
    correct_option,
    option_a, option_b, option_c, option_d,
    topic,
    is_correct_answer,
    order_number,
    is_active,
    created_at,
    updated_at
FROM kasubaytech_db.questions
WHERE category IN ('IS', 'IT', 'CS')
ON DUPLICATE KEY UPDATE 
    question_text = VALUES(question_text),
    category = VALUES(category),
    course_tag = VALUES(course_tag);

-- Step 3b: Migrate diagnostic questions
-- Strategy: Infer course_tag from answer_options scores
-- If option scores are not available, default to 'IT' (you should review these)
INSERT INTO kasubaytech_catlite_db.questions (
    id, question_text, question_type, category, course_tag, 
    difficulty, weight, correct_option, 
    option_a, option_b, option_c, option_d, 
    topic, is_correct_answer, order_number, is_active, 
    created_at, updated_at
)
SELECT 
    q.id, 
    q.question_text, 
    q.question_type,
    'DIAGNOSTIC' as category,  -- Keep as DIAGNOSTIC
    COALESCE(
        -- Try to infer from answer_options scores
        (SELECT 
            CASE 
                WHEN AVG(ao.it_score) >= AVG(ao.cs_score) 
                     AND AVG(ao.it_score) >= AVG(ao.is_score) THEN 'IT'
                WHEN AVG(ao.cs_score) >= AVG(ao.is_score) THEN 'CS'
                ELSE 'IS'
            END
         FROM kasubaytech_db.answer_options ao
         WHERE ao.question_id = q.id
         GROUP BY ao.question_id),
        'IT'  -- Default if no options or scores available
    ) as course_tag,
    COALESCE(q.difficulty, 'MEDIUM') as difficulty,
    COALESCE(q.weight, 1) as weight,
    q.correct_option,
    q.option_a, q.option_b, q.option_c, q.option_d,
    q.topic,
    q.is_correct_answer,
    q.order_number,
    q.is_active,
    q.created_at,
    q.updated_at
FROM kasubaytech_db.questions q
WHERE q.category = 'DIAGNOSTIC'
ON DUPLICATE KEY UPDATE 
    question_text = VALUES(question_text),
    category = VALUES(category),
    course_tag = VALUES(course_tag);

-- ============================================================================
-- STEP 4: Copy Answer Options
-- ============================================================================

INSERT INTO kasubaytech_catlite_db.answer_options (
    id, question_id, option_text, it_score, cs_score, is_score, created_at
)
SELECT 
    id, question_id, option_text, it_score, cs_score, is_score, created_at
FROM kasubaytech_db.answer_options
ON DUPLICATE KEY UPDATE 
    option_text = VALUES(option_text),
    it_score = VALUES(it_score),
    cs_score = VALUES(cs_score),
    is_score = VALUES(is_score);

-- ============================================================================
-- STEP 5: Migrate Exam Sessions (Optional - if you want to preserve sessions)
-- ============================================================================
-- Note: Only migrate if you want to preserve existing exam sessions
-- Otherwise, start fresh with new sessions
-- ============================================================================

/*
INSERT INTO kasubaytech_catlite_db.exam_sessions (
    id, user_id, current_question_id, dominant_category, 
    stage, confidence_score, created_at
)
SELECT 
    id, user_id, current_question_id, dominant_category,
    stage, confidence_score, created_at
FROM kasubaytech_db.exam_sessions
ON DUPLICATE KEY UPDATE 
    user_id = VALUES(user_id),
    current_question_id = VALUES(current_question_id),
    dominant_category = VALUES(dominant_category),
    stage = VALUES(stage),
    confidence_score = VALUES(confidence_score);
*/

-- ============================================================================
-- STEP 6: Migrate Exam Answers (Optional - if migrating sessions)
-- ============================================================================
-- Note: This updates exam_answers with new category and course_tag structure
-- ============================================================================

/*
INSERT INTO kasubaytech_catlite_db.exam_answers (
    id, session_id, question_id, selected_option, is_correct,
    category, course_tag, points_awarded, created_at
)
SELECT 
    ea.id,
    ea.session_id,
    ea.question_id,
    ea.selected_option,
    ea.is_correct,
    q.category as category,      -- New category from questions table
    q.course_tag as course_tag,   -- New course_tag from questions table
    ea.points_awarded,
    ea.created_at
FROM kasubaytech_db.exam_answers ea
INNER JOIN kasubaytech_catlite_db.questions q ON ea.question_id = q.id
ON DUPLICATE KEY UPDATE 
    selected_option = VALUES(selected_option),
    is_correct = VALUES(is_correct),
    category = VALUES(category),
    course_tag = VALUES(course_tag),
    points_awarded = VALUES(points_awarded);
*/

-- ============================================================================
-- STEP 7: Verification Queries
-- ============================================================================
-- Run these to verify the migration was successful
-- ============================================================================

-- Check question counts by category
SELECT 
    category, 
    COUNT(*) as count 
FROM kasubaytech_catlite_db.questions 
GROUP BY category;

-- Check question counts by course_tag
SELECT 
    course_tag, 
    COUNT(*) as count 
FROM kasubaytech_catlite_db.questions 
GROUP BY course_tag;

-- Check category + course_tag combination
SELECT 
    category, 
    course_tag, 
    COUNT(*) as count 
FROM kasubaytech_catlite_db.questions 
GROUP BY category, course_tag
ORDER BY category, course_tag;

-- Check for diagnostic questions without proper course_tag
SELECT 
    id, 
    question_text, 
    course_tag 
FROM kasubaytech_catlite_db.questions 
WHERE category = 'DIAGNOSTIC' 
  AND course_tag = 'IT'  -- Adjust if you want to find defaults
LIMIT 10;

-- Verify answer_options count matches
SELECT 
    (SELECT COUNT(*) FROM kasubaytech_db.answer_options) as old_count,
    (SELECT COUNT(*) FROM kasubaytech_catlite_db.answer_options) as new_count;

-- ============================================================================
-- POST-MIGRATION TASKS
-- ============================================================================
-- 1. Review diagnostic questions and manually adjust course_tag if needed
-- 2. Update application config to point to new database
-- 3. Test the adaptive algorithm with new structure
-- 4. Verify all queries work correctly
-- ============================================================================

-- Example: Manually update diagnostic question course_tag
-- UPDATE kasubaytech_catlite_db.questions 
-- SET course_tag = 'IS' 
-- WHERE id = 1 AND category = 'DIAGNOSTIC';
