<?php
/**
 * Import Questions from PDF - Final Version
 * 
 * Imports all questions from the PDF with proper:
 * - Category (IS, IT, CS, DIAGNOSTIC)
 * - Difficulty (EASY, MEDIUM, HARD)
 * - Weight (1-3 based on difficulty)
 * - Correct option (A, B, C, D)
 * - Option A, B, C, D fields
 * - No duplicate choices
 */

include '../database/config.php';

// Function to determine difficulty based on question content
function determineDifficulty($questionText, $topic) {
    $questionLower = strtolower($questionText);
    
    // EASY: Basic definitions, simple concepts
    $easyKeywords = ['what is', 'which device', 'what does', 'what is the full', 'what part', 'which one is', 'which is'];
    
    // MEDIUM: Application, understanding concepts
    $mediumKeywords = ['function', 'process', 'used to', 'helps', 'allows', 'connects', 'converts', 'stands for'];
    
    // HARD: Technical details, specific knowledge
    $hardKeywords = ['generation', 'port', 'bus', 'alu', 'bios', 'gui', 'ssd', 'gpu', 'syntax', 'debugging', 'binary', 'variable', 'loop', 'function', 'operator'];
    
    foreach ($hardKeywords as $keyword) {
        if (strpos($questionLower, $keyword) !== false) {
            return 'HARD';
        }
    }
    
    foreach ($mediumKeywords as $keyword) {
        if (strpos($questionLower, $keyword) !== false) {
            return 'MEDIUM';
        }
    }
    
    return 'EASY';
}

// Function to determine weight based on difficulty
function getWeight($difficulty) {
    switch ($difficulty) {
        case 'HARD':
            return 3;
        case 'MEDIUM':
            return 2;
        case 'EASY':
        default:
            return 1;
    }
}

