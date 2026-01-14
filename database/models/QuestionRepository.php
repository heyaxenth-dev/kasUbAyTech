<?php
/**
 * QuestionRepository
 * 
 * Handles database operations for questions
 * Optimized queries with proper indexing
 */

class QuestionRepository
{
    private $conn;

    public function __construct($connection)
    {
        $this->conn = $connection;
    }

    /**
     * Get question by ID with options
     * 
     * @param int $questionId
     * @return array|null Question data with options or null
     */
    public function getQuestionById($questionId)
    {
        // CAT-lite: Include course_tag in query
        $stmt = $this->conn->prepare(
            "SELECT id, question_text, question_type, option_a, option_b, option_c, option_d, 
                    correct_option, category, course_tag, difficulty, weight 
             FROM questions 
             WHERE id = ? AND is_active = 1"
        );
        
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $questionId);
        $stmt->execute();
        $result = $stmt->get_result();
        $question = $result->fetch_assoc();
        $stmt->close();
        
        if (!$question) {
            return null;
        }

        // Also fetch from answer_options for backward compatibility
        $stmt2 = $this->conn->prepare(
            "SELECT id, option_text, it_score, cs_score, is_score 
             FROM answer_options 
             WHERE question_id = ? 
             ORDER BY id"
        );
        
        if ($stmt2) {
            $stmt2->bind_param("i", $questionId);
            $stmt2->execute();
            $optionsResult = $stmt2->get_result();
            $question['options'] = $optionsResult->fetch_all(MYSQLI_ASSOC);
            $stmt2->close();
        }
        
        return $question;
    }

    /**
     * Get diagnostic questions (not yet answered)
     * 
     * @param array $answeredQuestionIds Array of question IDs already answered
     * @param int $limit Maximum number of questions to return
     * @return array Array of question records
     */
    public function getDiagnosticQuestions($answeredQuestionIds = [], $limit = 10)
    {
        if (empty($answeredQuestionIds)) {
            // CAT-lite: Include course_tag in query
            $stmt = $this->conn->prepare(
                "SELECT id, question_text, option_a, option_b, option_c, option_d, 
                        correct_option, category, course_tag, difficulty, weight 
                 FROM questions 
                 WHERE category = 'DIAGNOSTIC' AND is_active = 1 
                 ORDER BY difficulty, id 
                 LIMIT ?"
            );
            
            if (!$stmt) {
                return [];
            }

            $stmt->bind_param("i", $limit);
        } else {
            $placeholders = str_repeat('?,', count($answeredQuestionIds) - 1) . '?';
            // CAT-lite: Include course_tag in query
            $stmt = $this->conn->prepare(
                "SELECT id, question_text, option_a, option_b, option_c, option_d, 
                        correct_option, category, course_tag, difficulty, weight 
                 FROM questions 
                 WHERE category = 'DIAGNOSTIC' AND is_active = 1 
                   AND id NOT IN ($placeholders)
                 ORDER BY difficulty, id 
                 LIMIT ?"
            );
            
            if (!$stmt) {
                return [];
            }

            $types = str_repeat('i', count($answeredQuestionIds)) . 'i';
            $params = array_merge($answeredQuestionIds, [$limit]);
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $questions = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $questions;
    }

    /**
     * Get questions by course_tag (not yet answered)
     * CAT-lite: Updated to use course_tag instead of category for course identification
     * 
     * @param string $courseTag 'IS', 'IT', or 'CS' (course identity)
     * @param array $answeredQuestionIds Array of question IDs already answered
     * @param int $limit Maximum number of questions to return
     * @return array Array of question records
     */
    public function getQuestionsByCategory($courseTag, $answeredQuestionIds = [], $limit = 10)
    {
        // CAT-lite: Validate course_tag instead of category
        if (!in_array($courseTag, ['IS', 'IT', 'CS'])) {
            return [];
        }

        if (empty($answeredQuestionIds)) {
            // CAT-lite: Query by course_tag and category='ADAPTIVE'
            $stmt = $this->conn->prepare(
                "SELECT id, question_text, option_a, option_b, option_c, option_d, 
                        correct_option, category, course_tag, difficulty, weight 
                 FROM questions 
                 WHERE category = 'ADAPTIVE' AND course_tag = ? AND is_active = 1 
                 ORDER BY difficulty, id 
                 LIMIT ?"
            );
            
            if (!$stmt) {
                return [];
            }

            $stmt->bind_param("si", $courseTag, $limit);
        } else {
            $placeholders = str_repeat('?,', count($answeredQuestionIds) - 1) . '?';
            // CAT-lite: Query by course_tag and category='ADAPTIVE'
            $stmt = $this->conn->prepare(
                "SELECT id, question_text, option_a, option_b, option_c, option_d, 
                        correct_option, category, course_tag, difficulty, weight 
                 FROM questions 
                 WHERE category = 'ADAPTIVE' AND course_tag = ? AND is_active = 1 
                   AND id NOT IN ($placeholders)
                 ORDER BY difficulty, id 
                 LIMIT ?"
            );
            
            if (!$stmt) {
                return [];
            }

            $types = 's' . str_repeat('i', count($answeredQuestionIds)) . 'i';
            $params = array_merge([$courseTag], $answeredQuestionIds, [$limit]);
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $questions = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $questions;
    }

    /**
     * Get all active question IDs
     * 
     * @return array Array of question IDs
     */
    public function getAllQuestionIds()
    {
        $result = $this->conn->query(
            "SELECT id FROM questions WHERE is_active = 1 ORDER BY category, difficulty, id"
        );
        
        if (!$result) {
            return [];
        }

        $ids = [];
        while ($row = $result->fetch_assoc()) {
            $ids[] = (int)$row['id'];
        }
        
        return $ids;
    }

    /**
     * Check if answer is correct
     * 
     * @param int $questionId
     * @param string $selectedOption 'A', 'B', 'C', or 'D'
     * @return bool True if correct, false otherwise
     */
    public function isAnswerCorrect($questionId, $selectedOption)
    {
        $stmt = $this->conn->prepare(
            "SELECT correct_option FROM questions WHERE id = ?"
        );
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $questionId);
        $stmt->execute();
        $result = $stmt->get_result();
        $question = $result->fetch_assoc();
        $stmt->close();
        
        if (!$question || !$question['correct_option']) {
            return false;
        }

        return strtoupper($selectedOption) === strtoupper($question['correct_option']);
    }

    /**
     * Get question category
     * 
     * @param int $questionId
     * @return string|null Category or null if not found
     */
    public function getQuestionCategory($questionId)
    {
        $stmt = $this->conn->prepare(
            "SELECT category FROM questions WHERE id = ?"
        );
        
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $questionId);
        $stmt->execute();
        $result = $stmt->get_result();
        $question = $result->fetch_assoc();
        $stmt->close();
        
        return $question ? $question['category'] : null;
    }
}

