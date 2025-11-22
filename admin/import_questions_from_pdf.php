<?php
/**
 * Import Questions from PDF Data
 * This script imports questions from the assessment PDF
 */

include '../database/config.php';

// Questions data from PDF
$questionsData = [
    // DIAGNOSTIC QUESTIONS - Start with these to pre-determine course
    [
        'category' => 'DIAGNOSTIC',
        'topic' => 'Fundamentals',
        'question_text' => 'What is the full meaning of ICT?',
        'question_type' => 'single',
        'order_number' => 1,
        'correct_option' => 'B',
        'options' => [
            ['text' => 'Information and Computer Technology', 'it_score' => 2.0, 'cs_score' => 1.0, 'is_score' => 3.0],
            ['text' => 'Information and Communication Technology', 'it_score' => 5.0, 'cs_score' => 3.0, 'is_score' => 5.0], // Correct
            ['text' => 'Integrated Computer Training', 'it_score' => 1.0, 'cs_score' => 1.0, 'is_score' => 2.0],
            ['text' => 'Internal Connection Tool', 'it_score' => 1.0, 'cs_score' => 1.0, 'is_score' => 1.0],
        ]
    ],
    [
        'category' => 'DIAGNOSTIC',
        'topic' => 'Fundamentals',
        'question_text' => 'What is a computer?',
        'question_type' => 'single',
        'order_number' => 2,
        'correct_option' => 'A',
        'options' => [
            ['text' => 'A device that stores and processes data', 'it_score' => 3.0, 'cs_score' => 5.0, 'is_score' => 4.0], // Correct
            ['text' => 'A printer', 'it_score' => 1.0, 'cs_score' => 1.0, 'is_score' => 1.0],
            ['text' => 'A network cable', 'it_score' => 1.0, 'cs_score' => 1.0, 'is_score' => 1.0],
            ['text' => 'A type of storage', 'it_score' => 2.0, 'cs_score' => 2.0, 'is_score' => 2.0],
        ]
    ],
    [
        'category' => 'DIAGNOSTIC',
        'topic' => 'Programming',
        'question_text' => 'Which symbol is used to end a statement in C?',
        'question_type' => 'single',
        'order_number' => 3,
        'correct_option' => 'C',
        'options' => [
            ['text' => '.', 'it_score' => 1.0, 'cs_score' => 1.0, 'is_score' => 1.0],
            ['text' => ',', 'it_score' => 1.0, 'cs_score' => 1.0, 'is_score' => 1.0],
            ['text' => ';', 'it_score' => 2.0, 'cs_score' => 5.0, 'is_score' => 3.0], // Correct
            ['text' => ':', 'it_score' => 1.0, 'cs_score' => 1.0, 'is_score' => 1.0],
        ]
    ],
];

// IS Questions - Fundamentals of Computer
$isFundamentals = [
    ['What is the full meaning of ICT?', 'B', 'Information and Communication Technology'],
    ['Which device is used to display output on a screen?', 'B', 'Monitor'],
    ['The main function of a computer\'s CPU is to:', 'B', 'Perform calculations'],
    ['What is the brain of the computer?', 'B', 'CPU'],
    ['Which one is an example of software?', 'B', 'Microsoft Word'],
    ['What does a keyboard help you do?', 'B', 'Type data'],
    ['What is the full form of "WWW"?', 'A', 'World Wide Web'],
    ['Data is processed into:', 'C', 'Information'],
    ['What part of the computer temporarily stores data?', 'B', 'RAM'],
    ['Which is an example of an input device?', 'B', 'Mouse'],
];

// IT Questions - Fundamentals of Computer
$itFundamentals = [
    ['What does "IT" stand for?', 'B', 'Information Technology'],
    ['Which device is used to process data?', 'B', 'CPU'],
    ['Which is a storage device?', 'B', 'Hard Disk'],
    ['What is the function of an operating system?', 'B', 'Controls computer operations'],
    ['What type of device is a keyboard?', 'A', 'Input'],
    ['Which computer generation used microprocessors?', 'C', 'Fourth'],
    ['Which port is used to connect USB devices?', 'B', 'USB'],
    ['Which of these is not a hardware device?', 'C', 'Gmail'],
    ['What is an example of an output device?', 'C', 'Speaker'],
    ['Which one is a system software?', 'A', 'Windows'],
];