// All questions from PDF
$allQuestions = [
    // DIAGNOSTIC QUESTIONS (First 3)
    [
        'category' => 'DIAGNOSTIC',
        'topic' => 'Fundamentals',
        'question' => 'What is the full meaning of ICT?',
        'options' => [
            'A' => 'Information and Computer Technology',
            'B' => 'Information and Communication Technology',
            'C' => 'Integrated Computer Training',
            'D' => 'Internal Connection Tool'
        ],
        'correct' => 'B'
    ],
    [
        'category' => 'DIAGNOSTIC',
        'topic' => 'Fundamentals',
        'question' => 'What is a computer?',
        'options' => [
            'A' => 'A device that stores and processes data',
            'B' => 'A printer',
            'C' => 'A network cable',
            'D' => 'A type of storage'
        ],
        'correct' => 'A'
    ],
    [
        'category' => 'DIAGNOSTIC',
        'topic' => 'Fundamentals',
        'question' => 'Which of the following is an example of application software?',
        'options' => [
            'A' => 'MS Word',
            'B' => 'BIOS',
            'C' => 'RAM',
            'D' => 'ROM'
        ],
        'correct' => 'A'
    ],
    
    // INFORMATION SYSTEM (IS) - Fundamentals (1-10)
    [
        'category' => 'IS',
        'topic' => 'Fundamentals of Computer',
        'question' => 'What is the full meaning of ICT?',
        'options' => [
            'A' => 'Information and Computer Technology',
            'B' => 'Information and Communication Technology',
            'C' => 'Integrated Computer Training',
            'D' => 'Internal Connection Tool'
        ],
        'correct' => 'B'
    ],
    [
        'category' => 'IS',
        'topic' => 'Fundamentals of Computer',
        'question' => 'Which device is used to display output on a screen?',
        'options' => [
            'A' => 'Mouse',
            'B' => 'Monitor',
            'C' => 'Keyboard',
            'D' => 'Scanner'
        ],
        'correct' => 'B'
    ],
    [
        'category' => 'IS',
        'topic' => 'Fundamentals of Computer',
        'question' => 'The main function of a computer\'s CPU is to:',
        'options' => [
            'A' => 'Store data',
            'B' => 'Perform calculations',
            'C' => 'Print documents',
            'D' => 'Connect to the internet'
        ],
        'correct' => 'B'
    ],
    [
        'category' => 'IS',
        'topic' => 'Fundamentals of Computer',
        'question' => 'What is the brain of the computer?',
        'options' => [
            'A' => 'Hard Disk',
            'B' => 'CPU',
            'C' => 'RAM',
            'D' => 'Monitor'
        ],
        'correct' => 'B'
    ],
    [
        'category' => 'IS',
        'topic' => 'Fundamentals of Computer',
        'question' => 'Which one is an example of software?',
        'options' => [
            'A' => 'Printer',
            'B' => 'Microsoft Word',
            'C' => 'USB Cable',
            'D' => 'Mouse'
        ],
        'correct' => 'B'
    ],
    [
        'category' => 'IS',
        'topic' => 'Fundamentals of Computer',
        'question' => 'What does a keyboard help you do?',
        'options' => [
            'A' => 'Print documents',
            'B' => 'Type data',
            'C' => 'Store files',
            'D' => 'Scan images'
        ],
        'correct' => 'B'
    ],
    [
        'category' => 'IS',
        'topic' => 'Fundamentals of Computer',
        'question' => 'What is the full form of "WWW"?',
        'options' => [
            'A' => 'World Wide Web',
            'B' => 'Wide World Web',
            'C' => 'World Web Wide',
            'D' => 'Web World Wide'
        ],
        'correct' => 'A'
    ],
    [
        'category' => 'IS',
        'topic' => 'Fundamentals of Computer',
        'question' => 'Data is processed into:',
        'options' => [
            'A' => 'Facts',
            'B' => 'Instructions',
            'C' => 'Information',
            'D' => 'Numbers'
        ],
        'correct' => 'C'
    ],
    [
        'category' => 'IS',
        'topic' => 'Fundamentals of Computer',
        'question' => 'What part of the computer temporarily stores data?',
        'options' => [
            'A' => 'Hard Drive',
            'B' => 'RAM',
            'C' => 'CD-ROM',
            'D' => 'USB Drive'
        ],
        'correct' => 'B'
    ],
    [
        'category' => 'IS',
        'topic' => 'Fundamentals of Computer',
        'question' => 'Which is an example of an input device?',
        'options' => [
            'A' => 'Monitor',
            'B' => 'Mouse',
            'C' => 'Printer',
            'D' => 'Speaker'
        ],
        'correct' => 'B'
    ],
    
    // INFORMATION TECHNOLOGY (IT) - Fundamentals (11-20)
    [
        'category' => 'IT',
        'topic' => 'Fundamentals of Computer',
        'question' => 'What does "IT" stand for?',
        'options' => [
            'A' => 'Information Tool',
            'B' => 'Information Technology',
            'C' => 'Internet Technology',
            'D' => 'Internal Transmission'
        ],
        'correct' => 'B'
    ],
    [
        'category' => 'IT',
        'topic' => 'Fundamentals of Computer',
        'question' => 'Which device is used to process data?',
        'options' => [
            'A' => 'Mouse',
            'B' => 'CPU',
            'C' => 'Monitor',
            'D' => 'Keyboard'
        ],
        'correct' => 'B'
    ],
    [
        'category' => 'IT',
        'topic' => 'Fundamentals of Computer',
        'question' => 'Which is a storage device?',
        'options' => [
            'A' => 'Printer',
            'B' => 'Hard Disk',
            'C' => 'Monitor',
            'D' => 'Speaker'
        ],
        'correct' => 'B'
    ],
    [
        'category' => 'IT',
        'topic' => 'Fundamentals of Computer',
        'question' => 'What is the function of an operating system?',
        'options' => [
            'A' => 'Hardware connection',
            'B' => 'Controls computer operations',
            'C' => 'Runs hardware',
            'D' => 'Plays videos'
        ],
        'correct' => 'B'
    ],
    [
        'category' => 'IT',
        'topic' => 'Fundamentals of Computer',
        'question' => 'What type of device is a keyboard?',
        'options' => [
            'A' => 'Input',
            'B' => 'Output',
            'C' => 'Storage',
            'D' => 'Processing'
        ],
        'correct' => 'A'
    ],
    [
        'category' => 'IT',
        'topic' => 'Fundamentals of Computer',
        'question' => 'Which computer generation used microprocessors?',
        'options' => [
            'A' => 'First',
            'B' => 'Second',
            'C' => 'Fourth',
            'D' => 'Fifth'
        ],
        'correct' => 'C'
    ],
    [
        'category' => 'IT',
        'topic' => 'Fundamentals of Computer',
        'question' => 'Which port is used to connect USB devices?',
        'options' => [
            'A' => 'Serial',
            'B' => 'USB',
            'C' => 'HDMI',
            'D' => 'VGA'
        ],
        'correct' => 'B'
    ],
    [
        'category' => 'IT',
        'topic' => 'Fundamentals of Computer',
        'question' => 'Which of these is not a hardware device?',
        'options' => [
            'A' => 'Keyboard',
            'B' => 'Monitor',
            'C' => 'Gmail',
            'D' => 'CPU'
        ],
        'correct' => 'C'
    ],
    [
        'category' => 'IT',
        'topic' => 'Fundamentals of Computer',
        'question' => 'What is an example of an output device?',
        'options' => [
            'A' => 'Mouse',
            'B' => 'Keyboard',
            'C' => 'Speaker',
            'D' => 'Scanner'
        ],
        'correct' => 'C'
    ],
    [
        'category' => 'IT',
        'topic' => 'Fundamentals of Computer',
        'question' => 'Which one is a system software?',
        'options' => [
            'A' => 'Windows',
            'B' => 'Word',
            'C' => 'Excel',
            'D' => 'Photoshop'
        ],
        'correct' => 'A'
    ],
    
    // COMPUTER SCIENCE (CS) - Fundamentals (21-30)
    [
        'category' => 'CS',
        'topic' => 'Fundamentals of Computer',
        'question' => 'What is a computer?',
        'options' => [
            'A' => 'A device that stores and processes data',
            'B' => 'A printer',
            'C' => 'A network cable',
            'D' => 'A type of storage'
        ],
        'correct' => 'A'
    ],
    [
        'category' => 'CS',
        'topic' => 'Fundamentals of Computer',
        'question' => 'Which of the following is an example of application software?',
        'options' => [
            'A' => 'MS Word',
            'B' => 'BIOS',
            'C' => 'RAM',
            'D' => 'ROM'
        ],
        'correct' => 'A'
    ],
    [
        'category' => 'CS',
        'topic' => 'Fundamentals of Computer',
        'question' => 'The process of turning data into information is called:',
        'options' => [
            'A' => 'Processing',
            'B' => 'Input',
            'C' => 'Output',
            'D' => 'Storage'
        ],
        'correct' => 'A'
    ],
    [
        'category' => 'CS',
        'topic' => 'Fundamentals of Computer',
        'question' => 'The physical parts of a computer are called:',
        'options' => [
            'A' => 'Software',
            'B' => 'Hardware',
            'C' => 'Data',
            'D' => 'Output'
        ],
        'correct' => 'B'
    ],
    [
        'category' => 'CS',
        'topic' => 'Fundamentals of Computer',
        'question' => 'What is the smallest unit of data?',
        'options' => [
            'A' => 'Bit',
            'B' => 'Byte',
            'C' => 'Kilobyte',
            'D' => 'Megabyte'
        ],
        'correct' => 'A'
    ],
    [
        'category' => 'CS',
        'topic' => 'Fundamentals of Computer',
        'question' => 'The main function of a storage device is to:',
        'options' => [
            'A' => 'Save data',
            'B' => 'Process data',
            'C' => 'Input data',
            'D' => 'Display data'
        ],
        'correct' => 'A'
    ],
    [
        'category' => 'CS',
        'topic' => 'Fundamentals of Computer',
        'question' => 'Which memory is volatile?',
        'options' => [
            'A' => 'ROM',
            'B' => 'RAM',
            'C' => 'Hard Disk',
            'D' => 'Flash Drive'
        ],
        'correct' => 'B'
    ],
    [
        'category' => 'CS',
        'topic' => 'Fundamentals of Computer',
        'question' => 'What does GUI stand for?',
        'options' => [
            'A' => 'Graphical User Interface',
            'B' => 'General User Interaction',
            'C' => 'Graphical Unit Integration',
            'D' => 'General Utility Input'
        ],
        'correct' => 'A'
    ],
    [
        'category' => 'CS',
        'topic' => 'Fundamentals of Computer',
        'question' => 'Which device is used for permanent data storage?',
        'options' => [
            'A' => 'RAM',
            'B' => 'Hard Drive',
            'C' => 'Cache',
            'D' => 'Register'
        ],
        'correct' => 'B'
    ],
    [
        'category' => 'CS',
        'topic' => 'Fundamentals of Computer',
        'question' => 'Which software helps the computer to start?',
        'options' => [
            'A' => 'Operating System',
            'B' => 'Application Software',
            'C' => 'Utility Software',
            'D' => 'BIOS'
        ],
        'correct' => 'D'
    ],
    
    // Continue with Programming and Hardware questions...
    // Due to length, I'll create a separate function to add remaining questions
];

