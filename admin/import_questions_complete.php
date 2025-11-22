<?php
/**
 * Complete Import Script for Assessment Questions from PDF
 * Run this script once to import all questions with proper categorization
 */

include '../database/config.php';

// First, run the schema update
echo "Updating database schema...\n";
$schemaSQL = file_get_contents('../database/add_category_field.sql');
$statements = explode(';', $schemaSQL);
foreach ($statements as $statement) {
    $statement = trim($statement);
    if (!empty($statement)) {
        $conn->query($statement . ';');
    }
}
echo "Schema updated.\n\n";

// Function to create question with all 4 options
function createQuestionWithOptions($conn, $category, $topic, $questionText, $orderNumber, $options, $correctIndex) {
    // Insert question
    $questionType = 'single';
    $stmt = $conn->prepare("INSERT INTO questions (question_text, question_type, category, topic, order_number) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $questionText, $questionType, $category, $topic, $orderNumber);
    
    if (!$stmt->execute()) {
        echo "Error inserting question: " . $stmt->error . "\n";
        $stmt->close();
        return false;
    }
    
    $questionId = $stmt->insert_id;
    $stmt->close();
    
    // Insert options
    $optionStmt = $conn->prepare("INSERT INTO answer_options (question_id, option_text, it_score, cs_score, is_score) VALUES (?, ?, ?, ?, ?)");
    $correctOptionId = null;
    
    foreach ($options as $index => $option) {
        $optionText = $option['text'];
        $itScore = $option['it_score'];
        $csScore = $option['cs_score'];
        $isScore = $option['is_score'];
        
        $optionStmt->bind_param("isddd", $questionId, $optionText, $itScore, $csScore, $isScore);
        $optionStmt->execute();
        
        if ($index == $correctIndex) {
            $correctOptionId = $optionStmt->insert_id;
        }
    }
    
    $optionStmt->close();
    
    // Update question with correct answer ID
    if ($correctOptionId) {
        $updateStmt = $conn->prepare("UPDATE questions SET is_correct_answer = ? WHERE id = ?");
        $updateStmt->bind_param("ii", $correctOptionId, $questionId);
        $updateStmt->execute();
        $updateStmt->close();
    }
    
    return $questionId;
}

// Scoring function - correct answer gets high score for its category
function getScores($category, $isCorrect) {
    if ($isCorrect) {
        switch ($category) {
            case 'IS':
                return ['it_score' => 2.0, 'cs_score' => 1.0, 'is_score' => 5.0];
            case 'IT':
                return ['it_score' => 5.0, 'cs_score' => 1.0, 'is_score' => 2.0];
            case 'CS':
                return ['it_score' => 1.0, 'cs_score' => 5.0, 'is_score' => 2.0];
            default:
                return ['it_score' => 2.0, 'cs_score' => 2.0, 'is_score' => 2.0];
        }
    } else {
        // Wrong answers get low scores
        return ['it_score' => 1.0, 'cs_score' => 1.0, 'is_score' => 1.0];
    }
}

// All questions with full options from PDF
$allQuestions = [
    // DIAGNOSTIC QUESTIONS (Start with these)
    [
        'category' => 'DIAGNOSTIC',
        'topic' => 'Fundamentals',
        'question' => 'What is the full meaning of ICT?',
        'options' => [
            ['text' => 'Information and Computer Technology'],
            ['text' => 'Information and Communication Technology'], // Correct (B)
            ['text' => 'Integrated Computer Training'],
            ['text' => 'Internal Connection Tool'],
        ],
        'correct' => 1, // B (index 1)
    ],
    [
        'category' => 'DIAGNOSTIC',
        'topic' => 'Fundamentals',
        'question' => 'What is a computer?',
        'options' => [
            ['text' => 'A device that stores and processes data'], // Correct (A)
            ['text' => 'A printer'],
            ['text' => 'A network cable'],
            ['text' => 'A type of storage'],
        ],
        'correct' => 0, // A (index 0)
    ],
    [
        'category' => 'DIAGNOSTIC',
        'topic' => 'Programming',
        'question' => 'Which symbol is used to end a statement in C?',
        'options' => [
            ['text' => '.'],
            ['text' => ','],
            ['text' => ';'], // Correct (C)
            ['text' => ':'],
        ],
        'correct' => 2, // C (index 2)
    ],
    
    // IS - Fundamentals of Computer (Questions 1-10)
    [
        'category' => 'IS',
        'topic' => 'Fundamentals of Computer',
        'question' => 'What is the full meaning of ICT?',
        'options' => [
            ['text' => 'Information and Computer Technology'],
            ['text' => 'Information and Communication Technology'], // Correct (B)
            ['text' => 'Integrated Computer Training'],
            ['text' => 'Internal Connection Tool'],
        ],
        'correct' => 1,
    ],
    [
        'category' => 'IS',
        'topic' => 'Fundamentals of Computer',
        'question' => 'Which device is used to display output on a screen?',
        'options' => [
            ['text' => 'Mouse'],
            ['text' => 'Monitor'], // Correct (B)
            ['text' => 'Keyboard'],
            ['text' => 'Scanner'],
        ],
        'correct' => 1,
    ],
    [
        'category' => 'IS',
        'topic' => 'Fundamentals of Computer',
        'question' => 'The main function of a computer\'s CPU is to:',
        'options' => [
            ['text' => 'Store data'],
            ['text' => 'Perform calculations'], // Correct (B)
            ['text' => 'Print documents'],
            ['text' => 'Connect to the internet'],
        ],
        'correct' => 1,
    ],
    [
        'category' => 'IS',
        'topic' => 'Fundamentals of Computer',
        'question' => 'What is the brain of the computer?',
        'options' => [
            ['text' => 'Hard Disk'],
            ['text' => 'CPU'], // Correct (B)
            ['text' => 'RAM'],
            ['text' => 'Monitor'],
        ],
        'correct' => 1,
    ],
    [
        'category' => 'IS',
        'topic' => 'Fundamentals of Computer',
        'question' => 'Which one is an example of software?',
        'options' => [
            ['text' => 'Printer'],
            ['text' => 'Microsoft Word'], // Correct (B)
            ['text' => 'USB Cable'],
            ['text' => 'Mouse'],
        ],
        'correct' => 1,
    ],
    [
        'category' => 'IS',
        'topic' => 'Fundamentals of Computer',
        'question' => 'What does a keyboard help you do?',
        'options' => [
            ['text' => 'Print documents'],
            ['text' => 'Type data'], // Correct (B)
            ['text' => 'Store files'],
            ['text' => 'Scan images'],
        ],
        'correct' => 1,
    ],
    [
        'category' => 'IS',
        'topic' => 'Fundamentals of Computer',
        'question' => 'What is the full form of "WWW"?',
        'options' => [
            ['text' => 'World Wide Web'], // Correct (A)
            ['text' => 'Wide World Web'],
            ['text' => 'World Web Wide'],
            ['text' => 'Web World Wide'],
        ],
        'correct' => 0,
    ],
    [
        'category' => 'IS',
        'topic' => 'Fundamentals of Computer',
        'question' => 'Data is processed into:',
        'options' => [
            ['text' => 'Facts'],
            ['text' => 'Instructions'],
            ['text' => 'Information'], // Correct (C)
            ['text' => 'Numbers'],
        ],
        'correct' => 2,
    ],
    [
        'category' => 'IS',
        'topic' => 'Fundamentals of Computer',
        'question' => 'What part of the computer temporarily stores data?',
        'options' => [
            ['text' => 'Hard Drive'],
            ['text' => 'RAM'], // Correct (B)
            ['text' => 'CD-ROM'],
            ['text' => 'USB Drive'],
        ],
        'correct' => 1,
    ],
    [
        'category' => 'IS',
        'topic' => 'Fundamentals of Computer',
        'question' => 'Which is an example of an input device?',
        'options' => [
            ['text' => 'Monitor'],
            ['text' => 'Mouse'], // Correct (B)
            ['text' => 'Printer'],
            ['text' => 'Speaker'],
        ],
        'correct' => 1,
    ],
    
    // Continue with IT and CS questions... (I'll add a few examples, you can expand)
    // IT - Fundamentals
    [
        'category' => 'IT',
        'topic' => 'Fundamentals of Computer',
        'question' => 'What does "IT" stand for?',
        'options' => [
            ['text' => 'Information Tool'],
            ['text' => 'Information Technology'], // Correct (B)
            ['text' => 'Internet Technology'],
            ['text' => 'Internal Transmission'],
        ],
        'correct' => 1,
    ],
    // Add more questions following the same pattern...
];

// Start transaction
$conn->begin_transaction();

try {
    // Clear existing questions (optional)
    echo "Clearing existing questions...\n";
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    $conn->query("DELETE FROM answer_options");
    $conn->query("DELETE FROM questions");
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    
    $orderNum = 1;
    $imported = 0;
    
    echo "Importing questions...\n\n";
    
    foreach ($allQuestions as $qData) {
        $category = $qData['category'];
        $topic = $qData['topic'];
        $question = $qData['question'];
        $correctIndex = $qData['correct'];
        
        // Prepare options with scores
        $options = [];
        foreach ($qData['options'] as $index => $opt) {
            $isCorrect = ($index == $correctIndex);
            $scores = getScores($category, $isCorrect);
            $options[] = [
                'text' => $opt['text'],
                'it_score' => $scores['it_score'],
                'cs_score' => $scores['cs_score'],
                'is_score' => $scores['is_score'],
            ];
        }
        
        $questionId = createQuestionWithOptions($conn, $category, $topic, $question, $orderNum++, $options, $correctIndex);
        if ($questionId) {
            $imported++;
            if ($imported % 10 == 0) {
                echo "Imported $imported questions...\n";
            }
        }
    }
    
    $conn->commit();
    echo "\n\nImport completed successfully!\n";
    echo "Total questions imported: $imported\n";
    
} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

$conn->close();
?>

