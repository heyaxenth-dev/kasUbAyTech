<?php
/**
 * Exam API Endpoints
 * 
 * Handles all exam-related API requests:
 * - POST /api/exam.php?action=start-exam
 * - POST /api/exam.php?action=get-question
 * - POST /api/exam.php?action=submit-answer
 * - POST /api/exam.php?action=finish-exam
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../database/models/ExamRepository.php';
require_once __DIR__ . '/../database/models/QuestionRepository.php';

// Initialize repositories
$examRepo = new ExamRepository($conn);
$questionRepo = new QuestionRepository($conn);

// Get action from query parameter
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Handle CORS if needed
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Route to appropriate handler
switch ($action) {
    case 'start-exam':
        handleStartExam($examRepo, $conn);
        break;
    
    case 'get-question':
        handleGetQuestion($examRepo, $questionRepo, $conn);
        break;
    
    case 'submit-answer':
        handleSubmitAnswer($examRepo, $questionRepo, $conn);
        break;
    
    case 'finish-exam':
        handleFinishExam($examRepo, $questionRepo, $conn);
        break;
    
    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid action. Use: start-exam, get-question, submit-answer, or finish-exam'
        ]);
        break;
}

/**
 * Start a new exam session
 * POST /api/exam.php?action=start-exam
 * Body: { "user_id": 123 }
 */
function handleStartExam($examRepo, $conn)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['user_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'user_id is required']);
        return;
    }

    $userId = intval($data['user_id']);

    // Verify user exists
    $stmt = $conn->prepare("SELECT id FROM client WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'User not found']);
        return;
    }
    $stmt->close();

    // Create session
    $sessionId = $examRepo->createSession($userId);
    
    if (!$sessionId) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to create exam session']);
        return;
    }

    echo json_encode([
        'success' => true,
        'session_id' => $sessionId,
        'stage' => 'DIAGNOSTIC'
    ]);
}

/**
 * Get next question for the exam
 * POST /api/exam.php?action=get-question
 * Body: { "session_id": 123 }
 * 
 * This endpoint integrates with the Python adaptive service
 */
function handleGetQuestion($examRepo, $questionRepo, $conn)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['session_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'session_id is required']);
        return;
    }

    $sessionId = intval($data['session_id']);
    $session = $examRepo->getSession($sessionId);
    
    if (!$session) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Session not found']);
        return;
    }

    // If session is finished, return error
    if ($session['stage'] === 'FINISHED') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Exam session is already finished']);
        return;
    }

    // Get answered questions for adaptive service
    $answers = $examRepo->getSessionAnswers($sessionId);
    $answeredQuestions = [];
    
    foreach ($answers as $answer) {
        // Convert to format expected by Python service
        // The Python service expects option_ids from answer_options table
        // We store selected_option as A/B/C/D, need to map to option_id
        $question = $questionRepo->getQuestionById($answer['question_id']);
        
        if ($question && isset($question['options']) && is_array($question['options'])) {
            // Map selected_option (A/B/C/D) to option_id from answer_options table
            $optionMap = ['A' => 0, 'B' => 1, 'C' => 2, 'D' => 3];
            $optionIndex = $optionMap[strtoupper($answer['selected_option'])] ?? null;
            
            if ($optionIndex !== null && isset($question['options'][$optionIndex])) {
                $optionId = (int)$question['options'][$optionIndex]['id'];
                $answeredQuestions[] = [
                    'question_id' => (int)$answer['question_id'],
                    'option_ids' => [$optionId]
                ];
            }
        }
    }

    // Call Python adaptive service
    $adaptiveServiceUrl = 'http://localhost:5000/get_next_question';
    $allQuestionIds = $questionRepo->getAllQuestionIds();
    
    $ch = curl_init($adaptiveServiceUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'answered_questions' => $answeredQuestions,
        'all_question_ids' => $allQuestionIds
    ]));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        // Fallback: get next diagnostic question
        $answeredIds = array_column($answers, 'question_id');
        $questions = $questionRepo->getDiagnosticQuestions($answeredIds, 1);
        
        if (empty($questions)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'No more questions available']);
            return;
        }
        
        $question = $questions[0];
        $questionData = formatQuestionResponse($question);
        
        echo json_encode([
            'success' => true,
            'stop' => false,
            'question' => $questionData
        ]);
        return;
    }

    $adaptiveResponse = json_decode($response, true);
    
    if (!$adaptiveResponse || !$adaptiveResponse['success']) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Adaptive service error']);
        return;
    }

    // If adaptive service says to stop
    if (isset($adaptiveResponse['stop']) && $adaptiveResponse['stop']) {
        echo json_encode([
            'success' => true,
            'stop' => true,
            'reason' => $adaptiveResponse['reason'] ?? 'completed',
            'scores' => $adaptiveResponse['scores'] ?? [],
            'recommended_course' => $adaptiveResponse['recommended_course'] ?? 'UNDECIDED'
        ]);
        return;
    }

    // Get question details from database
    $questionId = $adaptiveResponse['question']['question_id'] ?? null;
    
    if (!$questionId) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Invalid question ID from adaptive service']);
        return;
    }

    $question = $questionRepo->getQuestionById($questionId);
    
    if (!$question) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Question not found']);
        return;
    }

    // Update session with current question
    $examRepo->updateSession($sessionId, ['current_question_id' => $questionId]);

    // Format response
    $questionData = formatQuestionResponse($question);
    $questionData['current_scores'] = $adaptiveResponse['question']['current_scores'] ?? [];
    $questionData['utility_score'] = $adaptiveResponse['question']['utility_score'] ?? null;
    $questionData['reason'] = $adaptiveResponse['question']['reason'] ?? '';

    echo json_encode([
        'success' => true,
        'stop' => false,
        'question' => $questionData
    ]);
}