// CS Questions - Fundamentals of Computer
$csFundamentals = [
    ['What is a computer?', 'A', 'A device that stores and processes data'],
    ['Which of the following is an example of application software?', 'A', 'MS Word'],
    ['The process of turning data into information is called:', 'A', 'Processing'],
    ['The physical parts of a computer are called:', 'B', 'Hardware'],
    ['What is the smallest unit of data?', 'A', 'Bit'],
    ['The main function of a storage device is to:', 'A', 'Save data'],
    ['Which memory is volatile?', 'B', 'RAM'],
    ['What does GUI stand for?', 'A', 'Graphical User Interface'],
    ['Which device is used for permanent data storage?', 'B', 'Hard Drive'],
    ['Which software helps the computer to start?', 'D', 'BIOS'],
];

// IS Programming Questions
$isProgramming = [
    ['Which symbol is used to end a statement in C?', 'C', ';'],
    ['A set of instructions written for a computer is called a:', 'B', 'Program'],
    ['What is used to store data in a program?', 'A', 'Variable'],
    ['Which of the following is a programming language?', 'B', 'Java'],
    ['Which operator is used for addition?', 'D', '+'],
    ['What does "print()" do in Python?', 'B', 'Displays output'],
    ['A loop is used to:', 'B', 'Repeat actions'],
    ['What symbol is used for comments in Python?', 'B', '#'],
    ['Which of these is used for decision-making?', 'B', 'if statement'],
    ['A programming error is called:', 'A', 'Bug'],
];

// IT Programming Questions
$itProgramming = [
    ['In programming, "syntax" means:', 'A', 'The rules of writing code'],
    ['In Python, which keyword is used to print output?', 'B', 'print()'],
    ['What is a loop used for?', 'B', 'To repeat tasks'],
    ['What does "if" statement do?', 'B', 'Checks conditions'],
    ['In C language, "printf" is used for:', 'B', 'Output'],
    ['Which of these is a programming language?', 'C', 'Python'],
    ['What does HTML stand for?', 'A', 'HyperText Markup Language'],
    ['A group of related statements in a program is called a:', 'C', 'Function'],
    ['What does IDE stand for?', 'A', 'Integrated Development Environment'],
    ['Which of these errors stops a program from running?', 'A', 'Syntax Error'],
];

// CS Programming Questions
$csProgramming = [
    ['What is a variable?', 'A', 'A name used to store data'],
    ['In programming, what is a "loop"?', 'B', 'A repeating structure'],
    ['What does the operator "=" mean?', 'B', 'Assign'],
    ['Which of the following is a valid binary number?', 'A', '1101'],
    ['Which keyword is used to create a function in Python?', 'A', 'def'],
    ['What is debugging?', 'A', 'Fixing errors'],
    ['What is the value can be represented by 2^4?', 'C', '16'],
    ['In a program, "output" refers to:', 'A', 'Displayed result'],
    ['What does "for loop" do?', 'A', 'Repeat code for a range'],
    ['What is a syntax error?', 'A', 'A mistake in code writing'],
];

// IS Hardware Questions
$isHardware = [
    ['What is the main storage device in a computer?', 'B', 'Hard Drive'],
    ['Which device is used to print documents?', 'C', 'Printer'],
    ['What connects all computer parts together?', 'A', 'Motherboard'],
    ['Which hardware is used to hear sounds?', 'B', 'Speaker'],
    ['A device that stores data permanently is:', 'C', 'Hard Disk'],
    ['Which is used to move the cursor on the screen?', 'A', 'Mouse'],
    ['What is the function of RAM?', 'B', 'Temporary storage'],
    ['The power supply converts:', 'A', 'AC to DC'],
    ['What is a USB used for?', 'C', 'Transferring files'],
    ['Which device allows you to enter data by touch?', 'C', 'Touchscreen'],
];

// IT Hardware Questions
$itHardware = [
    ['What does CPU stand for?', 'A', 'Central Processing Unit'],
    ['Which part stores the BIOS?', 'A', 'ROM'],
    ['What does GPU stand for?', 'B', 'Graphics Processing Unit'],
    ['Which device is used to store backups?', 'B', 'Flash Drive'],
    ['What does "bit" represent?', 'A', 'Binary Digit'],
    ['What hardware connects the computer to a network?', 'C', 'Network Card'],
    ['Which hardware is used to read CDs?', 'A', 'DVD Drive'],
    ['Which part of CPU performs calculations?', 'B', 'ALU'],
    ['What is the full form of BIOS?', 'A', 'Basic Input Output System'],
    ['Which connector is used for monitors?', 'A', 'HDMI'],
];

// CS Hardware Questions
$csHardware = [
    ['What is the main circuit board called?', 'B', 'Motherboard'],
    ['What connects the CPU to memory?', 'A', 'System Bus'],
    ['The device that converts digital signals to analog is:', 'A', 'Modem'],
    ['What does SSD stand for?', 'A', 'Solid-State Drive'],
    ['What does the ALU do?', 'B', 'Performs calculations'],
    ['What hardware controls data flow between computer parts?', 'A', 'Control Unit'],
    ['Which port connects external monitors?', 'A', 'HDMI'],
    ['Which device is used for video output?', 'A', 'Projector'],
    ['What type of memory is ROM?', 'C', 'Non-volatile'],
    ['The device used to store data magnetically is:', 'A', 'Hard Disk'],
];