// Add Programming questions (IS, IT, CS - 30 questions)
$programmingQuestions = [
    // IS Programming (1-10)
    ['IS', 'Programming', 'Which symbol is used to end a statement in C?', ['A' => '.', 'B' => ',', 'C' => ';', 'D' => ':'], 'C'],
    ['IS', 'Programming', 'A set of instructions written for a computer is called a:', ['A' => 'Database', 'B' => 'Program', 'C' => 'File', 'D' => 'Record'], 'B'],
    ['IS', 'Programming', 'What is used to store data in a program?', ['A' => 'Variable', 'B' => 'Function', 'C' => 'Class', 'D' => 'Loop'], 'A'],
    ['IS', 'Programming', 'Which of the following is a programming language?', ['A' => 'HTML', 'B' => 'Java', 'C' => 'Google', 'D' => 'Excel'], 'B'],
    ['IS', 'Programming', 'Which operator is used for addition?', ['A' => '*', 'B' => '/', 'C' => '-', 'D' => '+'], 'D'],
    ['IS', 'Programming', 'What does "print()" do in Python?', ['A' => 'Saves a file', 'B' => 'Displays output', 'C' => 'Reads input', 'D' => 'Exits program'], 'B'],
    ['IS', 'Programming', 'A loop is used to:', ['A' => 'Stop a program', 'B' => 'Repeat actions', 'C' => 'Delete data', 'D' => 'Format output'], 'B'],
    ['IS', 'Programming', 'What symbol is used for comments in Python?', ['A' => '//', 'B' => '#', 'C' => ';', 'D' => '*'], 'B'],
    ['IS', 'Programming', 'Which of these is used for decision-making?', ['A' => 'loop', 'B' => 'if statement', 'C' => 'function', 'D' => 'variable'], 'B'],
    ['IS', 'Programming', 'A programming error is called:', ['A' => 'Bug', 'B' => 'Loop', 'C' => 'Variable', 'D' => 'Output'], 'A'],
    
    // IT Programming (11-20)
    ['IT', 'Programming', 'In programming, "syntax" means:', ['A' => 'The rules of writing code', 'B' => 'The program\'s result', 'C' => 'The memory type', 'D' => 'The variable name'], 'A'],
    ['IT', 'Programming', 'In Python, which keyword is used to print output?', ['A' => 'show()', 'B' => 'print()', 'C' => 'write()', 'D' => 'display()'], 'B'],
    ['IT', 'Programming', 'What is a loop used for?', ['A' => 'To store data', 'B' => 'To repeat tasks', 'C' => 'To stop execution', 'D' => 'To save files'], 'B'],
    ['IT', 'Programming', 'What does "if" statement do?', ['A' => 'Repeats code', 'B' => 'Checks conditions', 'C' => 'Prints output', 'D' => 'Saves data'], 'B'],
    ['IT', 'Programming', 'In C language, "printf" is used for:', ['A' => 'Input', 'B' => 'Output', 'C' => 'Saving', 'D' => 'Calculation'], 'B'],
    ['IT', 'Programming', 'Which of these is a programming language?', ['A' => 'Windows', 'B' => 'Chrome', 'C' => 'Python', 'D' => 'PowerPoint'], 'C'],
    ['IT', 'Programming', 'What does HTML stand for?', ['A' => 'HyperText Markup Language', 'B' => 'HighText Machine Language', 'C' => 'Hyper Transfer Markup Language', 'D' => 'Home Tool Markup Language'], 'A'],
    ['IT', 'Programming', 'A group of related statements in a program is called a:', ['A' => 'Variable', 'B' => 'Loop', 'C' => 'Function', 'D' => 'Output'], 'C'],
    ['IT', 'Programming', 'What does IDE stand for?', ['A' => 'Integrated Development Environment', 'B' => 'Internal Data Editor', 'C' => 'Input Device Emulator', 'D' => 'Integrated Device Encoder'], 'A'],
    ['IT', 'Programming', 'Which of these errors stops a program from running?', ['A' => 'Syntax Error', 'B' => 'Logical Error', 'C' => 'Comment', 'D' => 'Loop'], 'A'],
    
    // CS Programming (21-30)
    ['CS', 'Programming', 'What is a variable?', ['A' => 'A name used to store data', 'B' => 'A command', 'C' => 'A loop', 'D' => 'A bug'], 'A'],
    ['CS', 'Programming', 'In programming, what is a "loop"?', ['A' => 'A function', 'B' => 'A repeating structure', 'C' => 'A condition', 'D' => 'A comment'], 'B'],
    ['CS', 'Programming', 'What does the operator "=" mean?', ['A' => 'Add', 'B' => 'Assign', 'C' => 'Compare', 'D' => 'Subtract'], 'B'],
    ['CS', 'Programming', 'Which of the following is a valid binary number?', ['A' => '1101', 'B' => '1021', 'C' => '1234', 'D' => '2004'], 'A'],
    ['CS', 'Programming', 'Which keyword is used to create a function in Python?', ['A' => 'def', 'B' => 'function', 'C' => 'create', 'D' => 'start'], 'A'],
    ['CS', 'Programming', 'What is debugging?', ['A' => 'Fixing errors', 'B' => 'Creating loops', 'C' => 'Declaring variables', 'D' => 'Writing syntax'], 'A'],
    ['CS', 'Programming', 'What is the value can be represented by 2^4?', ['A' => '8', 'B' => '12', 'C' => '16', 'D' => '32'], 'C'],
    ['CS', 'Programming', 'In a program, "output" refers to:', ['A' => 'Displayed result', 'B' => 'Input data', 'C' => 'Error', 'D' => 'Storage'], 'A'],
    ['CS', 'Programming', 'What does "for loop" do?', ['A' => 'Repeat code for a range', 'B' => 'Stop the program', 'C' => 'Declare variable', 'D' => 'Print one time'], 'A'],
    ['CS', 'Programming', 'What is a syntax error?', ['A' => 'A mistake in code writing', 'B' => 'A correct command', 'C' => 'A math error', 'D' => 'A logic rule'], 'A'],
];

