<?php
session_start();
include '../../database/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get all questions or single question
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $stmt = $conn->prepare("SELECT id, question_text, question_type, category, difficulty, weight, correct_option, option_a, option_b, option_c, option_d, order_number, is_active FROM questions WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $question = $result->fetch_assoc();
            
            if ($question) {
                // Get answer options for backward compatibility
                $stmt2 = $conn->prepare("SELECT * FROM answer_options WHERE question_id = ? ORDER BY id");
                $stmt2->bind_param("i", $id);
                $stmt2->execute();
                $options = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
                $question['options'] = $options;
            }
            
            echo json_encode($question ? $question : ['error' => 'Question not found']);
        } else {
            $result = $conn->query("SELECT id, question_text, question_type, category, difficulty, weight, correct_option, option_a, option_b, option_c, option_d, order_number, is_active FROM questions ORDER BY category, order_number, id");
            $questions = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode($questions);
        }
        break;
        
    case 'POST':
        // Create new question
        $data = json_decode(file_get_contents('php://input'), true);
        $question_text = $conn->real_escape_string($data['question_text']);
        $question_type = $data['question_type'] ?? 'single';
        $category = $data['category'] ?? 'DIAGNOSTIC';
        $difficulty = $data['difficulty'] ?? 'MEDIUM';
        $weight = intval($data['weight'] ?? 1);
        $correct_option = $data['correct_option'] ?? null;
        $option_a = isset($data['option_a']) ? $conn->real_escape_string($data['option_a']) : null;
        $option_b = isset($data['option_b']) ? $conn->real_escape_string($data['option_b']) : null;
        $option_c = isset($data['option_c']) ? $conn->real_escape_string($data['option_c']) : null;
        $option_d = isset($data['option_d']) ? $conn->real_escape_string($data['option_d']) : null;
        $order_number = intval($data['order_number'] ?? 0);
        
        $stmt = $conn->prepare("INSERT INTO questions (question_text, question_type, category, difficulty, weight, correct_option, option_a, option_b, option_c, option_d, order_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssisssssi", $question_text, $question_type, $category, $difficulty, $weight, $correct_option, $option_a, $option_b, $option_c, $option_d, $order_number);
        
        if ($stmt->execute()) {
            $question_id = $stmt->insert_id;
            
            // Insert answer options
            if (isset($data['options']) && is_array($data['options'])) {
                $stmt2 = $conn->prepare("INSERT INTO answer_options (question_id, option_text, it_score, cs_score, is_score) VALUES (?, ?, ?, ?, ?)");
                foreach ($data['options'] as $option) {
                    $option_text = mysqli_real_escape_string($conn, $option['option_text']);
                    $it_score = floatval($option['it_score'] ?? 0);
                    $cs_score = floatval($option['cs_score'] ?? 0);
                    $is_score = floatval($option['is_score'] ?? 0);
                    $stmt2->bind_param("isddd", $question_id, $option_text, $it_score, $cs_score, $is_score);
                    $stmt2->execute();
                }
                $stmt2->close();
            }
            
            echo json_encode(['success' => true, 'id' => $question_id]);
        } else {
            echo json_encode(['error' => 'Failed to create question']);
        }
        $stmt->close();
        break;
        
    case 'PUT':
        // Update question
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id']);
        $question_text = $conn->real_escape_string($data['question_text']);
        $question_type = $data['question_type'] ?? 'single';
        $category = $data['category'] ?? 'DIAGNOSTIC';
        $difficulty = $data['difficulty'] ?? 'MEDIUM';
        $weight = intval($data['weight'] ?? 1);
        $correct_option = $data['correct_option'] ?? null;
        $option_a = isset($data['option_a']) ? $conn->real_escape_string($data['option_a']) : null;
        $option_b = isset($data['option_b']) ? $conn->real_escape_string($data['option_b']) : null;
        $option_c = isset($data['option_c']) ? $conn->real_escape_string($data['option_c']) : null;
        $option_d = isset($data['option_d']) ? $conn->real_escape_string($data['option_d']) : null;
        $order_number = intval($data['order_number'] ?? 0);
        $is_active = intval($data['is_active'] ?? 1);
        
        $stmt = $conn->prepare("UPDATE questions SET question_text = ?, question_type = ?, category = ?, difficulty = ?, weight = ?, correct_option = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, order_number = ?, is_active = ? WHERE id = ?");
        $stmt->bind_param("ssssissssssii", $question_text, $question_type, $category, $difficulty, $weight, $correct_option, $option_a, $option_b, $option_c, $option_d, $order_number, $is_active, $id);
        
        if ($stmt->execute()) {
            // Update or insert answer options
            if (isset($data['options']) && is_array($data['options'])) {
                // Delete existing options
                $stmt2 = $conn->prepare("DELETE FROM answer_options WHERE question_id = ?");
                $stmt2->bind_param("i", $id);
                $stmt2->execute();
                $stmt2->close();
                
                // Insert new options
                $stmt3 = $conn->prepare("INSERT INTO answer_options (question_id, option_text, it_score, cs_score, is_score) VALUES (?, ?, ?, ?, ?)");
                foreach ($data['options'] as $option) {
                    $option_text = mysqli_real_escape_string($conn, $option['option_text']);
                    $it_score = floatval($option['it_score'] ?? 0);
                    $cs_score = floatval($option['cs_score'] ?? 0);
                    $is_score = floatval($option['is_score'] ?? 0);
                    $stmt3->bind_param("isddd", $id, $option_text, $it_score, $cs_score, $is_score);
                    $stmt3->execute();
                }
                $stmt3->close();
            }
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Failed to update question']);
        }
        $stmt->close();
        break;
        
    case 'DELETE':
        // Delete question
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id']);
        
        $stmt = $conn->prepare("DELETE FROM questions WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Failed to delete question']);
        }
        $stmt->close();
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
$conn->close();
?>