/**
 * Submit an answer
 * POST /api/exam.php?action=submit-answer
 * Body: { "session_id": 123, "question_id": 456, "selected_option": "A" }
 */
function handleSubmitAnswer($examRepo, $questionRepo, $conn)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['session_id']) || !isset($data['question_id']) || !isset($data['selected_option'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'session_id, question_id, and selected_option are required']);
        return;
    }

    $sessionId = intval($data['session_id']);
    $questionId = intval($data['question_id']);
    $selectedOption = strtoupper($data['selected_option']);

    // Validate selected option
    if (!in_array($selectedOption, ['A', 'B', 'C', 'D'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'selected_option must be A, B, C, or D']);
        return;
    }

    // Verify session exists
    $session = $examRepo->getSession($sessionId);
    if (!$session) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Session not found']);
        return;
    }

    // Get question details
    $question = $questionRepo->getQuestionById($questionId);
    if (!$question) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Question not found']);
        return;
    }

    $category = $question['category'] ?? 'DIAGNOSTIC';
    $isCorrect = $questionRepo->isAnswerCorrect($questionId, $selectedOption);
    $weight = intval($question['weight'] ?? 1);
    $pointsAwarded = $isCorrect ? $weight : 0;

    // Save answer
    $answerId = $examRepo->saveAnswer($sessionId, $questionId, $selectedOption, $category, $isCorrect, $pointsAwarded);
    
    if (!$answerId) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to save answer']);
        return;
    }

    // Update session stage if needed (transition from DIAGNOSTIC to CATEGORY)
    if ($session['stage'] === 'DIAGNOSTIC' && $category !== 'DIAGNOSTIC') {
        // Determine dominant category from answers
        $answers = $examRepo->getSessionAnswers($sessionId);
        $categoryScores = ['IS' => 0, 'IT' => 0, 'CS' => 0];
        
        foreach ($answers as $answer) {
            if ($answer['category'] !== 'DIAGNOSTIC' && $answer['is_correct']) {
                $cat = $answer['category'];
                if (isset($categoryScores[$cat])) {
                    $categoryScores[$cat] += $answer['points_awarded'];
                }
            }
        }
        
        $dominantCategory = array_search(max($categoryScores), $categoryScores);
        $examRepo->updateSession($sessionId, [
            'stage' => 'CATEGORY',
            'dominant_category' => $dominantCategory
        ]);
    }

    echo json_encode([
        'success' => true,
        'answer_id' => $answerId,
        'is_correct' => $isCorrect,
        'points_awarded' => $pointsAwarded
    ]);
}

/**
 * Finish exam and calculate final results
 * POST /api/exam.php?action=finish-exam
 * Body: { "session_id": 123 }
 */