// Add Hardware questions (IS, IT, CS - 30 questions)
$hardwareQuestions = [
    // IS Hardware (1-10)
    ['IS', 'Computer Hardware', 'What is the main storage device in a computer?', ['A' => 'RAM', 'B' => 'Hard Drive', 'C' => 'CD-ROM', 'D' => 'Flash Drive'], 'B'],
    ['IS', 'Computer Hardware', 'Which device is used to print documents?', ['A' => 'Monitor', 'B' => 'Mouse', 'C' => 'Printer', 'D' => 'Keyboard'], 'C'],
    ['IS', 'Computer Hardware', 'What connects all computer parts together?', ['A' => 'Motherboard', 'B' => 'Processor', 'C' => 'Hard Disk', 'D' => 'Power Supply'], 'A'],
    ['IS', 'Computer Hardware', 'Which hardware is used to hear sounds?', ['A' => 'Mouse', 'B' => 'Speaker', 'C' => 'Monitor', 'D' => 'Scanner'], 'B'],
    ['IS', 'Computer Hardware', 'A device that stores data permanently is:', ['A' => 'RAM', 'B' => 'Cache', 'C' => 'Hard Disk', 'D' => 'Register'], 'C'],
    ['IS', 'Computer Hardware', 'Which is used to move the cursor on the screen?', ['A' => 'Mouse', 'B' => 'Monitor', 'C' => 'CPU', 'D' => 'Printer'], 'A'],
    ['IS', 'Computer Hardware', 'What is the function of RAM?', ['A' => 'Permanent storage', 'B' => 'Temporary storage', 'C' => 'Display images', 'D' => 'Print text'], 'B'],
    ['IS', 'Computer Hardware', 'The power supply converts:', ['A' => 'AC to DC', 'B' => 'DC to AC', 'C' => 'Sound to Power', 'D' => 'Data to Information'], 'A'],
    ['IS', 'Computer Hardware', 'What is a USB used for?', ['A' => 'Viewing images', 'B' => 'Typing', 'C' => 'Transferring files', 'D' => 'Printing'], 'C'],
    ['IS', 'Computer Hardware', 'Which device allows you to enter data by touch?', ['A' => 'Mouse', 'B' => 'Keyboard', 'C' => 'Touchscreen', 'D' => 'Scanner'], 'C'],
    
    // IT Hardware (11-20)
    ['IT', 'Computer Hardware', 'What does CPU stand for?', ['A' => 'Central Processing Unit', 'B' => 'Computer Power Unit', 'C' => 'Central Print Unit', 'D' => 'Core Processor Unit'], 'A'],
    ['IT', 'Computer Hardware', 'Which part stores the BIOS?', ['A' => 'ROM', 'B' => 'RAM', 'C' => 'Hard Disk', 'D' => 'Cache'], 'A'],
    ['IT', 'Computer Hardware', 'What does GPU stand for?', ['A' => 'General Power Unit', 'B' => 'Graphics Processing Unit', 'C' => 'Graphic Print Utility', 'D' => 'General Processing Unit'], 'B'],
    ['IT', 'Computer Hardware', 'Which device is used to store backups?', ['A' => 'Printer', 'B' => 'Flash Drive', 'C' => 'Monitor', 'D' => 'Keyboard'], 'B'],
    ['IT', 'Computer Hardware', 'What does "bit" represent?', ['A' => 'Binary Digit', 'B' => 'Basic Instruction Table', 'C' => 'Bit Information Type', 'D' => 'Byte in Transfer'], 'A'],
    ['IT', 'Computer Hardware', 'What hardware connects the computer to a network?', ['A' => 'Router', 'B' => 'Switch', 'C' => 'Network Card', 'D' => 'CPU'], 'C'],
    ['IT', 'Computer Hardware', 'Which hardware is used to read CDs?', ['A' => 'DVD Drive', 'B' => 'ROM', 'C' => 'RAM', 'D' => 'SSD'], 'A'],
    ['IT', 'Computer Hardware', 'Which part of CPU performs calculations?', ['A' => 'Control Unit', 'B' => 'ALU', 'C' => 'Memory', 'D' => 'Input Unit'], 'B'],
    ['IT', 'Computer Hardware', 'What is the full form of BIOS?', ['A' => 'Basic Input Output System', 'B' => 'Binary Input Output Setup', 'C' => 'Basic Internal Operation System', 'D' => 'Built-in Operating System'], 'A'],
    ['IT', 'Computer Hardware', 'Which connector is used for monitors?', ['A' => 'HDMI', 'B' => 'RJ45', 'C' => 'USB', 'D' => 'PS/2'], 'A'],
    
    // CS Hardware (21-30)
    ['CS', 'Computer Hardware', 'What is the main circuit board called?', ['A' => 'CPU', 'B' => 'Motherboard', 'C' => 'RAM', 'D' => 'ROM'], 'B'],
    ['CS', 'Computer Hardware', 'What connects the CPU to memory?', ['A' => 'System Bus', 'B' => 'HDMI Cable', 'C' => 'Hard Disk', 'D' => 'Power Supply'], 'A'],
    ['CS', 'Computer Hardware', 'The device that converts digital signals to analog is:', ['A' => 'Modem', 'B' => 'Router', 'C' => 'Switch', 'D' => 'Printer'], 'A'],
    ['CS', 'Computer Hardware', 'What does SSD stand for?', ['A' => 'Solid-State Drive', 'B' => 'System Storage Device', 'C' => 'Serial Storage Disk', 'D' => 'Standard State Drive'], 'A'],
    ['CS', 'Computer Hardware', 'What does the ALU do?', ['A' => 'Controls input', 'B' => 'Performs calculations', 'C' => 'Stores data', 'D' => 'Displays output'], 'B'],
    ['CS', 'Computer Hardware', 'What hardware controls data flow between computer parts?', ['A' => 'Control Unit', 'B' => 'Keyboard', 'C' => 'Mouse', 'D' => 'Router'], 'A'],
    ['CS', 'Computer Hardware', 'Which port connects external monitors?', ['A' => 'HDMI', 'B' => 'RJ45', 'C' => 'USB', 'D' => 'PS/2'], 'A'],
    ['CS', 'Computer Hardware', 'Which device is used for video output?', ['A' => 'Projector', 'B' => 'Speaker', 'C' => 'Microphone', 'D' => 'Keyboard'], 'A'],
    ['CS', 'Computer Hardware', 'What type of memory is ROM?', ['A' => 'Volatile', 'B' => 'Temporary', 'C' => 'Non-volatile', 'D' => 'Random'], 'C'],
    ['CS', 'Computer Hardware', 'The device used to store data magnetically is:', ['A' => 'Hard Disk', 'B' => 'CD-ROM', 'C' => 'USB', 'D' => 'RAM'], 'A'],
];