// Function to create question with options
function createQuestion($conn, $category, $topic, $questionText, $questionType, $orderNumber, $options, $correctAnswer) {
    // Insert question
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
    $optionIndex = 0;
    
    foreach ($options as $option) {
        $optionText = $option['text'];
        $itScore = $option['it_score'];
        $csScore = $option['cs_score'];
        $isScore = $option['is_score'];
        
        $optionStmt->bind_param("isddd", $questionId, $optionText, $itScore, $csScore, $isScore);
        $optionStmt->execute();
        
        // Track correct answer (A=0, B=1, C=2, D=3)
        $optionLetter = chr(65 + $optionIndex); // A, B, C, D
        if ($optionLetter == $correctAnswer) {
            $correctOptionId = $optionStmt->insert_id;
        }
        
        $optionIndex++;
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

// Function to generate options with scoring
function generateOptions($correctText, $wrongOptions, $category) {
    $options = [];
    
    // Correct answer gets high score for its category
    $correctScores = [
        'IS' => ['it_score' => 2.0, 'cs_score' => 1.0, 'is_score' => 5.0],
        'IT' => ['it_score' => 5.0, 'cs_score' => 1.0, 'is_score' => 2.0],
        'CS' => ['it_score' => 1.0, 'cs_score' => 5.0, 'is_score' => 2.0],
    ];
    
    $scores = $correctScores[$category];
    $options[] = ['text' => $correctText, 'it_score' => $scores['it_score'], 'cs_score' => $scores['cs_score'], 'is_score' => $scores['is_score']];
    
    // Wrong options get lower scores
    foreach ($wrongOptions as $wrong) {
        $options[] = ['text' => $wrong, 'it_score' => 1.0, 'cs_score' => 1.0, 'is_score' => 1.0];
    }
    
    return $options;
}

// Start transaction
$conn->begin_transaction();

try {
    $orderNum = 1;
    
    // Clear existing questions (optional - comment out if you want to keep existing)
    // $conn->query("DELETE FROM answer_options");
    // $conn->query("DELETE FROM questions");
    
    echo "Starting question import...\n\n";
    
    // Import IS Fundamentals
    echo "Importing IS Fundamentals questions...\n";
    foreach ($isFundamentals as $q) {
        $wrongOptions = ['Option A', 'Option B', 'Option C', 'Option D'];
        $wrongOptions = array_filter($wrongOptions, function($opt) use ($q) {
            return $opt != $q[2]; // Remove correct answer
        });
        $wrongOptions = array_slice($wrongOptions, 0, 3); // Take first 3 wrong options
        
        $options = generateOptions($q[2], $wrongOptions, 'IS');
        // Add actual wrong options from other questions
        $allWrongs = array_merge(
            array_column(array_slice($isFundamentals, 0, 3), 2),
            array_column(array_slice($itFundamentals, 0, 2), 2),
            array_column(array_slice($csFundamentals, 0, 2), 2)
        );
        $wrongOptions = array_diff($allWrongs, [$q[2]]);
        $wrongOptions = array_slice($wrongOptions, 0, 3);
        
        $options = generateOptions($q[2], $wrongOptions, 'IS');
        createQuestion($conn, 'IS', 'Fundamentals of Computer', $q[0], 'single', $orderNum++, $options, $q[1]);
    }
    
    // Import IT Fundamentals
    echo "Importing IT Fundamentals questions...\n";
    foreach ($itFundamentals as $q) {
        $allWrongs = array_merge(
            array_column(array_slice($isFundamentals, 0, 3), 2),
            array_column(array_slice($itFundamentals, 0, 3), 2),
            array_column(array_slice($csFundamentals, 0, 3), 2)
        );
        $wrongOptions = array_diff($allWrongs, [$q[2]]);
        $wrongOptions = array_slice($wrongOptions, 0, 3);
        
        $options = generateOptions($q[2], $wrongOptions, 'IT');
        createQuestion($conn, 'IT', 'Fundamentals of Computer', $q[0], 'single', $orderNum++, $options, $q[1]);
    }
    
    // Import CS Fundamentals
    echo "Importing CS Fundamentals questions...\n";
    foreach ($csFundamentals as $q) {
        $allWrongs = array_merge(
            array_column(array_slice($isFundamentals, 0, 3), 2),
            array_column(array_slice($itFundamentals, 0, 3), 2),
            array_column(array_slice($csFundamentals, 0, 3), 2)
        );
        $wrongOptions = array_diff($allWrongs, [$q[2]]);
        $wrongOptions = array_slice($wrongOptions, 0, 3);
        
        $options = generateOptions($q[2], $wrongOptions, 'CS');
        createQuestion($conn, 'CS', 'Fundamentals of Computer', $q[0], 'single', $orderNum++, $options, $q[1]);
    }
    
    // Import Programming questions (similar pattern)
    echo "Importing Programming questions...\n";
    // IS Programming
    foreach ($isProgramming as $q) {
        $allWrongs = array_merge(
            array_column(array_slice($isProgramming, 0, 3), 2),
            array_column(array_slice($itProgramming, 0, 2), 2),
            array_column(array_slice($csProgramming, 0, 2), 2)
        );
        $wrongOptions = array_diff($allWrongs, [$q[2]]);
        $wrongOptions = array_slice($wrongOptions, 0, 3);
        
        $options = generateOptions($q[2], $wrongOptions, 'IS');
        createQuestion($conn, 'IS', 'Programming', $q[0], 'single', $orderNum++, $options, $q[1]);
    }
    
    // IT Programming
    foreach ($itProgramming as $q) {
        $allWrongs = array_merge(
            array_column(array_slice($isProgramming, 0, 2), 2),
            array_column(array_slice($itProgramming, 0, 3), 2),
            array_column(array_slice($csProgramming, 0, 2), 2)
        );
        $wrongOptions = array_diff($allWrongs, [$q[2]]);
        $wrongOptions = array_slice($wrongOptions, 0, 3);
        
        $options = generateOptions($q[2], $wrongOptions, 'IT');
        createQuestion($conn, 'IT', 'Programming', $q[0], 'single', $orderNum++, $options, $q[1]);
    }
    
    // CS Programming
    foreach ($csProgramming as $q) {
        $allWrongs = array_merge(
            array_column(array_slice($isProgramming, 0, 2), 2),
            array_column(array_slice($itProgramming, 0, 2), 2),
            array_column(array_slice($csProgramming, 0, 3), 2)
        );
        $wrongOptions = array_diff($allWrongs, [$q[2]]);
        $wrongOptions = array_slice($wrongOptions, 0, 3);
        
        $options = generateOptions($q[2], $wrongOptions, 'CS');
        createQuestion($conn, 'CS', 'Programming', $q[0], 'single', $orderNum++, $options, $q[1]);
    }
    
    // Import Hardware questions
    echo "Importing Hardware questions...\n";
    // IS Hardware
    foreach ($isHardware as $q) {
        $allWrongs = array_merge(
            array_column(array_slice($isHardware, 0, 3), 2),
            array_column(array_slice($itHardware, 0, 2), 2),
            array_column(array_slice($csHardware, 0, 2), 2)
        );
        $wrongOptions = array_diff($allWrongs, [$q[2]]);
        $wrongOptions = array_slice($wrongOptions, 0, 3);
        
        $options = generateOptions($q[2], $wrongOptions, 'IS');
        createQuestion($conn, 'IS', 'Computer Hardware', $q[0], 'single', $orderNum++, $options, $q[1]);
    }
    
    // IT Hardware
    foreach ($itHardware as $q) {
        $allWrongs = array_merge(
            array_column(array_slice($isHardware, 0, 2), 2),
            array_column(array_slice($itHardware, 0, 3), 2),
            array_column(array_slice($csHardware, 0, 2), 2)
        );
        $wrongOptions = array_diff($allWrongs, [$q[2]]);
        $wrongOptions = array_slice($wrongOptions, 0, 3);
        
        $options = generateOptions($q[2], $wrongOptions, 'IT');
        createQuestion($conn, 'IT', 'Computer Hardware', $q[0], 'single', $orderNum++, $options, $q[1]);
    }
    
    // CS Hardware
    foreach ($csHardware as $q) {
        $allWrongs = array_merge(
            array_column(array_slice($isHardware, 0, 2), 2),
            array_column(array_slice($itHardware, 0, 2), 2),
            array_column(array_slice($csHardware, 0, 3), 2)
        );
        $wrongOptions = array_diff($allWrongs, [$q[2]]);
        $wrongOptions = array_slice($wrongOptions, 0, 3);
        
        $options = generateOptions($q[2], $wrongOptions, 'CS');
        createQuestion($conn, 'CS', 'Computer Hardware', $q[0], 'single', $orderNum++, $options, $q[1]);
    }
    
    $conn->commit();
    echo "\n\nQuestion import completed successfully!\n";
    echo "Total questions imported: " . ($orderNum - 1) . "\n";
    
} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage() . "\n";
}

$conn->close();
?>

