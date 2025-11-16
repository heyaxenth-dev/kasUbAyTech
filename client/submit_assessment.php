<?php
include '../database/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $client_id = intval($_POST['client_id']);
    $answers = json_decode($_POST['answers'], true);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Create assessment result record
        $stmt = $conn->prepare("INSERT INTO assessment_results (client_id, total_questions, answered_questions, completed_at) VALUES (?, ?, ?, NOW())");
        $total_questions = count($answers);
        $answered_count = 0;
        foreach ($answers as $ans) {
            if (!empty($ans['option_ids'])) {
                $answered_count++;
            }
        }
        $stmt->bind_param("iii", $client_id, $total_questions, $answered_count);
        $stmt->execute();
        $result_id = $stmt->insert_id;
        $stmt->close();
        
        // Calculate scores
        $it_total = 0;
        $cs_total = 0;
        $is_total = 0;
        $total_weight = 0;
        
        // Insert student answers and calculate scores
        $stmt2 = $conn->prepare("INSERT INTO student_answers (result_id, question_id, option_id) VALUES (?, ?, ?)");
        
        foreach ($answers as $answer) {
            $question_id = intval($answer['question_id']);
            $option_ids = is_array($answer['option_ids']) ? $answer['option_ids'] : [$answer['option_ids']];
            
            foreach ($option_ids as $option_id) {
                if (!empty($option_id)) {
                    $option_id = intval($option_id);
                    $stmt2->bind_param("iii", $result_id, $question_id, $option_id);
                    $stmt2->execute();
                    
                    // Get option scores
                    $stmt3 = $conn->prepare("SELECT it_score, cs_score, is_score FROM answer_options WHERE id = ?");
                    $stmt3->bind_param("i", $option_id);
                    $stmt3->execute();
                    $score_result = $stmt3->get_result();
                    if ($score_row = $score_result->fetch_assoc()) {
                        $it_total += floatval($score_row['it_score']);
                        $cs_total += floatval($score_row['cs_score']);
                        $is_total += floatval($score_row['is_score']);
                        $total_weight++;
                    }
                    $stmt3->close();
                }
            }
        }
        $stmt2->close();
        
        // Calculate average scores
        $it_score = $total_weight > 0 ? ($it_total / $total_weight) * 20 : 0; // Scale to 0-100
        $cs_score = $total_weight > 0 ? ($cs_total / $total_weight) * 20 : 0;
        $is_score = $total_weight > 0 ? ($is_total / $total_weight) * 20 : 0;
        
        // Determine recommended course
        $recommended = 'IT';
        if ($cs_score > $it_score && $cs_score > $is_score) {
            $recommended = 'CS';
        } elseif ($is_score > $it_score && $is_score > $cs_score) {
            $recommended = 'IS';
        }
        
        // Insert compatibility scores
        $stmt4 = $conn->prepare("INSERT INTO compatibility_scores (result_id, it_score, cs_score, is_score, recommended_course) VALUES (?, ?, ?, ?, ?)");
        $stmt4->bind_param("iddds", $result_id, $it_score, $cs_score, $is_score, $recommended);
        $stmt4->execute();
        $stmt4->close();
        
        // Commit transaction
        $conn->commit();
        
        // Return success with scores
        echo json_encode([
            'success' => true,
            'result_id' => $result_id,
            'scores' => [
                'IT' => round($it_score, 2),
                'CS' => round($cs_score, 2),
                'IS' => round($is_score, 2)
            ],
            'recommended' => $recommended
        ]);
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}

$conn->close();
?>