// Add all programming and hardware questions to main array
foreach ($programmingQuestions as $q) {
    $allQuestions[] = [
        'category' => $q[0],
        'topic' => $q[1],
        'question' => $q[2],
        'options' => $q[3],
        'correct' => $q[4]
    ];
}

foreach ($hardwareQuestions as $q) {
    $allQuestions[] = [
        'category' => $q[0],
        'topic' => $q[1],
        'question' => $q[2],
        'options' => $q[3],
        'correct' => $q[4]
    ];
}

// Function to insert question
function insertQuestion($conn, $qData, $orderNum) {
    $category = $qData['category'];
    $topic = $qData['topic'] ?? '';
    $questionText = $qData['question'];
    $options = $qData['options'];
    $correct = $qData['correct'];
    
    // Determine difficulty and weight
    $difficulty = determineDifficulty($questionText, $topic);
    $weight = getWeight($difficulty);
    
    // Get option values
    $optionA = $options['A'] ?? '';
    $optionB = $options['B'] ?? '';
    $optionC = $options['C'] ?? '';
    $optionD = $options['D'] ?? '';
    
    // Insert question
    $stmt = $conn->prepare("INSERT INTO questions (question_text, question_type, category, difficulty, weight, correct_option, option_a, option_b, option_c, option_d, order_number, is_active) VALUES (?, 'single', ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("sssisssssi", $questionText, $category, $difficulty, $weight, $correct, $optionA, $optionB, $optionC, $optionD, $orderNum);
    
    if (!$stmt->execute()) {
        echo "Error: " . $stmt->error . "\n";
        $stmt->close();
        return false;
    }
    
    $questionId = $stmt->insert_id;
    $stmt->close();
    
    // Also create answer_options for backward compatibility with scoring
    $scores = [
        'IS' => ['is_score' => 5.0, 'it_score' => 2.0, 'cs_score' => 1.0],
        'IT' => ['it_score' => 5.0, 'is_score' => 2.0, 'cs_score' => 1.0],
        'CS' => ['cs_score' => 5.0, 'it_score' => 1.0, 'is_score' => 2.0],
        'DIAGNOSTIC' => ['is_score' => 3.0, 'it_score' => 3.0, 'cs_score' => 3.0]
    ];
    
    $catScores = $scores[$category] ?? $scores['DIAGNOSTIC'];
    
    $optionStmt = $conn->prepare("INSERT INTO answer_options (question_id, option_text, it_score, cs_score, is_score) VALUES (?, ?, ?, ?, ?)");
    $optionLabels = ['A', 'B', 'C', 'D'];
    
    foreach ($optionLabels as $label) {
        if (isset($options[$label]) && !empty($options[$label])) {
            $isCorrect = ($label == $correct);
            $itScore = $isCorrect ? $catScores['it_score'] : 1.0;
            $csScore = $isCorrect ? $catScores['cs_score'] : 1.0;
            $isScore = $isCorrect ? $catScores['is_score'] : 1.0;
            
            $optionStmt->bind_param("isddd", $questionId, $options[$label], $itScore, $csScore, $isScore);
            $optionStmt->execute();
        }
    }
    
    $optionStmt->close();
    return $questionId;
}

// Start import
echo "<h2>Importing Questions from PDF</h2>\n";
echo "<pre>\n";

$conn->begin_transaction();

try {
    // Clear existing questions (optional - comment out if you want to keep existing)
    echo "Clearing existing questions...\n";
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    $conn->query("DELETE FROM answer_options");
    $conn->query("DELETE FROM questions");
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    echo "Cleared.\n\n";
    
    $orderNum = 1;
    $imported = 0;
    
    foreach ($allQuestions as $qData) {
        if (insertQuestion($conn, $qData, $orderNum)) {
            $imported++;
            echo "✓ Imported: [{$qData['category']}] {$qData['question']}\n";
        } else {
            echo "✗ Failed: {$qData['question']}\n";
        }
        $orderNum++;
    }
    
    $conn->commit();
    echo "\n\n";
    echo "========================================\n";
    echo "Import Complete!\n";
    echo "Total imported: {$imported} questions\n";
    echo "========================================\n";
    
} catch (Exception $e) {
    $conn->rollback();
    echo "\n\nError: " . $e->getMessage() . "\n";
    echo "Import rolled back.\n";
}

$conn->close();
echo "</pre>\n";
?>

