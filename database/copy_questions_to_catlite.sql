-- ============================================================================
-- Copy Questions from Old Database to New CAT-lite Database
-- ============================================================================
-- This script automatically transforms and copies questions from
-- kasubaytech_db to kasubaytech_catlite_db with proper CAT-lite structure
-- ============================================================================

USE kasubaytech_catlite_db;

-- ============================================================================
-- STEP 1: Copy Non-Diagnostic Questions (IS/IT/CS → ADAPTIVE)
-- ============================================================================

INSERT INTO `questions` (
    `id`, `question_text`, `question_type`, `category`, `course_tag`,
    `difficulty`, `weight`, `correct_option`,
    `option_a`, `option_b`, `option_c`, `option_d`,
    `topic`, `is_correct_answer`, `order_number`, `is_active`,
    `created_at`, `updated_at`
)
SELECT 
    `id`,
    `question_text`,
    `question_type`,
    'ADAPTIVE' as `category`,  -- Transform: IS/IT/CS → ADAPTIVE
    `category` as `course_tag`, -- Old category becomes course_tag
    COALESCE(`difficulty`, 'MEDIUM') as `difficulty`,
    COALESCE(`weight`, 1) as `weight`,
    `correct_option`,
    `option_a`, `option_b`, `option_c`, `option_d`,
    `topic`,
    `is_correct_answer`,
    `order_number`,
    `is_active`,
    `created_at`,
    `updated_at`
FROM kasubaytech_db.questions
WHERE `category` IN ('IS', 'IT', 'CS')
ON DUPLICATE KEY UPDATE
    `question_text` = VALUES(`question_text`),
    `category` = VALUES(`category`),
    `course_tag` = VALUES(`course_tag`),
    `difficulty` = VALUES(`difficulty`),
    `weight` = VALUES(`weight`);

-- ============================================================================
-- STEP 2: Copy Diagnostic Questions with Inferred course_tag
-- ============================================================================
-- For diagnostic questions, we infer course_tag from answer_options scores
-- If no options available, defaults to 'IT'
-- ============================================================================

INSERT INTO `questions` (
    `id`, `question_text`, `question_type`, `category`, `course_tag`,
    `difficulty`, `weight`, `correct_option`,
    `option_a`, `option_b`, `option_c`, `option_d`,
    `topic`, `is_correct_answer`, `order_number`, `is_active`,
    `created_at`, `updated_at`
)
SELECT 
    q.`id`,
    q.`question_text`,
    q.`question_type`,
    'DIAGNOSTIC' as `category`,  -- Keep as DIAGNOSTIC
    COALESCE(
        -- Infer course_tag from answer_options scores
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
    ) as `course_tag`,
    COALESCE(q.`difficulty`, 'MEDIUM') as `difficulty`,
    COALESCE(q.`weight`, 1) as `weight`,
    q.`correct_option`,
    q.`option_a`, q.`option_b`, q.`option_c`, q.`option_d`,
    q.`topic`,
    q.`is_correct_answer`,
    q.`order_number`,
    q.`is_active`,
    q.`created_at`,
    q.`updated_at`
FROM kasubaytech_db.questions q
WHERE q.`category` = 'DIAGNOSTIC'
ON DUPLICATE KEY UPDATE
    `question_text` = VALUES(`question_text`),
    `category` = VALUES(`category`),
    `course_tag` = VALUES(`course_tag`),
    `difficulty` = VALUES(`difficulty`),
    `weight` = VALUES(`weight`);

-- ============================================================================
-- STEP 3: Copy Answer Options
-- ============================================================================

INSERT INTO `answer_options` (
    `id`, `question_id`, `option_text`, `it_score`, `cs_score`, `is_score`, `created_at`
)
SELECT 
    `id`, `question_id`, `option_text`, `it_score`, `cs_score`, `is_score`, `created_at`
FROM kasubaytech_db.answer_options
ON DUPLICATE KEY UPDATE
    `option_text` = VALUES(`option_text`),
    `it_score` = VALUES(`it_score`),
    `cs_score` = VALUES(`cs_score`),
    `is_score` = VALUES(`is_score`);

-- ============================================================================
-- VERIFICATION
-- ============================================================================

-- Check question distribution
SELECT 
    category, 
    course_tag, 
    COUNT(*) as count 
FROM questions 
GROUP BY category, course_tag
ORDER BY category, course_tag;

-- List diagnostic questions with their course_tag
SELECT 
    id, 
    LEFT(question_text, 50) as question_preview,
    category,
    course_tag
FROM questions 
WHERE category = 'DIAGNOSTIC'
ORDER BY id;

-- Count questions by type
SELECT 
    category,
    COUNT(*) as total,
    SUM(CASE WHEN course_tag = 'IT' THEN 1 ELSE 0 END) as IT_count,
    SUM(CASE WHEN course_tag = 'IS' THEN 1 ELSE 0 END) as IS_count,
    SUM(CASE WHEN course_tag = 'CS' THEN 1 ELSE 0 END) as CS_count
FROM questions
GROUP BY category;
