<?php
/**
 * Submit Assessment Handler
 * 
 * Handles assessment submission using the new exam system
 * Maintains backward compatibility with old format
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/../database/models/ExamRepository.php';
require_once __DIR__ . '/../database/models/QuestionRepository.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

$clientId = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
$answers = isset($_POST['answers']) ? json_decode($_POST['answers'], true) : [];
$isUnfinished = isset($_POST['is_unfinished']) && $_POST['is_unfinished'] == '1';

if (!$clientId || empty($answers)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit();
}

// Initialize repositories
$examRepo = new ExamRepository($conn);
$questionRepo = new QuestionRepository($conn);

// Start transaction
$conn->begin_transaction();

try {
    // Create exam session
    $sessionId = $examRepo->createSession($clientId);
    
    if (!$sessionId) {
        throw new Exception('Failed to create exam session');
    }

    // Process answers and save them
    $totalPoints = 0;
    $correctAnswersCount = 0;
    $categoryScores = ['IS' => 0, 'IT' => 0, 'CS' => 0];
    
    foreach ($answers as $answer) {
        $questionId = intval($answer['question_id'] ?? 0);
        $optionIds = $answer['option_ids'] ?? [];
        
        if (!$questionId || empty($optionIds)) {
            continue;
        }

        // Get question details
        $question = $questionRepo->getQuestionById($questionId);
        if (!$question) {
            continue;
        }

        // CAT-lite: category is now 'DIAGNOSTIC' or 'ADAPTIVE' (exam phase)
        $category = $question['category'] ?? 'DIAGNOSTIC';
        // CAT-lite: course_tag identifies which course (IT/IS/CS)
        $courseTag = $question['course_tag'] ?? 'IT';
        
        // Map option_ids to selected_option (A/B/C/D)
        // For now, we'll use the first option_id and map it to A/B/C/D based on position
        // This is a simplified mapping - in production, you'd want a proper mapping table
        $selectedOption = 'A'; // Default
        
        if (isset($question['options']) && is_array($question['options'])) {
            $firstOptionId = intval($optionIds[0]);
            foreach ($question['options'] as $index => $opt) {
                if (intval($opt['id']) === $firstOptionId) {
                    $selectedOption = ['A', 'B', 'C', 'D'][$index] ?? 'A';
                    break;
                }
            }
        }

        // Check if answer is correct
        $isCorrect = $questionRepo->isAnswerCorrect($questionId, $selectedOption);
        $weight = intval($question['weight'] ?? 1);
        $pointsAwarded = $isCorrect ? $weight : 0;
        $totalPoints += $pointsAwarded;
        
        // Count correct answers for final score
        if ($isCorrect) {
            $correctAnswersCount++;
        }

        // Save answer (course_tag will be automatically retrieved if not provided)
        $examRepo->saveAnswer($sessionId, $questionId, $selectedOption, $category, $isCorrect, $pointsAwarded, $courseTag);
        
        // Track category scores using course_tag (CAT-lite)
        // For diagnostic phase, track by course_tag to determine dominant course
        if ($isCorrect) {
            if (isset($categoryScores[$courseTag])) {
                $categoryScores[$courseTag] += $pointsAwarded;
            }
        }
    }

    // Determine dominant category
    $dominantCategory = 'IT';
    if ($categoryScores['CS'] > $categoryScores['IT'] && $categoryScores['CS'] > $categoryScores['IS']) {
        $dominantCategory = 'CS';
    } elseif ($categoryScores['IS'] > $categoryScores['IT'] && $categoryScores['IS'] > $categoryScores['CS']) {
        $dominantCategory = 'IS';
    }

    // Update session
    $stage = $isUnfinished ? 'CATEGORY' : 'FINISHED';
    $examRepo->updateSession($sessionId, [
        'stage' => $stage,
        'dominant_category' => $dominantCategory
    ]);

    // Calculate final scores using Python service
    $adaptiveServiceUrl = 'http://localhost:5000/calculate_scores';
    $answeredQuestions = [];
    
    foreach ($answers as $answer) {
        $questionId = intval($answer['question_id'] ?? 0);
        $optionIds = $answer['option_ids'] ?? [];
        
        if ($questionId && !empty($optionIds)) {
            $answeredQuestions[] = [
                'question_id' => $questionId,
                'option_ids' => array_map('intval', $optionIds)
            ];
        }
    }

    $scores = ['IT' => 0, 'CS' => 0, 'IS' => 0];
    $recommendedCourse = 'UNDECIDED';
    $confidenceScore = 0.0;

    // Call Python service for scoring
    if (!empty($answeredQuestions)) {
        $ch = curl_init($adaptiveServiceUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['answered_questions' => $answeredQuestions]));
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $scoreData = json_decode($response, true);
            if ($scoreData && isset($scoreData['scores'])) {
                $scores = $scoreData['scores'];
                $recommendedCourse = $scoreData['recommended_course'] ?? 'UNDECIDED';
                
                // Calculate confidence score
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
    }

    // Save exam result
    if ($stage === 'FINISHED') {
        $examRepo->saveResult($sessionId, $recommendedCourse, $correctAnswersCount, $confidenceScore);
    }

    // Commit transaction
    $conn->commit();

    // Return response (maintaining backward compatibility)
    $response = [
        'success' => true,
        'result_id' => $sessionId, // Using session_id as result_id for compatibility
        'session_id' => $sessionId,
        'scores' => [
            'IT' => round($scores['IT'] ?? 0, 2),
            'CS' => round($scores['CS'] ?? 0, 2),
            'IS' => round($scores['IS'] ?? 0, 2)
        ],
        'is_unfinished' => $isUnfinished
    ];

    // Include recommended course if assessment is complete or has enough answers
    if (!$isUnfinished || count($answers) > 0) {
        $response['recommended'] = $recommendedCourse;
    }

    echo json_encode($response);

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    $conn->close();
}
