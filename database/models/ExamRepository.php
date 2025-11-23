<?php
/**
 * ExamRepository
 * 
 * Handles all database operations for the exam system
 * Uses the new exam_sessions, exam_answers, and exam_results tables
 */

class ExamRepository
{
    private $conn;

    public function __construct($connection)
    {
        $this->conn = $connection;
    }

    /**
     * Create a new exam session
     * 
     * @param int $userId The client/user ID
     * @return int|false Session ID on success, false on failure
     */
    public function createSession($userId)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO exam_sessions (user_id, stage, confidence_score) VALUES (?, 'DIAGNOSTIC', 0)"
        );
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            $sessionId = $stmt->insert_id;
            $stmt->close();
            return $sessionId;
        }
        
        $stmt->close();
        return false;
    }

    /**
     * Get session by ID
     * 
     * @param int $sessionId
     * @return array|null Session data or null if not found
     */
    public function getSession($sessionId)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM exam_sessions WHERE id = ?"
        );
        
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $sessionId);
        $stmt->execute();
        $result = $stmt->get_result();
        $session = $result->fetch_assoc();
        $stmt->close();
        
        return $session ? $session : null;
    }

    /**
     * Update session
     * 
     * @param int $sessionId
     * @param array $updates Array of fields to update ['field' => 'value']
     * @return bool Success status
     */
    public function updateSession($sessionId, $updates)
    {
        if (empty($updates)) {
            return false;
        }

        $allowedFields = ['current_question_id', 'dominant_category', 'stage', 'confidence_score'];
        $setParts = [];
        $types = '';
        $values = [];

        foreach ($updates as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $setParts[] = "`{$field}` = ?";
                $types .= $this->getBindType($value);
                $values[] = $value;
            }
        }

        if (empty($setParts)) {
            return false;
        }

        $sql = "UPDATE exam_sessions SET " . implode(', ', $setParts) . " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            return false;
        }

        $types .= 'i';
        $values[] = $sessionId;
        $stmt->bind_param($types, ...$values);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    /**
     * Save an answer
     * 
     * @param int $sessionId
     * @param int $questionId
     * @param string $selectedOption 'A', 'B', 'C', or 'D'
     * @param string $category Question category
     * @param bool $isCorrect Whether the answer is correct
     * @param int $pointsAwarded Points awarded for this answer
     * @return int|false Answer ID on success, false on failure
     */
    public function saveAnswer($sessionId, $questionId, $selectedOption, $category, $isCorrect, $pointsAwarded = 0)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO exam_answers (session_id, question_id, selected_option, is_correct, category, points_awarded) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("iisisi", $sessionId, $questionId, $selectedOption, $isCorrect, $category, $pointsAwarded);
        
        if ($stmt->execute()) {
            $answerId = $stmt->insert_id;
            $stmt->close();
            return $answerId;
        }
        
        $stmt->close();
        return false;
    }

    /**
     * Get all answers for a session
     * 
     * @param int $sessionId
     * @return array Array of answer records
     */
    public function getSessionAnswers($sessionId)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM exam_answers WHERE session_id = ? ORDER BY created_at ASC"
        );
        
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $sessionId);
        $stmt->execute();
        $result = $stmt->get_result();
        $answers = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $answers;
    }

    /**
     * Save exam result
     * 
     * @param int $sessionId
     * @param string $recommendedCourse 'IS', 'IT', 'CS', or 'UNDECIDED'
     * @param int $finalScore Final score
     * @param float $confidenceScore Confidence score
     * @return int|false Result ID on success, false on failure
     */
    public function saveResult($sessionId, $recommendedCourse, $finalScore, $confidenceScore)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO exam_results (session_id, recommended_course, final_score, confidence_score) 
             VALUES (?, ?, ?, ?)"
        );
        
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("isid", $sessionId, $recommendedCourse, $finalScore, $confidenceScore);
        
        if ($stmt->execute()) {
            $resultId = $stmt->insert_id;
            $stmt->close();
            return $resultId;
        }
        
        $stmt->close();
        return false;
    }

    /**
     * Get result by session ID
     * 
     * @param int $sessionId
     * @return array|null Result data or null if not found
     */
    public function getResult($sessionId)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM exam_results WHERE session_id = ?"
        );
        
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $sessionId);
        $stmt->execute();
        $result = $stmt->get_result();
        $examResult = $result->fetch_assoc();
        $stmt->close();
        
        return $examResult ? $examResult : null;
    }

    /**
     * Get bind type for prepared statement
     * 
     * @param mixed $value
     * @return string 'i', 'd', or 's'
     */
    private function getBindType($value)
    {
        if (is_int($value)) {
            return 'i';
        } elseif (is_float($value) || is_double($value)) {
            return 'd';
        } else {
            return 's';
        }
    }
}