function handleFinishExam($examRepo, $questionRepo, $conn)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['session_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'session_id is required']);
        return;
    }

    $sessionId = intval($data['session_id']);
    $session = $examRepo->getSession($sessionId);
    
    if (!$session) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Session not found']);
        return;
    }

    if ($session['stage'] === 'FINISHED') {
        // Return existing result
        $result = $examRepo->getResult($sessionId);
        if ($result) {
            echo json_encode([
                'success' => true,
                'result' => $result
            ]);
            return;
        }
    }

    // Get all answers
    $answers = $examRepo->getSessionAnswers($sessionId);
    
    // Calculate scores using Python service
    $answeredQuestions = [];
    foreach ($answers as $answer) {
        $question = $questionRepo->getQuestionById($answer['question_id']);
        if ($question && isset($question['options']) && is_array($question['options'])) {
            $optionMap = ['A' => 0, 'B' => 1, 'C' => 2, 'D' => 3];
            $optionIndex = $optionMap[strtoupper($answer['selected_option'])] ?? null;
            if ($optionIndex !== null && isset($question['options'][$optionIndex])) {
                $optionId = (int)$question['options'][$optionIndex]['id'];
                $answeredQuestions[] = [
                    'question_id' => (int)$answer['question_id'],
                    'option_ids' => [$optionId]
                ];
            }
        }
    }

    // Call Python service for final scores
    $adaptiveServiceUrl = 'http://localhost:5000/calculate_scores';
    $ch = curl_init($adaptiveServiceUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['answered_questions' => $answeredQuestions]));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $scores = ['IT' => 0, 'CS' => 0, 'IS' => 0];
    $recommendedCourse = 'UNDECIDED';
    $confidenceScore = 0.0;

    if ($httpCode === 200 && $response) {
        $scoreData = json_decode($response, true);
        if ($scoreData && isset($scoreData['scores'])) {
            $scores = $scoreData['scores'];
            $recommendedCourse = $scoreData['recommended_course'] ?? 'UNDECIDED';
            
            // Calculate confidence score (gap between top and second)
            $sortedScores = $scores;
            arsort($sortedScores);
            $sortedArray = array_values($sortedScores);
            if (count($sortedArray) >= 2) {
                $top = $sortedArray[0];
                $second = $sortedArray[1];
                $confidenceScore = ($top - $second) / max($top, 1);
            } else {
                $confidenceScore = $sortedArray[0] / 100.0;
            }
        }
    }

    // Calculate final score (sum of points)
    $finalScore = 0;
    foreach ($answers as $answer) {
        $finalScore += intval($answer['points_awarded']);
    }

    // Update session
    $examRepo->updateSession($sessionId, [
        'stage' => 'FINISHED',
        'confidence_score' => $confidenceScore
    ]);

    // Save result
    $resultId = $examRepo->saveResult($sessionId, $recommendedCourse, $finalScore, $confidenceScore);

    if (!$resultId) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to save exam result']);
        return;
    }

    $result = $examRepo->getResult($sessionId);

    echo json_encode([
        'success' => true,
        'result' => $result,
        'scores' => $scores,
        'recommended_course' => $recommendedCourse
    ]);
}

/**
 * Format question for API response
 * 
 * @param array $question Question data from database
 * @return array Formatted question data
 */
function formatQuestionResponse($question)
{
    $formatted = [
        'question_id' => (int)$question['id'],
        'question_text' => $question['question_text'],
        'category' => $question['category'] ?? 'DIAGNOSTIC',
        'difficulty' => $question['difficulty'] ?? 'MEDIUM',
        'weight' => (int)($question['weight'] ?? 1),
        'options' => []
    ];

    // Build options from option_a, option_b, option_c, option_d
    $optionLabels = ['A', 'B', 'C', 'D'];
    foreach ($optionLabels as $label) {
        $optionKey = 'option_' . strtolower($label);
        if (!empty($question[$optionKey])) {
            $formatted['options'][] = [
                'label' => $label,
                'text' => $question[$optionKey]
            ];
        }
    }

    // Also include options from answer_options table for backward compatibility
    if (isset($question['options']) && is_array($question['options'])) {
        foreach ($question['options'] as $opt) {
            $formatted['options'][] = [
                'id' => (int)$opt['id'],
                'option_text' => $opt['option_text'],
                'it_score' => floatval($opt['it_score'] ?? 0),
                'cs_score' => floatval($opt['cs_score'] ?? 0),
                'is_score' => floatval($opt['is_score'] ?? 0)
            ];
        }
    }

    return $formatted;
}

$conn->close();

